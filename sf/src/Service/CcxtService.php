<?php

namespace App\Service;

use App\Entity\Market;
use App\Entity\Ticker;
use ccxt\kucoin as Kucoin;
use ccxt\bittrex as Bittrex;
use ccxt\binance as Binance;
use DateTime;
use Exception;

class CcxtService
{
    public function getExchangeInstance(Market $market)
    {
        if ($market->getId() === 1) {
            return new Bittrex([
                'apiKey' => $market->getApiKey(),
                'secret' => $market->getApiSecret()
            ]);
        }
        if ($market->getId() === 2) {
            return new Kucoin([
                'apiKey' => $market->getApiKey(),
                'secret' => $market->getApiSecret(),
                'password' => $market->getApiPassword(),
            ]);
        }
        if ($market->getId() === 3) {
            return new Binance([
                'apiKey' => $market->getApiKey(),
                'secret' => $market->getApiSecret(),
                'password' => $market->getApiPassword(),
            ]);
        }
        return null;
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

    public function fetchBalance(Market $market): array
    {
        $balance = null;

        $exchange = $this->getExchangeInstance($market);

        if ($exchange instanceof Bittrex) {
            if ($exchange->checkRequiredCredentials()) {
                $exchangeBalance = $exchange->fetch_balance();
                if (isset($exchangeBalance['info'])) {
                    $balance = [];
                    foreach ($exchangeBalance['info'] as $currency) {
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
        if ($exchange instanceof Kucoin) {
            if ($exchange->checkRequiredCredentials()) {
                $exchangeBalance = $exchange->fetch_balance();
                if (isset($exchangeBalance['info']) && isset($exchangeBalance['info']['data'])) {
                    $balance = [];
                    foreach ($exchangeBalance['info']['data'] as $currency) {
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
        if ($exchange instanceof Binance) {
            if ($exchange->checkRequiredCredentials()) {
                $exchangeBalance = $exchange->fetch_balance();
                if (isset($exchangeBalance['info']) && isset($exchangeBalance['info']['balances'])) {
                    $balance = [];
                    foreach ($exchangeBalance['info']['balances'] as $currency) {
                        $balance[] = [
                            'currency' => $currency['asset'],
                            'type' => $exchangeBalance['info']['accountType'],
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

    public function fetchOpenOrders(Market $market): array
    {
        $openOrders = [];
        $exchange = $this->getExchangeInstance($market);

        if ($exchange) {
            foreach ($market->getTickers() as $ticker) {
                if ($exchange->has['fetchOpenOrders']) {
                    $openOrders = $exchange->fetch_open_orders($ticker->getName());
                }
            }
        }

        return $openOrders;
    }

    public function fetchOrderById(Market $market, Int $clientOrderId): ?array
    {
        $exchange = $this->getExchangeInstance($market);

        if ($exchange) {
            foreach ($market->getTickers() as $ticker) {
                if ($exchange->has['fetchOrder']) {
                    try {
                        return $exchange->fetch_order($clientOrderId, $ticker->getName());
                    } catch (Exception $e) {
                        continue;
                    }
                }
            }
        }

        return null;
    }

    public function sendLimitSellOrder(Market $market, Ticker $ticker, $amount, $price)
    {
        $exchange = $this->getExchangeInstance($market);

        if ($exchange) {
            if ($exchange->has['createLimitOrder']) {
                try {
                    $order = $exchange->create_limit_sell_order($ticker->getName(), $amount, $price);
                    unset($order['id']);
                    $order['market'] = $market->getId();
                    $order['ticker'] = $ticker->getId();
                    return $order;
                } catch (Exception $e) {

                }
            }
        }

        return null;
    }

    public function cancelOrderById(Market $market, Int $clientOrderId): ?array
    {
        $exchange = $this->getExchangeInstance($market);

        if ($exchange) {
            foreach ($market->getTickers() as $ticker) {
                if ($exchange->has['cancelOrder']) {
                    try {
                        return $exchange->cancel_order($ticker->getName(), $clientOrderId);
                    } catch (Exception $e) {
                        continue;
                    }
                }
            }
        }

        return null;
    }

    public function cancelOrders(Market $market): ?array
    {
        $exchange = $this->getExchangeInstance($market);

        if ($exchange) {
            foreach ($market->getTickers() as $ticker) {
                if ($exchange->has['cancelAllOrders']) {
                    try {
                        return $exchange->cancel_all_orders($ticker->getName());
                    } catch (Exception $e) {
                        continue;
                    }
                }
            }
        }

        return null;
    }

    public function fetchOrders(Market $market)
    {
        $binance = new Binance([
            'apiKey' => $market->getApiKey(),
            'secret' => $market->getApiSecret(),
            'password' => $market->getApiPassword(),
        ]);

        dd($binance->fetch_order_book('FLUX/USDT'));

        //dd($binance->has);
        //dd($binance->fetch_order_book('FLUX/USDT'));
        //dd($binance->fetch_orders('FLUX/USDT'));
        //$order = $exchange->fetch_order($id);

//        if ($binance->has['fetchOrder']) {
//            dd($binance->fetch_order($id));
//        }

//        if ($binance->has['fetchOpenOrders']) {
//            dd($binance->fetch_open_orders('FLUX/USDT'));
//        }

        if ($binance->has['fetchOrder']) {
            dd($binance->f($id));
        }
    }

}