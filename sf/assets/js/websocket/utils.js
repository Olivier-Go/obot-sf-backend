export const subscribe = (topic, symbol, privateChannel = false) => {
    return JSON.stringify({
        id: Date.now(),
        type: "subscribe",
        topic: topic + symbol,
        privateChannel,
        response: true
    })
}

const ping = () => {
    return JSON.stringify({
        id: Date.now(),
        type: "ping",
        response: true
    })
}

export const interval = {
    intervals : new Set(),
    make(w) {
        let newInterval = setInterval(() => {
            w.send(ping());
        }, 20000);
        this.intervals.add(newInterval);
        return newInterval;
    },
    clear(id) {
        this.intervals.delete(id);
        return clearInterval(id);
    },
    clearAll() {
        for (let id of this.intervals) {
            this.clear(id);
        }
    }
}

export const getExchangeWsData = async (market, ticker) => {
    const response = await fetch('/trading/ws/data', {
        method: 'post',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ market, ticker })
    })
    if (response.ok) {
        return await response.json();
    }
    return null;
}