import { Controller } from '@hotwired/stimulus';
import { getExchangeWsData, subscribe } from "../js/websocket/utils";
import { KucoinWs } from "../js/websocket/kucoin";

export default class extends Controller {
    static targets = [ "form" ]

    connect() {
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

    setParams() {
        this.disconnect();
        this.formTarget.requestSubmit();
    }

    loadBuyExchangeWs(exchange, endPoint, symbol) {
        switch (exchange) {
            case 'kucoin':
                this.buyExchangeWs = KucoinWs.loadOrderBook(
                    endPoint,
                    symbol,
                    (data) => {
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
                this.sellExchangeWs = KucoinWs.loadOrderBook(
                    endPoint,
                    symbol,
                    (data) => {
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

    disconnect() {
        if (this.buyExchangeWs) {
            this.buyExchangeWs.close();
        }
        if (this.sellExchangeWs) {
            this.sellExchangeWs.close();
        }
    }
}
