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
      ws.buyOrders = updateOrdersArr(ws.buyOrders, bids);
      ws.sellOrders = updateOrdersArr(ws.sellOrders, asks, false);
      ws.filteredBuyOrders = drawOrdersArr(ws.buyOrders);
      ws.filteredSellOrders = drawOrdersArr(ws.sellOrders);
    });
    kucoinClient.subscribeLevel2Updates(ws.market);
  },

  reset: () => {
    kucoinClient.reconnect();
  },

  printOrderBook: () => {
    console.log(`-----------------------------------------------------------`);
    console.log(`  KUCOIN  |  State : ${ws.state}  |  Market : ${ws.market.id}`);
    console.log(`-----------------------------------------------------------`);
    console.log(`                      BUY ORDERS                         `);
    console.table(ws.filteredBuyOrders);
    console.log(`                      SELL ORDERS                         `);
    console.table(ws.filteredSellOrders);
  }
};