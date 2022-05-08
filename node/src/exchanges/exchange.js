import { ws as binanceWs} from "./binance.js";
import { ws as kucoinWs } from "./kucoin.js";
import { ws as bittrexWs } from "./bittrex.js";
import { ws as gateioWs } from "./gateio.js";
import express from "express";

export const exchange = {
    server: express(),

    loadWebsocket: (exchangeNb, exchangeName) => {
        switch (exchangeName) {
            case 'BINANCE':
                return binanceWs;
            case 'KUCOIN':
                return kucoinWs;
            case 'BITTREX':
                return bittrexWs;
            case 'GATEIO':
                return gateioWs;
            default:
                console.log(`Variable EXCHANGE ${exchangeNb} non definie !`);
                process.exit(1);
        }
    },

    loadOrderbook: (exchangeName, exchangeWs) => {
        exchange.server.get(`/orderbook/${exchangeName}`, function (req, res) {
            const bestBuyOrder = exchangeWs.filteredSellOrders.find(e => true);
            const bestSellOrder = exchangeWs.filteredBuyOrders.find(e => true);
            let response = {};
            if (bestBuyOrder && bestSellOrder) {
                response = {
                    'askPrice': bestSellOrder.price,
                    'askSize': bestSellOrder.size,
                    'bidPrice': bestBuyOrder.price,
                    'bidSize': bestBuyOrder.size
                }
            }
            res.send(response);
        })
    },

    initOrderbook: () => {
        exchange.server.listen(process.env.EXCHANGE_ORDERBOOK_PORT);
    },
}

