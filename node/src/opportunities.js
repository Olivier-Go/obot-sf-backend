import { isEmptyObj } from "./utils/functions.js";

export const updateBuySellDiff = (sellOrders, buyOrders, orderSize) => {
  let diff = {};
  let bestSellOrder = {};
  let bestBuyOrder = {};

  sellOrders.forEach((order, index) => {
    if (index === 0 && (parseFloat(order.size) > parseFloat(orderSize))) {
      bestSellOrder = { ...order };
    }
  });

  buyOrders.forEach((order, index) => {
    if (index === 0 && (parseFloat(order.size) > parseFloat(orderSize))) {
      bestBuyOrder = { ...order };
    }
  });

  const price = (bestSellOrder.price - bestBuyOrder.price).toFixed(4);
  const size = parseFloat(bestSellOrder.size) > parseFloat(bestBuyOrder.size) ? bestBuyOrder.size : bestSellOrder.size;
  const datetime = bestSellOrder.datetime > bestBuyOrder.datetime ? bestBuyOrder.datetime : bestSellOrder.datetime;
  const received = bestSellOrder.received > bestBuyOrder.received ? bestBuyOrder.received : bestSellOrder.received;

  if (!isNaN(price) && (parseFloat(size) >= parseFloat(orderSize))) {
    diff = {
      price,
      size,
      datetime,
      received
    };
  }

  return {
    'diff': [diff],
    'bestSellOrder': [bestSellOrder],
    'bestBuyOrder': [bestBuyOrder],
  }
};


export const updateBuySellOp = (buySellOp, buySellDiff, orderDiff, orderSize, ticker = null, buyMarket = null, sellMarket = null) => {
  if (!isEmptyObj(buySellDiff)) {
    buySellDiff.diff.forEach(tick => {
      if (tick.price >= orderDiff && tick.size >= orderSize) {
        // Ne pas compter les opérations pour le même datetime
        let validOp = true;
        if (buySellOp.history.length) {
          buySellOp.history.forEach(history => {
            if (!validOp) {
              return;
            }
            const opDatetime = Number((history.received/1000).toFixed(0));
            const diffDatetime = Number((buySellDiff.diff[0].received/1000).toFixed(0));
            if (opDatetime === diffDatetime) {
              validOp = false;
            }
          })
        }

        if (validOp) {
          let count = buySellOp.count + 1;
          let history = [tick, ...buySellOp.history];

          // Memory Heap : suppression de l'historique en mémoire
          if (count > 10) {
            history = history.slice(0, -1);
          }

          buySellOp = {
            count,
            'order': {},
            history,
          };

          // Envoi à l'API
          if (ticker && buyMarket && sellMarket) {
            const order = {
              ticker,
              'direction': 'Buy->Sell',
              buyMarket,
              sellMarket,
              'buyPrice': buySellDiff.bestBuyOrder[0].price,
              'sellPrice': buySellDiff.bestSellOrder[0].price,
              'size': tick.size,
              'priceDiff': tick.price,
              'received': (tick.received / 1000).toFixed(0)
            };
            buySellOp = {
              ...buySellOp,
              order
            };
          }
        }
      }
    });
  }
    
  return buySellOp;
};

export const updateSellBuyDiff = (buyOrders, sellOrders, orderSize) => {
  let diff = {};
  let bestBuyOrder = {};
  let bestSellOrder = {};

  buyOrders.forEach((order, index) => {
    if (index === 0 && (parseFloat(order.size) > parseFloat(orderSize))) {
      bestBuyOrder = { ...order };
    }
  });

  sellOrders.forEach((order, index) => {
    if (index === 0 && (parseFloat(order.size) > parseFloat(orderSize))) {
      bestSellOrder = { ...order };
    }
  });

  const price = (bestSellOrder.price - bestBuyOrder.price).toFixed(4);
  const size = parseFloat(bestSellOrder.size) < parseFloat(bestBuyOrder.size) ? bestSellOrder.size : bestBuyOrder.size;
  const datetime = bestSellOrder.datetime > bestBuyOrder.datetime ? bestBuyOrder.datetime : bestSellOrder.datetime;
  const received = bestSellOrder.received > bestBuyOrder.received ? bestBuyOrder.received : bestSellOrder.received;

  if (!isNaN(price) && (parseFloat(size) >= parseFloat(orderSize))) {
    diff = {
      price,
      size,
      datetime,
      received
    };
  }

  return {
    'diff': [diff],
    'bestBuyOrder': [bestBuyOrder],
    'bestSellOrder': [bestSellOrder],
  }
};

export const updateSellBuyOp = (sellBuyOp, sellBuyDiff, orderDiff, orderSize, ticker = null, buyMarket = null, sellMarket = null) => {
  if (!isEmptyObj(sellBuyDiff)) {
    sellBuyDiff.diff.forEach(tick => {
      if (tick.price >= orderDiff && tick.size >= orderSize) {
        // Ne pas compter les opérations pour le même datetime
        let validOp = true;
        if (sellBuyOp.history.length) {
          sellBuyOp.history.forEach(history => {
            if (!validOp) {
              return;
            }
            const opDatetime = Number((history.received/1000).toFixed(0));
            const diffDatetime = Number((sellBuyDiff.diff[0].received/1000).toFixed(0));
            if (opDatetime === diffDatetime) {
              validOp = false;
            }
          })
        }

        if (validOp) {
          let count = sellBuyOp.count + 1;
          let history = [tick, ...sellBuyOp.history];

          // Memory Heap : suppression de l'historique en mémoire
          if (count > 10) {
            history = history.slice(0, -1);
          }

          sellBuyOp = {
            count,
            'order': {},
            history
          };

          // Envoi à l'API
          if (ticker && buyMarket && sellMarket) {
            const order = {
              ticker,
              'direction': 'Sell->Buy',
              buyMarket,
              sellMarket,
              'buyPrice': sellBuyDiff.bestBuyOrder[0].price,
              'sellPrice': sellBuyDiff.bestSellOrder[0].price,
              'size': tick.size,
              'priceDiff': tick.price,
              'received': (tick.received/1000).toFixed(0)
            };
            sellBuyOp = {
              ...sellBuyOp,
              order
            };
          }
        }
      }
    });
  }

  return sellBuyOp;
};