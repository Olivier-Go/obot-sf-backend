<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\Market;
use App\Entity\Opportunity;
use App\Repository\OrderRepository;
use App\Repository\BalanceRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class WorkerService
{
    private ContainerBagInterface $params;
    private CcxtService $ccxtService;
    private BalanceRepository $balanceRepository;
    private OrderRepository $orderRepository;
    private OrderService $orderService;
    private ManagerRegistry $doctrine;
    private string $logs;
    private string $startTime;
    private array $buyMarketBalances;
    private array $sellMarketBalances;
    private ?array $buyMarketOrderBook;
    private ?array $sellMarketOrderBook;
    private ?array $buyOrder;
    private ?array $sellOrder;


    public function __construct(ContainerBagInterface $params, CcxtService $ccxtService, BalanceRepository $balanceRepository, OrderRepository $orderRepository, OrderService $orderService, ManagerRegistry $doctrine)
    {
        $this->params = $params;
        $this->ccxtService = $ccxtService;
        $this->balanceRepository = $balanceRepository;
        $this->orderRepository = $orderRepository;
        $this->orderService = $orderService;
        $this->doctrine = $doctrine;
        $this->logs = '=================== Worker start ===================' . PHP_EOL;
        $this->startTime = microtime(true);
        $this->buyOrder = null;
        $this->sellOrder = null;
    }


    public function execute(Opportunity $opportunity)
    {
        $sendOrder = $this->params->get('worker_send_order');
        $priceDiff = $this->params->get('worker_order_diff');
        $orderSize = $this->params->get('worker_order_size');
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
                if ($sendOrder) {
                    $this->trace('OK: send Order ' . $opportunity->getDirection());
                    $this->printExecTime();
                    $this->updateBalances();
                    return $this->exit($opportunity);
                }
                if (!$this->sendBuyOrder($buyMarket, $ticker, $orderSize))
                    return $this->exit($opportunity);
                if (!$this->sendSellOrder($sellMarket, $ticker, $orderSize))
                    return $this->exit($opportunity);
                if (!$this->validateOrder($buyMarket, $ticker, $this->buyOrder)) 
                    $this->cancelOrder($buyMarket, $ticker, $this->buyOrder);
                if (!$this->validateOrder($sellMarket, $ticker, $this->sellOrder)) 
                    $this->cancelOrder($sellMarket, $ticker, $this->sellOrder);
                // Update Balances
                $this->printExecTime();
                $this->updateBalances();
                break;

            case 'Sell->Buy':
                $this->trace('OK: direction ' . $opportunity->getDirection());
                if (!$this->checkSellMarketConditions($opportunity, $orderSize))
                    return $this->exit($opportunity);
                if (!$this->checkBuyMarketConditions($opportunity, $orderSize))
                    return $this->exit($opportunity);
                if ($sendOrder) {
                    $this->trace('OK: send Order ' . $opportunity->getDirection());
                    $this->printExecTime();
                    $this->updateBalances();
                    return $this->exit($opportunity);
                }
                if (!$this->sendSellOrder($sellMarket, $ticker, $orderSize))
                    return $this->exit($opportunity);
                if (!$this->sendBuyOrder($buyMarket, $ticker, $orderSize))
                    return $this->exit($opportunity);
                if (!$this->validateOrder($sellMarket, $ticker, $this->sellOrder))
                    $this->cancelOrder($sellMarket, $ticker, $this->sellOrder);
                if (!$this->validateOrder($buyMarket, $ticker, $this->buyOrder))
                    $this->cancelOrder($buyMarket, $ticker, $this->buyOrder);
                // Update Balances
                $this->printExecTime();
                $this->updateBalances();
                break;

            default:
                $this->trace('ERROR: invalid direction');
        }

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
        return $opportunity;
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
        $this->buyMarketOrderBook = $this->ccxtService->fetchOrderBook($buyMarket, $ticker);
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

        $this->sellMarketOrderBook = $this->ccxtService->fetchOrderBook($sellMarket, $ticker);
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
            $this->trace('ERROR: buyMarketOrderBook askPrice > buyPrice');
            return false;
        }
        $this->trace('OK: buyMarketOrderBook askPrice <= buyPrice');

        if (!($this->buyMarketOrderBook['askSize'] >= $orderSize)) {
            $this->trace('ERROR: buyMarketOrderBook askSize < orderSize');
            return false;
        }
        $this->trace('OK: buyMarketOrderBook askSize >= orderSize');
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
            $this->trace('ERROR: sellMarketOrderBook bidPrice < sellPrice');
            return false;
        }
        $this->trace('OK: sellMarketOrderBook bidPrice >= sellPrice');

        if (!($this->sellMarketOrderBook['bidSize'] >= $orderSize)) {
            $this->trace('ERROR: sellMarketOrderBook bidSize < orderSize');
            return false;
        }
        $this->trace('OK: sellMarketOrderBook bidSize >= orderSize');
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

}