import "./utils/env.js";
import { app } from "./index.js";
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
        app.init();
        app.run();
      })
      .catch((error) => {
        console.warn(error.response.data);
      })
      .finally(() => {
      })
);

export const apiAddOpportunity = (op) => {
    app.stop();
    axios({
        method: 'post',
        url: `${process.env.API_URL}/api/arbitrage/opportunity/add`,
        headers: {'Authorization': `Bearer ${state.apiToken}`},
        data: { ...op.order },
    })
        .then((response) => {
            //console.log(response.data);
            if (response.status === 201) {
                app.run();
            }
        })
        .catch((error) => {
            console.warn(error.response.data);
        })
        .finally(() => {
        });
};