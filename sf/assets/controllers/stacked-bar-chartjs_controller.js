import { Controller } from 'stimulus';
import 'chartjs-adapter-date-fns';

export default class extends Controller {

    connect() {
        this.element.addEventListener('chartjs:pre-connect', this._onPreConnect);
    }

    _onPreConnect(event) {
        // let total = 0;
        // event.detail.options.plugins.tooltip = {
        //     titleAlign: 'center',
        //     footerAlign: 'center',
        //     callbacks: {
        //         label: (context) => {
        //             const label = context.dataset.label;
        //             console.log(context)
        //             const value = context.parsed.y;
        //             const datasets = context.parsed._stacks.y;
        //             total = 0
        //
        //             for (const [key, value] of Object.entries(datasets)) {
        //                 const dataset = parseInt(key);
        //                 if (Number.isInteger(dataset)) {
        //                     total += parseInt(value);
        //                 }
        //             }
        //
        //             return `${label}: ${value}`;
        //         },
        //         footer: () => `Total: ${total}`
        //     }
        // };
    }
}
