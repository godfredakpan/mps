import Vue from 'vue';
import Vuex from 'vuex';
import * as getters from './getters';

Vue.use(Vuex);

function builder(data) {
  return new Vuex.Store({
    state: {
      taxes: [],
      auth_url: '',
      user: data.user,
      token: data.token,
      register: data.register,
      demo: data.settings.demo,
      locations: data.locations,
      url: data.settings.baseURL,
      settings: data.settings.data,
      modules: data.settings.modules,
      payment: data.settings.payment,
      selected_location: data.location,
      user_language: data.user_language || 'en',
      languages: data.languages,
      last_pos_sale: null,
      oId: null,
      orders: {},
      menuList: {},
      customers: [],
      fullscreen: false,
    },
    getters,
    mutations: {
      set(state, val) {
        Object.keys(val).map(el => {
          state[el] = val[el];
        });
      },
      setUserLanguage(state, language) {
        state.user_language = language || 'en';
      },
      setUser(state, user) {
        state.user = user;
      },
      setToken(state, token) {
        state.token = token;
      },
      setFullscreen(state, fullscreen) {
        state.fullscreen = fullscreen;
      },
      setSettings(state, settings) {
        settings.scale_barcode = state.settings.scale_barcode;
        window.user_locale = settings.user_locale || navigator.language || undefined;
        state.settings = settings;
      },
      setUserLocale(state, user_locale) {
        window.user_locale = user_locale;
        state.settings.user_locale = user_locale;
      },
      setPayment(state, payment) {
        state.payment = payment;
      },
      setScaleBarcode(state, scale_barcode) {
        state.settings.scale_barcode = scale_barcode;
      },
      setUserSettings(state, settings) {
        state.user.settings = settings;
      },
      setTaxes(state, taxes) {
        state.taxes = taxes;
      },
      setLocation(state, v) {
        let loc = state.locations.filter(l => l.value == v);
        state.selected_location = loc ? loc[0] : null;
      },
      setRegister(state, reg) {
        state.register = reg;
      },
      setPrevious(state, page) {
        state.previous = page;
      },
      setCurrent(state, oId) {
        state.oId = oId;
      },
      setOrders(state, orders) {
        for (const [oId, order] of Object.entries(orders)) {
          order.discount_method = order && order.discount_method ? order.discount_method : 'items';
          order.discount = order.discount ? parseFloat(order.discount) : null;
        }
        state.orders = orders;
      },
      setOrder(state, oId, order) {
        if (order) {
          order.discount_method = order.discount_method ? order.discount_method : 'items';
          order.discount = order.discount ? parseFloat(order.discount) : null;
          state.orders[oId] = order;
        }
      },
      addOrder(state, oId) {
        if (oId) {
          state.oId = oId;
          state.orders[oId] = {
            note: '',
            items: [],
            reference: '',
            discount: null,
            order_taxes: [],
            discount_method: 'items',
            customer_id: state.settings.default_customer,
          };
        }
      },
      updateOrder(state, params) {
        if (params) {
          if (!state.orders[params.oId]) {
            this.commit('addOrder', params.oId);
          }
          state.orders[params.oId][params.key] = params.value;
        }
      },
      setOrderRow(state, item) {
        let items = state.orders[state.oId].items;
        items[
          items.findIndex(
            i => i.id == item.id && i.promo == item.promo && (!i.variation || (i.variation && i.variation.sku == item.variation.sku))
          )
        ] = item;
        state.orders[state.oId].items = items;
      },
      addCustomer(state, customer) {
        if (customer && !state.customers.find(c => c.value == customer.value)) {
          // state.customers.push(customer);
          state.customers = [...state.customers, customer];
        }
      },
      setCustomers(state, customers) {
        state.customers = [...customers];
      },
      addOrderRow(state, item) {
        state.orders[state.oId].items = [...state.orders[state.oId].items, item];
      },
      delOrderRow(state, item) {
        state.orders[state.oId].items = state.orders[state.oId].items.filter(i => i.guid !== item.guid);
      },
      delPromotionItem(state, params) {
        state.orders[state.oId].items = state.orders[state.oId].items.filter(
          i => i.id != params.id && i.promo_item_id != params.promo_item_id
        );
      },
      delOrder(state, oId) {
        let orders = { ...state.orders };
        delete orders[oId];
        state.oId = null;
        state.orders = { ...orders };
      },
      emptyOrders(state) {
        state.oId = null;
        state.orders = {};
        state.customers = state.customers.filter(c => c.value == state.settings.default_customer);
      },
      setSVGString(state, svg_string) {
        state.settings.svg_string = svg_string;
      },
      setUserProfile(state, user) {
        state.user.name = user.name;
        state.user.phone = user.phone;
        state.user.avatar = user.avatar;
      },
      authUrl(state, authUrl) {
        state.auth_url = authUrl;
      },
      setActingUser(state, user) {
        if (state.user) {
          state.user.acting_user = user;
        }
      },
      changeDefaultLogo(state, logo) {
        state.settings.default_logo = logo;
      },
      UpdateLastPOSSale(state, sale) {
        state.last_pos_sale = sale;
      },
    },
  });
}
export default builder;
