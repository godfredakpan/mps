import Vue from 'vue';
import VueI18n from 'vue-i18n';
Vue.use(VueI18n);
// Vue.locale = () => {};

import en from 'view-design/dist/locale/en-US';
import es from 'view-design/dist/locale/es-ES';
import fr from 'view-design/dist/locale/fr-Fr';

const enL = require('../lang/en.json');
const esL = require('../lang/es.json');
const frL = require('../lang/fr.json');

const messages = {
  en: Object.assign(enL, en),
  es: Object.assign(esL, es),
  fr: Object.assign(frL, fr),
};

function builder(locale) {
  return new VueI18n({
    messages,
    locale: locale,
    fallbackLocale: 'en',
    silentTranslationWarn: false,
  });
}
export default builder;
