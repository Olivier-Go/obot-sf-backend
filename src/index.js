import './utils/env.js';
import { updateBuySellDiff, drawOrdersArr, isEmptyObj, updateBuySellOp } from './utils/functions.js';
import { ws as kucoinWs } from "./exchanges/kucoin.js";
import { ws as bittrexWs } from "./exchanges/bittrex.js";

const app = {
  threshold: process.env.APP_THRESHOLD,
  orderSize: process.env.ORDER_SIZE,
  orderDiff: process.env.ORDER_DIFF,
  buySellDiffKtoB: {},
  buySellDiffBtoK: {},
  buySellOpKtoB: {
    'count' : 0,
    'history': [],
  },
  buySellOpBtoK: {
    'count' : 0,
    'history': [],
  },

  init: () => {
    kucoinWs.run();
    bittrexWs.run();
  },

  printBanner: () => {
    console.log(`-----------------------------------------------------------`);
    console.log(` Threshold : ${app.threshold}  |  OrderSize : ${app.orderSize}  |  OrderDiff : ${app.orderDiff}`);
  },

  printBuySellDiff: () => {
    console.log(`          DIFF  :   BUY KUCOIN / SELL BITTREX                   `);
    app.buySellDiffKtoB = updateBuySellDiff(kucoinWs.sellOrders, bittrexWs.buyOrders, app.orderSize);
    console.table(drawOrdersArr(app.buySellDiffKtoB.diff));
    console.log(`          DIFF  :   BUY BITTREX / SELL KUCOIN                   `);
    app.buySellDiffBtoK = updateBuySellDiff(bittrexWs.sellOrders, kucoinWs.buyOrders, app.orderSize);
    console.table(drawOrdersArr(app.buySellDiffBtoK.diff));
  },

  printBuySellOp: () => {
    console.log(`          OP  :   BUY KUCOIN / SELL BITTREX                   `);
    if (!isEmptyObj(app.buySellDiffKtoB)) {
      app.buySellOpKtoB = updateBuySellOp(app.buySellOpKtoB, app.buySellDiffKtoB, app.orderDiff);
      console.log(app.buySellOpKtoB.count);
      console.table(drawOrdersArr(app.buySellOpKtoB.history, 10));
    }
    console.log(`          OP  :   BUY BITTREX / SELL KUCOIN                   `);
    if (!isEmptyObj(app.buySellDiffBtoK)) {
      app.buySellOpBtoK = updateBuySellOp(app.buySellOpBtoK, app.buySellDiffBtoK, app.orderDiff);
      console.log(app.buySellOpBtoK.count);
      console.table(drawOrdersArr(app.buySellOpBtoK.history, 10));
    }
  },

  draw: () => {
    console.clear();
    app.printBanner();
    //kucoinWs.printOrderBook();
    //bittrexWs.printOrderBook();
    app.printBuySellDiff();
    app.printBuySellOp();
  },

  run: () => {
    app.init();
    setInterval(app.draw, app.threshold);
  }
};

app.run();

