import { Controller } from 'stimulus';
import { connectStreamSource, disconnectStreamSource } from '@hotwired/turbo';

const robot = document.getElementById('robot');

export default class extends Controller {
    connect() {
        // can be relative
        const url = this.element.getAttribute('data-stream-source');
        if (!url) {
            console.log('Stream controller connected without a stream source.');
            robot.classList.remove('text-success', 'text-primary');
            robot.classList.add('text-error');
            return;
        }

        if (url.startsWith('ws')) {
            this.es = new WebSocket(url);
            console.log('Established WebSocket stream source at url:', url);
            this.es.onmessage = function handleMessage(message) {
                robot.classList.remove('text-primary', 'text-error');
                robot.classList.add('text-success');

                const consoleTab = document.getElementById('console');
                if (consoleTab) {
                    consoleTab.innerText = message.data;
                }
            };
        } else {
            // server sent events (SSE, not WebSocket) endpoint
            this.es = new EventSource(url);
            console.log('Established server sent event (SSE) stream source at url:', url);

            this.es.onmessage = function handleMessage(event) {
                console.log('SSE: got message:', event.data);
            };
        }

        console.log(this.es)

        connectStreamSource(this.es);
    }

    disconnect() {
        console.log('Disconnection stream source');
        this.es.close();
        disconnectStreamSource(this.es);
        robot.classList.remove('text-success', 'text-error');
        robot.classList.add('text-primary');
    }
}
