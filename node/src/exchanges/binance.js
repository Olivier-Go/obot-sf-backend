import './../utils/env.js';
import { updateOrdersArr, drawOrdersArr } from './../utils/functions.js';
import { BinanceClient } from "ccxws";

const binanceClient = new BinanceClient();

export const ws = {
  state: 'Disconnected',
  market: {
    id: process.env.BINANCE_MARKET_ID,
    base: process.env.BINANCE_MARKET_BASE,
    quote: process.env.BINANCE_MARKET_QUOTE,
  },
  buyOrders: [],
  sellOrders: [],
  filteredBuyOrders: [],
  filteredSellOrders: [],

  run: () => {
    binanceClient.on("error", err => ws.state = err);
    binanceClient.on("connecting", data => ws.state = 'Connecting');
    binanceClient.on("connected", data => ws.state = 'Connected');
    binanceClient.on("disconnected", data => ws.state = 'Disconnected');
    binanceClient.on("closed", () => {
      ws.state = 'Closed';
      ws.buyOrders = [];
      ws.sellOrders = [];
      ws.filteredBuyOrders = [];
      ws.filteredSellOrders = [];
    });
    binanceClient.on("l2update", ({ asks, bids }, market) => {
      ws.buyOrders = updateOrdersArr(ws.buyOrders, bids);
      ws.sellOrders = updateOrdersArr(ws.sellOrders, asks, false);
      ws.filteredBuyOrders = drawOrdersArr(ws.buyOrders);
      ws.filteredSellOrders = drawOrdersArr(ws.sellOrders);
    });
    binanceClient.subscribeLevel2Updates(ws.market);
  },

  reset: () => {
    binanceClient.reconnect();
  },

  printOrderBook: () => {
    console.log(`-----------------------------------------------------------`);
    console.log(`  BINANCE  |  State : ${ws.state}  |  Market : ${ws.market.id}`);
    console.log(`-----------------------------------------------------------`);
    console.log(`                      BUY ORDERS                         `);
    console.table(ws.filteredBuyOrders);
    console.log(`                      SELL ORDERS                         `);
    console.table(ws.filteredSellOrders);
  }
};