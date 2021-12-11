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
import axios from "axios";

const app = {
  ticker: 1, // FLUX/USDT
  interval: null,
  startTime: 0,
  resetTime: 0,
  memHeapUsed: 0,
  apiToken: null,
  threshold: process.env.APP_THRESHOLD,
  orderSize: process.env.ORDER_SIZE,
  orderDiff: process.env.ORDER_DIFF,
  buySellDiffKtoB: {},
  buySellDiffBtoK: {},
  buySellOpKtoB: {
    'count' : 0,
    'order': {},
    'history': [],
  },
  buySellOpBtoK: {
    'count' : 0,
    'order': {},
    'history': [],
  },
  sellBuyDiffKtoB: {},
  sellBuyDiffBtoK: {},
  sellBuyOpKtoB: {
    'count' : 0,
    'order': {},
    'history': [],
  },
  sellBuyOpBtoK: {
    'count' : 0,
    'order': {},
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
    const date = `${twoDigit(today.getDate())}/${twoDigit(today.getMonth()+1)}/${today.getFullYear()}`;
    const time = `${twoDigit(today.getHours())}:${twoDigit(today.getMinutes())}:${twoDigit(today.getSeconds())}`;
    const since = new Date(timeAgo(app.startTime) * 1000).toISOString().substr(11, 8);
    app.used = Math.round(process.memoryUsage().heapUsed / 1024 / 1024 * 100) / 100;

    console.log(` ${date} ${time} - Started : ${since} -  Mem : ${app.used} MB `);
    console.log(` Connexion API : ${app.apiToken ? 'OK' : 'KO'}             `);
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

  buySellOp: (print = false, ticker = false) => {
    console.log(`          OP  :   BUY KUCOIN / SELL BITTREX                   `);
    if (!isEmptyObj(app.buySellDiffKtoB)) {
      const op = updateBuySellOp(app.buySellOpKtoB, app.buySellDiffKtoB, app.orderDiff, ticker, 2, 1); // 1 = Bittrex, 2 = Kucoin
      //console.log(op)
      if (app.apiToken && ticker && !isEmptyObj(op.order)) {
        app.stop();
        axios({
          method: 'post',
          url: `${process.env.API_URL}/api/arbitrage/opportunity/add`,
          headers: {'Authorization': `Bearer ${app.apiToken}`},
          data: { ...op.order },
        })
            .then((response) => {
              //console.log(response.data);
              if (response.status === 201) {
                app.run();
              }
            })
            .catch((error) => {
              console.warn(error.response.data);
            })
            .finally(() => {
            });
      }
      if (print) {
        app.buySellOpKtoB = !isEmptyObj(op.order) ? updateBuySellOp(app.buySellOpKtoB, app.buySellDiffKtoB, app.orderDiff) : app.buySellOpKtoB;
        console.log(app.buySellOpKtoB.count);
        console.table(drawOrdersArr(app.buySellOpKtoB.history, 1));
      }
    }
    console.log(`          OP  :   BUY BITTREX / SELL KUCOIN                   `);
    if (!isEmptyObj(app.buySellDiffBtoK)) {
      const op = updateBuySellOp(app.buySellOpBtoK, app.buySellDiffBtoK, app.orderDiff, ticker, 1, 2); // 1 = Bittrex, 2 = Kucoin
      //console.log(op)
      if (app.apiToken && ticker && !isEmptyObj(op.order)) {
        app.stop();
        axios({
          method: 'post',
          url: `${process.env.API_URL}/api/arbitrage/opportunity/add`,
          headers: {'Authorization': `Bearer ${app.apiToken}`},
          data: { ...op.order },
        })
            .then((response) => {
              //console.log(response.data);
              if (response.status === 201) {
                app.run();
              }
            })
            .catch((error) => {
              console.warn(error.response.data);
            })
            .finally(() => {
            });
      }
      if (print) {
        app.buySellOpBtoK = !isEmptyObj(op.order) ? updateBuySellOp(app.buySellOpBtoK, app.buySellDiffBtoK, app.orderDiff) : app.buySellOpBtoK;
        console.log(app.buySellOpBtoK.count);
        console.table(drawOrdersArr(app.buySellOpBtoK.history, 1));
      }
    }
  },

  sellBuyOp: (print = false, ticker = false) => {
    console.log(`          OP  :   SELL KUCOIN / BUY BITTREX                   `);
    if (!isEmptyObj(app.sellBuyDiffKtoB)) {
      const op = updateSellBuyOp(app.sellBuyOpKtoB, app.sellBuyDiffKtoB, app.orderDiff, ticker, 1, 2); // 1 = Bittrex, 2 = Kucoin
      //console.log(op)
      if (app.apiToken && ticker && !isEmptyObj(op.order)) {
        app.stop();
        axios({
          method: 'post',
          url: `${process.env.API_URL}/api/arbitrage/opportunity/add`,
          headers: {'Authorization': `Bearer ${app.apiToken}`},
          data: { ...op.order },
        })
          .then((response) => {
            //console.log(response.data);
            if (response.status === 201) {
              app.run();
            }
          })
          .catch((error) => {
            console.warn(error.response.data);
          })
          .finally(() => {
          });
      }
      if (print) {
        app.sellBuyOpKtoB = !isEmptyObj(op.order) ? updateSellBuyOp(app.sellBuyOpKtoB, app.sellBuyDiffKtoB, app.orderDiff) : app.sellBuyOpKtoB;
        console.log(app.sellBuyOpKtoB.count);
        console.table(drawOrdersArr(app.sellBuyOpKtoB.history, 1));
      }
    }
    console.log(`          OP  :   SELL BITTREX / BUY KUCOIN                   `);
    if (!isEmptyObj(app.sellBuyDiffBtoK)) {
      const op = updateSellBuyOp(app.sellBuyOpBtoK, app.sellBuyDiffBtoK, app.orderDiff, ticker, 2, 1); // 1 = Bittrex, 2 = Kucoin
      //console.log(op)
      if (app.apiToken && ticker && !isEmptyObj(op.order)) {
        app.stop();
        axios({
          method: 'post',
          url: `${process.env.API_URL}/api/arbitrage/opportunity/add`,
          headers: {'Authorization': `Bearer ${app.apiToken}`},
          data: { ...op.order },
        })
            .then((response) => {
              //console.log(response.data);
              if (response.status === 201) {
                app.run();
              }
            })
            .catch((error) => {
              console.warn(error.response.data);
            })
            .finally(() => {
            });
      }
      if (print) {
        app.sellBuyOpBtoK = !isEmptyObj(op.order) ? updateSellBuyOp(app.sellBuyOpBtoK, app.sellBuyDiffBtoK, app.orderDiff) : app.sellBuyOpBtoK;
        console.log(app.sellBuyOpBtoK.count);
        console.table(drawOrdersArr(app.sellBuyOpBtoK.history, 1));
      }
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
    app.buySellOp(true, app.ticker);
    app.printSellBuyDiff();
    app.sellBuyOp(true, app.ticker);
  },

  start: (api = false) => {
    if (api) {
      axios({
        method: 'post',
        url: `${process.env.API_URL}/api/login_check`,
        data: {
          'username': process.env.API_USERNAME,
          'password': process.env.API_PASSWORD,
        },
      })
        .then((response) => {
          app.apiToken = response.data.token;
          app.init();
          app.run();
        })
        .catch((error) => {
          console.warn(error.response.data);
        })
        .finally(() => {
        });
    }
    else {
      app.init();
      app.run();
    }
  },

  run: () => {
    app.interval = setInterval(app.draw, app.threshold);
  },

  stop: () => {
    clearInterval(app.interval);
  }
};

app.start(true);

