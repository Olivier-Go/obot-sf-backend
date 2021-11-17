export const isEmptyObj = (obj) => {
  return Object.keys(obj).length === 0;
}


export const updateOrdersArr = (arr, changes, buy = true) => {
  let result = [...arr];

  changes.forEach(change => {
    const existingOrder = result.find(({ price }) => price === change.price);
    if (existingOrder) {
      existingOrder.size = change.size;
      existingOrder.received = new Date().getTime();
    } else {
      result.push({ ...change, received: new Date().getTime() });
    }
  });

  result = result.filter(({ size }) => size > 0);
  result = buy ? result.sort((a, b) => parseFloat(b.price) - parseFloat(a.price)) : result.sort((a, b) => parseFloat(a.price) - parseFloat(b.price));

  return result;
};


export const drawOrdersArr = (arr, dept = 3) => {
  let result = [];
  const currentMs = new Date().getTime();

  for (let i = 0; i < dept; i++) {
    const order = arr[i];
    if (order && order.price && order.size) {
      const secsAgo = (currentMs - order.received) / 1000;
      result[i] = { 
        price: Number(order.price), 
        size: Number(order.size),
        datetime: Number(order.received),
        received: `${secsAgo.toFixed(1)}s ago` }
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
        buySellOp = {
            'count': buySellOp.count + 1,
            'history': [...buySellOp.history, tick],
        };
        return buySellOp;
      }
    });
  }
    
  return buySellOp;
};