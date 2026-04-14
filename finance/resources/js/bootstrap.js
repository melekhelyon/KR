import axios from 'axios';
import router from './router';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;
window.axios.defaults.withXSRFToken = true;
window.axios.interceptors.response.use( {}, err => {
    if(err.response.status === 401 || err.response.status === 419) {
        const token = localStorage.getItem('token')
        if (token) {
            localStorage.removeItem('token')
        }
        router.push({name: 'app.login'})
    }

    if(err.response.status === 403) {
        router.push({name: 'user.profile'})
    }
});