require('./bootstrap');
import Vue from 'vue';
import ViewUI from 'view-design';
import VueHotkey from 'v-hotkey';
import VueRouter from 'vue-router';
// import { locale } from 'view-design';
import Vue2TouchEvents from 'vue2-touch-events';
import VueNativeSock from 'vue-native-websocket';

window.Vue = Vue;
window.search_delay = 250;
Vue.config.performance = true;
Vue.config.productionTip = false;

Vue.use(VueHotkey);
Vue.use(VueRouter);
Vue.use(Vue2TouchEvents, {
  disableClick: true,
  tapTolerance: 10,
  swipeTolerance: 30,
  longTapTimeInterval: 200,
});

import i18n from './i18n';
/*eslint-disable */
import en from 'view-design/dist/locale/en-US';
Vue.use(ViewUI, { locale: en, transfer: true });
/*eslint-enable */
import Core from './core/index';
Vue.use(Core, window);

import store from './store/index';
import router from './routes/index';
import AppComponent from '@mpscom/AppComponent.vue';
import LoginComponent from '@mpscom/LoginComponent.vue';
import PrintComponent from '@mpscom/helpers/PrintComponent.vue';
import TableComponent from '@mpscom/helpers/TableComponent.vue';
import ActionsComponent from '@mpscom/helpers/ActionsComponent.vue';
import LoadingComponent from '@mpscom/helpers/LoadingComponent.vue';
import CenterLoadingComponent from '@mpscom/helpers/CenterLoadingComponent';
import SelectLocationComponent from '@mpscom/helpers/SelectLocationComponent.vue';
import ActionsDropdownComponent from '@mpscom/helpers/ActionsDropdownComponent.vue';
import FormCustomFieldsComponent from '@mpscom/helpers/FormCustomFieldsComponent.vue';
import TransactionsIconComponent from '@mpscom/helpers/TransactionsIconComponent.vue';
Vue.component('Loading', LoadingComponent);
Vue.component('app-component', AppComponent);
Vue.component('login-component', LoginComponent);
Vue.component('print-component', PrintComponent);
Vue.component('table-component', TableComponent);
Vue.component('actions-component', ActionsComponent);
Vue.component('form-custom-fields', FormCustomFieldsComponent);
Vue.component('center-loading-component', CenterLoadingComponent);
Vue.component('select-location-component', SelectLocationComponent);
Vue.component('actions-dropdown-component', ActionsDropdownComponent);
Vue.component('transactions-icon-component', TransactionsIconComponent);
Vue.use(VueNativeSock, 'ws://localhost:6441', { format: 'json', connectManually: true });

const app = new Vue({
  el: '#app',
  store: store(data),
  i18n: i18n(data.user_language),
  router: router(data.user_language),
  computed: {
    isSignedIn() {
      return this.$store.getters.user ? true : false;
    },
    keymap(e) {
      return {
        'alt+shift+s': () => this.$router.push({ name: 'sales.add' }),
        'alt+shift+n': () => this.$router.push({ name: 'incomes.add' }),
        'alt+shift+a': () => this.$router.push({ name: 'payments.add' }),
        'alt+shift+e': () => this.$router.push({ name: 'expenses.add' }),
        'alt+shift+c': () => this.$router.push({ name: 'customers.add' }),
        'alt+shift+v': () => this.$router.push({ name: 'suppliers.add' }),
        'alt+shift+m': () => this.$router.push({ name: 'modifiers.add' }),
        'alt+shift+b': () => this.$router.push({ name: 'purchases.add' }),
        'alt+shift+d': () => this.$router.push({ name: 'recurring.add' }),
        'alt+shift+g': () => this.$router.push({ name: 'gift_cards.add' }),
        'alt+shift+q': () => this.$router.push({ name: 'quotations.add' }),
        'alt+shift+r': () => this.$router.push({ name: 'return_orders.add' }),
        'alt+shift+t': () => this.$router.push({ name: 'transfers.stock.add' }),

        'alt+shift+i': () => this.$router.push({ name: 'items.add' }),
        'ctrl+shift+i': () => this.$router.push({ name: 'items.list' }),
        'alt+shift+z': () => this.$router.push({ name: 'items.combos.add' }),
        'ctrl+shift+z': () => this.$router.push({ name: 'items.combos.list' }),
        'alt+shift+x': () => this.$router.push({ name: 'items.recipes.add' }),
        'ctrl+shift+x': () => this.$router.push({ name: 'items.recipes.list' }),
        'alt+shift+y': () => this.$router.push({ name: 'items.services.add' }),
        'ctrl+shift+y': () => this.$router.push({ name: 'items.services.list' }),

        'ctrl+shift+p': () => this.$router.push({ name: 'pos' }),
        'ctrl+shift+h': () => this.$router.push({ name: 'dashboard' }),
        'ctrl+shift+d': () => this.$router.push({ name: 'recurring' }),
        'ctrl+shift+s': () => this.$router.push({ name: 'sales.list' }),
        'ctrl+shift+o': () => this.$router.push({ name: 'orders.list' }),
        'ctrl+shift+n': () => this.$router.push({ name: 'incomes.list' }),
        'ctrl+shift+a': () => this.$router.push({ name: 'payments.list' }),
        'ctrl+shift+e': () => this.$router.push({ name: 'expenses.list' }),
        'ctrl+shift+c': () => this.$router.push({ name: 'customers.list' }),
        'ctrl+shift+m': () => this.$router.push({ name: 'modifiers.list' }),
        'ctrl+shift+b': () => this.$router.push({ name: 'purchases.list' }),
        'ctrl+shift+v': () => this.$router.push({ name: 'suppliers.list' }),
        'ctrl+shift+g': () => this.$router.push({ name: 'gift_cards.list' }),
        'ctrl+shift+q': () => this.$router.push({ name: 'quotations.list' }),
        'ctrl+shift+r': () => this.$router.push({ name: 'return_orders.list' }),
        'ctrl+shift+t': () => this.$router.push({ name: 'transfers.stock.list' }),

        'alt+meta+h': () => this.$router.push({ name: 'halls.add' }),
        'alt+meta+t': () => this.$router.push({ name: 'taxes.add' }),
        'alt+meta+x': () => this.$router.push({ name: 'units.add' }),
        'alt+meta+u': () => this.$router.push({ name: 'users.add' }),
        'alt+meta+b': () => this.$router.push({ name: 'brands.add' }),
        'alt+meta+f': () => this.$router.push({ name: 'fields.add' }),
        'alt+meta+p': () => this.$router.push({ name: 'promos.add' }),
        'alt+meta+l': () => this.$router.push({ name: 'locations.add' }),
        'alt+meta+c': () => this.$router.push({ name: 'categories.add' }),
        'alt+meta+g': () => this.$router.push({ name: 'customer_groups.add' }),
        'alt+meta+z': () => this.$router.push({ name: 'transfers.asset.add' }),

        'ctrl+meta+r': () => this.$router.push({ name: 'reports' }),
        'ctrl+meta+,': () => this.$router.push({ name: 'settings' }),
        'ctrl+meta+h': () => this.$router.push({ name: 'halls.list' }),
        'ctrl+meta+t': () => this.$router.push({ name: 'taxes.list' }),
        'ctrl+meta+x': () => this.$router.push({ name: 'units.list' }),
        'ctrl+meta+u': () => this.$router.push({ name: 'users.list' }),
        'ctrl+meta+b': () => this.$router.push({ name: 'brands.list' }),
        'ctrl+meta+f': () => this.$router.push({ name: 'fields.list' }),
        'ctrl+meta+p': () => this.$router.push({ name: 'promos.list' }),
        'ctrl+meta+y': () => this.$router.push({ name: 'utilities.logs' }),
        'ctrl+meta+l': () => this.$router.push({ name: 'locations.list' }),
        'ctrl+meta+c': () => this.$router.push({ name: 'categories.list' }),
        'ctrl+meta+g': () => this.$router.push({ name: 'customer_groups.list' }),
        'ctrl+meta+z': () => this.$router.push({ name: 'transfers.asset.list' }),

        'ctrl+alt+a': () => this.$event.fire('openAlerts'),
        'ctrl+shift+k': () => this.$event.fire('showShortcutKeys'),

        // POS Keymap
        'ctrl+alt+o': () => this.$event.fire('pos:openOrder'),
        'ctrl+alt+n': () => this.$event.fire('pos:openOrder'),
        'ctrl+alt+d': () => this.$event.fire('pos:deleteOrder'),
        'ctrl+alt+i': () => this.$event.fire('pos:updateInfo'),
        'ctrl+alt+p': () => this.$event.fire('pos:makePayment'),
        'ctrl+alt+k': () => this.$event.fire('pos:printOrder'),
        'ctrl+alt+b': () => this.$event.fire('pos:printBill'),
        'ctrl+alt+l': () => this.$event.fire('pos:printReceipt'),
        'ctrl+alt+x': () => this.$event.fire('toggleFullScreen'),
        'ctrl+alt+s': () => this.$event.fire('toggleCategories'),
        'ctrl+alt+c': () => this.$event.fire('toggleCalculator'),
        // 'ctrl+alt+f': () => this.$event.fire('pos:focusBarcodeInput'),
        'ctrl+alt+f': () => {
          if (document.querySelector('#scan_barcode')) {
            document.querySelector('#scan_barcode').focus();
          }
        },
      };
    },
  },
  mounted() {
    this.$refs.preloaderapp.remove();
    this.$store.commit('setLocation', data.location);
    window.search_delay = this.$store.getters.search_delay;
    window.user_locale = this.$store.getters.settings.user_locale || navigator.language || undefined;
  },
  created() {
    if (this.$store.getters.customer) {
      window.location.href = '/';
    } else {
      // locale(data.user_language);
      if (!data.settings.data.user_locale && data.user_language) {
        this.$store.commit('setUserLocale', data.user_language + '-' + data.user_language.toUpperCase());
      }
      this.$event.listen('locale:change', locale => {
        if (locale != this.$store.state.user_language) {
          this.$http.get('app/language/' + locale).then(res => {
            if (res.data.success) {
              localStorage.setItem('language', locale);
              window.location.reload();
            }
          });
        }
      });
      this.$Loading.config({ color: '#19be6b', failedColor: '#ed3f14', height: 2 });
      this.$router.beforeEach((to, from, next) => {
        if (this.$store.getters.customer) {
          window.location.href = '/';
        }
        if (to.path.includes('views')) {
          next();
        }
        this.$Loading.start();
        let appName = this.$store.getters.settings.name ? this.$store.getters.settings.name : 'Modern POS Solution';
        document.title = to.meta.title + ' - ' + appName;
        if (to.path != '/login' && !to.path.includes('/views')) {
          if (this.$store.getters.user) {
            if (!to.meta.access || !to.meta.permission) {
              next();
            } else {
              if (to.meta.access == 'super' && !this.can(to.meta.permission)) {
                document.title = from.meta.title + ' - ' + appName;
                this.$Loading.error();
                this.redirectBack();
              } else {
                next();
              }
            }
          } else {
            this.$Loading.error();
            next('/login');
          }
        } else {
          next();
        }
      });
      this.$router.afterEach((to, from) => {
        if (!to.path.includes('/views')) {
          this.$event.fire('menu:update', to);
          this.$store.commit('setPrevious', from.path);
          setTimeout(() => this.$Loading.finish(), 250);
        }
      });
      this.$event.listen('logOut', () => {
        this.$store.commit('setLocation');
        this.$http.defaults.baseURL = window.baseURL;
        this.$http
          .get('/logout')
          .then(() => {
            this.$http.defaults.baseURL = window.mpsURL;
            this.$store.commit('setUser', null);
            this.$nextTick(() => {
              this.$router.push('/login');
              this.refreshToken();
            });
          })
          .catch(err => console.log(err));
      });
      this.$event.listen('expand:view', () => {
        this.toggle();
      });
      this.$event.listen('okSound', () => this.playSound('aDing.mp3'));
      this.$event.listen('errorSound', () => this.playSound('sDing.mp3'));
      this.$event.listen('appError', res => {
        if (res) {
          this.handleError(res);
        } else if (this.isOnline) {
          this.$Notice.error({
            title: this.$t('unknown_error'),
            desc: this.$t('unknown_error_text'),
            duration: 15,
          });
          this.$Loading.error();
        } else {
          this.$Notice.error({
            title: this.$t('network_error'),
            desc: this.$t('network_error_text'),
            duration: 15,
          });
          this.$Loading.error();
        }
      });
      window.axios.interceptors.request.use(
        config => {
          this.$Loading.start();
          return config;
        },
        err => {
          this.$Loading.error();
          return Promise.reject(err);
        }
      );

      window.axios.interceptors.response.use(
        response => {
          this.$Loading.finish();
          return response;
        },
        err => {
          let error = { message: '', form: {} };
          if (err.response.status == 422) {
            error.message = err.response.data.message;
            if (err.response.data.lang_key) {
              error.message = this.$t(err.response.data.lang_key);
              for (const k in err.response.data.errors) {
                err.response.data.errors[k] = err.response.data.errors[k].map(e => {
                  return this.$t(e);
                });
              }
            }
            error.form = err.response.data.errors ? err.response.data.errors : {};
            let errors_a = Object.values(error.form);
            error.message = error.message + (errors_a.length ? '<br>' + errors_a.join('<br>') : '');
          }
          this.$Loading.error();
          this.$event.fire('appError', err.response);
          return Promise.reject(error);
        }
      );
    }
  },
  methods: {
    displayErrors(err) {
      if (err.lang_key) {
        this.$Notice.error({ title: this.$tc('failed'), desc: this.$t(err.lang_key), duration: 10 });
      } else {
        let errors = err.errors ? Object.values(err.errors) : [err.message];
        this.$Notice.error({
          title: this.$t('failed'),
          desc: errors.length ? errors.join('<br>') : this.$t('failed_error_text'),
          duration: 10,
        });
      }
    },
    redirectBack(stay) {
      this.$Notice.error({
        title: this.$t('access_denied'),
        desc: this.$t('not_allowed_resource'),
        duration: 10,
      });
      if (!stay) {
        this.$router.back();
      }
    },
    refreshPage() {
      if (this.$route.path == '/login') {
        this.$Notice.error({
          title: this.$t('token_expired'),
          desc: this.$t('token_expired_text'),
          duration: 10,
        });
        if (window) {
          window.location.reload();
        }
      } else {
        this.refreshToken();
        this.$Notice.info({
          title: this.$t('token_was_expired'),
          desc: this.$t('token_was_expired_text'),
          duration: 5,
        });
      }
    },
    refreshToken() {
      this.$http.get('app/token').then(res => {
        document.head.querySelector('meta[name="csrf-token"]').setAttribute('content', res.data.token);
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = res.data.token;
      });
    },
    handleError(res) {
      switch (res.status) {
        case 422: {
          this.displayErrors(res.data);
          break;
        }
        case 403: {
          this.redirectBack();
          break;
        }
        case 401: {
          this.$router.push('/login/force');
          break;
        }
        case 419: {
          this.refreshPage();
          break;
        }
        case 404: {
          this.$router.push({ name: 'error-404' });
          break;
        }
        default: {
          let error = res.data.error ? res.data.error : res.data.message ? res.data.message : this.$t('unknown_error_text2');
          this.$Notice.error({ title: res.status + ' ' + res.statusText, desc: error, duration: 10 });
          break;
        }
      }
      this.$Loading.error();
    },
    playSound(sound) {
      if (sound) {
        new Audio(this.$store.state.url + '/sounds/' + sound).play();
      }
    },
  },
});
if (!data && !navigator.onLine) {
  document.querySelector('.spin').style.display = 'none';
  document.querySelector('.error').style.display = 'block';
  window.console.error('Network Error! Unable to load application.');
}
