import { Controller } from '@hotwired/stimulus';
// import { connectStreamSource, disconnectStreamSource } from '@hotwired/turbo';

export default class extends Controller {
    static targets = [ 'result' ]

    static values = {
        wsUrl: String,
        wsKey: String,
        commandUrl: String,
    }

    connect() {
        this.state = 0;
        const robot = document.getElementById('robot');
        const startBtn = document.getElementById('startRobot');
        const stopBtn = document.getElementById('stopRobot');
        const consoleTab = document.getElementById('console');

        if (!this.wsKeyValue) setTimeout(() => location.reload(), 500);

        if (!this.wsUrlValue) {
            console.log('Stream controller connected without stream source.');
            robot.classList.remove('text-success', 'text-primary');
            robot.classList.add('text-danger');
            startBtn.setAttribute('disabled', 'disabled');
            stopBtn.setAttribute('disabled', 'disabled');
            return;
        }

        if (this.wsUrlValue.startsWith('ws')) {
            this.es = new WebSocket(`${this.wsUrlValue}?key=${this.wsKeyValue}`);
            console.log('Established WebSocket stream source at url:', this.wsUrlValue);
            stopBtn.setAttribute('disabled', 'disabled');

            this.es.onopen = (event) => {
                // console.log('Opened Websocket Connexion')
                robot.classList.remove('text-primary', 'text-danger');
                robot.classList.add('text-success');
                startBtn.setAttribute('disabled', 'disabled');
                stopBtn.removeAttribute('disabled');
                if (consoleTab) {
                    this.es.send(JSON.stringify({
                        cmd: 'console',
                    }));
                }
            };
            this.es.onmessage = function handleMessage(message) {
                if (consoleTab) consoleTab.innerText = message.data;
            };
            this.es.onclose = (event) => {
                robot.classList.remove('text-success', 'text-danger');
                robot.classList.add('text-primary');
                if (consoleTab) consoleTab.innerText = '_';
            };
        }
        else {
            // server sent events (SSE, not WebSocket) endpoint
            this.es = new EventSource(this.wsUrlValue);
            console.log('Established server sent event (SSE) stream source at url:', this.wsUrlValue);

            this.es.onmessage = function handleMessage(event) {
                console.log('SSE: got message:', event.data);
            };
        }

        // connectStreamSource(this.es);
    }

    disconnect() {
        //console.log('Disconnection stream source');
        // disconnectStreamSource(this.es);
        const robot = document.getElementById('robot');
        this.es.close();
        robot.classList.remove('text-success', 'text-error');
        robot.classList.add('text-primary');
    }

    command(event) {
        const commandUrl = this.commandUrlValue;
        const cmd = event.currentTarget.getAttribute('data-stream-command');
        const startBtn = document.getElementById('startRobot');
        const stopBtn = document.getElementById('stopRobot');
        const buttonTarget = event.currentTarget;
        const originalButton = buttonTarget.innerHTML;
        buttonTarget.setAttribute('disabled', 'disabled');
        buttonTarget.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

        fetch(commandUrl, {
            method: 'post',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ cmd })
        })
            .then(response => response.json())
            .then(responseJson => {
                this.connect();
                this.state = 1;
                buttonTarget.innerHTML = originalButton;
                let html = '';
                if (responseJson.app) {
                    html += `
                        <div class="border-rounder-full shadow-sm w-100">
                            <div class="card-header d-flex justify-content-between">
                                <strong class="text-primary">App</strong>
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
                        <div class="border-rounder-full shadow-sm w-100">
                            <div class="card-header d-flex justify-content-between">
                                <strong class="text-primary">Server</strong>
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
                        startBtn.setAttribute('disabled', 'disabled');
                        stopBtn.removeAttribute('disabled');
                    } else {
                        startBtn.removeAttribute('disabled');
                        stopBtn.setAttribute('disabled', 'disabled');
                    }
                }
                this.resultTarget.innerHTML = html;
            });
    }
}
