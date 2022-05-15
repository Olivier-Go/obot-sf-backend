import { subscribe, interval } from './utils';

export const KucoinWs = {
    loadOrderBook: async (endPoint, symbol, callback) => {
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
                w.send(subscribe('/market/level2:', symbol));
            }
            if (msg_data.type === 'message') {
                callback(msg_data.data);
            }
        }
        w.onclose = () => interval.clearAll();
        return w;
    }
}
