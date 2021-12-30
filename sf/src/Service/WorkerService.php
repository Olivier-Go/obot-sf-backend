<?php

namespace App\Service;

use App\Entity\Market;
use App\Entity\Opportunity;
use App\Repository\BalanceRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class WorkerService
{
    private ContainerBagInterface $params;
    private CcxtService $ccxtService;
    private BalanceRepository $balanceRepository;
    private string $logs;
    private Array $buyMarketBalances;
    private Array $sellMarketBalances;
    private /*?Array*/ $buyMarketOrderBook;
    private /*?Array*/ $sellMarketOrderBook;


    public function __construct(ContainerBagInterface $params, CcxtService $ccxtService, BalanceRepository $balanceRepository)
    {
        $this->params = $params;
        $this->logs = '============= Worker start ==============' . PHP_EOL;
        $this->ccxtService = $ccxtService;
        $this->balanceRepository = $balanceRepository;

        /*$this->buyMarketOrderBook = [
            'askPrice' => 2.13811,
            'askSize' => 177.809,
            'bidPrice' => 2.1381,
            'bidSize' => 38.35051355
        ];
        $this->sellMarketOrderBook = [
            'askPrice' => 2.113,
            'askSize' => 16.0,
            'bidPrice' => 2.109,
            'bidSize' => 308.35
        ];*/
    }

    private function trace(string $message, ?Opportunity $opportunity = null): array
    {
        $this->logs = !empty($message) ? $this->logs . $message . PHP_EOL : $this->logs;
        return [
            'logs' => $this->logs,
            'opportunity' => $opportunity,
        ];
    }

    public function execute(Opportunity $opportunity)
    {
        $startTime = microtime(true);
        $priceDiff = $this->params->get('worker_order_diff');
        $orderSize = $this->params->get('worker_order_size');
        $ticker = $opportunity->getTicker();
        $buyMarket = $opportunity->getBuyMarket();
        $sellMarket = $opportunity->getSellMarket();

        // Check priceDiff
        if (!$this->checkPriceDiff($opportunity, $priceDiff)) return $this->logs;

        // Check orderSize
        if (!$this->checkOrderSize($opportunity, $orderSize)) return $this->logs;

        // Get Balances
        if (!$this->getBalances($buyMarket, $sellMarket, $ticker)) return $this->logs;

        // Fetch Orderbooks
        if (!$this->fetchOrderBooks($buyMarket, $sellMarket, $ticker)) return $this->logs;

        //Direction
        $this->trace('-----------------------------------------');
        switch ($opportunity->getDirection()) {
            case 'Buy->Sell':
                /*$this->trace('OK: direction = ' . $opportunity->getDirection());
                if (!$this->checkBuyMarketConditions($opportunity, $orderSize)) return $this->logs;
                if (!$this->checkSellMarketConditions($opportunity, $orderSize)) return $this->logs;*/
                break;
            case 'Sell->Buy':
                $this->trace('OK: direction = ' . $opportunity->getDirection());
                if (!$this->checkSellMarketConditions($opportunity, $orderSize)) return $this->logs;
                /*if (!$this->checkBuyMarketConditions($opportunity, $orderSize)) return $this->logs;*/
                dd($this->ccxtService->sendSellMarketOrder($sellMarket, $ticker, 1));
                break;
            default:
                $this->trace('ERROR: invalid direction');
                return $this->logs;
        }


        $timeElapsedMs = intval((microtime(true) - $startTime) * 1000);
        dump($timeElapsedMs);
        dump($this->logs);
        dd($this->buyMarketOrderBook, $this->sellMarketOrderBook);
    }

    /**
     * @param Opportunity $opportunity
     * @param float $priceDiff
     * @return bool
     */
    private function checkPriceDiff(Opportunity $opportunity, float $priceDiff): bool
    {
        if ($opportunity->getPriceDiff() < $priceDiff) {
            $this->trace('ERROR: priceDiff < ' . $priceDiff);
            return false;
        }
        $this->trace('OK: priceDiff > ' . $priceDiff);
        return true;
    }

    /**
     * @param Opportunity $opportunity
     * @param int $orderSize
     * @return bool
     */
    private function checkOrderSize(Opportunity $opportunity, int $orderSize): bool
    {
        if ($opportunity->getSize() < $orderSize) {
            $this->trace('ERROR: orderSize < ' . $orderSize);
            return false;
        }
        $this->trace('OK: orderSize > ' . $orderSize);
        return true;
    }

    /**
     * @param Market $buyMarket
     * @param Market $sellMarket
     * @param string $ticker
     * @return bool
     */
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

    /**
     * @param Market $buyMarket
     * @param Market $sellMarket
     * @param string $ticker
     * @return bool
     */
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
            $buyMarketOBTrace .= $key . ': ' . $value . ' / ';
        }
        $this->trace('==> OB Market: ' . strtoupper($buyMarket->getName()) . ' / ' . $buyMarketOBTrace);

        $this->sellMarketOrderBook = $this->ccxtService->fetchOrderBook($sellMarket, $ticker);
        if (!$this->sellMarketOrderBook) {
            $this->trace('ERROR: fetch sellMarketOrderBook');
            return false;
        }
        $this->trace('OK: fetch sellMarketOrderBook');
        $sellMarketOBTrace = '';
        foreach ($this->sellMarketOrderBook as $key => $value) {
            $sellMarketOBTrace .= $key . ': ' . $value . ' / ';
        }
        $this->trace('==> OB Market: ' . strtoupper($sellMarket->getName()) . ' / ' . $sellMarketOBTrace);
        return true;
    }

    /**
     * @param Opportunity $opportunity
     * @param int $orderSize
     * @return bool
     */
    private function checkBuyMarketConditions(Opportunity $opportunity, int $orderSize): bool
    {
        $cost = $orderSize * $opportunity->getBuyPrice();
        $costThreshold = $cost * 0.05;
        foreach ($this->buyMarketBalances as $balance) {
            if ($balance->getCurrency() === 'USDT') {
                if ($balance->getAvailable() > ($cost + $costThreshold)) {
                    $this->trace('OK: sufficient buyMarket USDT balance');
                }
                else {
                    $this->trace('ERROR: insufficient buyMarket USDT balance');
                    return false;
                }
            }
        }

        if (!$this->buyMarketOrderBook['askPrice'] <= $opportunity->getBuyPrice()) {
            $this->trace('ERROR: buyMarketOrderBook askPrice > buyPrice');
            return false;
        }
        $this->trace('OK: buyMarketOrderBook askPrice <= buyPrice');

        if (!$this->buyMarketOrderBook['askSize'] >= $orderSize) {
            $this->trace('ERROR: buyMarketOrderBook askSize < orderSize');
            return false;
        }
        $this->trace('OK: buyMarketOrderBook askSize >= orderSize');
        return true;
    }

    /**
     * @param Opportunity $opportunity
     * @param int $orderSize
     * @return bool
     */
    private function checkSellMarketConditions(Opportunity $opportunity, int $orderSize): bool
    {
        $cost = $orderSize / $opportunity->getSellPrice();
        $costThreshold = $cost * 0.05;
        foreach ($this->sellMarketBalances as $balance) {
            if ($balance->getCurrency() === 'FLUX') {
                if ($balance->getAvailable() > ($cost + $costThreshold)) {
                    $this->trace('OK: sufficient sellMarket USDT balance');
                }
                else {
                    $this->trace('ERROR: insufficient sellMarket USDT balance');
                    return false;
                }
            }
        }

        if (!$this->sellMarketOrderBook['bidPrice'] >= $opportunity->getSellPrice()) {
            $this->trace('ERROR: sellMarketOrderBook bidPrice < sellPrice');
            return false;
        }
        $this->trace('OK: sellMarketOrderBook bidPrice >= sellPrice');

        if (!$this->sellMarketOrderBook['bidSize'] >= $orderSize) {
            $this->trace('ERROR: sellMarketOrderBook bidSize < orderSize');
            return false;
        }
        $this->trace('OK: sellMarketOrderBook bidSize >= orderSize');
        return true;
    }

}