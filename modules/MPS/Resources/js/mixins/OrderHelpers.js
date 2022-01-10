import _isEqual from 'lodash/isEqual';
import _debounce from 'lodash/debounce';
import OrderCommonMethods from '@mpsjs/mixins/OrderCommonMethods';

function OrderHelpers(field, form, pos, adj) {
  return {
    mixins: [OrderCommonMethods(field, form, pos, adj)],
    data() {
      const itemDiscount = (rule, value, callback) => {
        if (value && value > this.edit_item.max_discount) {
          callback(new Error(this.$t('max_discount_error', { percent: parseFloat(this.edit_item.max_discount) })));
        } else {
          callback();
        }
      };
      const itemPrice = (rule, value, callback) => {
        if (value && (value > this.edit_item.max_price || value < this.edit_item.min_price)) {
          callback(
            new Error(
              this.$t('mm_price_error', {
                min: parseFloat(this.edit_item.min_price),
                max: parseFloat(this.edit_item.max_price),
              })
            )
          );
        } else {
          callback();
        }
      };
      return {
        show: false,
        field: field,
        gift_cards: [],
        gift_card: null,
        cloading: false,
        itemPrice: itemPrice,
        itemDiscount: itemDiscount,
        itemRules: {
          cost: [
            {
              required: true,
              type: 'number',
              message: this.$t('field_is_required', { field: this.$t('cost') }),
              trigger: ['change', 'blur'],
            },
          ],
          price: [
            {
              required: true,
              type: 'number',
              message: this.$t('field_is_required', { field: this.$t('price') }),
              trigger: ['change', 'blur'],
            },
            { validator: itemPrice, trigger: ['change', 'blur'] },
          ],
          quantity: [
            {
              required: true,
              type: 'number',
              message: this.$t('field_is_required', { field: this.$t('quantity') }),
              trigger: ['change', 'blur'],
            },
          ],
          discount: [{ validator: itemDiscount, trigger: ['change', 'blur'] }],
        },
      };
    },
    computed: {
      customer() {
        return this.customers.find(c => c.value == this[form].customer_id);
      },
      orderTotalItems() {
        return this[form] && this[form].items ? this[form].items.length : 0;
      },
      orderOriginalTotal() {
        if (this[form] && this[form].discount_method != 'order') {
          let items = [...this[form].items];
          let total = items
            .map(i => {
              let item = { tax_included: i.tax_included, allTaxes: [...i.allTaxes], quantity: i.quantity, selected: {} };
              item[this.field] = i[this.field];
              item.selected.variations = i.selected.variations.map(v => ({ ...v }));
              item.selected.portions = i.selected.portions.map(v => ({ ...v }));
              item.selected.modifiers = i.selected.modifiers.map(v => ({ ...v }));
              return parseFloat(this.calcRowOriginalTotal(item));
            })
            .reduce((a, i) => a + i, 0);
          return this.formatDecimal(total);
        }
        return 0;
      },
      orderDiscount() {
        return this[form] && this[form].discount_method == 'order'
          ? this.formatDecimal((this.orderTotalAmount * this[form].discount) / 100)
          : 0;
      },
      orderTotalQuantity() {
        return this[form] ? this[form].items.map(item => item.quantity).reduce((total, curr) => total + curr, 0) : 0;
      },
      orderPayableAmount() {
        return this[form] ? this.formatDecimal(this.orderTotalAmount - this.orderDiscount) : 0;
      },
      paymentChange() {
        return this.paymentForm.amount > this.orderTotalAmount ? this.paymentForm.amount - this.orderTotalAmount : 0;
      },
      totalItemTax: function() {
        let tax = this[form].items.reduce((tax, item) => {
          item = this.doTaxCalculation(item);
          let rowTax = item.taxes.reduce((a, t) => a + parseFloat(t.amount) * parseFloat(item.quantity), 0);
          return parseFloat(tax + rowTax);
        }, 0);
        return this.formatDecimal(tax);
      },
      orderTotalAmount: function() {
        let items = [...this[form].items];
        return this.formatDecimal(
          this[form].items.reduce((a, item) => {
            return a + (item ? this.calcRowTotal(item) : 0);
          }, 0)
        );
      },
    },
    methods: {
      giftCardSelected(n) {
        this.gift_card = this.gift_cards.find(c => c.number == n);
      },
      searchGiftCard(n) {
        this.getGiftCard(n, this);
      },
      getGiftCard: _debounce((search, vm) => {
        vm.searching = true;
        vm.$http
          .get('app/gift_cards/search?q=' + search)
          .then(res => {
            if (res.data.length == 1) {
              vm.gift_card = res.data[0];
              vm.paymentForm.gift_card_number = res.data[0].number;
            } else {
              vm.gift_cards = res.data;
            }
          })
          .finally(() => (vm.searching = false));
      }, search_delay || 250),
      getPortionById(p, id) {
        return p.find(p => p.id == id);
      },
      getPortionChoosable(p, gId, iId) {
        let group = p.choosables.find(g => g.id == gId);
        return group.selected == iId;
      },
      changeSelectedPortion(v, i) {
        let old = v ? this.edit_item.portions.find((p, pi) => pi == i) : {};
        let portion = v ? this.edit_item.portions.find(p => p.id == v) : {};
        let selected = { id: portion.id, name: portion.name, choosables: [], quantity: old.quantity ? old.quantity : 1 };
        portion.choosables.map(g => {
          selected.choosables.push({ id: g.id, selected: g.items[0].item_id });
        });
        this.edit_item.selected.portions[i] = { ...selected };
      },
      modOptLabel(opt) {
        return opt.item.name + ' (+' + this.formatDecimal(opt.item[this.field]) + ')';
      },
      itemQuantityChanged(item) {
        if (pos) {
          this.$event.fire('pos:order:update', { item, qty: item.quantity, set: true, force: false, vCheck: false });
        } else {
          this.addToOrder(item, item.quantity, true);
        }
      },
      itemUnitChanged() {
        let item = this.calculateUnitPrice(this.edit_item, this.field);
        this.edit_item = this.doTaxCalculation(item);
      },
      itemVariationQuantityChanged(item) {
        item.quantity = item.selected.variations.reduce((a, v) => a + parseFloat(v.quantity), 0);
        if (pos) {
          this.$event.fire('pos:order:update', { item, qty: item.quantity, set: true, force: true, vCheck: true });
        } else {
          this.addToOrder(item, item.quantity, true, true, true);
        }
      },
      itemPortionQuantityChanged(item) {
        item.quantity = item.selected.portions.reduce((a, p) => a + parseFloat(p.quantity), 0);
        if (pos) {
          this.$event.fire('pos:order:update', { item, qty: item.quantity, set: true, force: true, vCheck: true });
        } else {
          this.addToOrder(item, item.quantity, true, true, true);
        }
      },
      itemModifierQuantityChanged(item) {
        if (pos) {
          this.$event.fire('pos:order:update', { item, qty: item.quantity, set: true, force: true, vCheck: true });
        } else {
          this.addToOrder(item, item.quantity, true, true, true);
        }
      },
      checkDisc(disc, amt) {
        if (disc) {
          if (disc.toString().indexOf('%') !== -1) {
            var pds = disc.split('%');
            let amount = this.formatDecimal((parseFloat(amt) * parseFloat(pds[0])) / 100);
            return { discount: disc, discount_amount: isNaN(parseFloat(amount)) ? 0 : parseFloat(amount) };
          }
          return { discount: disc, discount_amount: isNaN(parseFloat(disc)) ? 0 : parseFloat(disc) };
        }
        return { discount: '', discount_amount: 0 };
      },
      calcRowOriginalTotal(item) {
        let modifiers_price = 0;
        if (item.selected.modifiers.length) {
          modifiers_price += item.selected.modifiers.reduce((a, m) => {
            let price = parseFloat(m[this.field]);
            m.taxes = this.calculateTaxes(item.allTaxes, price, item.tax_included);
            m.total_tax_amount = this.calcItemTax(m);
            return a + parseFloat(m.quantity) * (price + (item.tax_included == 1 ? 0 : parseFloat(m.total_tax_amount)));
          }, 0);
        }

        if (item.selected.variations.length || item.selected.portions.length) {
          if (item.selected.variations.length) {
            modifiers_price += item.selected.variations.reduce((a, v) => {
              let price = parseFloat(v[this.field] ? v[this.field] : item[this.field]);
              v.taxes = this.calculateTaxes(item.allTaxes, price, item.tax_included);
              v.total_tax_amount = this.calcItemTax(v);
              return a + parseFloat(v.quantity) * (price + (item.tax_included == 1 ? 0 : parseFloat(v.total_tax_amount)));
            }, 0);
          }
          if (item.selected.portions.length) {
            modifiers_price += item.selected.portions.reduce((a, p) => {
              let price = parseFloat(p[this.field]);
              p.taxes = this.calculateTaxes(item.allTaxes, price, item.tax_included);
              p.total_tax_amount = this.calcItemTax(p);
              return a + parseFloat(p.quantity) * (price + (item.tax_included == 1 ? 0 : parseFloat(p.total_tax_amount)));
            }, 0);
          }
          return modifiers_price;
        }

        item.taxes = this.calculateTaxes(item.allTaxes, item[this.field], item.tax_included);
        item.total_tax_amount = this.calcItemTax(item);
        return (
          modifiers_price +
          parseFloat(item.quantity) * (parseFloat(item[this.field]) + (item.tax_included == 1 ? 0 : item.total_tax_amount))
        );
      },
      doTotal(r, incl, ori) {
        return (
          parseFloat(r.quantity) *
          (parseFloat(r[this.field]) -
            parseFloat(r[this.field] && !ori && r.discount_amount ? r.discount_amount : 0) +
            (incl == 1 ? 0 : parseFloat(r[this.field] ? r.total_tax_amount : 0)))
        );
      },
      calcRowTotal(item, selected = false) {
        if (selected && selected.taxes && selected.taxes.length && !selected.total_tax_amount && selected.total_tax_amount != 0) {
          selected.taxes = this.calculateTaxes(selected.allTaxes, selected[this.field], selected.tax_included);
          selected.total_tax_amount = this.calcItemTax(selected);
        } else if (item.taxes && item.taxes.length && !item.total_tax_amount && item.total_tax_amount != 0) {
          item.taxes = this.calculateTaxes(item.allTaxes, item[this.field], item.tax_included);
          item.total_tax_amount = this.calcItemTax(item);
        }
        let modifiers_price = 0;
        if (item.selected.modifiers && item.selected.modifiers.length) {
          modifiers_price += item.selected.modifiers.reduce((a, m) => a + this.doTotal(m, item.tax_included), 0);
        }
        if (!selected && (item.selected.variations.length || item.selected.portions.length)) {
          if (item.selected.variations.length) {
            modifiers_price += item.selected.variations.reduce((a, v) => a + this.doTotal(v, item.tax_included), 0);
          }
          if (item.selected.portions.length) {
            modifiers_price += item.selected.portions.reduce((a, p) => a + this.doTotal(p, item.tax_included), 0);
          }
          return modifiers_price;
        }
        return (
          modifiers_price +
          this.formatDecimal(
            parseFloat(selected ? selected.quantity : item.quantity) *
              (parseFloat(selected ? selected[this.field] : item[this.field]) -
                parseFloat(selected ? selected.discount_amount : item.discount_amount ? item.discount_amount : 0) +
                (item.tax_included == 1 ? 0 : selected ? selected.total_tax_amount : item.total_tax_amount))
          )
        );
      },
      guid() {
        let sha1 = require('sha1');
        return sha1(this.uuid() + Date.now().toString() + Math.random().toString());
      },
      uuid() {
        return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, c =>
          (c ^ (crypto.getRandomValues(new Uint8Array(1))[0] & (15 >> (c / 4)))).toString(16)
        );
      },
      portionChanged(v) {
        let portion = v ? this.edit_item.portions.find(p => p.id == v) : {};
        this.edit_item.portion = { ...portion };
      },
      addPortionToEditItem() {
        let portion = {
          quantity: 1,
          choosables: [],
          essentials: [],
          portion_items: [],
          id: this.edit_item.portions[0].id,
          name: this.edit_item.portions[0].name,
        };
        this.edit_item.portions[0].portion_items.map(e => {
          portion.portion_items.push(e);
        });
        this.edit_item.portions[0].essentials.map(e => {
          portion.essentials.push(e);
        });
        this.edit_item.portions[0].choosables.map(g => {
          portion.choosables.push({ id: g.id, selected: g.items[0].item_id });
        });
        this.edit_item.selected.portions.push(portion);
      },
      editItem(index) {
        if (!this[form].items[index].guid) {
          this[form].items[index].guid = this.guid();
        }
        this.edit_item = JSON.parse(JSON.stringify(this[form].items[index]));
        let pl = this.edit_item.selected.portions.length;
        if (pl) {
          let dif = this.edit_item.quantity - pl;
          if (dif && dif > 0) {
            if (this.edit_item.portions.length == 1) {
              this.edit_item.selected.portions[0].quantity = this.edit_item.quantity;
            } else {
              for (let i = 1; i <= dif; i++) {
                let portion = {
                  quantity: 1,
                  choosables: [],
                  id: this.edit_item.portions[0].id,
                  name: this.edit_item.portions[0].name,
                };
                this.edit_item.portions[0].choosables.map(g => {
                  portion.choosables.push({ id: g.id, selected: g.items[0].item_id });
                });
                this.edit_item.selected.portions.push(portion);
              }
            }
          } else if (dif && dif < 0) {
            for (let i = dif; i < 0; i++) {
              this.edit_item.selected.portions.pop();
            }
          }
        }

        this.edit_item.discount = this.edit_item.discount ? parseFloat(this.edit_item.discount) : null;
        this.edit_item.allTaxes = this.edit_item.allTaxes.map(t => t.id);
        this.show_item_edit = true;
      },
      updateItem() {
        // TODO Manually check the edit_item
        this.$refs.editItemForm.validate(valid => {
          if (valid) {
            if (field == 'price') {
              if (
                this.edit_item.serials &&
                this.edit_item.has_serials &&
                this.edit_item.serials.length &&
                this.edit_item.selected.serials.length != this.edit_item.quantity
              ) {
                this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
                return false;
              }
            } else {
              if (this.edit_item.serials && this.edit_item.selected.serials.length != this.edit_item.quantity) {
                this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
                return false;
              }
            }
            this.edit_item.selected.modifiers = [];
            if (this.edit_item.modifiers && this.edit_item.modifiers.length) {
              this.edit_item.modifiers.map(m => {
                if (m.selected) {
                  if (m.show_as == 'checkbox' || m.show_as == 'select_multiple') {
                    m.selected.map(s => {
                      m.options.map(o => {
                        if (s == o.id) {
                          this.edit_item.selected.modifiers.push({
                            id: o.id,
                            mId: m.id,
                            quantity: 1,
                            cost: o.item.cost,
                            option: o.item.name,
                            price: o.item.price,
                            title: m.title,
                          });
                        }
                      });
                    });
                  } else {
                    m.options.map(o => {
                      if (m.selected == o.id) {
                        this.edit_item.selected.modifiers.push({
                          id: o.id,
                          mId: m.id,
                          quantity: 1,
                          cost: o.item.cost,
                          option: o.item.name,
                          price: o.item.price,
                          title: m.title,
                        });
                      }
                    });
                  }
                }
              });
            }
            if (this.edit_item.portions && this.edit_item.portions.length) {
              this.edit_item.quantity = this.edit_item.selected.portions.reduce((a, p) => a + parseFloat(p.quantity), 0);
            }
            if (this.edit_item.variations && this.edit_item.variations.length) {
              this.edit_item.quantity = this.edit_item.selected.variations.reduce((a, p) => a + parseFloat(p.quantity), 0);
            }
            this.edit_item.allTaxes = this.taxes.filter(t => this.edit_item.allTaxes.includes(t.id));
            this.edit_item = this.calculateItemDiscountAmount(this.edit_item);

            if (field == 'price') {
              let item = this[form].items.find(i => i.id == this.edit_item.id);
              if (item.promotions != this.edit_item.promotions) {
                this.edit_item.allPromotions.map(p => {
                  if (this.edit_item.promotions.includes(p.id)) {
                    if (p.type == 'BXGY') {
                      this[form].items = this[form].items.filter(i => i.id != p.item_id_to_get && i.promo_item_id != this.edit_item.id);
                    }
                  }
                });
              }

              this.applicablePromotions(this.edit_item).map(p => {
                if (p.type == 'BXGY' && this.edit_item.quantity >= p.quantity_to_buy) {
                  this.addOrderRowByItemID(p.item_id_to_get, this.edit_item.id);
                }
              });
            }
            this.edit_item = this.doTaxCalculation(this.edit_item);
            let item = JSON.parse(JSON.stringify(this.edit_item));
            this[form].items = this[form].items.map(i => (i.guid == item.guid ? item : i));
            if (pos) {
              this.$event.fire('pos:order:update', { item, qty: item.quantity, set: true, force: true, vCheck: false });
            } else {
              this.addToOrder(item, item.quantity, true, true);
            }
            this.edit_item = null;
            this.show_item_edit = false;
          } else {
            this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
          }
        });
      },
      deleteItem(item, ex, exi) {
        if (item.promotions && item.promotions.length) {
          item.promotions = item.allPromotions ? item.allPromotions.filter(p => item.promotions.includes(p.id)) : [];
          item.promotions.map(p => {
            if (p.type == 'BXGY' && item.quantity >= p.quantity_to_buy) {
              this.$store.commit('delPromotionItem', { id: p.item_id_to_get, promo_item_id: item.id });
            }
          });
        }
        if (pos) {
          this.$store.commit('delOrderRow', item);
        } else {
          this[form].items = this[form].items.filter(i => i.guid != item.guid);
        }
        this.edit_item = null;
        this.show_item_edit = false;
      },
      deleteItemVariation(row, variation, exi) {
        let item = this[form].items.find(i => i.guid == row.guid);
        item.selected_variation = null;
        item.selected.variations = item.selected.variations.filter(v => v.id !== variation.id);
        item.quantity = item.selected.variations.reduce((a, v) => a + parseFloat(v.quantity), 0);
        if (item.selected.variations.length <= 0) {
          this.deleteItem(item);
        } else if (pos) {
          this.$store.commit('setOrderRow', item);
        }
      },
      deleteItemPortion(row, portion, spi) {
        let item = this[form].items.find(i => i.guid == row.guid);
        item.selected_portion = null;
        item.selected.portions = item.selected.portions.filter((p, i) => i !== spi);
        item.quantity = item.selected.portions.reduce((a, p) => a + parseFloat(p.quantity), 0);
        if (item.selected.portions.length <= 0) {
          this.deleteItem(item);
        } else if (pos) {
          this.$store.commit('setOrderRow', item);
        }
      },
      deleteItemModifier(row, m, mi) {
        let item = this[form].items.find(i => i.guid == row.guid);
        item.selected.modifiers = item.selected.modifiers.filter(mo => mo.id !== m.id);
        item.modifiers = item.modifiers.map(mo => {
          if (mo.id == m.mId) {
            if (Array.isArray(mo.selected)) {
              mo.selected = mo.options.filter(o => o.id == m.id);
            } else {
              mo.selected = null;
            }
          }
          return mo;
        });
        if (pos) {
          this.$store.commit('setOrderRow', item);
        }
      },
      remove(row, type = '', extra, exindex) {
        if (!pos || this.$store.getters.superAdmin) {
          this['deleteItem' + type](row, extra, exindex);
        } else {
          let value = null;
          this.$Modal.confirm({
            width: 260,
            okText: this.$t('ok'),
            cancelText: this.$t('cancel'),
            title: this.$t('pin_required'),
            render: h => {
              return h('Input', {
                props: {
                  value,
                  autofocus: true,
                  placeholder: this.$t('type_pin'),
                },
                on: {
                  input: val => (value = val),
                },
              });
            },
            onOk: () => {
              // TODO check ping and then delete
              this.$Message.info('Check POS pin ' + value + ' then perform delete!');
              this['deleteItem' + type](row, extra, exindex);
            },
          });
        }
      },
    },
  };
}

export default OrderHelpers;
