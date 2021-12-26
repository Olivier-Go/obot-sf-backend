import "./utils/env.js";
import { flush } from "log-buffer";
import { drawOrdersArr, isEmptyObj, startTime, memoryUsage } from "./utils/functions.js";
import { updateBuySellDiff, updateSellBuyDiff, updateBuySellOp, updateSellBuyOp } from "./opportunities.js";
import { apiFetchConnection, apiAddOpportunity } from "./requests.js";
import { state } from "./state.js";
import { ws as kucoinWs } from "./exchanges/kucoin.js";
import { ws as bittrexWs } from "./exchanges/bittrex.js";
import { ws as binanceWs } from "./exchanges/binance.js";

export const app = {
  init: () => {
    state.startTime = Date.now();
    kucoinWs.run();
    bittrexWs.run();
    binanceWs.run();
    setInterval(() => {
      state.resetTime += 1;
    }, 1000);
  },

  printBanner: () => {
    console.log(`----------------------------------------------------------------------------`);
    console.log(` Threshold : ${state.threshold}  |  OrderSize : ${state.orderSize}  |  OrderDiff : ${state.orderDiff}`);
    const startedTime = startTime();
    console.log(` ${startedTime.date} ${startedTime.time} - Started : ${startedTime.since} -  Mem : ${memoryUsage()} MB `);
    console.log(` API : ${state.apiToken ? 'OK' : 'KO'} | Bittrex : ${bittrexWs.state} | Kucoin : ${kucoinWs.state} | Binance : ${binanceWs.state}`);
    console.log(`----------------------------------------------------------------------------`);
  },

  printBuySellDiff: () => {
    console.log(`          DIFF  :   BUY BITTREX / SELL BINANCE                   `);
    state.buySellDiffBittrexToBinance = updateBuySellDiff(bittrexWs.sellOrders, binanceWs.buyOrders, state.orderSize);
    console.table(drawOrdersArr(state.buySellDiffBittrexToBinance.diff));
    console.log(`          DIFF  :   BUY BINANCE / SELL BITTREX                   `);
    state.buySellDiffBinanceToBittrex = updateBuySellDiff(binanceWs.sellOrders, bittrexWs.buyOrders, state.orderSize);
    console.table(drawOrdersArr(state.buySellDiffBinanceToBittrex.diff));
  },

  printSellBuyDiff: () => {
    console.log(`          DIFF  :   SELL BITTREX / BUY BINANCE                   `);
    state.sellBuyDiffBittrexToBinance = updateSellBuyDiff(bittrexWs.buyOrders, binanceWs.sellOrders, state.orderSize);
    console.table(drawOrdersArr(state.sellBuyDiffBittrexToBinance.diff));
    console.log(`          DIFF  :   SELL BINANCE / BUY BITTREX                   `);
    state.sellBuyDiffBinanceToBittrex = updateSellBuyDiff(binanceWs.buyOrders, bittrexWs.sellOrders, state.orderSize);
    console.table(drawOrdersArr(state.sellBuyDiffBinanceToBittrex.diff));
  },

  buySellOp: (print = false, ticker = false) => {
    console.log(`          OP  :   BUY BITTREX / SELL BINANCE                   `);
    if (!isEmptyObj(state.buySellDiffBittrexToBinance)) {
      const op = updateBuySellOp(state.buySellOpBittrexToBinance, state.buySellDiffBittrexToBinance, state.orderDiff, ticker, 1, 3); // 3 = Binance, 1 = Bittrex
      //console.log(op)
      if (state.apiToken && ticker && !isEmptyObj(op.order)) {
        apiAddOpportunity(op);
      }
      if (print) {
        state.buySellOpBittrexToBinance = !isEmptyObj(op.order) ? updateBuySellOp(state.buySellOpBittrexToBinance, state.buySellDiffBittrexToBinance, state.orderDiff) : state.buySellOpBittrexToBinance;
        console.log(state.buySellOpBittrexToBinance.count);
        console.table(drawOrdersArr(state.buySellOpBittrexToBinance.history, 1));
      }
    }
    console.log(`          OP  :   BUY BINANCE / SELL BITTREX                   `);
    if (!isEmptyObj(state.buySellDiffBinanceToBittrex)) {
      const op = updateBuySellOp(state.buySellOpBinanceToBittrex, state.buySellDiffBinanceToBittrex, state.orderDiff, ticker, 3, 1); // 3 = Binance, 1 = Bittrex
      //console.log(op)
      if (state.apiToken && ticker && !isEmptyObj(op.order)) {
        apiAddOpportunity(op);
      }
      if (print) {
        state.buySellOpBinanceToBittrex = !isEmptyObj(op.order) ? updateBuySellOp(state.buySellOpBinanceToBittrex, state.buySellDiffBinanceToBittrex, state.orderDiff) : state.buySellOpBinanceToBittrex;
        console.log(state.buySellOpBinanceToBittrex.count);
        console.table(drawOrdersArr(state.buySellOpBinanceToBittrex.history, 1));
      }
    }
  },

  sellBuyOp: (print = false, ticker = false) => {
    console.log(`          OP  :   SELL BITTREX / BUY BINANCE                   `);
    if (!isEmptyObj(state.sellBuyDiffBittrexToBinance)) {
      const op = updateSellBuyOp(state.sellBuyOpBittrexToBinance, state.sellBuyDiffBittrexToBinance, state.orderDiff, ticker, 3, 1); // 3 = Binance, 1 = Bittrex
      //console.log(op)
      if (state.apiToken && ticker && !isEmptyObj(op.order)) {
        apiAddOpportunity(op);
      }
      if (print) {
        state.sellBuyOpBittrexToBinance = !isEmptyObj(op.order) ? updateSellBuyOp(state.sellBuyOpBittrexToBinance, state.sellBuyDiffBittrexToBinance, state.orderDiff) : state.sellBuyOpBittrexToBinance;
        console.log(state.sellBuyOpBittrexToBinance.count);
        console.table(drawOrdersArr(state.sellBuyOpBittrexToBinance.history, 1));
      }
    }
    console.log(`          OP  :   SELL BINANCE / BUY BITTREX                   `);
    if (!isEmptyObj(state.sellBuyDiffBinanceToBittrex)) {
      const op = updateSellBuyOp(state.sellBuyOpBinanceToBittrex, state.sellBuyDiffBinanceToBittrex, state.orderDiff, ticker, 1, 3); // 3 = Binance, 1 = Bittrex
      //console.log(op)
      if (state.apiToken && ticker && !isEmptyObj(op.order)) {
        apiAddOpportunity(op);
      }
      if (print) {
        state.sellBuyOpBinanceToBittrex = !isEmptyObj(op.order) ? updateSellBuyOp(state.sellBuyOpBinanceToBittrex, state.sellBuyDiffBinanceToBittrex, state.orderDiff) : state.sellBuyOpBinanceToBittrex;
        console.log(state.sellBuyOpBinanceToBittrex.count);
        console.table(drawOrdersArr(state.sellBuyOpBinanceToBittrex.history, 1));
      }
    }
  },

  reset: () => {
    kucoinWs.reset();
    bittrexWs.reset();
    binanceWs.reset();
    return state.resetTime = 0;
  },

  draw: () => {
    console.clear();
    if (state.resetTime > 600) app.reset(); // 10 minutes
    app.printBanner();
    //kucoinWs.printOrderBook();
    //bittrexWs.printOrderBook();
    //binanceWs.printOrderBook();
    app.printBuySellDiff();
    app.buySellOp(true, state.ticker);
    app.printSellBuyDiff();
    app.sellBuyOp(true, state.ticker);
    flush();
  },

  start: (api = false) => {
    if (api) {
      apiFetchConnection(true);
    }
    else {
      app.init();
      app.run();
    }
  },

  run: () => {
    state.interval = setInterval(app.draw, state.threshold);
  },

  stop: () => {
    clearInterval(state.interval);
  }
};

app.start(false);

