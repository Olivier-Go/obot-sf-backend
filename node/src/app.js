import "./utils/env.js";
import { flush } from "log-buffer";
import { drawOrdersArr, isEmptyObj, startTime, memoryUsage } from "./utils/functions.js";
import { updateBuySellDiff, updateSellBuyDiff, updateBuySellOp, updateSellBuyOp } from "./opportunities.js";
import { apiFetchConnection, apiAddOpportunity } from "./requests.js";
import { exchange } from "./exchanges/exchange.js";
import { state } from "./state.js";

let exchange1Ws;
let exchange2Ws;

export const app = {
  init: () => {
    state.startTime = Date.now();
    exchange1Ws = exchange.loadWebsocket(1, process.env.EXCHANGE1);
    exchange2Ws = exchange.loadWebsocket(2, process.env.EXCHANGE2);
    exchange1Ws.run();
    exchange2Ws.run();
    exchange.loadOrderbook(process.env.EXCHANGE1, exchange1Ws);
    exchange.loadOrderbook(process.env.EXCHANGE2, exchange2Ws);
    exchange.initOrderbook();
    setInterval(() => {
      state.resetTime += 1;
    }, 1000);
  },

  printBanner: () => {
    console.log(`----------------------------------------------------------------------------`);
    console.log(` Threshold : ${state.threshold}  |  OrderSize : ${state.orderSize}  |  OrderDiff : ${state.orderDiff}`);
    const startedTime = startTime();
    console.log(` ${startedTime.date} ${startedTime.time} - Started : ${startedTime.since} - Mem : ${memoryUsage()} MB `);
    console.log(` API : ${process.env.API_STATE === 'enable' ? state.apiToken ? state.resetTime > 30 ? 'OK' : '...' : 'KO' : 'Disabled'} | ${process.env.EXCHANGE1} : ${exchange1Ws.state} | ${process.env.EXCHANGE2} : ${exchange2Ws.state}`);
    console.log(`----------------------------------------------------------------------------`);
  },

  printBuySellDiff: () => {
    console.log(`          DIFF  :   BUY ${process.env.EXCHANGE1} / SELL ${process.env.EXCHANGE2}                   `);
    state.buySellDiff1To2 = updateBuySellDiff(exchange2Ws.sellOrders, exchange1Ws.buyOrders, state.orderSize);
    console.table(drawOrdersArr(state.buySellDiff1To2.diff));
    console.log(`          DIFF  :   BUY ${process.env.EXCHANGE2} / SELL ${process.env.EXCHANGE1}                   `);
    state.buySellDiff2To1 = updateBuySellDiff(exchange1Ws.sellOrders, exchange2Ws.buyOrders, state.orderSize);
    console.table(drawOrdersArr(state.buySellDiff2To1.diff));
  },

  printSellBuyDiff: () => {
    console.log(`          DIFF  :   SELL ${process.env.EXCHANGE1} / BUY ${process.env.EXCHANGE2}                    `);
    state.sellBuyDiff1To2 = updateSellBuyDiff(exchange2Ws.buyOrders, exchange1Ws.sellOrders, state.orderSize);
    console.table(drawOrdersArr(state.sellBuyDiff1To2.diff));
    console.log(`          DIFF  :   SELL ${process.env.EXCHANGE2}  / BUY ${process.env.EXCHANGE1}                   `);
    state.sellBuyDiff2To1 = updateSellBuyDiff(exchange1Ws.buyOrders, exchange2Ws.sellOrders, state.orderSize);
    console.table(drawOrdersArr(state.sellBuyDiff2To1.diff));
  },

  buySellOp: (print = false, ticker = false) => {
    console.log(`          OP  :   BUY ${process.env.EXCHANGE1} / SELL ${process.env.EXCHANGE2}                     `);
    if (!isEmptyObj(state.buySellDiff1To2)) {
      const op = updateBuySellOp(state.buySellOp1To2, state.buySellDiff1To2, state.orderDiff, state.orderSize, ticker, process.env.EXCHANGE1, process.env.EXCHANGE2);
      //console.log(op)
      if (state.apiToken && ticker && !isEmptyObj(op.order) && state.resetTime > 30) {
        apiAddOpportunity(op);
      }
      if (print) {
        state.buySellOp1To2 = !isEmptyObj(op.order) && state.resetTime > 30 ? updateBuySellOp(state.buySellOp1To2, state.buySellDiff1To2, state.orderDiff, state.orderSize) : state.buySellOp1To2;
        console.log(state.buySellOp1To2.count);
        console.table(drawOrdersArr(state.buySellOp1To2.history, 1));
      }
    }
    console.log(`          OP  :   BUY ${process.env.EXCHANGE2}  / SELL ${process.env.EXCHANGE1}                   `);
    if (!isEmptyObj(state.buySellDiff2To1)) {
      const op = updateBuySellOp(state.buySellOp2To1, state.buySellDiff2To1, state.orderDiff, state.orderSize, ticker, process.env.EXCHANGE2, process.env.EXCHANGE1);
      //console.log(op)
      if (state.apiToken && ticker && !isEmptyObj(op.order) && state.resetTime > 30) {
        apiAddOpportunity(op);
      }
      if (print) {
        state.buySellOp2To1 = !isEmptyObj(op.order) && state.resetTime > 30 ? updateBuySellOp(state.buySellOp2To1, state.buySellDiff2To1, state.orderDiff, state.orderSize) : state.buySellOp2To1;
        console.log(state.buySellOp2To1.count);
        console.table(drawOrdersArr(state.buySellOp2To1.history, 1));
      }
    }
  },

  sellBuyOp: (print = false, ticker = false) => {
    console.log(`          OP  :   SELL ${process.env.EXCHANGE1}  / BUY ${process.env.EXCHANGE2}                   `);
    if (!isEmptyObj(state.sellBuyDiff1To2)) {
      const op = updateSellBuyOp(state.sellBuyOp1To2, state.sellBuyDiff1To2, state.orderDiff, state.orderSize, ticker, process.env.EXCHANGE2, process.env.EXCHANGE1);
      //console.log(op)
      if (state.apiToken && ticker && !isEmptyObj(op.order) && state.resetTime > 30) {
        apiAddOpportunity(op);
      }
      if (print) {
        state.sellBuyOp1To2 = !isEmptyObj(op.order) && state.resetTime > 30 ? updateSellBuyOp(state.sellBuyOp1To2, state.sellBuyDiff1To2, state.orderDiff, state.orderSize) : state.sellBuyOp1To2;
        console.log(state.sellBuyOp1To2.count);
        console.table(drawOrdersArr(state.sellBuyOp1To2.history, 1));
      }
    }
    console.log(`          OP  :   SELL ${process.env.EXCHANGE2} / BUY ${process.env.EXCHANGE1}                   `);
    if (!isEmptyObj(state.sellBuyDiff2To1)) {
      const op = updateSellBuyOp(state.sellBuyOp2To1, state.sellBuyDiff2To1, state.orderDiff, state.orderSize, ticker, process.env.EXCHANGE1, process.env.EXCHANGE2);
      //console.log(op)
      if (state.apiToken && ticker && !isEmptyObj(op.order) && state.resetTime > 30) {
        apiAddOpportunity(op);
      }
      if (print) {
        state.sellBuyOp2To1 = !isEmptyObj(op.order) && state.resetTime > 30 ? updateSellBuyOp(state.sellBuyOp2To1, state.sellBuyDiff2To1, state.orderDiff, state.orderSize) : state.sellBuyOp2To1;
        console.log(state.sellBuyOp2To1.count);
        console.table(drawOrdersArr(state.sellBuyOp2To1.history, 1));
      }
    }
  },

  reset: () => {
    exchange1Ws.reset();
    exchange2Ws.reset();
    return state.resetTime = 0;
  },

  draw: () => {
    console.clear();
    if (state.resetTime > 3600) app.reset(); // 1 heure
    app.printBanner();
    //exchange1Ws.printOrderBook();
    //exchange2Ws.printOrderBook();
    app.printBuySellDiff();
    app.buySellOp(true, state.ticker);
    app.printSellBuyDiff();
    app.sellBuyOp(true, state.ticker);
    flush();
  },

  start: (apiState) => {
    switch (apiState) {
      case "enable":
        apiFetchConnection();
        break;
      case "disable":
        app.init();
        app.run();
        break;
      default:
        console.warn(`Variable API_STATE non definie !`);
        process.exit(1);
    }
  },

  run: () => {
    state.interval = setInterval(app.draw, state.threshold);
  },

  stop: () => {
    clearInterval(state.interval);
  }
};

app.start(process.env.API_STATE);

