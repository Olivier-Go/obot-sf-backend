<?php
namespace App\Service;

use App\Entity\Market;
use App\Entity\Ticker;
use ccxt\kucoin as Kucoin;
use ccxt\binance as Binance;
use DateTime;

class CcxtService
{

    public function __construct()
    {
    }


    public function fetchMarketTickerData(Market $market, Ticker $ticker): Ticker
    {
        $marketData = [];

        if ($market->getName() === 'Binance' && $ticker->getMarket() === $market) {
            $binance = new Binance();
            $marketData = $binance->fetch_ticker($ticker->getName());
        }
        if ($market->getName() === 'Kucoin' && $ticker->getMarket() === $market) {
            $kucoin = new Kucoin();
            $marketData = $kucoin->fetch_ticker($ticker->getName());
        }

        $ticker->time = !empty($marketData) ? new DateTime($marketData['datetime']) : 0;
        $ticker->volume = !empty($marketData) ? $marketData['baseVolume'] : 0;
        $ticker->last = !empty($marketData) ? $marketData['last'] : 0;
        $ticker->averagePrice = !empty($marketData) ? $marketData['average'] : 0;
        $ticker->low = !empty($marketData) ? $marketData['low'] : 0;
        $ticker->high = !empty($marketData) ? $marketData['high'] : 0;

        return $ticker;
    }

}