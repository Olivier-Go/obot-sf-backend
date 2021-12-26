import { Controller } from 'stimulus';
require('/assets/js/tradingview');

export default class extends Controller {
    static targets = [ 'result' ]

    static values = {
        url: String,
    }

    connect() {}

    clic(event) {
        const url = this.urlValue;
        const cmd = event.currentTarget.getAttribute('data-node-ws-command');
        const startBtn = document.getElementById('startRobot');
        const stopBtn = document.getElementById('stopRobot');

        const buttonTarget = event.currentTarget;
        const originalButton = buttonTarget.innerHTML;
        buttonTarget.setAttribute('disabled', true);
        buttonTarget.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

        fetch(url, {
            method: 'post',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ cmd })
        })
            .then(response => response.json())
            .then(responseJson => {
                buttonTarget.innerHTML = originalButton;
                let html = '';
                if (responseJson.app) {
                    html += `
                        <div class="border-rounder-full w-100">
                            <div class="card-header d-flex justify-content-between">
                                <strong>App</strong>
                                ${responseJson.app.state === 'running' ? ` 
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                ` : `
                                    <i class="bi bi-x-circle-fill text-danger"></i>
                                `}
                            </div>
                            <ul class="list-group list-group-flush">
                                ${responseJson.app.state ? ` 
                                    <li class="list-group-item">State : <code>${responseJson.app.state}</code></li>
                                ` : ``}
                                ${responseJson.app.pid ? ` 
                                    <li class="list-group-item">Pid : <code>${responseJson.app.pid}</code></li>
                                ` : ``}
                                ${responseJson.app.message && responseJson.app.message !== '' ? ` 
                                    <li class="list-group-item">Message : <code>${responseJson.app.message}</code></li>
                                ` : ``}
                            </ul>
                        </div>
                    `;
                }
                if (responseJson.server) {
                    html += `
                        <div class="border-rounder-full w-100">
                            <div class="card-header d-flex justify-content-between">
                                <strong>Server</strong>
                                ${responseJson.server.state === 'listening' ? ` 
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                ` : `
                                    <i class="bi bi-x-circle-fill text-danger"></i>
                                `}
                            </div>
                            <ul class="list-group list-group-flush">
                                ${responseJson.server.state ? ` 
                                    <li class="list-group-item">State : <code>${responseJson.server.state}</code></li>
                                ` : ``}
                                ${responseJson.server.pid ? ` 
                                    <li class="list-group-item">Pid : <code>${responseJson.server.pid}</code></li>
                                ` : ``}
                                ${responseJson.server.address ? ` 
                                    <li class="list-group-item">Address : <code>${responseJson.server.address}</code></li>
                                ` : ``}
                                ${responseJson.server.family ? ` 
                                    <li class="list-group-item">Family : <code>${responseJson.server.family}</code></li>
                                ` : ``}
                                ${responseJson.server.port ? ` 
                                    <li class="list-group-item">Port : <code>${responseJson.server.port}</code></li>
                                ` : ``}
                            </ul>
                        </div>
                    `;
                }
                if (responseJson.app && responseJson.server) {
                    if (responseJson.app.state === 'running' && responseJson.server.state === 'listening') {
                        startBtn.setAttribute('disabled', true);
                        stopBtn.removeAttribute('disabled');
                    } else {
                        startBtn.removeAttribute('disabled');
                        stopBtn.setAttribute('disabled', true);
                    }
                }
                this.resultTarget.innerHTML = html;
            });
    }

    reload() {
        setTimeout(() => location.reload(), 500);
    }
}
