import axios from 'axios';
import mod from '@mps/module.json';

window.mod = mod;
window.axios = axios;
window.baseURL = window.data.settings.baseURL;
window.mpsURL = window.data.settings.baseURL + '/' + window.mod.route + '/';
window.axios.defaults.baseURL = window.mpsURL;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
  console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}
