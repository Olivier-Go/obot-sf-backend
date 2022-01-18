import './../utils/env.js';
import { updateOrdersArr, drawOrdersArr } from './../utils/functions.js';
import { KucoinClient } from "ccxws";

const kucoinClient = new KucoinClient();

export const ws = {
  state: 'Disconnected',
  market: {
    id: process.env.KUCOIN_MARKET_ID,
    base: process.env.KUCOIN_MARKET_BASE,
    quote: process.env.KUCOIN_MARKET_QUOTE,
  },
  buyOrders: [],
  sellOrders: [],
  filteredBuyOrders: [],
  filteredSellOrders: [],

  run: () => {
    kucoinClient.on("error", err => ws.state = err);
    kucoinClient.on("connecting", data => ws.state = 'Connecting');
    kucoinClient.on("connected", data => ws.state = 'Connected');
    kucoinClient.on("disconnected", data => ws.state = 'Disconnected');
    kucoinClient.on("closed", () => {
      ws.state = 'Closed';
      ws.buyOrders = [];
      ws.sellOrders = [];
      ws.filteredBuyOrders = [];
      ws.filteredSellOrders = [];
    });
    kucoinClient.on("l2update", ({ asks, bids }, market) => {
      ws.buyOrders = updateOrdersArr(ws.buyOrders, asks);
      ws.sellOrders = updateOrdersArr(ws.sellOrders, bids, false);
      ws.filteredBuyOrders = drawOrdersArr(ws.buyOrders);
      ws.filteredSellOrders = drawOrdersArr(ws.sellOrders);
    });
    kucoinClient.subscribeLevel2Updates(ws.market);
  },

  reset: () => {
    kucoinClient.reconnect();
  },

  printOrderBook: () => {
    console.log(`  KUCOIN  BUY ORDERS    |  Market : ${ws.market.id}    `);
    console.table(ws.filteredBuyOrders);
    console.log(`  KUCOIN  SELL ORDERS   |  Market : ${ws.market.id}    `);
    console.table(ws.filteredSellOrders);
  }
};