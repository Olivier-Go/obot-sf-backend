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
    private ?Array $buyMarketOrderBook;
    private ?Array $sellMarketOrderBook;


    public function __construct(ContainerBagInterface $params, CcxtService $ccxtService, BalanceRepository $balanceRepository)
    {
        $this->params = $params;
        $this->logs = '============= Worker start ==============' . PHP_EOL;
        $this->ccxtService = $ccxtService;
        $this->balanceRepository = $balanceRepository;
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
        $this->checkPriceDiff($opportunity, $priceDiff);

        // Check orderSize
        $this->checkOrderSize($opportunity, $orderSize);

        // Get Balances
        $this->getBalances($buyMarket, $sellMarket, $ticker);

        // Fetch Orderbooks
        $this->fetchOrderBooks($buyMarket, $sellMarket, $ticker);


        $timeElapsedMs = intval((microtime(true) - $startTime) * 1000);
        dump($timeElapsedMs);
        dump($this->logs);
        dd($this->buyMarketOrderBook, $this->sellMarketOrderBook);
    }

    private function checkPriceDiff(Opportunity $opportunity, float $priceDiff): void
    {
        if ($opportunity->getPriceDiff() < $priceDiff) {
            $this->trace('ERROR: priceDiff < ' . $priceDiff);
            return;
        }
        $this->trace('OK: priceDiff > ' . $priceDiff);
    }

    private function checkOrderSize(Opportunity $opportunity, int $orderSize): void
    {
        if ($opportunity->getSize() < $orderSize) {
            $this->trace('ERROR: orderSize < ' . $orderSize);
            return;
        }
        $this->trace('OK: orderSize > ' . $orderSize);
    }

    private function getBalances(Market $buyMarket, Market $sellMarket, string $ticker): void
    {
        $this->buyMarketBalances = $this->balanceRepository->findMarketBalancesForTicker($buyMarket, $ticker);
        if (empty($this->buyMarketBalances)) {
            $this->trace('ERROR: get buyMarketBalances');
            return;
        }
        $this->trace('OK: get buyMarketBalances');
        foreach ($this->buyMarketBalances as $balance) {
            $this->trace('==> Balance ' . $balance);
        }
        $this->sellMarketBalances = $this->balanceRepository->findMarketBalancesForTicker($sellMarket, $ticker);
        if (empty($this->sellMarketBalances)) {
            $this->trace('ERROR: get sellMarketBalances');
            return;
        }
        $this->trace('OK: get sellMarketBalances');
        foreach ($this->sellMarketBalances as $balance) {
            $this->trace('==> Balance ' . $balance);
        }
    }

    private function fetchOrderBooks(Market $buyMarket, Market $sellMarket, string $ticker): void
    {
        $this->buyMarketOrderBook = $this->ccxtService->fetchOrderBook($buyMarket, $ticker);
        if (!$this->buyMarketOrderBook) {
            $this->trace('ERROR: fetch buyMarketOrderBook');
            return;
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
            return;
        }
        $this->trace('OK: fetch sellMarketOrderBook');
        $sellMarketOBTrace = '';
        foreach ($this->sellMarketOrderBook as $key => $value) {
            $sellMarketOBTrace .= $key . ': ' . $value . ' / ';
        }
        $this->trace('==> OB Market: ' . strtoupper($sellMarket->getName()) . ' / ' . $sellMarketOBTrace);
    }
}