<?php

namespace App\Service;

use App\Entity\Balance;
use App\Entity\Market;
use App\Entity\Ticker;
use App\Utils\Tools;
use ccxt\kucoin as Kucoin;
use ccxt\bittrex as Bittrex;
use ccxt\binance as Binance;
use DateTime;
use Exception;

class CcxtService extends Tools
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
                'options' => [
                    'recvWindow' => 10000
                ]
            ]);
        }
        return null;
    }

    public function fetchTickerInfos(Ticker $ticker): Ticker
    {
        $exchange = $this->getExchangeInstance($ticker->getMarket());
        $tickerInfos = $exchange->fetch_ticker($ticker->getName()) ?? [];

        if (!empty($tickerInfos)) {
            $ticker->setUpdated(new DateTime($this->convertTimestampMs($tickerInfos['timestamp'])));
            $ticker->setVolume($tickerInfos['baseVolume']);
            $ticker->setLast($tickerInfos['last']);
            $ticker->setAverage($tickerInfos['average']);
            $ticker->setLow($tickerInfos['low']);
            $ticker->setHigh($tickerInfos['high']);
        }

        return $ticker;
    }

    public function fetchAccountBalance(Balance $balance): Balance
    {
        $exchange = $this->getExchangeInstance($balance->getTicker()->getMarket());

        if ($exchange->checkRequiredCredentials()) {
            $exchangeBalances = $exchange->fetch_balance();
            if (isset($exchangeBalances)) {
                foreach ($exchangeBalances as $symbol => $data) {
                    if ($symbol === $balance->getCurrency()) {
                        $balance->setTotal($data['total']);
                        $balance->setAvailable($data['free']);
                        $balance->setHold($data['total'] - $data['free']);
                    }
                }
            }
        }

        return $balance;
    }

    public function fetchOrderBook(Market $market, string $ticker): ?array
    {
        $exchange = $this->getExchangeInstance($market);

        if ($exchange && $exchange->has['fetchOrderBook']) {
            try {
                $exchangeOrderBook = $exchange->fetch_order_book($ticker);
                if (is_array($exchangeOrderBook)) {
                    $orderBook['askPrice'] = $exchangeOrderBook['asks'][0][0];
                    $orderBook['askSize'] = $exchangeOrderBook['asks'][0][1];
                    $orderBook['bidPrice'] = $exchangeOrderBook['bids'][0][0];
                    $orderBook['bidSize'] = $exchangeOrderBook['bids'][0][1];
                    return $orderBook;
                }
            } catch (Exception $e) {
            }
        }

        return null;
    }

    public function fetchOrders(Market $market): array
    {
        $orders = [];
        $exchange = $this->getExchangeInstance($market);

        if ($exchange) {
            foreach ($market->getTickers() as $ticker) {
                if ($exchange->has['fetchOrders']) {
                    $exchangeOrders = $exchange->fetch_orders($ticker->getName());
                    foreach ($exchangeOrders as $exchangeOrder) {
                        unset($exchangeOrder['id']);
                        $exchangeOrder['opened'] = $this->convertTimestampMs($exchangeOrder['timestamp']);
                        $exchangeOrder['lastTrade'] = $this->convertTimestampMs($exchangeOrder['lastTradeTimestamp']);
                        $exchangeOrder['market'] = $market->getId();
                        $exchangeOrder['ticker'] = $ticker->getId();
                        $orders[] = $exchangeOrder;
                    }
                }
            }
        }

        return $orders;
    }

    public function fetchOrderById(Market $market, int $clientOrderId): ?array
    {
        $exchange = $this->getExchangeInstance($market);

        if ($exchange) {
            foreach ($market->getTickers() as $ticker) {
                if ($exchange->has['fetchOrder']) {
                    try {
                        $order = $exchange->fetch_order($clientOrderId, $ticker->getName());
                        if (is_array($order)) {
                            unset($order['id']);
                            $order['opened'] = $this->convertTimestampMs($order['timestamp']);
                            $order['lastTrade'] = $this->convertTimestampMs($order['lastTradeTimestamp']);
                            $order['market'] = $market->getId();
                            $order['ticker'] = $ticker->getId();
                            return $order;
                        }
                    } catch (Exception $e) {
                        continue;
                    }
                }
            }
        }

        return null;
    }

    public function fetchOpenOrders(Market $market): array
    {
        $openOrders = [];
        $exchange = $this->getExchangeInstance($market);

        if ($exchange) {
            foreach ($market->getTickers() as $ticker) {
                if ($exchange->has['fetchOpenOrders']) {
                    $exchangeOrders = $exchange->fetch_open_orders($ticker->getName());
                    foreach ($exchangeOrders as $exchangeOrder) {
                        unset($exchangeOrder['id']);
                        $exchangeOrder['opened'] = $this->convertTimestampMs($exchangeOrder['timestamp']);
                        $exchangeOrder['lastTrade'] = $this->convertTimestampMs($exchangeOrder['lastTradeTimestamp']);
                        $exchangeOrder['market'] = $market->getId();
                        $exchangeOrder['ticker'] = $ticker->getId();
                        $openOrders[] = $exchangeOrder;
                    }
                }
            }
        }

        return $openOrders;
    }

    public function sendLimitSellOrder(Market $market, Ticker $ticker, $amount, $price)
    {
        $exchange = $this->getExchangeInstance($market);

        if ($exchange) {
            if ($exchange->has['createLimitOrder']) {
                try {
                    $order = $exchange->create_limit_sell_order($ticker->getName(), $amount, $price);
                    if (is_array($order)) {
                        unset($order['id']);
                        $order['opened'] = $this->convertTimestampMs($order['timestamp']);
                        $order['lastTrade'] = $this->convertTimestampMs($order['lastTradeTimestamp']);
                        $order['market'] = $market->getId();
                        $order['ticker'] = $ticker->getId();
                        return $order;
                    }
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
                        $order = $exchange->cancel_order($ticker->getName(), $clientOrderId);
                        if (is_array($order)) {
                            unset($order['id']);
                            $order['opened'] = $this->convertTimestampMs($order['timestamp']);
                            $order['lastTrade'] = $this->convertTimestampMs($order['lastTradeTimestamp']);
                            $order['market'] = $market->getId();
                            $order['ticker'] = $ticker->getId();
                            return $order;
                        }
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
        $cancelOrders = [];
        $exchange = $this->getExchangeInstance($market);

        if ($exchange) {
            foreach ($market->getTickers() as $ticker) {
                if ($exchange->has['cancelAllOrders']) {
                    try {
                        $exchangeOrders = $exchange->cancel_all_orders($ticker->getName());
                        foreach ($exchangeOrders as $exchangeOrder) {
                            unset($exchangeOrder['id']);
                            $exchangeOrder['opened'] = $this->convertTimestampMs($exchangeOrder['timestamp']);
                            $exchangeOrder['lastTrade'] = $this->convertTimestampMs($exchangeOrder['lastTradeTimestamp']);
                            $exchangeOrder['market'] = $market->getId();
                            $exchangeOrder['ticker'] = $ticker->getId();
                            $cancelOrders[] = $exchangeOrder;
                        }
                    } catch (Exception $e) {
                        continue;
                    }
                }
            }
        }

        return $cancelOrders;
    }

    public function test(Market $market)
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