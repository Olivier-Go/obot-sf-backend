import { Controller } from '@hotwired/stimulus';
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

        event.detail.options.plugins.tooltip = {
            callbacks: {
                label: (context) => {
                    const label = context.dataset.label ?? context.label;
                    let rawData = context.dataset.rawData;
                    let value = context.parsed.y ?? context.formattedValue;
                    if (rawData) value = rawData[context.dataIndex];
                    return `${label}: ${value}`;
                },
            }
        };
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
