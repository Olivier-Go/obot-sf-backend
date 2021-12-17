<?php

namespace App\Service;

use App\Entity\Opportunity;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class WorkerService
{
    private ContainerBagInterface $params;
    private string $logs;
    private CcxtService $ccxtService;

    public function __construct(ContainerBagInterface $params, CcxtService $ccxtService)
    {
        $this->params = $params;
        $this->logs = '============= Worker start ==============' . PHP_EOL;
        $this->ccxtService = $ccxtService;
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

        if ($opportunity->getPriceDiff() < $priceDiff) {
            return $this->trace('error: priceDiff < ' . $priceDiff);
        }
        $this->trace('OK: priceDiff > ' . $priceDiff);

        if ($opportunity->getSize() < $orderSize) {
            return $this->trace('error: orderSize < ' . $orderSize);
        }
        $this->trace('OK: orderSize > ' . $orderSize);

        $buyMarketBalance = $this->ccxtService->fetchBalanceByTicker($buyMarket, $ticker);
        $sellMarketBalance = $this->ccxtService->fetchBalanceByTicker($sellMarket, $ticker);

        $timeElapsedMs = intval((microtime(true) - $startTime) * 1000);
        dump($timeElapsedMs);

        dd($buyMarketBalance, $sellMarketBalance);
    }
}