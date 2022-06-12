import { Controller } from '@hotwired/stimulus';
import { getExchangeWsData } from "../js/websocket/utils";
import { KucoinWs } from "../js/websocket/kucoin";
import { isEmptyObj, jsonSubmitFormData} from "../js/utils/functions";

export default class extends Controller {
    static targets = [ "form" ]

    connect() {
        this.init();
    }

    init() {
        this.closeExchangeWs();
        this.templateOB = document.getElementById('orderbook-template');
        this.buyMarketOB = document.getElementById('buyMarket-orderbook');
        this.sellMarketOB = document.getElementById('sellMarket-orderbook');
        this.buyMarketOrders = document.getElementById('buyMarket-orders');
        this.sellMarketOrders = document.getElementById('sellMarket-orders');
        this.buyMarketCurrency = document.getElementById('buyMarket-currency');
        this.sellMarketCurrency = document.getElementById('sellMarket-currency');
        this.buyMarket = this.buyMarketOB.dataset.buyMarket;
        this.sellMarket = this.sellMarketOB.dataset.sellMarket;
        this.buyTicker = this.buyMarketOB.dataset.buyTicker;
        this.sellTicker = this.sellMarketOB.dataset.sellTicker;

        if (this.buyMarket) {
            getExchangeWsData(this.buyMarket, this.buyTicker).then(response => {
                this.loadBuyExchangeWs(response.exchange, response.endpoint, response.symbol);
                this.setBtnCurrency(this.buyMarketCurrency, response.symbol);
            });
        }
        if (this.sellMarket) {
            getExchangeWsData(this.sellMarket, this.sellTicker).then(response => {
                this.loadSellExchangeWs(response.exchange, response.endpoint, response.symbol);
                this.setBtnCurrency(this.sellMarketCurrency, response.symbol);
            });
        }
    }

    change() {
        jsonSubmitFormData(this.formTarget)
            .then(response => {
                if (response.content) {
                    this.formTarget.innerHTML = response.content;
                    this.init();
                }
            });
    }

    setBtnCurrency(marketCurrency, symbol) {
        if (symbol) {
            marketCurrency.innerHTML = symbol.split('-')[0];
            marketCurrency.closest('button').classList.remove('d-none');
        }
    }

    loadBuyExchangeWs(exchange, endPoint, symbol) {
        switch (exchange) {
            case 'kucoin':
                KucoinWs.loadOrderBook(
                    endPoint,
                    symbol,
                    (w) => {
                        this.buyExchangeWs = w;
                        this.fetchOrders(this.buyMarket, this.buyTicker, this.buyMarketOrders);
                    },
                    (price) => {
                        this.buyMarketPrice = price;
                    },
                    (orderBook) => {
                        this.drawMarketOrderbook(orderBook, this.buyMarketOB, symbol, this.buyMarketPrice);
                    }
                );
                break;
        }
    }

    loadSellExchangeWs(exchange, endPoint, symbol) {
        switch (exchange) {
            case 'kucoin':
                KucoinWs.loadOrderBook(
                    endPoint,
                    symbol,
                    (w) => {
                        this.sellExchangeWs = w;
                        this.fetchOrders(this.sellMarket, this.sellTicker, this.sellMarketOrders);
                    },
                    (price) => {
                        this.sellMarketPrice = price;
                    },
                    (orderBook) => {
                        this.drawMarketOrderbook(orderBook, this.sellMarketOB, symbol, this.sellMarketPrice);
                    }
                );
                break;
        }
    }

    drawMarketOrderbook(exchangeOB, htmlOB, symbol, marketPrice) {
        htmlOB.innerHTML = '';
        let Node = {};
        Node.clone = document.importNode(this.templateOB.content, true);
        let headerRow = Node.clone.querySelector('.orderbook-title');
        let priceRow = Node.clone.querySelector('.current-price');
        let buyRows = Node.clone.querySelector('.orders-buy');
        let sellRows = Node.clone.querySelector('.orders-sell');
        headerRow.innerHTML = '';
        priceRow.innerHTML = '';
        buyRows.innerHTML = '';
        sellRows.innerHTML = '';

        const currency = symbol.split('-');
        headerRow.innerHTML = `<tr>
            <th class="orderbook-header" scope="col">Price(${currency[1]})</th>
                <th class="orderbook-header" scope="col">Amount(${currency[0]})</th>
                <th class="orderbook-header" scope="col">Total(${currency[1]})</th>
            </tr>`;
        if (marketPrice) {
            priceRow.innerHTML = marketPrice;
        }

        if (!isEmptyObj(exchangeOB)) {
            exchangeOB.bids.forEach(element => {
                buyRows.innerHTML += element;
            });
            exchangeOB.asks.forEach(element => {
                sellRows.innerHTML += element;
            });
        }
        htmlOB.appendChild(Node.clone);
        delete Node.clone;
    }

    fetchOrders(market, ticker, htmlOrders) {
        fetch('/trading/orders', {
            method: 'post',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ market, ticker })
        })
            .then(response => response.json())
            .then(responseJson => {
                if (responseJson.html) {
                    htmlOrders.innerHTML = responseJson.html;
                }
            });
    }

    closeExchangeWs() {
        if (this.buyExchangeWs) {
            this.buyExchangeWs.close();
        }
        if (this.sellExchangeWs) {
            this.sellExchangeWs.close();
        }
    }

    disconnect() {
        this.closeExchangeWs();
    }
}
