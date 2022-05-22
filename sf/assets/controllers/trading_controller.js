import { Controller } from '@hotwired/stimulus';
import { getExchangeWsData } from "../js/websocket/utils";
import { KucoinWs } from "../js/websocket/kucoin";
import { isEmptyObj, jsonSubmitFormData} from "../js/utils/functions";

export default class extends Controller {
    static targets = [ "form" ]

    connect() {
        this.init();
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

    init() {
        this.closeExchangeWs();
        this.templateOB = document.getElementById('orderbook-template');
        this.buyMarketOB = document.getElementById('buyMarket-orderbook');
        this.sellMarketOB = document.getElementById('sellMarket-orderbook');
        this.buyMarket = this.buyMarketOB.dataset.buyMarket;
        this.sellMarket = this.sellMarketOB.dataset.sellMarket;
        this.buyTicker = this.buyMarketOB.dataset.buyTicker;
        this.sellTicker = this.sellMarketOB.dataset.sellTicker;

        if (this.buyMarket) {
            getExchangeWsData(this.buyMarket, this.buyTicker).then(response => {
                this.loadBuyExchangeWs(response.exchange, response.endpoint, response.symbol);
            });
        }
        if (this.sellMarket) {
            getExchangeWsData(this.sellMarket, this.sellTicker).then(response => {
                this.loadSellExchangeWs(response.exchange, response.endpoint, response.symbol);
            });
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
