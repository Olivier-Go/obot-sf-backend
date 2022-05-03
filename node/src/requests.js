import "./utils/env.js";
import { app } from "./app.js";
import { state } from "./state.js";
import axios from "axios";

export const apiFetchConnection = () => (
    axios({
        method: 'post',
        url: `${process.env.API_URL}/api/login_check`,
        data: {
            'username': process.env.API_USERNAME,
            'password': process.env.API_PASSWORD,
        },
    })
        .then((response) => {
            state.apiToken = response.data.token;
            apiFetchParameters();
            app.init();
            app.run();
        })
        .catch((error) => {
            console.warn(`${error.code}: ${error.address}:${error.port}`);
        })
        .finally(() => {
        })
);

const apiFetchParameters = () => (
    axios({
        method: 'get',
        url: `${process.env.API_URL}/api/parameters`,
        headers: {'Authorization': `Bearer ${state.apiToken}`},
    })
        .then((response) => {
            console.log(response.data);
            if (response.status === 200) {
                state.orderDiff = response.data.worker_order_diff;
                state.orderSize = response.data.worker_order_size;
            }
        })
        .catch((error) => {
            console.warn(`${error.code}: ${error.address}:${error.port}`);
        })
        .finally(() => {
        })
);

export const apiAddOpportunity = (op) => {
    if (!state.apiBusy) {
        app.stop();
        state.apiBusy = true;
        axios({
            method: 'post',
            url: `${process.env.API_URL}/api/opportunity/new`,
            headers: {'Authorization': `Bearer ${state.apiToken}`},
            data: { ...op.order },
        })
            .then((response) => {
                //console.log(response.data);
                if (response.status === 201) {
                    app.run();
                    state.apiBusy = false;
                }
            })
            .catch((error) => {
                state.apiBusy = false;
                console.warn(`${error.code}: ${error.address}:${error.port}`);
            })
            .finally(() => {
            });
    }

};