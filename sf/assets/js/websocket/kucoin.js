import { subscribe, interval } from './utils';

export const KucoinWs = {
    loadOrderBook: async (endPoint, symbol, connectCB, tickerCB, level2CB) => {
        const w = await new WebSocket(endPoint);
        w.onmessage = (msg) => {
            let msg_data = JSON.parse(msg.data);
            if (msg_data.type === 'error') {
                console.warn(msg);
            }
            if (msg_data.type === 'welcome') {
                // Add heartbeat
                interval.make(w);
                // Subscribe
                w.send(subscribe('/market/ticker:', symbol));
                w.send(subscribe('/spotMarket/level2Depth5:', symbol));
                connectCB(w);
            }
            if (msg_data.type === 'message') {
                if (msg_data.subject === 'trade.ticker') {
                    tickerCB(msg_data.data.price);
                }
                if (msg_data.subject === 'level2') {
                    level2CB(KucoinWs.drawOrderBook(msg_data.data));
                }
            }
        }
        w.onclose = () => interval.clearAll();
        return w;
    },

    drawOrderBook: (data, dept = 3) => {
        let result = {};
        let asks = [];
        let bids = [];

        data.asks.forEach((element, index) => {
            const order = {
                price: element[0],
                size: element[1],
            }
            if (index < dept) {
                asks[index] = `<tr>
                <td class="price-sell">${Number(order.price)}</td>
                    <td>${Number(order.size)}</td>
                    <td>${(Number(order.price) * Number(order.size)).toFixed(4)}</td>
                </tr>`;
            }
        })

        data.bids.forEach((element, index) => {
            const order = {
                price: element[0],
                size: element[1],
            }
            if (index < dept) {
                bids[index] = `<tr>
                <td class="price-buy">${Number(order.price)}</td>
                    <td>${Number(order.size)}</td>
                    <td>${(Number(order.price) * Number(order.size)).toFixed(4)}</td>
                </tr>`
                ;
            }
        })

        result.asks = asks.reverse();
        result.bids = bids;
        return result;
    }
}
