import '../utils/env.js';
import { updateOrdersArr, drawOrdersArr } from '../utils/functions.js';
import { KucoinClient } from "ccxws";

const kucoinClient = new KucoinClient({
  apiKey: process.env.KUCOIN_API_KEY,
  apiSecret: process.env.KUCOIN_API_SECRET,
});

const market = {
  id: "FLUX-USDT",
  base: "FLUX",
  quote: "USDT"
};

export const ws = {
  state: 'Disconnected',
  buyOrders: [],
  sellOrders: [],

  run: () => {
    kucoinClient.on("error", err => ws.state = err);
    kucoinClient.on("connecting", data => ws.state = 'Connecting');
    kucoinClient.on("connected", data => ws.state = 'Connected');
    kucoinClient.on("disconnected", data => ws.state = 'Disconnected');
    kucoinClient.on("l2update", ({ asks, bids }, market) => {
      ws.buyOrders = updateOrdersArr(ws.buyOrders, bids);
      ws.sellOrders = updateOrdersArr(ws.sellOrders, asks, false);
    });

    kucoinClient.subscribeLevel2Updates(market);

    ws.printResults();
  },

  printResults: () => {
    console.log(`========KUCOIN==========`);
    console.log(`Etat : ${ws.state}`);
    console.log(`Market : ${market.id}`);
    console.log("******BUY ORDERS********");
    console.log(drawOrdersArr(ws.buyOrders));
    console.log("******SELL ORDERS*******");
    console.log(drawOrdersArr(ws.sellOrders));
    console.log(`========================`);
  }
};