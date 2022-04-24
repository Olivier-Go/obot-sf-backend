import "./utils/env.js";
import { createServer as HttpServer } from 'http';
import { createServer as HttpsServer } from 'https';
import fs from 'fs';
import url from 'url';
import { WebSocketServer } from 'ws';
import { spawn } from "child_process";

const server = process.env.APP_ENV === 'prod'
    ? HttpsServer({
        cert: fs.readFileSync(process.env.WS_CERT_PATH),
        key: fs.readFileSync(process.env.WS_KEY_PATH),
    })
    : HttpServer();

const wss = new WebSocketServer({ noServer: true });

let app = spawn('node', ['./src/app.js']);
app.stdout.setEncoding('utf-8')
    .on('data', (data) => console.log(JSON.stringify({ app: { state: 'running', pid: app.pid, message: '' } })));
app.stderr.on('data', (data) => console.log(JSON.stringify({ app: { state: 'error', pid: app.pid, message: data.toString() } })));
app.on('exit', (code) => console.log(JSON.stringify({ app: { state: 'exit', pid: app.pid, message: 'App child process exited with code', code } })));

wss.on('connection', (ws, request) => {
    const ip = request.socket.remoteAddress;
    console.log('Connexion open :', { 'address': ip })
    console.log('Clients :', wss.clients.size);

    ws.on('message', (message) => {
        const data = JSON.parse(message);
        if (data.cmd === 'console') {
            console.log('Printing console...')
            app.stdout.on('data', (data) => ws.send(data.toString()));
            app.stderr.on('data', (data) => ws.send(data.toString()));
        }
    });

    ws.on('close', (code) => {
        console.log('Connexion closed :', { 'code': code, 'address': ip })
        console.log('Clients :', wss.clients.size);
    });
});

server.listen(process.env.WS_PORT, () => console.log(JSON.stringify({
        server: {
            state: 'listening',
            pid: process.pid,
            address: server.address().address,
            family: server.address().family,
            port: server.address().port
        }
    })))
    .on('error', (error) => console.log(JSON.stringify({ server: { state: 'error', pid: process.pid, message: error.toString() } })))
    .on('close', () => console.log(JSON.stringify({ server: { state: 'closed', pid: process.pid, message: '' } })))
    .on('upgrade', (request, socket, head) => {
        const queryObject = url.parse(request.url, true).query;
        if (request.headers['upgrade'] !== 'websocket') {
            return socket.end('HTTP/1.1 400 Bad Request');
        }
        if (request.headers['cookie']) {
            let key;
            const content = request.headers['cookie'].split(';')
            content.forEach(str => {
                if (str.includes('PHPSESSID')) {
                    key = str.replace('PHPSESSID=','').trim();
                }
            });
            if (key !== queryObject.key) {
                return socket.end('HTTP/1.1 403 Forbidden');
            }
        }
        return wss.handleUpgrade(request, socket, head, (ws) => {
            wss.emit('connection', ws, request);
        });
    });

process.on('SIGTERM', () => app.exit());