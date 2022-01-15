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

    public function sendSellMarketOrder(Market $market, string $ticker, $amount)
    {
        $exchange = $this->getExchangeInstance($market);

        if ($exchange) {
            if ($exchange->has['createMarketOrder']) {
                try {
                    $order = $exchange->create_market_sell_order($ticker, $amount);
                    if (is_array($order)) {
                        $order['orderId'] = $order['id'];
                        $order['opened'] = $this->convertTimestampMs($order['timestamp']);
                        $order['lastTrade'] = $this->convertTimestampMs($order['lastTradeTimestamp']);
                        $order['market'] = $market->getId();
                        $order['ticker'] = $ticker;
                        unset($order['id']);
                        return $order;
                    }
                } catch (Exception $e) {
                    return $e->getMessage();
                }
            }
        }

        return null;
    }

    public function sendBuyMarketOrder(Market $market, string $ticker, $amount)
    {
        $exchange = $this->getExchangeInstance($market);

        if ($exchange) {
            if ($exchange->has['createMarketOrder']) {
                try {
                    $order = $exchange->create_market_buy_order($ticker, $amount);
                    if (is_array($order)) {
                        $order['orderId'] = $order['id'];
                        $order['opened'] = $this->convertTimestampMs($order['timestamp']);
                        $order['lastTrade'] = $this->convertTimestampMs($order['lastTradeTimestamp']);
                        $order['market'] = $market->getId();
                        $order['ticker'] = $ticker;
                        unset($order['id']);
                        return $order;
                    }
                } catch (Exception $e) {
                    return $e->getMessage();
                }
            }
        }

        return null;
    }

    public function sendLimitSellOrder(Market $market, string $ticker, $amount, $price)
    {
        $exchange = $this->getExchangeInstance($market);

        if ($exchange) {
            if ($exchange->has['createLimitOrder']) {
                try {
                    $order = $exchange->create_limit_sell_order($ticker, $amount, $price);
                    if (is_array($order)) {
                        $order['orderId'] = $order['id'];
                        $order['opened'] = $this->convertTimestampMs($order['timestamp']);
                        $order['lastTrade'] = $this->convertTimestampMs($order['lastTradeTimestamp']);
                        $order['market'] = $market->getId();
                        $order['ticker'] = $ticker;
                        unset($order['id']);
                        return $order;
                    }
                } catch (Exception $e) {
                    return $e->getMessage();
                }
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
                        $exchangeOrder['orderId'] = $exchangeOrder['id'];
                        $exchangeOrder['opened'] = $this->convertTimestampMs($exchangeOrder['timestamp']);
                        $exchangeOrder['lastTrade'] = $this->convertTimestampMs($exchangeOrder['lastTradeTimestamp']);
                        $exchangeOrder['market'] = $market->getId();
                        $exchangeOrder['ticker'] = $ticker->getName();
                        unset($exchangeOrder['id']);
                        $orders[] = $exchangeOrder;
                    }
                }
            }
        }

        return $orders;
    }

    public function fetchOrder(Market $market, string $ticker, string $orderId)
    {
        $exchange = $this->getExchangeInstance($market);

        if ($exchange && $exchange->has['fetchOrder']) {
            try {
                $order = $exchange->fetch_order($orderId, $ticker);
                if (is_array($order)) {
                    $order['orderId'] = $order['id'];
                    $order['opened'] = $this->convertTimestampMs($order['timestamp']);
                    $order['lastTrade'] = $this->convertTimestampMs($order['lastTradeTimestamp']);
                    $order['market'] = $market->getId();
                    $order['ticker'] = $ticker;
                    unset($order['id']);
                    return $order;
                }
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

        return null;
    }

    public function cancelOrder(Market $market, string $ticker, string $orderId)
    {
        $exchange = $this->getExchangeInstance($market);

        if ($exchange && $exchange->has['cancelOrder']) {
            try {
                $order = $exchange->cancel_order($orderId, $ticker);
                if (is_array($order)) {
                    $order['orderId'] = $order['id'];
                    $order['opened'] = $this->convertTimestampMs($order['timestamp']);
                    $order['lastTrade'] = $this->convertTimestampMs($order['lastTradeTimestamp']);
                    $order['market'] = $market->getId();
                    $order['ticker'] = $ticker;
                    unset($order['id']);
                    return $order;
                }
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

        return null;
    }

}