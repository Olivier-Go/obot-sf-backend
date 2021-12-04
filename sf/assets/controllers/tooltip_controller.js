import { Controller } from 'stimulus';
import { Tooltip } from "bootstrap";

export default class extends Controller {
    connect() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(tooltipTriggerEl => new Tooltip(tooltipTriggerEl));
    }
}
