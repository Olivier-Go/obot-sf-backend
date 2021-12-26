import { spawn } from "child_process";
import { isEmptyObj } from "./utils/functions.js";

let appStdout = {};
let serverStdout = {};

let ws = spawn('node', ['./src/ws.js']);
ws.stdout.setEncoding('utf-8');
ws.stdout.on('data', (data) => {
    const rtn = JSON.parse(data);
    appStdout = { ...appStdout, ...rtn.app };
    serverStdout = { ...serverStdout, ...rtn.server };

    if (!isEmptyObj(appStdout) && !isEmptyObj(serverStdout)) {
        const response = JSON.stringify({ app: appStdout, server: serverStdout });
        switch (serverStdout.state) {
            case 'error':
            case 'closed':
                console.log(response);
                process.exit(1);
                break;
        }
        switch (appStdout.state) {
            case 'running':
                console.log(response);
                process.exit(0);
                break;
            case 'error':
            case 'exit':
                console.log(response);
                if (serverStdout.pid) process.kill(serverStdout.pid, 'SIGTERM');
                process.exit(1);
                break;
        }
    }
});
ws.stderr.on('data', (data) => console.log('stderr', data.toString()));
ws.on('exit', (code) => {
    console.log('Ws server child process exited with code', code);
    process.exit(1);
});
