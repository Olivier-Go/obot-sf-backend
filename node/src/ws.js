import { WebSocketServer } from 'ws';
import { spawn } from "child_process";

const wss = new WebSocketServer({ port: 8080 });

let child = spawn('node', ['./src/index.js']);
child.stdout.setEncoding('utf-8');

wss.on('connection', function connection(ws) {
    child.stdout.on('data', (data) => {
        ws.send(data.toString());
    });
    child.stderr.on('data', function(data) {
        ws.send(data.toString());
    });
    child.on('close', function(code) {
        ws.send(data.toString());
    });
});


// wss.clients.forEach(function each(client) {
//     console.log(client)
//     if (client.readyState === ws.OPEN) {
//         client.send('ping');
//     }
// });