import './utils/env.js';
import { updateBuySellDiff, drawOrdersArr } from './utils/functions.js';
import { ws as kucoinWs } from "./exchanges/kucoin.js";
import { ws as bittrexWs } from "./exchanges/bittrex.js";

const app = {
  threshold: process.env.APP_THRESHOLD,
  orderSize: process.env.ORDER_SIZE,
  buySellDiff1: {},
  buySellDiff2: {},

  init: () => {
    kucoinWs.run();
    bittrexWs.run();
  },

  printBanner: () => {
    console.log(`-----------------------------------------------------------`);
    console.log(`  Threshold : ${app.threshold}  |  OrderSize : ${app.orderSize}`);
  },

  printBuySellDiff: () => {
    console.log(`          DIFF 1  :   BUY KUCOIN / SELL BITTREX                   `);
    app.buySellDiff1 = updateBuySellDiff(kucoinWs.sellOrders, bittrexWs.buyOrders, app.orderSize);
    console.table(drawOrdersArr(app.buySellDiff1.diff));
    console.log(`          DIFF 2  :   BUY BITTREX / SELL KUCOIN                   `);
    app.buySellDiff2 = updateBuySellDiff(bittrexWs.sellOrders, kucoinWs.buyOrders, app.orderSize);
    console.table(drawOrdersArr(app.buySellDiff2.diff));
  },

  draw: () => {
    console.clear();
    app.printBanner();
    kucoinWs.printOrderBook();
    bittrexWs.printOrderBook();
    app.printBuySellDiff();
  },

  run: () => {
    app.init();
    setInterval(app.draw, app.threshold);
  }
};

app.run();

