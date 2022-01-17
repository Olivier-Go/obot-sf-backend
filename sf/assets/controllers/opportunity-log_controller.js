import { Controller } from 'stimulus';
import { Modal } from "bootstrap";

export default class extends Controller {

    connect() {}

    log(event) {
        const id = event.currentTarget.dataset.opportunityLogId;
        const modalEl = document.getElementById('logModal');
        const modal = Modal.getOrCreateInstance(modalEl);
        const title = modalEl.querySelector('.modal-title');
        const content = modalEl.querySelector('div.modal-body small samp');

        console.log(modalEl.children)

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
}
