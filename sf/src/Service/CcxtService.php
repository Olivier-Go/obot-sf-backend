<?php
namespace App\Service;

use App\Entity\Market;
use App\Entity\Ticker;
use ccxt\kucoin as Kucoin;
use ccxt\bittrex as Bittrex;
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

        if ($market->getId() === 1 && $ticker->getMarket() === $market) {
            $bittrex = new Bittrex();
            $marketData = $bittrex->fetch_ticker($ticker->getName());
        }
        if ($market->getId() === 2 && $ticker->getMarket() === $market) {
            $kucoin = new Kucoin();
            $marketData = $kucoin->fetch_ticker($ticker->getName());
        }
        if ($market->getId() === 3 && $ticker->getMarket() === $market) {
            $binance = new Binance();
            $marketData = $binance->fetch_ticker($ticker->getName());
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
        $balance = null;

        if ($market->getId() === 1) {
            $bittrex = new Bittrex([
                'apiKey' => $market->getApiKey(),
                'secret' => $market->getApiSecret()
            ]);

            if ($bittrex->checkRequiredCredentials()) {
                $bittrexBalance = $bittrex->fetch_balance();
                if (isset($bittrexBalance['info'])) {
                    $balance = [];
                    foreach ($bittrexBalance['info'] as $currency) {
                        $balance[] = [
                            'currency' => $currency['currencySymbol'],
                            'type' => null,
                            'balance' => $currency['total'],
                            'available' => $currency['available'],
                            'holds' => $currency['total'] - $currency['available']
                        ];
                    }
                }
            }
        }
        if ($market->getId() === 2) {
            $kucoin = new Kucoin([
                'apiKey' => $market->getApiKey(),
                'secret' => $market->getApiSecret(),
                'password' => $market->getApiPassword(),
            ]);

            if ($kucoin->checkRequiredCredentials()) {
                $kucoinBalance = $kucoin->fetch_balance();
                if (isset($kucoinBalance['info']) && isset($kucoinBalance['info']['data'])) {
                    $balance = [];
                    foreach ($kucoinBalance['info']['data'] as $currency) {
                        $balance[] = [
                            'currency' => $currency['currency'],
                            'type' => $currency['type'],
                            'balance' => $currency['balance'],
                            'available' => $currency['available'],
                            'holds' => $currency['holds']
                        ];
                    }
                }
            }
        }
        if ($market->getId() === 3) {
            $binance = new Binance([
                'apiKey' => $market->getApiKey(),
                'secret' => $market->getApiSecret(),
                'password' => $market->getApiPassword(),
            ]);

            if ($binance->checkRequiredCredentials()) {
                $binanceBalance = $binance->fetch_balance();
                if (isset($binanceBalance['info']) && isset($binanceBalance['info']['balances'])) {
                    $balance = [];
                    foreach ($binanceBalance['info']['balances'] as $currency) {
                        $balance[] = [
                            'currency' => $currency['asset'],
                            'type' => $binanceBalance['info']['accountType'],
                            'balance' => $currency['free'] + $currency['locked'],
                            'available' => $currency['free'],
                            'holds' => $currency['locked']
                        ];
                    }
                }
            }
        }

        return $balance;
    }

}