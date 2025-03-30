import { createApp } from 'vue';
import App from './components/App.vue';
import router from './router';
import './asssets/index.css';  // Assuming you have a Tailwind CSS setup

const app = createApp(App);

app.use(router);

app.mount('#app');