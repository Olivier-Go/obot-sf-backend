import { Controller } from '@hotwired/stimulus';
import { Modal } from "bootstrap";

export default class extends Controller {

    connect() {}

    log(event) {
        const id = event.currentTarget.dataset.opportunityLogId;
        const modalEl = document.getElementById('logModal');
        const modal = Modal.getOrCreateInstance(modalEl);
        const title = modalEl.querySelector('.modal-title');
        const content = modalEl.querySelector('div.modal-body small samp');

        fetch('/opportunity/log', {
            method: 'post',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id })
        })
            .then(response => response.json())
            .then(responseJson => {
                if (responseJson.logs) {
                    title.innerText = `Logs opportunité n° ${id} du ${responseJson.received}`;
                    content.innerText = responseJson.logs;
                    modal.show();
                }
            });
    }

    order(event) {
        const id = event.currentTarget.dataset.opportunityOrderId;
        const modalEl = document.getElementById('orderModal');
        const modal = Modal.getOrCreateInstance(modalEl);
        const title = modalEl.querySelector('.modal-title');
        const content = modalEl.querySelector('div.modal-body');

        fetch(`/order/${id}`, {
            method: 'post',
        })
            .then(response => response.json())
            .then(responseJson => {
                if (responseJson.html) {
                    title.innerText = `Ordre n° ${id} du ${responseJson.opened}`;
                    content.innerHTML = responseJson.html;
                    modal.show();
                }
            });
    }
}
