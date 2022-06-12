import { Controller } from '@hotwired/stimulus';
import { Litepicker } from "litepicker";
import { jsonSubmitFormData } from "../js/utils/functions";

export default class extends Controller {
    static values = {
        source: String,
        target: String,
    }

    connect() {
        const form = document.querySelector(`form[name="${this.sourceValue}"]`);
        const chart = document.getElementById(this.targetValue);

        const picker = new Litepicker({
            element: document.querySelector('.litepicker-start'),
            elementEnd: document.querySelector('.litepicker-end'),
            format: 'DD-MM-YYYY',
            singleMode: false,
            allowRepick: true,
            autoApply: true,
            lang: 'fr-FR',
            numberOfColumns: 1,
            numberOfMonths: 1,
            dropdowns: {
                'months': true,
                'years': true
            },
            showWeekNumbers: false,
            showTooltip: true,
            tooltipText: {
                one: 'jour',
                other: 'jours'
            },
            splitView: true,
            setup: (picker) => {
                picker.on('selected', (dateStart, dateEnd) => {
                    this.searchSelection(form, chart);
                });
            }
        });

        document.querySelector('.litepicker-clear').addEventListener('click', (event) => {
            picker.clearSelection();
            this.searchSelection(form, chart);
        });
    }

    searchSelection(form, chart) {
        jsonSubmitFormData(form)
            .then(response => {
                chart.innerHTML = response.content;
            });
    };
}
