export const user = state => {
  return state.user;
};
export const settings = state => {
  return state.settings;
};
export const theme = state => {
  return state.user && state.user.settings && state.user.settings.theme ? state.user.settings.theme : state.settings.theme;
};
export const fixed_layout = state => {
  return state.user && state.user.settings && (state.user.settings.fixed_layout || state.user.settings.fixed_layout == 0)
    ? state.user.settings.fixed_layout
    : state.settings.fixed_layout;
};
export const play_sound = state => {
  return state.user && state.user.settings && (state.user.settings.play_sound || state.user.settings.play_sound == 0)
    ? state.user.settings.play_sound
    : state.settings.play_sound;
};
export const restaurant = state => {
  return state.settings.restaurant == 1 ? true : false;
};
export const superAdmin = state => {
  return state.user && state.user.roles.find(r => r.name == 'super') ? true : false;
};
export const customer = state => {
  return state.user && (state.user.customer_id || state.user.roles.find(r => r.name == 'customer')) ? true : false;
};
export const vendor = state => {
  return state.user && (state.user.vendor_id || state.user.roles.find(r => r.name == 'vendor')) ? true : false;
};
export const staff = state => {
  return (
    state.user &&
    !state.user.vendor_id &&
    !state.user.customer_id &&
    !state.user.roles.find(r => r.name == 'customer') &&
    !state.user.roles.find(r => r.name == 'vendor')
  );
};
export const taxes = state => {
  return state.taxes;
};
export const isUserLocation = state => location_id => {
  return state.user && state.user.locations.find(l => l.id == location_id);
};
export const locations = state => {
  return state.locations;
};
export const location = state => {
  return state.selected_location;
};
export const gateway = state => {
  return state.payment.gateway;
};
export const payment = state => {
  return state.payment;
};
export const register = state => {
  return state.register;
};
export const demo = state => {
  return state.demo ? true : false;
};
export const stock = state => {
  return state.settings.stock == 1 ? true : false;
};
export const require_country = state => {
  return state.settings.require_country == 1 ? true : false;
};
export const notifications = state => {
  return false;
  // return state.notifications.payment_due > 0 || state.notifications.payment_review > 0;
};
export const previous = state => page => {
  return state.previous ? state.previous : page;
};
export const order = state => oId => {
  return state.orders[oId ? oId : state.oId];
};
export const orders = state => {
  return state.orders;
};
export const current = state => {
  return state.oId;
};
export const orderNos = state => {
  return Object.keys(state.orders);
};
export const customers = state => {
  return state.customers;
};
export const menuList = state => {
  return state.menuList;
};
export const scale_barcode = state => {
  return state.settings.scale_barcode;
};
export const search_delay = state => {
  return state.settings.search_delay ? state.settings.search_delay : 250;
};
export const last_pos_sale = state => {
  return state.last_pos_sale;
};
