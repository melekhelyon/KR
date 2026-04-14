import './bootstrap';
import { createApp } from 'vue';
import AppComponent from './App.vue';
import NavBar from './NavBar.vue';
import router from './router';

createApp({
    components: {
        AppComponent,
        NavBar,
    },
}).use(router).mount('#app');