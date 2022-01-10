import sha1 from 'sha1';
import { ulid } from 'ulid';
import { v4 as uuidv4 } from 'uuid';
const myM = {
  methods: {
    alert: function(msg, level = 'success') {
      if (window && document.documentElement.clientWidth <= 768) {
        this.$Message[level](msg);
      }
    },
    can: function(permission) {
      return this.$store.getters.superAdmin ||
        (this.$store.getters.user &&
          this.$store.getters.user.all_permissions &&
          this.$store.getters.user.all_permissions.includes(permission))
        ? true
        : false;
    },
    canAny: function(permissions) {
      if (Array.isArray(permissions)) {
        return permissions.map(p => this.can(p)).find(e => e == true);
      }
      return false;
    },
    trimString(str, n) {
      return this.$options.filters.trimString(str, n);
    },
    datetimeFormatString() {
      return 'YYYY-MM-DD HH:mm:ss';
    },
    queryString(params) {
      return Object.keys(params)
        .map(key => key + '=' + params[key])
        .join('&');
    },
    capitalize: function(value) {
      return this.$options.filters.capitalize(value);
    },
    time: function(date, style) {
      return this.$options.filters.time(date, style);
    },
    date: function(date, style) {
      return this.$options.filters.date(date, style);
    },
    dateDay: function(date) {
      return this.$options.filters.dateDay(date);
    },
    monthToLocale: function(month, style) {
      return this.$options.filters.month(month, style);
    },
    datetime: function(date, style) {
      return this.$options.filters.datetime(date, style);
    },
    formatDecimal: function(n, d, df = null) {
      let v = this.$options.filters.formatDecimal(n, d ? d : this.$store.state.settings.decimals);
      return df ? v : parseFloat(v);
    },
    formatQtyDecimal: function(n, d) {
      return parseFloat(this.$options.filters.formatDecimal(n, d ? d : this.$store.state.settings.quantity_decimals));
    },
    formatNumber: function(n, d) {
      return this.$options.filters.formatNumber(n, d ? d : this.$store.state.settings.decimals);
    },
    formatJournalBalance: function(n, d) {
      return this.$options.filters.formatJournalBalance(n, d ? d : this.$store.state.settings.decimals);
    },
    formatQuantity: function(n, d) {
      return this.$options.filters.formatNumber(n, d ? d : this.$store.state.settings.quantity_decimals);
    },
    formatAttributes: function(attributes, extra) {
      attributes.map(a => {
        if (a.type == 'number' && a.required) {
          extra[a.slug] = parseFloat(extra[a.slug]);
        }
      });
      return extra;
    },
    appendChar(data, key = 'children', append = false) {
      return data.map(c => {
        if (Array.isArray(c[key]) && c[key].length > 0) {
          c[key] = c[key].map(c => ({ ...c, name: append ? append + c.name : '⊢  ' + c.name }));
          c[key] = this.appendChar(c[key]);
        }
        return c;
      });
    },
    flattenDeep(data, key = 'children', append = true) {
      return data.reduce((acc, cat) => {
        if (key && Array.isArray(cat[key]) && cat[key].length > 0) {
          let result = append ? this.appendChar(acc.concat(cat), key) : acc.concat(cat);
          return result.concat(acc.concat(this.flattenDeep(cat[key], key, append)));
        } else {
          return acc.concat(cat);
        }
      }, []);
    },
    mapDeep(data, cb, key = 'children', listAll = false) {
      return data.reduce((acc, cat) => {
        if (key && Array.isArray(cat[key]) && cat[key].length > 0) {
          cat[key] = this.mapDeep(cat[key], cb, key, listAll);
          if (listAll) {
            cat[key].unshift({ ...cat, value: cat.id, label: this.$t('list_all_items'), children: [] });
          }
        }
        return acc.concat(cb(cat));
      }, []);
    },
    filterDeep(data, cb, key = 'children') {
      data = this.flattenDeep(data, key, false);
      return data.filter(i => cb(i) && i.name == i.label);
    },
    uuid: function() {
      return uuidv4();
    },
    guid() {
      return sha1(this.uuid() + Date.now().toString() + Math.random().toString());
    },
    sku(uniq = false) {
      return ulid().toLowerCase();
    },
    metaString(meta, noHtml = false, join = false) {
      let str = [];
      for (const [key, value] of Object.entries(meta)) {
        noHtml ? str.push(key + ': ' + value) : str.push(key + ': <strong>' + value + '</strong>');
      }
      return str.join(join ? '\n ' : ', ');
    },
    parseScaleBarcode(barcode, scale_barcode) {
      if (!scale_barcode) {
        scale_barcode = this.$store.state.settings.scale_barcode;
      }
      let price = 0;
      let weight = 0;
      let item_code = barcode.substr(scale_barcode.item_code_start - 1, scale_barcode.item_code_digits);
      if (scale_barcode.type == 'price') {
        try {
          price = barcode.substr(scale_barcode.price_start - 1, scale_barcode.price_digits);
          price = scale_barcode.price_divide_by ? price / scale_barcode.price_divide_by : price;
        } catch (err) {
          price = 0;
        }
      } else {
        try {
          weight = barcode.substr(scale_barcode.weight_start - 1, scale_barcode.weight_digits);
          weight = scale_barcode.weight_divide_by ? weight / scale_barcode.weight_divide_by : weight;
        } catch (err) {
          weight = 0;
        }
      }

      return { item_code, price, weight };
    },
    sleep: function(ms) {
      ms = parseInt(ms);
      if (!isNaN(ms)) {
        var e = new Date().getTime() + ms;
        while (new Date().getTime() <= e) {}
      }
    },
    booleanView(key) {
      return key == 1
        ? '<i class="ivu-icon ivu-icon-md-checkmark" style="font-size: 16px; color: #19be6b;" />'
        : '<i class="ivu-icon ivu-icon-md-close" style="font-size: 16px; color: #ed4014;" />';
    },
    checkExtraAttributes(extra) {
      if (extra) {
        let ifea = false;
        for (const [key, value] of Object.entries(extra)) {
          if (value) {
            ifea = true;
          }
        }
        return ifea;
      }
      return false;
    },
    renderExtraAttributes(extra, attrs) {
      let ev = [];
      for (const [key, value] of Object.entries(extra)) {
        let attr = null;
        if (attrs) {
          attr = attrs.find(a => a.slug == key);
        }
        ev.push(
          '<tr><td>' +
            (attr && attr.name ? attr.name : key) +
            ':</td><td><strong>' +
            (attr && attr.type == 'datetime' ? this.datetime(value) : attr && attr.type == 'date' ? this.date(value) : value) +
            '</strong></td></tr>'
        );
      }
      return '<table class="table">' + ev.join('') + '</table>';
    },
    calculateUnitPrice(item, field) {
      let unit = item.allUnits.find(u => u.id == item.unit_id);
      if (unit[field]) {
        item[field] = unit[field];
        return item;
      } else {
        item.allUints = item.allUnits.map(u => {
          if (item.unit.id == item.unit_id) {
            u[field] = item.real_value || item[field];
          } else {
            let con;
            switch (u.operator) {
              case '*':
                con = 1 * parseFloat(u.operation_value);
                break;
              case '/':
                con = 1 / parseFloat(u.operation_value);
                break;
              case '+':
                con = 1 + parseFloat(u.operation_value);
                break;
              case '-':
                con = 1 - parseFloat(u.operation_value);
                break;
              default:
                con = 1;
            }
            u[field] = con * parseFloat(item[field]);
          }
          return u;
        });
      }
      unit = item.allUnits.find(u => u.id == item.unit_id);
      item[field] = unit[field];
      return item;
    },
    isMobile() {
      return window && navigator ? /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) : false;
    },
    isTouchDevice() {
      return window ? 'ontouchstart' in window : false;
    },
  },
};

export default myM;
