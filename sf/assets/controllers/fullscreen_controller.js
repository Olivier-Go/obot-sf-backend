import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [ "container" ]

    connect() {
        if ('ccxt_trading_fullscreen' in localStorage && localStorage.ccxt_trading_fullscreen === '1') {
            this.toggle();
        }
    }

    toggle() {
        if (this.hasContainerTarget) {
            this.containerTarget.classList.toggle('fullscreen');
            localStorage.ccxt_trading_fullscreen = this.containerTarget.classList.contains('fullscreen') ? 1 : 0;
        }
    }
}
