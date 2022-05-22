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
                    (orderBook) => {
                        this.drawMarketOrderbook(orderBook, this.buyMarketOB);
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
                    (orderBook) => {
                        this.drawMarketOrderbook(orderBook, this.sellMarketOB);
                    }
                );
                break;
        }
    }

    drawMarketOrderbook(exchangeOB, htmlOB) {
        htmlOB.innerHTML = '';
        const clone = document.importNode(this.templateOB.content, true);
        if (!isEmptyObj(exchangeOB)) {
            let buyRows = clone.querySelector('.orders-buy');
            let sellRows = clone.querySelector('.orders-sell');
            buyRows.innerHTML = '';
            sellRows.innerHTML = '';

            exchangeOB.bids.forEach(element => {
                buyRows.innerHTML += element;
            });
            exchangeOB.asks.forEach(element => {
                sellRows.innerHTML += element;
            });
        }
        htmlOB.appendChild(clone);
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
