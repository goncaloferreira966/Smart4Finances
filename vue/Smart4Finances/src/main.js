import './assets/main.css';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import 'bootstrap-icons/font/bootstrap-icons.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import axios from 'axios';
import App from './App.vue';
import router from './router'; // Importa o router que acabamos de criar
import FieldErrorMessage from './components/icons/errorMessage.vue';
import Toastify from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';

const app = createApp(App);
const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);
app.use(pinia);
const serverBaseUrl = import.meta.env.VITE_API_DOMAIN;
app.provide('serverBaseUrl', serverBaseUrl);
axios.defaults.baseURL = serverBaseUrl + '/api';
axios.defaults.headers.common['Content-type'] = 'application/json';

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

app.use(router); // Registra o Vue Router

app.component('FieldErrorMessage', FieldErrorMessage);

app.mount('#app');
