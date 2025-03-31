import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import './assets/index.css';  // Assuming you have a Tailwind CSS setup

const app = createApp(App);

app.use(router);

app.mount('#app');