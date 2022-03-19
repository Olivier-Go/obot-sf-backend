import { Controller } from 'stimulus';
import { Tooltip } from "bootstrap";

export default class extends Controller {
    connect() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(tooltipTriggerEl => new Tooltip(tooltipTriggerEl, { trigger: 'hover' }));

        // Hide tooltips when click
        document.addEventListener('click', () => {
            tooltipTriggerList.map(tooltipTriggerEl => Tooltip.getInstance(tooltipTriggerEl).hide());
        });
    }
}
