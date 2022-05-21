import { Controller } from '@hotwired/stimulus';
import { getExchangeWsData } from "../js/websocket/utils";
import { KucoinWs } from "../js/websocket/kucoin";
import { jsonSubmitFormData } from "../js/utils/functions";

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
        const buyMarketOB = document.getElementById('buyMarket-orderbook');
        const sellMarketOB = document.getElementById('sellMarket-orderbook');
        this.buyMarket = buyMarketOB.dataset.buyMarket;
        this.sellMarket = sellMarketOB.dataset.sellMarket;
        this.buyTicker = buyMarketOB.dataset.buyTicker;
        this.sellTicker = sellMarketOB.dataset.sellTicker;

        if (this.buyMarket) {
            getExchangeWsData(this.buyMarket, this.buyTicker).then(response => {
                this.loadBuyExchangeWs(response.exchange, response.endpoint, response.symbol);
            });
        }
        if (this.sellMarket) {
            getExchangeWsData(this.sellMarket, this.sellTicker).then(response => {
                if (this.buyMarket !== this.sellMarket) {
                    this.loadSellExchangeWs(response.exchange, response.endpoint, response.symbol);
                }
            });
        }
    }

    loadBuyExchangeWs(exchange, endPoint, symbol) {
        switch (exchange) {
            case 'kucoin':
                KucoinWs.loadOrderBook(
                    endPoint,
                    symbol,
                    (w, data) => {
                        this.buyExchangeWs = w;
                        this.drawBuyMarketOB(data)
                        if (this.buyMarket === this.sellMarket) {
                            this.drawSellMarketOB(data)
                        }
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
                    (w, data) => {
                        this.sellExchangeWs = w;
                        this.drawSellMarketOB(data)
                    }
                );
                break;
        }
    }

    drawBuyMarketOB(data) {
        console.log(data)
    }

    drawSellMarketOB(data) {
        console.log(data)
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
