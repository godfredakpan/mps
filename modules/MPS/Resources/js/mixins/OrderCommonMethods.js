import InputNumberComponent from '@mpscom/helpers/InputNumberComponent';
import SelectVariationComponent from '@mpscom/helpers/SelectVariationComponent';
function CommonMethods(field, form, pos, adj) {
  return {
    components: { InputNumberComponent, SelectVariationComponent },
    data() {
      return {
        item: {},
        variantModal: false,
      };
    },
    watch: {
      variantModal: function(newVal) {
        if (!newVal) {
          this.item = {};
          this.$refs.selectVarMod.$refs.form.resetFields();
        }
      },
    },
    methods: {
      variationSubmitted(v) {
        let qty = v.quantity ? v.quantity : 1;
        this.item.selected = { modifiers: [], variations: [], portions: [], serials: [] };
        if (v.serials && v.serials.length) {
          this.item.selected.serials = v.serials;
        }
        if (v.modifiers && v.modifiers.length) {
          this.item.selected.modifiers = v.modifiers;
        }
        if (v.selected_variation) {
          this.item.selected.variations.push(v.selected_variation);
        }
        if (v.selected_portion) {
          this.item.selected.portions.push(v.selected_portion);
        }
        this.addToOrder(this.item, qty, false, false, true);
        this.variantModal = false;
      },
      calculateItemDiscountAmount(item) {
        let discount_amount = 0;
        if (item.selected.modifiers.length) {
          if (item.selected.modifiers.length) {
            item.selected.modifiers = item.selected.modifiers.map(m => {
              let m_discount_amount = this.formatDecimal((parseFloat(m[this.field]) * parseFloat(item.discount)) / 100);
              this.applicablePromotions(item).map(pr => {
                m_discount_amount += this.formatDecimal((parseFloat(m[this.field] - m_discount_amount) * parseFloat(pr.discount)) / 100);
              });
              discount_amount += m_discount_amount;
              m.discount_amount = m_discount_amount;
              return m;
            });
          }
        }
        if (item.selected.variations.length || item.selected.portions.length) {
          if (item.selected.variations.length) {
            item.selected.variations = item.selected.variations.map(v => {
              let v_discount_amount = this.formatDecimal((parseFloat(v[this.field]) * parseFloat(item.discount)) / 100);
              this.applicablePromotions(item).map(pr => {
                v_discount_amount += this.formatDecimal((parseFloat(v[this.field] - v_discount_amount) * parseFloat(pr.discount)) / 100);
              });
              discount_amount += v_discount_amount;
              v.discount_amount = v_discount_amount;
              return v;
            });
          }
          if (item.selected.portions.length) {
            item.selected.portions = item.selected.portions.map(p => {
              let p_discount_amount = this.formatDecimal((parseFloat(p[this.field]) * parseFloat(item.discount)) / 100);
              this.applicablePromotions(item).map(pr => {
                p_discount_amount += this.formatDecimal((parseFloat(p[this.field] - p_discount_amount) * parseFloat(pr.discount)) / 100);
              });
              discount_amount += p_discount_amount;
              p.discount_amount = p_discount_amount;
              return p;
            });
          }
          item.discount_amount = parseFloat(this.formatDecimal(discount_amount));
          return item;
        }
        discount_amount = this.formatDecimal((parseFloat(item[this.field]) * parseFloat(item.discount)) / 100);
        this.applicablePromotions(item).map(p => {
          discount_amount += this.formatDecimal((parseFloat(item[this.field] - discount_amount) * parseFloat(p.discount)) / 100);
        });
        item.discount_amount = discount_amount;
        return item;
      },
      applySXGD(p) {
        this[form].items = this[form].items.map(item => {
          if (item.promotions && item.promotions.length) {
            item.promotions.map((p, i) => {
              item = this.calculateItemDiscountAmount(item);
            });
          }
        });
      },
      applicablePromotions(item) {
        return item.allPromotions ? item.allPromotions.filter(p => item.promotions.includes(p.id)) : [];
      },
      applyPromotions(item, reset) {
        if (!item.allPromotions || item.allPromotions.length < 1) {
          return [];
        }
        let promos = item.allPromotions.filter(p => this.applyPromoDiscount(p, item));
        return promos.map(p => p.id);
      },
      addPromoDiscount(p, item) {
        if (!p.id) {
          p = item.allPromotions.find(ap => ap.id == p);
        }
        if (!item.allPromotions.find(ap => ap.id == p.id)) {
          item.allPromotions.push(p);
        }
        return item;
      },
      applyPromoDiscount(p, item) {
        if (p.type == 'simple') {
          return true;
        } else if (p.type == 'advance') {
          if (item.quantity >= parseFloat(p.quantity_to_buy)) {
            return true;
          }
        } else if (p.type == 'BXGY') {
          if (item.quantity >= parseFloat(p.quantity_to_buy)) {
            // TODO apply after selected
            this.addOrderRowByItemID(p.item_id_to_get, item.id);
            return true;
          }
        } else if (p.type == 'SXGD') {
          if (this.orderTotalAmount >= parseFloat(p.amount_to_spend)) {
            // TODO apply after selected
            // this.applySXGD(p);
            return true;
          }
        }
        return false;
      },
      checkCustomerGroup(cId) {
        this.$nextTick(() => {
          let customer = this.customers.find(c => c.value == this[form].customer_id);
          if (customer && customer.customer_group) {
            this[form].items.map(item => {
              item.discount = (item.discount ? parseFloat(item.discount) : 0) + parseFloat(customer.customer_group.discount);
              this.addToOrder(item, item.quantity, true, true);
            });
          }
        });
      },
      checkPromos(item) {
        item.promotions = item.valid_promotions;
        delete item.valid_promotions;
        if (item.promotions && item.promotions.length) {
          item.promotions.map(p => {
            item = this.addPromoDiscount(p, item);
          });
        }
        if (item.categories && item.categories[0].valid_promotions && item.categories[0].valid_promotions.length) {
          item.categories[0].valid_promotions.map(p => {
            item = this.addPromoDiscount(p, item);
          });
        }
        item.promotions = this.applyPromotions(item);
        item = this.calculateItemDiscountAmount(item);
        item = this.doTaxCalculation(item);
        return item;
      },
      calcItemTax(item) {
        return item.taxes && item.taxes.length ? item.taxes.reduce((a, t) => a + parseFloat(t.amount), 0) : 0;
      },
      doTaxCalculation(item, discounted) {
        let amount = item[this.field] - (!discounted && item.discount_amount ? parseFloat(item.discount_amount) : 0);
        item.taxes = this.calculateTaxes(item.allTaxes, amount, item.tax_included);

        if (item.selected.modifiers.length) {
          item.selected.modifiers = item.selected.modifiers.map(m => {
            let amount = m[this.field] - (!discounted && m.discount_amount ? parseFloat(m.discount_amount) : 0);
            m.taxes = this.calculateTaxes(item.allTaxes, amount);
            m.total_tax_amount = this.calcItemTax(m);
            return m;
          });
        }
        if (item.selected.variations.length) {
          item.selected.variations = item.selected.variations.map(v => {
            let amount = v[this.field] - (!discounted && v.discount_amount ? parseFloat(v.discount_amount) : 0);
            v.taxes = this.calculateTaxes(item.allTaxes, amount);
            v.total_tax_amount = this.calcItemTax(v);
            return v;
          });
        }
        if (item.selected.portions.length) {
          item.selected.portions = item.selected.portions.map(p => {
            let amount = p[this.field] - (!discounted && p.discount_amount ? parseFloat(p.discount_amount) : 0);
            p.taxes = this.calculateTaxes(item.allTaxes, amount);
            p.total_tax_amount = this.calcItemTax(p);
            return p;
          });
        }
        return item;
      },
      calculateTaxes(taxes, amount, included) {
        taxes = this.applicableTaxes(taxes);
        let c_taxes = taxes.filter(t => t.compound == true);
        let nc_taxes = taxes.filter(t => t.compound == false);
        let non_compound = nc_taxes.map(t => ({
          ...t,
          value: t.id,
          on: parseFloat(amount),
          amount: this.calculateTaxOn(t.rate, amount, included),
        }));
        let tax_amount = non_compound.reduce((a, nct) => a + parseFloat(nct.amount), 0);
        let compound = c_taxes.map(t => ({
          ...t,
          value: t.id,
          on: parseFloat(amount) + parseFloat(tax_amount),
          amount: this.calculateTaxOn(t.rate, parseFloat(amount) + parseFloat(tax_amount), included),
        }));
        return [...non_compound, ...compound];
      },
      calculateTaxOn(rate, amount, included) {
        return included == 1 ? this.calculateInclusiveTax(rate, amount) : this.calculateExclusiveTax(rate, amount);
      },
      calculateInclusiveTax(rate, amount) {
        return this.formatDecimal((parseFloat(amount) * parseFloat(rate)) / (100 + parseFloat(rate)), 4);
      },
      calculateExclusiveTax(rate, amount) {
        return this.formatDecimal((parseFloat(amount) * parseFloat(rate)) / 100, 4);
      },
      applicableTaxes(taxes) {
        return taxes.filter(t => {
          let location = this.$store.getters.location;
          if (field == 'cost') {
            if (adj) {
              return !t.same;
            }
            let supplier = this.suppliers.find(c => c.value == this[form].supplier_id);
            if (t.state && supplier && location) {
              let same = supplier.state == location.state;
              return t.same ? same : !same;
            }
          } else {
            let customer = this.customers ? this.customers.find(c => c.value == this[form].customer_id) : null;
            if (t.state && customer && location) {
              let same = customer.state == location.state;
              return t.same ? same : !same;
            }
          }
          return true;
        });
      },
      playSound(ok = true) {
        if (this.$store.getters.play_sound == 1) {
          this.$event.fire(ok ? 'okSound' : 'errorSound');
        }
      },
      addOrderRowByItemID(id, item_id) {
        this.$http.get('app/items/' + id).then(res => {
          res.data[this.field] = 0;
          res.data.promo = 1;
          res.data.promotions = null;
          res.data.promo_item_id = item_id;
          res.data.categories[0].promotions = null;
          this.addToOrder(res.data);
        });
      },
      selectItem(item) {
        this.query = '';
        this.addToOrder(JSON.parse(item));
      },
      selectSaleItem(id, item = null) {
        if (pos) {
          this.cloading = true;
        } else {
          this.loading = true;
        }
        if (item) {
          this.addToOrder(item);
          this.loading = false;
        } else {
          this.$http
            .get('app/sale_order_items/' + id)
            .then(res => this.addToOrder(res.data))
            .catch(err => console.log(err))
            .finally(() => {
              if (pos) {
                this.query = '';
                this.cloading = false;
              } else {
                this.loading = false;
              }
            });
        }
      },
      addToOrder(item, qty, set, force, vCheck) {
        if (!item) {
          return false;
        }
        if (!this[form] || !item.code || !item.name) {
          return false;
        }
        if (!item.selected) {
          item.selected = { modifiers: [], portions: [], variations: [], serials: [] };
          if (!adj && field == 'cost') {
            item.selected.serials = [{ number: '', till: '' }];
          }
        }
        if (force) {
          if (!set) {
            item = this.checkPromos(item);
          }
          if (pos) {
            this.$store.commit('setOrderRow', item);
          } else {
            this[form].items = this[form].items.map(i => (i.item_id == item.id ? item : i));
          }
        } else {
          this.cloading = true;
          if (
            !vCheck &&
            ((item.serials && item.serials.length) ||
              (item.modifiers && item.modifiers.length > 0) ||
              (item.portions && item.portions.length > 0) ||
              (item.variants && item.variants.length > 0 && item.variations && item.variations.length > 0))
          ) {
            if (item.type == 'recipe' && item.portions.length == 1 && (!item.modifiers || !item.modifiers.length)) {
              item.selected.portions = [...item.portions];
              this.addToOrder(item, 1, false, false, true);
              return true;
            }
            this.cloading = false;
            this.item = JSON.parse(JSON.stringify(item));
            this.variantModal = true;
            return false;
          }
          let nItem = {};
          let exists =
            this[form].items && this[form].items.length > 0
              ? this[form].items.find(
                  i =>
                    i.id == item.id &&
                    i.promo == item.promo &&
                    (!i.variation || (i.variation && item.variation && i.variation.id == item.variation.id))
                )
              : false;
          if (exists) {
            exists.quantity = set
              ? parseFloat(qty)
              : parseFloat(exists.quantity) + (qty ? parseFloat(qty) : item.barcode_qty ? item.barcode_qty : 1);
            if (item.selected.portions.length) {
              item.selected.portions = item.selected.portions.map(p => {
                p.quantity = p.quantity ? p.quantity : 1;
                return p;
              });
              exists.selected.portions.push(item.selected.portions[0]);
            }
            if (item.selected.variations.length) {
              exists.selected.variations.push(item.selected.variations[0]);
            }
            if (item.selected.modifiers.length) {
              if (exists.selected.modifiers.length) {
                item.selected.modifiers.map(m => {
                  exists.selected.modifiers = exists.selected.modifiers.map(e => {
                    if (e.id == m.id) {
                      e.quantity = parseFloat(e.quantity) + 1;
                    }
                    return e;
                  });
                  if (!exists.selected.modifiers.find(e => e.id == m.id)) {
                    exists.selected.modifiers.push(m);
                  }
                });
              } else {
                exists.selected.modifiers = item.selected.modifiers;
              }
            }
            exists = this.doTaxCalculation(exists);
            if (field == 'price') {
              exists = this.checkPromos(exists);
            }

            if (pos) {
              this.$store.commit('setOrderRow', exists);
            } else {
              this[form].items = this[form].items.map(i => (i.item_id == exists.id ? exists : i));
            }
            this.alert(this.$root.$t('cart_item_updated', { item: item.name }));
            this.playSound(true);
          } else {
            if (field == 'price' && this.customer && this.customer.customer_group) {
              item.discount =
                parseFloat(item.discount ? item.discount : 0) +
                (this.customer.customer_group ? parseFloat(this.customer.customer_group.discount) : 0);
            }
            if (item.unit) {
              item.allUnits = [{ ...item.unit, subunits: null }, ...item.unit.subunits];
            }
            nItem = {
              ...item,
              comment: '',
              item_id: item.id,
              item_unit_id: item.unit_id,
              unit_id:
                field == 'price'
                  ? item.sale_unit_id
                    ? item.sale_unit_id
                    : item.unit_id
                  : item.purchase_unit_id
                  ? item.purchase_unit_id
                  : item.unit_id,
              guid: this.guid(),
              allTaxes: [...item.taxes],
              selected: { ...item.selected },
              discount: item.discount ? item.discount : null,
              quantity: qty ? parseFloat(qty) : item.barcode_qty ? item.barcode_qty : 1,
            };
            nItem.selected.portions = nItem.selected.portions.map(p => {
              p.quantity = p.quantity ? p.quantity : 1;
              return p;
            });
            nItem['price'] =
              nItem.location_stock.length > 0 && nItem.location_stock[0]['price']
                ? parseFloat(nItem.location_stock[0]['price'])
                : parseFloat(nItem['price']);
            nItem['cost'] =
              nItem.location_stock.length > 0 && nItem.location_stock[0]['cost']
                ? parseFloat(nItem.location_stock[0]['cost'])
                : parseFloat(nItem['cost']);
            nItem = this.doTaxCalculation(nItem);
            nItem.total_tax_amount = this.calcItemTax(nItem);
            if (field == 'price') {
              nItem.promotions = item.promotions ? item.promotions : [];
              nItem.allPromotions = item.allPromotions ? item.allPromotions : [];
              nItem = this.checkPromos(nItem);
            }
            if (pos) {
              this.$store.commit('addOrderRow', nItem);
            } else {
              this[form].items.push(nItem);
            }
            this.alert(this.$root.$t('added_to_cart', { item: nItem.name }));
            this.playSound(true);
          }
          if (pos && window && document.documentElement.clientWidth > 768) {
            this.$event.fire('order:scroll');
          }
          this.cloading = false;
        }
        this.$nextTick(() => {
          this.query = '';
          if (!this.isMobile()) {
            document.querySelector('#scan_barcode').focus();
          }
        });
      },
    },
  };
}

export default CommonMethods;
