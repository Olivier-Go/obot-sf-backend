import '../utils/env.js';
import { updateOrdersArr, drawOrdersArr } from '../utils/functions.js';
import { BittrexClient } from "ccxws";

const bittrexClient = new BittrexClient({
  apiKey: process.env.BITTREX_API_KEY,
  apiSecret: process.env.BITTREX_API_SECRET,
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

  run: (orderSize) => {
    console.log(orderSize);
    bittrexClient.on("error", err => ws.state = err);
    bittrexClient.on("connecting", data => ws.state = 'Connecting');
    bittrexClient.on("connected", data => ws.state = 'Connected');
    bittrexClient.on("disconnected", data => ws.state = 'Disconnected');
    bittrexClient.on("l2update", (l2update, market) => {
      ws.buyOrders = updateOrdersArr(ws.buyOrders, l2update.bids);
      ws.sellOrders = updateOrdersArr(ws.sellOrders, l2update.asks, false);
    });
    bittrexClient.subscribeLevel2Updates(market);

    ws.printResults();
  },

  printResults: () => {
    console.log(`========BITTREX==========`);
    console.log(`Etat : ${ws.state}`);
    console.log(`Market : ${market.id}`);
    console.log("******BUY ORDERS********");
    console.log(drawOrdersArr(ws.buyOrders));
    console.log("******SELL ORDERS*******");
    console.log(drawOrdersArr(ws.sellOrders));
    console.log(`========================`);
  }
};
