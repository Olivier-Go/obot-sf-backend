import '../utils/env.js';
import { updateOrdersArr, drawOrdersArr } from '../utils/functions.js';
import { BittrexClient } from "ccxws";

const bittrexClient = new BittrexClient({
  apiKey: process.env.BITTREX_API_KEY,
  apiSecret: process.env.BITTREX_API_SECRET,
});

export const ws = {
  state: 'Disconnected',
  market: {
    id: process.env.BITTREX_MARKET_ID,
    base: process.env.BITTREX_MARKET_BASE,
    quote: process.env.BITTREX_MARKET_QUOTE,
  },
  buyOrders: [],
  sellOrders: [],
  filteredBuyOrders: [],
  filteredSellOrders: [],

  run: () => {
    bittrexClient.on("error", err => ws.state = err);
    bittrexClient.on("connecting", data => ws.state = 'Connecting');
    bittrexClient.on("connected", data => ws.state = 'Connected');
    bittrexClient.on("disconnected", data => ws.state = 'Disconnected');
    bittrexClient.on("closed", () => {
      ws.state = 'Closed';
      ws.buyOrders = [];
      ws.sellOrders = [];
      ws.filteredBuyOrders = [];
      ws.filteredSellOrders = [];
    });
    bittrexClient.on("l2update", (l2update, market) => {
      ws.buyOrders = updateOrdersArr(ws.buyOrders, l2update.bids);
      ws.sellOrders = updateOrdersArr(ws.sellOrders, l2update.asks, false);
      ws.filteredBuyOrders = drawOrdersArr(ws.buyOrders);
      ws.filteredSellOrders = drawOrdersArr(ws.sellOrders);
    });
    bittrexClient.subscribeLevel2Updates(ws.market);
  },

  reset: () => {
    bittrexClient.reconnect();
  },

  printOrderBook: () => {
    console.log(`-----------------------------------------------------------`);
    console.log(`  BITTREX  |  State : ${ws.state}  |  Market : ${ws.market.id}`);
    console.log(`-----------------------------------------------------------`);
    console.log(`                      BUY ORDERS                         `);
    console.table(ws.filteredBuyOrders);
    console.log(`                      SELL ORDERS                         `);
    console.table(ws.filteredSellOrders);
  }
};
