import './assets/main.css'
// Importar o Bootstrap e o BootstrapVue CSS
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
//importar os icones  do bootstrap
import 'bootstrap-icons/font/bootstrap-icons.css';


import { createApp } from 'vue'
import { createPinia } from 'pinia';
import axios from 'axios'
import { io } from "socket.io-client"
import App from './App.vue'

const app = createApp(App);

app.provide('socket', io(import.meta.env.VITE_WS_CONNECTION))   

const pinia = createPinia();
app.use(pinia);
app.mount('#app');