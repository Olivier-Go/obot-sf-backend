import { Controller } from 'stimulus';
require('/assets/js/tradingview');

export default class extends Controller {
    static values = {
        id: String,
        ticker: String,
    }

    connect() {
        const container_id = this.idValue;
        new TradingView.widget(
            {
                "width": '100%',
                "height": 500,
                "symbol": this.tickerValue,
                "interval": "D",
                "timezone": "Europe/Paris",
                "theme": "dark",
                "style": "1",
                "locale": "fr",
                "toolbar_bg": "#f1f3f6",
                "enable_publishing": false,
                "withdateranges": true,
                "hide_side_toolbar": false,
                "allow_symbol_change": true,
                "save_image": false,
                "studies": [
                    "RSI@tv-basicstudies"
                ],
                container_id
            }
        );
    }

}
