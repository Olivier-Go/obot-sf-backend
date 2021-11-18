export const isEmptyObj = (obj) => {
  return Object.keys(obj).length === 0;
};

export const timeAgo = (timestamp) => {
  const currentMs = Date.now();
  return (currentMs - timestamp) / 1000;
};

export const twoDigit = (number) => {
  return number.toLocaleString('fr-FR', { minimumIntegerDigits: 2, useGrouping: false });
};


export const updateOrdersArr = (arr, changes, buy = true) => {
  let result = [...arr];

  changes.forEach(change => {
    const existingOrder = result.find(({ price }) => price === change.price);
    if (existingOrder) {
      existingOrder.size = change.size;
      existingOrder.received = Date.now();
    } else {
      result.push({ ...change, received: Date.now() });
    }
  });

  result = result.filter(({ size }) => size > 0);
  result = buy ? result.sort((a, b) => parseFloat(b.price) - parseFloat(a.price)) : result.sort((a, b) => parseFloat(a.price) - parseFloat(b.price));

  return result;
};


export const drawOrdersArr = (arr, dept = 3) => {
  let result = [];

  for (let i = 0; i < dept; i++) {
    const order = arr[i];
    if (order && order.price && order.size) {
      result[i] = { 
        price: Number(order.price), 
        size: Number(order.size),
        datetime: Number(order.received),
        received: `${timeAgo(order.received).toFixed(1)}s ago` 
      }
    }
  }

  return result;
};


export const updateBuySellDiff = (sellOrders, buyOrders, orderSize) => {
  let diff = {};
  let bestSellOrder = {};
  let bestBuyOrder = {};

  sellOrders.forEach((order, index) => {
    if (index === 0 && order.size > orderSize) {
      bestSellOrder = { ...order };
    }
  });

  buyOrders.forEach((order, index) => {
    if (index === 0 && order.size > orderSize) {
      bestBuyOrder = { ...order };
    }
  });

  const price = (bestBuyOrder.price - bestSellOrder.price).toFixed(4);
  const size = bestSellOrder.size > bestBuyOrder.size ? bestBuyOrder.size : bestSellOrder.size;
  const datetime = bestSellOrder.datetime > bestBuyOrder.datetime ? bestBuyOrder.datetime : bestSellOrder.datetime;
  const received = bestSellOrder.received > bestBuyOrder.received ? bestBuyOrder.received : bestSellOrder.received;

  if (!isNaN(price) && size > 0) {
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


export const updateBuySellOp = (buySellOp, buySellDiff, orderDiff) => {
  if (!isEmptyObj(buySellDiff)) {
    buySellDiff.diff.forEach(tick => {
      if (tick.price >= orderDiff) {
        let count = buySellOp.count + 1;
        let history = [tick, ...buySellOp.history];

        if (count > 10) {
          history = history.slice(0, -1);
        }

        buySellOp = {
          count,
          history,
        };
        return buySellOp;
      }
    });
  }
    
  return buySellOp;
};