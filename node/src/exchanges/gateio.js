import './../utils/env.js';
import { updateOrdersArr, drawOrdersArr } from './../utils/functions.js';
import { GateioClient } from "ccxws";

const gateioClient = new GateioClient();

export const ws = {
  state: 'Disconnected',
  market: {
    id: process.env.GATEIO_MARKET_ID,
    base: process.env.GATEIO_MARKET_BASE,
    quote: process.env.GATEIO_MARKET_QUOTE,
  },
  buyOrders: [],
  sellOrders: [],
  filteredBuyOrders: [],
  filteredSellOrders: [],

  run: () => {
    gateioClient.on("error", err => ws.state = err);
    gateioClient.on("connecting", data => ws.state = 'Connecting');
    gateioClient.on("connected", data => ws.state = 'Connected');
    gateioClient.on("disconnected", data => ws.state = 'Disconnected');
    gateioClient.on("closed", () => {
      ws.state = 'Closed';
      ws.buyOrders = [];
      ws.sellOrders = [];
      ws.filteredBuyOrders = [];
      ws.filteredSellOrders = [];
    });
    gateioClient.on("l2update", ({ asks, bids }, market) => {
      ws.buyOrders = updateOrdersArr(ws.buyOrders, asks);
      ws.sellOrders = updateOrdersArr(ws.sellOrders, bids, false);
      ws.filteredBuyOrders = drawOrdersArr(ws.buyOrders);
      ws.filteredSellOrders = drawOrdersArr(ws.sellOrders);
    });
    gateioClient.subscribeLevel2Updates(ws.market);
  },

  reset: () => {
    gateioClient.reconnect();
  },

  printOrderBook: () => {
    console.log(`  GATE.IO  BUY ORDERS    |  Market : ${ws.market.id}    `);
    console.table(ws.filteredBuyOrders);
    console.log(`  GATE.IO  SELL ORDERS   |  Market : ${ws.market.id}    `);
    console.table(ws.filteredSellOrders);
  }
};