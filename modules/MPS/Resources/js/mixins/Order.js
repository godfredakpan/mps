import inflection from 'inflection';
import _debounce from 'lodash/debounce';

import Form from './Form';
import Attachment from '@mpsjs/mixins/Attachment';

const dict = {
  custom: {
    tax: { regex: 'Only % and numbers are allowed.' },
    discount: { regex: 'Only % and numbers are allowed.' },
  },
};

const formatRes = (data, vm, did) => {
  vm.attributes = data.attributes;
  delete data.customer;
  delete data.supplier;
  delete data.user;
  delete data.attributes;
  data.discount_method = 'items';
  data.items = data.items.map(i => {
    i.item.guid = vm.guid();
    i.item.id = did ? '' : i.id;
    i.item.item_id = i.item_id;
    i.item.sale_item_id = i.id;
    i.item.item_unit_id = i.item.unit_id;
    i.item.unit_id = i.unit_id;
    i.item.batch_no = i.batch_no;
    if (i.item.unit) {
      i.item.allUnits = [{ ...i.item.unit }, ...i.item.unit.subunits];
    }
    i.item.quantity = parseFloat(i.quantity);
    i.item['real_value'] = parseFloat(i.item[vm.field]);
    i.item[vm.field] = parseFloat(i[vm.field]);
    i.item.expiry_date = new Date(i.expiry_date);
    i.item.discount_amount = parseFloat(i.discount_amount);
    let amount = i.item[vm.field] - i.item.discount_amount;
    i.item.discount = i.discount && parseFloat(i.discount) > 0 ? parseFloat(i.discount) : null;
    i.item.allTaxes = [...i.item.taxes];
    i.item.taxes = vm.calculateTaxes(i.item.allTaxes, amount);
    if (vm.field == 'price') {
      i.promotions = i.promotions;
      i.item.promotions = i.promotions;
      i.item.allPromotions = [...i.item.promotions];
    }
    i.item.selected = { modifiers: [], variations: [], portions: [] };
    if (i.modifier_options && i.modifier_options.length) {
      i.item.selected.modifiers = i.modifier_options.map(o => {
        let m = i.item.modifiers.find(m => m.id == o.modifier_id);
        let option = m.options.find(mo => mo.id == o.id);
        let modifier = {
          id: o.id,
          meta: o.meta,
          title: m.title,
          mId: o.modifier_id,
          option: option.name,
          cost: parseFloat(o.pivot.cost),
          price: parseFloat(o.pivot.price),
          quantity: parseFloat(o.pivot.quantity),
        };
        modifier.discount_amount = parseFloat(o.pivot.discount_amount);
        let amount = modifier[vm.field] - modifier.discount_amount;
        modifier.discount = i.discount && parseFloat(i.discount) > 0 ? parseFloat(i.discount) : null;
        modifier.taxes = vm.calculateTaxes(i.item.allTaxes, amount);
        return modifier;
      });
    }
    if (i.variations && i.variations.length) {
      i.item.selected.variations = i.variations.map(v => {
        let o = i.item.variations.find(o => v.id == o.id);
        let variation = {
          id: v.id,
          meta: v.meta,
          cost: parseFloat(v.pivot.cost),
          price: parseFloat(v.pivot.price),
          quantity: parseFloat(v.pivot.quantity),
          available: parseFloat(v.pivot.quantity + o.location_stock[0].quantity),
        };
        variation.discount_amount = parseFloat(v.pivot.discount_amount);
        let amount = variation[vm.field] - variation.discount_amount;
        variation.discount = i.discount && parseFloat(i.discount) > 0 ? parseFloat(i.discount) : null;
        variation.taxes = vm.calculateTaxes(i.item.allTaxes, amount);
        return variation;
      });
    }
    if (i.portions && i.portions.length) {
      i.item.selected.portions = i.portions.map(p => {
        let portion = {
          id: p.id,
          name: p.name,
          cost: parseFloat(p.pivot.cost),
          price: parseFloat(p.pivot.price),
          quantity: parseFloat(p.pivot.quantity),
          choosables: p.choosables.map(e => {
            let se = p.pivot.choosables.find(es => es.id == e.id);
            let item = e.items.find(i => i.item_id == se.item_id).item;
            let sv = item.has_variants ? item.variations.find(v => v.id == se.variation_id) : null;
            if (item.has_variants) {
              return {
                ...e,
                selected: se.item_id,
                meta: sv ? sv.meta : null,
                variation_id: se.variation_id,
                variants: [...item.variants],
                variations: [...item.variations],
              };
            }
            return { ...e, selected: se.item_id, variation_id: null };
          }),
          essentials: p.essentials.map(e => {
            let se = p.pivot.essentials.find(es => es.id == e.id);
            if (e.item.has_variants) {
              let sv = e.item.variations.find(v => v.id == se.variation_id);
              return {
                ...e,
                meta: sv ? sv.meta : null,
                variation_id: se.variation_id,
                variants: [...e.item.variants],
                variations: [...e.item.variations],
              };
            }
            return e;
          }),
          portion_items: p.portion_items.map(e => {
            let se = p.pivot.portion_items.find(es => es.id == e.id);
            if (e.item.has_variants) {
              let sv = e.item.variations.find(v => v.id == se.variation_id);
              return {
                ...e,
                meta: sv ? sv.meta : null,
                variation_id: se.variation_id,
                variants: [...e.item.variants],
                variations: [...e.item.variations],
              };
            }
            return e;
          }),
        };
        portion.discount_amount = parseFloat(p.pivot.discount_amount);
        let amount = portion[vm.field] - portion.discount_amount;
        portion.discount = i.discount && parseFloat(i.discount) > 0 ? parseFloat(i.discount) : null;
        portion.taxes = vm.calculateTaxes(i.item.allTaxes, amount);
        return portion;
      });
    }
    return i.item;
  });
  data.shipping_fee = data.shipping_fee ? parseFloat(data.shipping_fee) : null;
  vm.is_draft = data.draft ? true : false;
  data.draft = data.draft ? '' + data.draft : '0';
  data.payment = '0';
  data.type = data.type || 'nett';
  data.deduct_from_register = '0';
  if (data.date) {
    data.date = new Date(data.date);
  }
  if (data.start_date) {
    data.start_date = new Date(data.start_date);
  }
  if (did) {
    data.id = '';
    data.reference = '';
    data.date = new Date();
  }
  vm.attachments = data.attachments && data.attachments.length ? [...data.attachments] : [];
  vm.form = { ...data, ...data.extra_attributes };
  vm.changed = false;
  return vm.form;
};

function Order(model, route, field, duplicate, adj) {
  var oName = inflection.capitalize(model);
  var route = route ? route : 'app/' + inflection.pluralize(model);
  var field = field ? field : model == 'purchase' ? 'cost' : 'price';

  return {
    mixins: [Attachment, Form(model, route, true, formatRes, false)],
    data() {
      const discountV = (rule, value, callback) => {
        if (value && parseFloat(value) > parseFloat(this.$store.state.settings.max_discount)) {
          callback(new Error(this.$t('max_discount_error', { percent: parseFloat(this.$store.state.settings.max_discount) })));
        } else {
          callback();
        }
      };

      return {
        query: '',
        taxes: [],
        result: [],
        photo: null,
        customers: [],
        suppliers: [],
        attributes: [],
        is_draft: true,
        changed: false,
        deleting: false,
        new_photo: null,
        edit_item: null,
        searching: false,
        field: field,
        attachments: null,
        show_item_edit: false,
        expiry_date_options: {
          disabledDate(date) {
            return date && date.valueOf() < Date.now() - 86400000;
          },
        },
        form: {
          id: '',
          taxes: [],
          items: [],
          draft: 0,
          payment: 0,
          repeat: '',
          details: '',
          reference: '',
          discount: null,
          attachments: [],
          date: new Date(),
          shipping_fee: null,
          create_before: null,
          start_date: new Date(),
          deduct_from_register: 0,
          discount_method: 'items',
          type: model == 'return_order' ? '' : 'nett',
          customer_id: '',
          supplier_id: '',
        },
        rules: {
          discount: [{ validator: discountV, type: 'number', trigger: ['change', 'blur'] }],
          details: [{ message: this.$t('field_is_required', { field: this.$t('details') }), trigger: 'blur' }],
          customer_id: [{ required: true, message: this.$t('field_is_required', { field: this.$tc('customer') }), trigger: 'change' }],
          supplier_id: [{ required: true, message: this.$t('field_is_required', { field: this.$tc('supplier') }), trigger: 'change' }],
          date: [
            {
              type: 'date',
              required: true,
              trigger: 'change',
              message: this.$t('field_is_required', { field: this.$t('date') }),
            },
          ],
          start_date: [
            {
              type: 'date',
              required: true,
              trigger: 'change',
              message: this.$t('field_is_required', { field: this.$t('start_date') }),
            },
          ],
          type: [{ required: true, message: this.$t('field_is_required', { field: this.$t('type') }), trigger: 'change' }],
          repeat: [{ required: true, message: this.$t('field_is_required', { field: this.$t('repeat') }), trigger: 'change' }],
          taxes: [
            {
              type: 'array',
              required: true,
              trigger: 'change',
              message: this.$t('field_is_required', { field: this.$t('category') }),
            },
          ],
        },
      };
    },
    watch: {
      'form.customer_id': function(newVal, oldVal) {
        if (oldVal) {
          let customer = this.customers.find(c => c.value == oldVal);
          if (customer && customer.customer_group && customer.customer_group.discount) {
            this.form.items.map(item => {
              item.discount = parseFloat(item.discount) - parseFloat(customer.customer_group.discount);
              this.addToOrder(item, item.quantity, true, true);
            });
          }
        }
        if (newVal) {
          this.checkCustomerGroup(newVal);
        }
      },
      'form.discount_method': function(newVal, oldVal) {
        if (this.form.discount_method == 'order') {
          this.form.items = this.form.items.map(i => {
            i.discount = null;
            i.discount_amount = 0;
            return i;
          });
        } else {
          this.form.items = this.form.items.map(i => {
            i.discount = this.form.discount ? this.form.discount : null;
            i.discount_amount = this.form.discount ? this.formatDecimal((i[this.field] * i.discount) / 100) : 0;
            return i;
          });
        }
      },
      'form.discount': _debounce(function(newVal, oldVal) {
        newVal = newVal ? parseFloat(newVal) : 0;
        if (newVal <= parseFloat(this.$store.state.settings.max_discount)) {
          if (this.form.discount_method == 'items') {
            this.form.items = this.form.items.map(i => {
              i.discount = newVal;
              i.discount_amount = newVal ? this.formatDecimal((i[this.field] * i.discount) / 100) : 0;
              return i;
            });
          }
        }
      }, 500),
    },
    created() {
      if (!this.$route.params.id) {
        this.$event.fire('location:select');
      }
      this.$event.listen('addOrderRowByItemID', params => {
        this.addOrderRowByItemID(params.id, params.item_id);
      });
      this.$http
        .get('app/taxes/search')
        .then(res => (this.taxes = res.data))
        .then(() => {
          if (this.$route.params.id) {
            this.fetch(this.$route.params.id);
          }
        });
      if (duplicate && duplicate.id) {
        if (this.$route.query[duplicate.qId]) {
          this.$http
            .get(`/app/quotations/${this.$route.query[duplicate.qId]}?attr=sale`)
            .then(res => formatRes(res.data, this, true))
            .finally(() => (this.loading = false));
        } else if (this.$route.query[duplicate.id]) {
          this.fetch(this.$route.query[duplicate.id], true);
        }
      } else {
        this.form.customer_id = this.$store.state.settings.default_customer;
      }
    },
    methods: {
      create() {
        this.$http
          .get(`${route}/create`)
          .then(res => (this.attributes = res.data))
          .finally(() => (this.loading = false));
      },
      fetch(id, did) {
        this.$http
          .get(`${route}/${id}`)
          .then(res => formatRes(res.data, this, did))
          .finally(() => (this.loading = false));
      },
      // selectItem(item) {
      //   this.query = '';
      //   this.addToOrder(JSON.parse(item));
      // },
      selectPurchaseItem(id) {
        this.loading = true;
        this.$http
          .get('app/purchase_order_items/' + id)
          .then(res => this.addToOrder(res.data))
          .catch(err => console.log(err))
          .finally(() => (this.loading = false));
      },
      // selectSaleItem(id, item = null) {
      //   this.loading = true;
      //   if (item) {
      //     this.addToOrder(item);
      //     this.loading = false;
      //   } else {
      //     this.$http
      //       .get('app/sale_order_items/' + id)
      //       .then(res => this.addToOrder(res.data))
      //       .catch(err => console.log(err))
      //       .finally(() => (this.loading = false));
      //   }
      // },
      searchItems(search) {
        if (model == 'return_order' && !this.form.type) {
          this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
          return false;
        }
        if (search.length < 35 && !search.includes('(')) {
          let purchase = model == 'purchase' || model == 'adjustment';
          this.getItems(search, this, purchase);
        }
      },
      getItems: _debounce((search, vm, purchase) => {
        vm.searching = true;
        let iUrl = 'app/items/search?q=' + search;
        if (purchase) {
          iUrl = 'app/items_wp/search?type=standard,service&q=' + search;
        }
        let parsedBarcode = null;
        const search_delay = vm.$store.getters.search_delay;
        const scale_barcode = vm.$store.getters.scale_barcode;
        if (scale_barcode && scale_barcode.total_digits && scale_barcode.total_digits == search.length) {
          parsedBarcode = vm.parseScaleBarcode(search, scale_barcode);
          iUrl = 'app/items/search?scale=1&q=' + parsedBarcode.item_code + '&barcode=' + search;
        }
        vm.$http
          .get(iUrl)
          .then(res => {
            if (parsedBarcode && res.data && res.data.id) {
              let price =
                res.data.location_stock.length > 0 && res.data.location_stock[0].price
                  ? parseFloat(res.data.location_stock[0].price)
                  : parseFloat(res.data.price);
              res.data.barcode_price = vm.formatDecimal(parsedBarcode.price, 4);
              res.data.barcode_qty = vm.formatQtyDecimal(parsedBarcode.weight, 4);
              if (!res.data.barcode_price) {
                res.data.barcode_price = vm.formatDecimal(price / res.data.barcode_qty);
              }
              if (!res.data.barcode_qty) {
                res.data.barcode_qty = vm.formatQtyDecimal(parsedBarcode.price / price);
              }
              vm.selectSaleItem(res.data.id, res.data);
              vm.result = [];
            } else {
              if (res.data && res.data.length == 1) {
                if (purchase) {
                  vm.selectPurchaseItem(res.data[0].id);
                } else {
                  vm.selectSaleItem(res.data[0].id);
                }
                vm.result = [];
              } else {
                vm.result = res.data || [];
              }
            }
          })
          .finally(() => (vm.searching = false));
      }, search_delay || 250),
      searchCustomers(search) {
        if (search !== '' && !this.customers.find(c => c.label == search)) {
          this.getCustomers(search, this);
        }
      },
      getCustomers: _debounce((search, vm) => {
        vm.searching = true;
        vm.$http
          .get('app/customers/search?q=' + search)
          .then(res => (vm.customers = res.data))
          .finally(() => (vm.searching = false));
      }, search_delay || 250),
      searchSuppliers(search) {
        if (search !== '' && !this.suppliers.find(c => c.label == search)) {
          this.getSuppliers(search, this);
        }
      },
      getSuppliers: _debounce((search, vm) => {
        vm.searching = true;
        vm.$http
          .get('app/suppliers/search?q=' + search)
          .then(res => (vm.suppliers = res.data))
          .finally(() => (vm.searching = false));
      }, search_delay || 250),
      is_draftable() {
        if (this.$route.params.id) {
          return this.is_draft;
        }
        return true;
      },
    },
  };
}

export default Order;
