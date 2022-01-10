const Filters = {
  setAll: (obj, val = '') => Object.keys(obj).forEach(k => (obj[k] = val)),
  a2s: (value = '') => (value && value.constructor === Array ? value.join(', ') : value),
  trimString: (str, n = 32) => (str.length < n ? str : str.substr(0, n) + '...'),
  capitalize: (value = '') =>
    value
      .toString()
      .charAt(0)
      .toUpperCase() + value.toString().slice(1),

  formatDecimal: (value = 0, d = 4) => numberFormat(value, d),
  // formatDecimal: (value = 0, d = 4) => accounting.toFixed(value, d),
  formatQtyDecimal: (value = 0, d = 4) => numberFormat(value, d),
  // formatQtyDecimal: (value = 0, d = 4) => accounting.toFixed(value, d),
  unformatNumber: (value = 0, ds = '.') => accounting.unformat(value, ds),
  formatNumber: (value = 0, d = 4) =>
    parseFloat(numberFormat(value, d)).toLocaleString(window.user_locale, { minimumFractionDigits: d, maximumFractionDigits: d }),
  // formatNumber: (value = 0, d = 4, ts = ',', ds = '.') => accounting.formatNumber(value, d, ts, ds),
  formatJournalBalance: (value = 0, d = 4) =>
    parseFloat(numberFormat(value / 100, d)).toLocaleString(window.user_locale, { minimumFractionDigits: d, maximumFractionDigits: d }),
  // formatJournalBalance: (value = 0, d = 4, ts = ',', ds = '.') => accounting.formatNumber(value / 100, d, ts, ds),

  formatDate: (value = '', format = 'DD/MM/YYYY hh:mm A') => moment(String(value), 'YYYY-MM-DD HH:mm').format(format),

  date: (date, style) => {
    let formatted = new Date(Date.parse(date));
    return formatted.toLocaleString(window.user_locale, { dateStyle: style ? style : 'medium' });
  },

  dateDay: date => {
    let formatted = new Date(Date.parse(date));
    return formatted.toLocaleString(window.user_locale, { day: 'numeric', weekday: 'short' });
  },
  month: (month, style = 'short') => {
    let formatted = new Date(Date.parse(month));
    return formatted.toLocaleString(window.user_locale, { month: style });
  },

  monthYear: (month, style = 'short') => {
    let formatted = new Date(Date.parse(month));
    return formatted.toLocaleString(window.user_locale, { month: style, year: 'numeric' });
  },

  time: (date, style) => {
    let formatted = new Date(Date.parse(date));
    return formatted.toLocaleString(window.user_locale, { timeStyle: 'short', hour12: true });
  },

  datetime: (datetime, style) => {
    let formatted = new Date(Date.parse(datetime));
    return formatted.toLocaleString(window.user_locale, { dateStyle: style ? style : 'medium', timeStyle: 'short', hour12: true });
  },

  regionName: lang => {
    const langs = { en: 'English', es: 'Español', fr: 'Français' };
    return langs[lang];
    // const regionName = new Intl.DisplayNames([lang], { type: 'region' });
    // // return regionName.of('US');
    // return regionName.of(lang.toUpperCase);
  },
};

export default Filters;
