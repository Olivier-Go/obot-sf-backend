import { ws as binanceWs } from "./binance.js";
import { ws as kucoinWs } from "./kucoin.js";
import { ws as bittrexWs } from "./bittrex.js";

export const loadExchangeWs = (exchangeNb, exchangeName) => {
    switch (exchangeName) {
        case "BINANCE":
            return binanceWs;
        case "KUCOIN":
            return kucoinWs;
        case "BITTREX":
            return bittrexWs;
        default:
            console.log(`Variable EXCHANGE${exchangeNb} non definie !`);
            process.exit(1);
    }
};