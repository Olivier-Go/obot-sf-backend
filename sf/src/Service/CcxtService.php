<?php

namespace App\Service;

use App\Entity\Balance;
use App\Entity\Log;
use App\Entity\Market;
use App\Utils\Tools;
use ccxt\kucoin as Kucoin;
use ccxt\bittrex as Bittrex;
use ccxt\binance as Binance;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CcxtService extends Tools
{

    private ManagerRegistry $doctrine;
    private NormalizerInterface $normalizer;

    public function __construct(ManagerRegistry $doctrine, NormalizerInterface $normalizer)
    {
        $this->doctrine = $doctrine;
        $this->normalizer = $normalizer;
    }

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

    public function fetchTickerInfos(Market $market): Market
    {
        $exchange = $this->getExchangeInstance($market);

        foreach ($market->getTickers() as $ticker) {
            $tickerInfos = $exchange->fetch_ticker($ticker->getName()) ?? [];
            if (!empty($tickerInfos)) {
                $ticker->setUpdated(new DateTime($this->convertTimestampMs($tickerInfos['timestamp'])));
                $ticker->setVolume($tickerInfos['baseVolume']);
                $ticker->setLast($tickerInfos['last']);
                $ticker->setAverage($tickerInfos['average']);
                $ticker->setLow($tickerInfos['low']);
                $ticker->setHigh($tickerInfos['high']);
            }
            $this->doctrine->getManager()->persist($ticker);
        }

        return $market;
    }


    public function fetchAccountBalance(Balance $balance): Balance
    {
        $exchange = $this->getExchangeInstance($balance->getTicker()->getMarket());

        if ($exchange->checkRequiredCredentials()) {
            $exchangeBalances = $exchange->fetch_balance();
            if (isset($exchangeBalances)) {
                $this->updateBalances($exchangeBalances, $balance);
            }
        }

        return $balance;
    }

    public function fetchAccountAllBalances(Market $market): Market
    {
        $exchange = $this->getExchangeInstance($market);

        if ($exchange->checkRequiredCredentials()) {
            $exchangeBalances = $exchange->fetch_balance();
            if (isset($exchangeBalances)) {
                foreach ($market->getBalances() as $balance) {
                    $this->updateBalances($exchangeBalances, $balance);
                    $this->doctrine->getManager()->persist($balance);
                }
            }
        }

        return $market;
    }

    private function updateBalances(array $exchangeBalances, Balance $balance): void
    {
        foreach ($exchangeBalances as $symbol => $data) {
            if ($symbol === $balance->getCurrency()) {
                $balance->setTotal($data['total']);
                $balance->setAvailable($data['free']);
                $balance->setHold($data['total'] - $data['free']);

                $log = new Log();
                $log->setEntityName('Balance');
                $log->setEntityId($balance->getId());
                $log->setContent($this->normalizer->normalize($balance, null, ['groups' => 'log']));
                $this->doctrine->getManager()->persist($log);
            }
        }
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

    public function sendSellMarketOrder(Market $market, string $ticker, $amount): ?string
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

    public function sendBuyMarketOrder(Market $market, string $ticker, $amount): ?string
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

    public function sendLimitSellOrder(Market $market, string $ticker, $amount, $price): ?string
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