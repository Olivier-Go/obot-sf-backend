import { subscribe, interval } from './utils';

export const KucoinWs = {
    loadOrderBook: async (endPoint, symbol, connectCB, tickerCB, level2CB) => {
        const w = await new WebSocket(endPoint);
        w.onmessage = (msg) => {
            let msg_data = JSON.parse(msg.data);
            //console.log(msg_data);

            if (msg_data.type === 'error') {
                console.warn(msg.data);
            }
            if (msg_data.type === 'welcome') {
                // Add heartbeat
                interval.make(w);
                // Subscribe
                w.send(subscribe('/market/ticker:', symbol));
                w.send(subscribe('/spotMarket/level2Depth5:', symbol));
                w.send(subscribe('/contractMarket/tradeOrders', '', true));
                // Trade Orders
                // {
                //     "type": "message",
                //     "topic": "/contractMarket/tradeOrders",
                //     "subject": "orderChange",
                //     "channelType": "private",
                //     "data": {
                //         "orderId": "5cdfc138b21023a909e5ad55", //Order ID
                //         "symbol": "XBTUSDM",  //symbol
                //         "type": "match",  // Message Type: "open", "match", "filled", "canceled", "update"
                //         "status": "open", // Order Status: "match", "open", "done"
                //         "matchSize": "", // Match Size (when the type is "match")
                //         "matchPrice": "",// Match Price (when the type is "match")
                //         "orderType": "limit", // Order Type, "market" indicates market order, "limit" indicates limit order
                //         "side": "buy",  // Trading direction,include buy and sell
                //         "price": "3600",  // Order Price
                //         "size": "20000",  // Order Size
                //         "remainSize": "20001",  // Remaining Size for Trading
                //         "filledSize":"20000",  // Filled Size
                //         "canceledSize": "0",  //  In the update message, the Size of order reduced
                //         "tradeId": "5ce24c16b210233c36eexxxx",  // Trade ID (when the type is "match")
                //         "clientOid": "5ce24c16b210233c36ee321d", // clientOid
                //         "orderTime": 1545914149935808589,  // Order Time
                //         "oldSize ": "15000", // Size Before Update (when the type is "update")
                //         "liquidity": "maker", //  Trading direction, buy or sell in taker
                //         "ts": 1545914149935808589 // Timestamp
                //     }
                // }
                w.send(subscribe('/contractAccount/wallet', '', true));
                // Available Balance Event
                // {
                //     "userId": "xbc453tg732eba53a88ggyt8c", // Deprecated, will detele later
                //     "topic": "/contractAccount/wallet",
                //     "subject": "availableBalance.change",
                //     "data": {
                //         "availableBalance": 5923, //Current available amount
                //         "holdBalance": 2312, //Frozen amount
                //         "currency":"USDT",//Currency
                //         "timestamp": 1553842862614
                //     }
                // }
                w.send(subscribe('/contractAccount/wallet', '', true));
                // Withdrawal Amount & Transfer-Out Amount Event
                // {
                //     "userId": "xbc453tg732eba53a88ggyt8c", // Deprecated, will detele later
                //     "topic": "/contractAccount/wallet",
                //     "subject": "withdrawHold.change",
                //     "data": {
                //         "withdrawHold": 5923, // Current frozen amount for withdrawal
                //         "currency":"USDT",//Currency
                //         "timestamp": 1553842862614
                //     }
                // }
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
