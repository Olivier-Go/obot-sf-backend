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

export const updateOrdersArr = (arr, changes, buy = true) => {
    let result = [...arr];

    console.log(changes)

    changes.forEach(element => {
        console.log(element)
        /*const change = {
            price: element[0],
            size: element[1],
            received: element[2],
        }
        const existingOrder = result.find(({ price }) => price === change.price);
        if (existingOrder) {
            existingOrder.size = change.size;
            existingOrder.received = Date.now();
        } else {
            result.push({ ...change, received: Date.now() });
        }*/
    })

    result = result.filter(({ size }) => size > 0);
    result = result.sort((a, b) => parseFloat(b.price) - parseFloat(a.price));

    return result;
};

export const drawOrderLine = (arr, buy = true, dept = 3) => {
    let result = [];

    if (!buy) arr.reverse();

    for (let i = 0; i < dept; i++) {
        const order = arr[i];
        if (order && order.price && order.size) {
            result[i] = `<tr>
                <td class="price-${buy ? 'buy' : 'sell'}">${Number(order.price)}</td>
                    <td>${Number(order.size)}</td>
                    <td>${Number(order.price) * Number(order.size)}</td>
                </tr>`
            ;
        }
    }

    if (!buy) result.reverse();

    return result;
};