import { Controller } from '@hotwired/stimulus';
// import { connectStreamSource, disconnectStreamSource } from '@hotwired/turbo';

export default class extends Controller {
    connect() {
        const url = this.element.getAttribute('data-stream-source');
        const key = this.element.getAttribute('data-stream-key');
        const robot = document.getElementById('robot');
        const startBtn = document.getElementById('startRobot');
        const stopBtn = document.getElementById('stopRobot');
        const consoleTab = document.getElementById('console');

        if (!url || !key) {
            console.log(`Stream controller connected without ${!url ? 'stream source' : ''} ${!key ? 'stream key' : ''}.`);
            robot.classList.remove('text-success', 'text-primary');
            robot.classList.add('text-danger');
            startBtn.setAttribute('disabled', true);
            stopBtn.setAttribute('disabled', true);
            return;
        }

        if (url.startsWith('ws')) {
            this.es = new WebSocket(`${url}?key=${key}`);
            console.log('Established WebSocket stream source at url:', url);
            stopBtn.setAttribute('disabled', true);

            this.es.onopen = (event) => {
                // console.log('Opened Websocket Connexion')
                robot.classList.remove('text-primary', 'text-error');
                robot.classList.add('text-success');
                startBtn.setAttribute('disabled', true);
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
                robot.classList.remove('text-success', 'text-error');
                robot.classList.add('text-primary');
            };
        }
        else {
            // server sent events (SSE, not WebSocket) endpoint
            this.es = new EventSource(url);
            console.log('Established server sent event (SSE) stream source at url:', url);

            this.es.onmessage = function handleMessage(event) {
                console.log('SSE: got message:', event.data);
            };
        }

        // connectStreamSource(this.es);
    }

    disconnect() {
        //console.log('Disconnection stream source');
        // disconnectStreamSource(this.es);
        this.es.close();
        const robot = document.getElementById('robot');
        robot.classList.remove('text-success', 'text-error');
        robot.classList.add('text-primary');
    }
}
