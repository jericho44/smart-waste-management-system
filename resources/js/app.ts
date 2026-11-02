import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { VueQueryPlugin } from '@tanstack/vue-query';
import VueApexCharts from 'vue3-apexcharts';

import App from './App.vue';
import router from './router';
import '../css/app.css';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);
app.use(VueQueryPlugin);
app.use(VueApexCharts);

app.mount('#app');

