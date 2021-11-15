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
  return buy ? result.sort((a, b) => parseFloat(b.price) - parseFloat(a.price)) : result.sort((a, b) => parseFloat(a.price) - parseFloat(b.price));
};

export const drawOrdersArr = (arr) => {
  let result = [];
  const currentMs = new Date().getTime();
  for (let i = 0; i < 5; i++) {
    const order = arr[i];
    if (order) {
      const secsAgo = (currentMs - order.received) / 1000;
      result[i] = `${order.price} for ${order.size} (${secsAgo.toFixed(1)}s ago )`;
    }
  }
  return result;
};
