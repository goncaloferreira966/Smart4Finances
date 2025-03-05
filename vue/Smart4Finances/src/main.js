import './assets/main.css';
// Importar o Bootstrap e o BootstrapVue CSS
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
// Importar os ícones do Bootstrap
import 'bootstrap-icons/font/bootstrap-icons.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import axios from 'axios';
import { io } from "socket.io-client";
import App from './App.vue';
import FieldErrorMessage from './components/icons/errorMessage.vue';
import Toastify from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const app = createApp(App);

// Configuração do WebSocket
//app.provide('socket', io("http://localhost:8080"));

const serverBaseUrl = import.meta.env.VITE_API_DOMAIN;
app.provide('serverBaseUrl', serverBaseUrl);
axios.defaults.baseURL = serverBaseUrl + '/api';
axios.defaults.headers.common['Content-type'] = 'application/json';

// Configuração do Toastify
app.use(Toastify, {
    position: "top-center",
    autoClose: 3000,
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    closeButton: true,
    icon: true,
});

app.use(createPinia());

app.component('FieldErrorMessage', FieldErrorMessage);

app.mount('#app');
