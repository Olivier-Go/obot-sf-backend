import './utils/env.js';
import { ws as kucoinWs } from "./exchanges/kucoin.js";
import { ws as bittrexWs } from "./exchanges/bittrex.js";

const orderSize = process.env.MARKET_ORDER_SIZE;

const myCallback = () => {
  console.clear();
  kucoinWs.run();
  bittrexWs.run(orderSize);
};

setInterval(myCallback, 1200);