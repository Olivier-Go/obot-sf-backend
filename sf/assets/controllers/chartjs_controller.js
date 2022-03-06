import { Controller } from 'stimulus';
import { Chart } from 'chart.js';
import moment from 'moment';
import 'chartjs-adapter-moment';
import zoomPlugin from 'chartjs-plugin-zoom';

Chart.register(zoomPlugin);
moment.locale('fr-FR');

export default class extends Controller {

    connect() {
        this.element.addEventListener('chartjs:pre-connect', this._onPreConnect);
        this.element.addEventListener('chartjs:connect', this._onConnect);
    }

    disconnect() {
        // You should always remove listeners when the controller is disconnected to avoid side effects
        this.element.removeEventListener('chartjs:pre-connect', this._onPreConnect);
        this.element.removeEventListener('chartjs:connect', this._onConnect);
    }

    _onPreConnect(event) {
        // The chart is not yet created
        //console.log(event.detail.options.scales.x); // You can access the chart options using the event details
        // event.detail.options.scales.x = {
        //     ticks : {
        //         callback: function( label, index, labels ) {
        //             console.log(label);
        //         }
        //     }
        // };

        // Tooltip.positioners.bottom = function(items) {
        //     const pos = Tooltip.positioners.average(items);
        //     if (pos === false) {
        //         return false;
        //     }
        //     const chart = this._chart;
        //     return {
        //         x: pos.x,
        //         y: chart.chartArea.bottom,
        //     };
        // };
        //
        // event.detail.options.plugins.tooltip = {
        //     position: 'bottom'
        // }

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

    _onConnect(event) {
        // The chart was just created
        // console.log(event.detail.chart); // You can access the chart instance using the event details

        // For instance you can listen to additional events
        event.detail.chart.options.onHover = (mouseEvent) => {
            /* ... */
        };
        event.detail.chart.options.onClick = (mouseEvent) => {
            /* ... */
        };
    }
}
