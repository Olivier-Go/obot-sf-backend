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
        url: `${process.env.API_URL}/api/opportunity/new`,
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