<?php
namespace App\Service;

use App\Entity\Market;
use App\Entity\Ticker;
use ccxt\kucoin as Kucoin;
use ccxt\bittrex as Bittrex;
use DateTime;

class CcxtService
{

    public function __construct()
    {
    }


    public function fetchMarketTickerData(Market $market, Ticker $ticker): Ticker
    {
        $marketData = [];

        if ($market->getId() === 1 && $ticker->getMarket() === $market) {
            $bittrex = new Bittrex();
            $marketData = $bittrex->fetch_ticker($ticker->getName());
        }
        if ($market->getId() === 2 && $ticker->getMarket() === $market) {
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

    public function fetchBalance(Market $market)
    {
        if ($market->getId() === 1) {
            $bittrex = new Bittrex([
                'apiKey' => $market->getApiKey(),
                'secret' => $market->getApiSecret()
            ]);

            if ($bittrex->checkRequiredCredentials()) {
                return $bittrex->fetch_balance();
            }
        }

        if ($market->getId() === 2) {
            $kucoin = new Kucoin([
                'apiKey' => $market->getApiKey(),
                'secret' => $market->getApiSecret(),
                'password' => $market->getApiPassword(),
            ]);

            if ($kucoin->checkRequiredCredentials()) {
                return $kucoin->fetch_balance();
            }
        }

        return null;
    }

}