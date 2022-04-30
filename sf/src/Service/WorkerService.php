<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\Market;
use App\Entity\Opportunity;
use App\Entity\Parameter;
use App\Repository\OrderRepository;
use App\Repository\BalanceRepository;
use App\Repository\ParameterRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Twig\Environment;

class WorkerService
{
    private ContainerBagInterface $containerBag;
    private ParameterRepository $parameterRepository;
    private CcxtService $ccxtService;
    private BalanceRepository $balanceRepository;
    private OrderRepository $orderRepository;
    private OrderService $orderService;
    private ManagerRegistry $doctrine;
    private OpportunityService $opportunityService;
    private NodeService $nodeService;
    private HubInterface $hub;
    private Environment $twig;
    private HttpClientInterface $client;
    private string $logs;
    private string $startTime;
    private array $buyMarketBalances;
    private array $sellMarketBalances;
    private ?array $buyMarketOrderBook;
    private ?array $sellMarketOrderBook;
    private ?array $buyOrder;
    private ?array $sellOrder;


    public function __construct(ContainerBagInterface $containerBag, ParameterRepository $parameterRepository, CcxtService $ccxtService, BalanceRepository $balanceRepository, OrderRepository $orderRepository, OrderService $orderService, ManagerRegistry $doctrine, OpportunityService $opportunityService, NodeService $nodeService, HubInterface $hub, Environment $twig, HttpClientInterface $client)
    {
        $this->containerBag = $containerBag;
        $this->parameterRepository = $parameterRepository;
        $this->ccxtService = $ccxtService;
        $this->balanceRepository = $balanceRepository;
        $this->orderRepository = $orderRepository;
        $this->orderService = $orderService;
        $this->doctrine = $doctrine;
        $this->opportunityService = $opportunityService;
        $this->nodeService = $nodeService;
        $this->hub = $hub;
        $this->twig = $twig;
        $this->client = $client;
        $this->logs = '=================== Worker start ===================' . PHP_EOL;
        $this->startTime = microtime(true);
        $this->buyOrder = null;
        $this->sellOrder = null;
    }


    /**
     * @throws Exception
     */
    public function execute(Opportunity $opportunity): Opportunity
    {
        $parameter = $this->parameterRepository->findFirst();
        if (!$parameter instanceof Parameter) {
            $this->trace('ERROR: Undefined parameters');
            return $this->exit($opportunity);
        }

        $notSendOrder = $parameter->getWorkerNotSendOrder();
        $stopFirstTransaction = $parameter->getWorkerStopAfterTransaction();
        $priceDiff = $parameter->getWorkerOrderDiff();
        $orderSize = $parameter->getWorkerOrderSize();
        $ticker = $opportunity->getTicker();
        $buyMarket = $opportunity->getBuyMarket();
        $sellMarket = $opportunity->getSellMarket();

        if (!$this->checkPriceDiff($opportunity, $priceDiff))
            return $this->exit($opportunity);

        if (!$this->checkOrderSize($opportunity, $orderSize))
            return $this->exit($opportunity);

        if (!$this->getBalances($buyMarket, $sellMarket, $ticker))
            return $this->exit($opportunity);

        if (!$this->fetchOrderBooks($buyMarket, $sellMarket, $ticker))
            return $this->exit($opportunity);

        // Direction
        $this->printExecTime();
        switch ($opportunity->getDirection()) {
            case 'Buy->Sell':
                $this->trace('OK: direction ' . $opportunity->getDirection());
                if (!$this->checkBuyMarketConditions($opportunity, $orderSize))
                    return $this->exit($opportunity);
                if (!$this->checkSellMarketConditions($opportunity, $orderSize))
                    return $this->exit($opportunity);
                if ($notSendOrder) {
                    $this->trace('TEST_MODE: Not send orders ' . $opportunity->getDirection());
                    $this->printExecTime();
                    $this->updateBalances();
                    break;
                }
                if (!$this->sendBuyOrder($buyMarket, $ticker, $orderSize))
                    return $this->exit($opportunity);
                if (!$this->sendSellOrder($sellMarket, $ticker, $orderSize))
                    return $this->exit($opportunity);
                if (!$this->validateOrder($buyMarket, $ticker, $this->buyOrder)) 
                    $this->cancelOrder($buyMarket, $ticker, $this->buyOrder);
                if (!$this->validateOrder($sellMarket, $ticker, $this->sellOrder)) 
                    $this->cancelOrder($sellMarket, $ticker, $this->sellOrder);
                break;

            case 'Sell->Buy':
                $this->trace('OK: direction ' . $opportunity->getDirection());
                if (!$this->checkSellMarketConditions($opportunity, $orderSize))
                    return $this->exit($opportunity);
                if (!$this->checkBuyMarketConditions($opportunity, $orderSize))
                    return $this->exit($opportunity);
                if ($notSendOrder) {
                    $this->trace('TEST_MODE: Not send orders ' . $opportunity->getDirection());
                    break;
                }
                if (!$this->sendSellOrder($sellMarket, $ticker, $orderSize))
                    return $this->exit($opportunity);
                if (!$this->sendBuyOrder($buyMarket, $ticker, $orderSize))
                    return $this->exit($opportunity);
                if (!$this->validateOrder($sellMarket, $ticker, $this->sellOrder))
                    $this->cancelOrder($sellMarket, $ticker, $this->sellOrder);
                if (!$this->validateOrder($buyMarket, $ticker, $this->buyOrder))
                    $this->cancelOrder($buyMarket, $ticker, $this->buyOrder);
                break;

            default:
                $this->trace('ERROR: invalid direction');
        }

        if ($stopFirstTransaction) {
            $this->nodeService->command('stop');
            $this->hub->publish(new Update(
                'notification',
                $this->twig->render('broadcast/Notification.stream.html.twig', [
                    'type' => 'info',
                    'message' => 'Node Server stoppé après première transaction'
                ])
            ));
            $this->trace('TEST_MODE: Node Server stopped after 1st transaction');
        }

        // Update Balances
        $this->printExecTime();
        $this->updateBalances();

        return $this->exit($opportunity);
    }

    private function trace(string $message): string
    {
        $this->logs = !empty($message) ? $this->logs . $message . PHP_EOL : $this->logs;
        return $this->logs;
    }

    private function printExecTime(): string
    {
        $timeElapsedMs = intval((microtime(true) - $this->startTime) * 1000);
        return $this->trace('***************** ' . $timeElapsedMs . 'ms elapsed ******************');
    }

    private function exit(Opportunity $opportunity): Opportunity
    {
        $timeElapsedMs = intval((microtime(true) - $this->startTime) * 1000);
        $this->trace('=============== Worker end in ' . $timeElapsedMs . 'ms ================');
        $opportunity->setBuyOrder($this->buyOrder ? $this->orderRepository->find($this->buyOrder['orderId']) : null);
        $opportunity->setSellOrder($this->sellOrder ? $this->orderRepository->find($this->sellOrder['orderId']) : null);
        $opportunity->setLogs($this->logs);
        return $this->opportunityService->createOpportunity($opportunity);
    }


    private function checkPriceDiff(Opportunity $opportunity, float $priceDiff): bool
    {
        if (floatval($opportunity->getPriceDiff()) < $priceDiff) {
            $this->trace('ERROR: priceDiff < ' . $priceDiff);
            return false;
        }
        $this->trace('OK: priceDiff > ' . $priceDiff);
        return true;
    }


    private function checkOrderSize(Opportunity $opportunity, int $orderSize): bool
    {
        if (floatval($opportunity->getSize()) < $orderSize) {
            $this->trace('ERROR: orderSize < ' . $orderSize);
            return false;
        }
        $this->trace('OK: orderSize > ' . $orderSize);
        return true;
    }


    private function getBalances(Market $buyMarket, Market $sellMarket, string $ticker): bool
    {
        $this->buyMarketBalances = $this->balanceRepository->findMarketBalancesForTicker($buyMarket, $ticker);
        if (empty($this->buyMarketBalances)) {
            $this->trace('ERROR: get buyMarketBalances');
            return false;
        }
        $this->trace('OK: get buyMarketBalances');
        foreach ($this->buyMarketBalances as $balance) {
            $this->trace('==> Balance ' . $balance);
        }
        $this->sellMarketBalances = $this->balanceRepository->findMarketBalancesForTicker($sellMarket, $ticker);
        if (empty($this->sellMarketBalances)) {
            $this->trace('ERROR: get sellMarketBalances');
            return false;
        }
        $this->trace('OK: get sellMarketBalances');
        foreach ($this->sellMarketBalances as $balance) {
            $this->trace('==> Balance ' . $balance);
        }
        return true;
    }


    private function updateBalances(): void
    {
        $this->trace('OK: updateBalances');
        foreach ($this->buyMarketBalances as $balance) {
            $balance = $this->ccxtService->fetchAccountBalance($balance);
            $this->doctrine->getManager()->persist($balance);
            $this->trace('==> Balance ' . $balance);
        }

        foreach ($this->sellMarketBalances as $balance) {
            $balance = $this->ccxtService->fetchAccountBalance($balance);
            $this->doctrine->getManager()->persist($balance);
            $this->trace('==> Balance ' . $balance);
        }

        $this->doctrine->getManager()->flush();
    }


    private function fetchOrderBooks(Market $buyMarket, Market $sellMarket, string $ticker): bool
    {
        //$this->buyMarketOrderBook = $this->ccxtService->fetchOrderBook($buyMarket, $ticker);
        $this->buyMarketOrderBook = $this->fetchNodeOb($buyMarket);
        if (!$this->buyMarketOrderBook) {
            $this->trace('ERROR: fetch buyMarketOrderBook');
            return false;
        }
        $this->trace('OK: fetch buyMarketOrderBook');
        $buyMarketOBTrace = '';
        foreach ($this->buyMarketOrderBook as $key => $value) {
            $buyMarketOBTrace .= $key . ': ' . $value . ' | ';
        }
        $this->trace('==> OB Market: ' . strtoupper($buyMarket->getName()) . ' | ' . $buyMarketOBTrace);

        //$this->sellMarketOrderBook = $this->ccxtService->fetchOrderBook($sellMarket, $ticker);
        $this->sellMarketOrderBook = $this->fetchNodeOb($sellMarket);
        if (!$this->sellMarketOrderBook) {
            $this->trace('ERROR: fetch sellMarketOrderBook');
            return false;
        }
        $this->trace('OK: fetch sellMarketOrderBook');
        $sellMarketOBTrace = '';
        foreach ($this->sellMarketOrderBook as $key => $value) {
            $sellMarketOBTrace .= $key . ': ' . $value . ' | ';
        }
        $this->trace('==> OB Market: ' . strtoupper($sellMarket->getName()) . ' | ' . $sellMarketOBTrace);
        return true;
    }


    private function checkBuyMarketConditions(Opportunity $opportunity, int $orderSize): bool
    {
        $cost = $orderSize * floatval($opportunity->getBuyPrice());
        $costThreshold = $cost * 0.05;  // fees 5% USDT
        foreach ($this->buyMarketBalances as $balance) {
            if ($balance->getCurrency() === 'USDT') {
                if ($balance->getAvailable() > ($cost + $costThreshold)) {
                    $this->trace('OK: sufficient buyMarket USDT balance > ' . ($cost + $costThreshold));
                }
                else {
                    $this->trace('ERROR: insufficient buyMarket USDT balance < ' . ($cost + $costThreshold));
                    return false;
                }
            }
        }

        if (!($this->buyMarketOrderBook['askPrice'] <= floatval($opportunity->getBuyPrice()))) {
            $this->trace("ERROR: buyMarketOrderBook askPrice {$this->buyMarketOrderBook['askPrice']} > buyPrice {$opportunity->getBuyPrice()}");
            return false;
        }
        $this->trace("OK: buyMarketOrderBook askPrice {$this->buyMarketOrderBook['askPrice']} <= buyPrice {$opportunity->getBuyPrice()}");

        if (!($this->buyMarketOrderBook['askSize'] >= $orderSize)) {
            $this->trace("ERROR: buyMarketOrderBook askSize {$this->buyMarketOrderBook['askSize']} < orderSize");
            return false;
        }
        $this->trace("OK: buyMarketOrderBook askSize {$this->buyMarketOrderBook['askSize']} >= orderSize");
        return true;
    }


    private function checkSellMarketConditions(Opportunity $opportunity, int $orderSize): bool
    {
        $cost = $orderSize + 1; // fees 1 FLUX
        foreach ($this->sellMarketBalances as $balance) {
            if ($balance->getCurrency() === 'FLUX') {
                if ($balance->getAvailable() > $cost) {
                    $this->trace('OK: sufficient sellMarket FLUX balance > ' . $cost);
                }
                else {
                    $this->trace('ERROR: insufficient sellMarket FLUX balance < ' . $cost);
                    return false;
                }
            }
        }

        if (!($this->sellMarketOrderBook['bidPrice'] >= floatval($opportunity->getSellPrice()))) {
            $this->trace("ERROR: sellMarketOrderBook bidPrice {$this->sellMarketOrderBook['bidPrice']} < sellPrice {$opportunity->getSellPrice()}");
            return false;
        }
        $this->trace("OK: sellMarketOrderBook bidPrice {$this->sellMarketOrderBook['bidPrice']} >= sellPrice {$opportunity->getSellPrice()}");

        if (!($this->sellMarketOrderBook['bidSize'] >= $orderSize)) {
            $this->trace("ERROR: sellMarketOrderBook bidSize {$this->sellMarketOrderBook['bidSize']} < orderSize");
            return false;
        }
        $this->trace("OK: sellMarketOrderBook bidSize {$this->sellMarketOrderBook['bidSize']} >= orderSize");
        return true;
    }


    private function sendBuyOrder(Market $market, string $ticker, int $orderSize): bool
    {
        $buyOrder = $this->ccxtService->sendBuyMarketOrder($market, $ticker, $orderSize);
        if (!is_array($buyOrder) || !isset($buyOrder['orderId'])) {
            $this->trace('ERROR: sendBuyOrder ' . strtoupper($market->getName()) . ' [' . $buyOrder ?? 'exchange createMarketOrder fail' . ']');;
            return false;
        }

        $this->buyOrder = $buyOrder;
        $this->trace('OK: sendBuyOrder ' . strtoupper($market->getName()));
        return true;
    }


    private function sendSellOrder(Market $market, string $ticker, int $orderSize): bool
    {
        $sellOrder = $this->ccxtService->sendSellMarketOrder($market, $ticker, $orderSize);
        if (!is_array($sellOrder) || !isset($sellOrder['orderId'])) {
            $this->trace('ERROR: sendSellOrder ' . strtoupper($market->getName()) . ' [' . $sellOrder ?? 'exchange createMarketOrder fail' . ']');
            return false;
        }

        $this->sellOrder = $sellOrder;
        $this->trace('OK: sendSellOrder ' . strtoupper($market->getName()));
        return true;
    }


    private function validateOrder(Market $market, string $ticker, array $order): bool
    {
        $order = $this->ccxtService->fetchOrder($market, $ticker, $order['orderId']);
        if (!is_array($order)) {
            $this->trace('ERROR: validateOrder ' . strtoupper($market->getName()) . ' [' . $order ?? 'exchange fetchOrder fail' . ']');
            return false;
        }

        $newOrder = $this->orderService->denormalizeOrder($order);
        if (!$newOrder instanceof Order) {
            $this->trace('ERROR: createOrder ' . strtoupper($market->getName()) . ' [' . $newOrder['field'] . ' | ' . $newOrder['message'] . ']');
        }
        else $this->orderService->createOrder($newOrder);

        if ($newOrder->getStatus() !== 'closed') {
            $this->trace('ERROR: validateOrder ' . strtoupper($market->getName()) . ' [Order status ' . $newOrder->getStatus() . ']');
            return false;
        }

        $this->trace('OK: validateOrder ' . strtoupper($market->getName()) . ' [Order status ' . $newOrder->getStatus() . ']');
        return true;
    }


    private function cancelOrder(Market $market, string $ticker, array $order): bool
    {
        $order = $this->ccxtService->cancelOrder($market, $ticker, $order['orderId']);
        if (!is_array($order)) {
            $this->trace('ERROR: cancelOrder ' . strtoupper($market->getName()) . ' [' . $order ?? 'exchange cancelOrder fail' . ']');
            return false;
        }

        $existOrder = $this->orderService->denormalizeOrder($order);
        if (!$existOrder instanceof Order) {
            $this->trace('ERROR: cancelOrder ' . strtoupper($market->getName()) . ' [' . $existOrder['field'] . ' | ' . $existOrder['message'] . ']');
            return false;
        } else {
            $updatedOrder = $this->orderService->updateOrder($existOrder);
            if (!$updatedOrder instanceof Order) {
                $this->trace('ERROR: cancelOrder ' . strtoupper($market->getName()) . ' [Order ' . $existOrder['orderId'] . ' not updated]');
                return false;
            }
        }

        $this->trace('OK: cancelOrder ' . strtoupper($market->getName()) . ' [Order ' . $updatedOrder->getOrderId() . ' canceled]');
        return true;
    }


    private function fetchNodeOb(Market $market): ?array
    {
        $nodeObUrl = $this->containerBag->get('node_ob_url');
        try {
            $response = $this->client->request('GET', $nodeObUrl . $market->getName());
            if (200 === $response->getStatusCode()) {
                return $response->toArray();
            }
        } catch (TransportExceptionInterface $e) {
        }

        return null;
    }

}