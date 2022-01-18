import { state } from "./../state.js";

export const isEmptyObj = (obj) => (
  Object.keys(obj).length === 0
);

export const timeAgo = (timestamp) => (
  (Date.now() - timestamp) / 1000
);

export const twoDigit = (number) => (
  number.toLocaleString('fr-FR', { minimumIntegerDigits: 2, useGrouping: false })
);

export const startTime = () => {
  const today = new Date();
  const date = `${twoDigit(today.getDate())}/${twoDigit(today.getMonth()+1)}/${today.getFullYear()}`;
  const time = `${twoDigit(today.getHours())}:${twoDigit(today.getMinutes())}:${twoDigit(today.getSeconds())}`;
  let since = new Date(timeAgo(state.startTime) * 1000).toISOString();
  const days = new Date(timeAgo(state.startTime) * 1000).toISOString().substring(8, 10);
  const hours = new Date(timeAgo(state.startTime) * 1000).toISOString().substring(11, 19);
  since = `${Number(days) - 1}D-${hours}`
  return {
    date,
    time,
    since
  }
};

export const memoryUsage = () => (
  Math.round(process.memoryUsage().heapUsed / 1024 / 1024 * 100) / 100
);

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
  result = buy ?
      result.sort((a, b) => parseFloat(a.price) - parseFloat(b.price))
      :
      result.sort((a, b) => parseFloat(b.price) - parseFloat(a.price));

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