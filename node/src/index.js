import './utils/env.js';
import { 
  updateBuySellDiff,
  updateSellBuyDiff,
  drawOrdersArr, 
  isEmptyObj, 
  timeAgo,
  twoDigit,
  updateBuySellOp,
  updateSellBuyOp
} from './utils/functions.js';
import { ws as kucoinWs } from "./exchanges/kucoin.js";
import { ws as bittrexWs } from "./exchanges/bittrex.js";

const app = {
  startTime: 0,
  resetTime: 0,
  memHeapUsed: 0,
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
  sellBuyDiffKtoB: {},
  sellBuyDiffBtoK: {},
  sellBuyOpKtoB: {
    'count' : 0,
    'history': [],
  },
  sellBuyOpBtoK: {
    'count' : 0,
    'history': [],
  },

  init: () => {
    app.startTime = Date.now();
    kucoinWs.run();
    bittrexWs.run();
    setInterval(() => {
      app.resetTime += 1;
    }, 1000);
  },

  printBanner: () => {
    console.log(`-----------------------------------------------------------`);
    console.log(` Threshold : ${app.threshold}  |  OrderSize : ${app.orderSize}  |  OrderDiff : ${app.orderDiff}`);

    const today = new Date();
    const date = `${today.getDate()}/${today.getMonth()+1}/${today.getFullYear()}`;
    const time = `${twoDigit(today.getHours())}:${twoDigit(today.getMinutes())}:${twoDigit(today.getSeconds())}`;
    const since = new Date(timeAgo(app.startTime) * 1000).toISOString().substr(11, 8);
    app.used = Math.round(process.memoryUsage().heapUsed / 1024 / 1024 * 100) / 100;

    console.log(` ${date} ${time} - Started : ${since} -  Mem : ${app.used} MB `);
    console.log(`-----------------------------------------------------------`);
  },

  printBuySellDiff: () => {
    console.log(`          DIFF  :   BUY KUCOIN / SELL BITTREX                   `);
    console.log(`                    ${kucoinWs.state}  / ${bittrexWs.state}     `);
    app.buySellDiffKtoB = updateBuySellDiff(kucoinWs.sellOrders, bittrexWs.buyOrders, app.orderSize);
    console.table(drawOrdersArr(app.buySellDiffKtoB.diff));
    console.log(`          DIFF  :   BUY BITTREX / SELL KUCOIN                   `);
    console.log(`                    ${bittrexWs.state}  / ${kucoinWs.state}     `);
    app.buySellDiffBtoK = updateBuySellDiff(bittrexWs.sellOrders, kucoinWs.buyOrders, app.orderSize);
    console.table(drawOrdersArr(app.buySellDiffBtoK.diff));
  },

  printSellBuyDiff: () => {
    console.log(`          DIFF  :   SELL KUCOIN / BUY BITTREX                   `);
    console.log(`                    ${kucoinWs.state}  / ${bittrexWs.state}     `);
    app.sellBuyDiffKtoB = updateSellBuyDiff(kucoinWs.buyOrders, bittrexWs.sellOrders, app.orderSize);
    console.table(drawOrdersArr(app.sellBuyDiffKtoB.diff));
    console.log(`          DIFF  :   SELL BITTREX / BUY KUCOIN                   `);
    console.log(`                    ${bittrexWs.state}  / ${kucoinWs.state}     `);
    app.sellBuyDiffBtoK = updateSellBuyDiff(bittrexWs.buyOrders, kucoinWs.sellOrders, app.orderSize);
    console.table(drawOrdersArr(app.sellBuyDiffBtoK.diff));
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

  printSellBuyOp: () => {
    console.log(`          OP  :   SELL KUCOIN / BUY BITTREX                   `);
    if (!isEmptyObj(app.sellBuyDiffKtoB)) {
      app.sellBuyOpKtoB = updateSellBuyOp(app.sellBuyOpKtoB, app.sellBuyDiffKtoB, app.orderDiff);
      console.log(app.sellBuyOpKtoB.count);
      console.table(drawOrdersArr(app.sellBuyOpKtoB.history, 10));
    }
    console.log(`          OP  :   SELL BITTREX / BUY KUCOIN                   `);
    if (!isEmptyObj(app.sellBuyDiffBtoK)) {
      app.sellBuyOpBtoK = updateSellBuyOp(app.sellBuyOpBtoK, app.sellBuyDiffBtoK, app.orderDiff);
      console.log(app.sellBuyOpBtoK.count);
      console.table(drawOrdersArr(app.sellBuyOpBtoK.history, 10));
    }
  },

  reset: () => {
    kucoinWs.reset();
    bittrexWs.reset();
    return app.resetTime = 0;
  },

  draw: () => {
    console.clear();
    if (app.resetTime > 600) app.reset(); // 10 minutes
    app.printBanner();
    //kucoinWs.printOrderBook();
    //bittrexWs.printOrderBook();
    app.printBuySellDiff();
    app.printBuySellOp();
    app.printSellBuyDiff();
    app.printSellBuyOp();
  },

  run: () => {
    app.init();
    setInterval(app.draw, app.threshold);
  }
};

app.run();

