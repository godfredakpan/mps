import Event from './Event';
import Filters from './Filters';
import Online from './Online';
import Screen from './Screen';
import Storage from './Storage';
import numberFormat from './numberFormat';
// import inflection from 'inflection';
import moment from 'moment';
import myM from './Mixins';
import objectToFormData from './Form';

const Core = {
  install(Vue, window) {
    Vue.mixin(myM);
    Vue.use(Online);
    Vue.use(Screen);
    Vue.prototype.$http = window.axios;
    Vue.prototype.$event = window.Event = Event;
    Vue.prototype.$moment = window.moment = moment;
    Vue.prototype.$storage = window.Storage = Storage;
    Vue.prototype.$form = window.Form = objectToFormData;
    // Vue.prototype.$inflection = window.inflection = inflection;
    Vue.prototype.$numberFormat = window.numberFormat = numberFormat;
    Vue.prototype.$accounting = window.accounting = require('accounting');
    for (const [key, value] of Object.entries(Filters)) {
      Vue.filter(key, value);
    }
    // Object.entries(Filters).forEach(([key, value]) => Vue.filter(key, value));
  },
};

export default Core;
