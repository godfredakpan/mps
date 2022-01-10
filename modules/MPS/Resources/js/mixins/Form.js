function Form(model, url, create, fnFormat, fetch) {
  fetch = fetch || true;
  create = create || false;
  fnFormat = fnFormat || false;
  return {
    data() {
      return {
        original: {},
        saving: false,
        loading: true,
        attributes: [],
        errors: { message: '', form: {} },
      };
    },
    created() {
      this.original = { ...this.form };
      if (this.$route.params.id) {
        if (fetch) {
          this.fetch(this.$route.params.id);
        }
      } else {
        create ? this.create() : (this.loading = false);
      }
    },
    watch: {
      $route(to, from) {
        if (to.name.includes('add')) {
          this.form = { ...this.original };
          this.handleReset();
        } else if (to.name.includes('edit') && this.$route.params.id) {
          this.loading = true;
          this.fetch(this.$route.params.id);
        }
      },
    },
    methods: {
      handleSubmit(page, stay = false) {
        this.$refs[model].validate(valid => {
          if (valid) {
            this.saving = true;
            this.submit(page, stay);
          } else {
            this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
          }
        });
      },
      handleReset() {
        this.form = { ...this.original };
        this.$refs[model].resetFields();
        this.saving = false;
      },
      submit(page, stay) {
        let post = url;
        let msg = 'added';
        this.errors.message = '';
        let msg_text = 'record_added';
        if (this.form.id && this.form.id != '') {
          msg = 'updated';
          post = url + '/' + this.form.id;
          msg_text = 'record_updated';
          this.form['_method'] = 'PUT';
        }
        post = post + (stay ? '?stay=1' : '');
        let data = { ...this.form };
        if (this.attributes && this.attributes.length > 0) {
          let extras = this.attributes.map(attr => {
            let extra = {};
            delete data[attr.slug];
            extra[attr.slug] = this.form[attr.slug];
            return extra;
          });
          data.extra_attributes = Object.assign(...extras);
        }

        if (model != 'promo' && data.items) {
          data.items = data.items.map(i => {
            let item = {
              id: i.id,
              name: i.name,
              code: i.code,
              cost: i.cost,
              price: i.price,
              item_id: i.item_id,
              unit_id: i.unit_id,
              item_unit_id: i.item_unit_id,
              quantity: i.quantity,
              batch_no: i.batch_no,
              net_cost: i.net_cost,
              net_price: i.net_price,
              tax_amount: i.tax_amount,
              unit_cost: i.unit_cost,
              unit_price: i.unit_price,
              taxes: i.taxes,
              allTaxes: i.allTaxes,
              categories: i.categories,
              selected: i.selected,
              has_serials: i.has_serials,
              tax_included: i.tax_included,
              discount_amount: i.discount_amount,
              discount: i.discount ? i.discount : 0,
              // promotions: model == 'purchase' ? [] : i.promotions,
              // allPromotions: model == 'purchase' ? [] : i.allPromotions,
              expiry_date: i.expiry_date ? this.$moment(i.expiry_date).format(this.$moment.HTML5_FMT.DATE) : '',
            };
            item.selected = { modifiers: [], variations: [], portions: [] };
            if (i.selected.modifiers.length) {
              item.selected.modifiers = i.selected.modifiers.map(m => ({
                id: m.mId,
                selected: m.id,
                price: m.price,
                quantity: m.quantity,
              }));
            }
            if (i.selected.variations.length) {
              item.selected.variations = i.selected.variations.map(v => ({ id: v.id, price: v.price, quantity: v.quantity }));
            }
            if (i.selected.portions.length) {
              item.selected.portions = i.selected.portions.map(p => ({
                id: p.id,
                cost: p.cost,
                price: p.price,
                quantity: p.quantity,
                essentials: p.essentials.map(e => ({ id: e.id, item_id: e.item_id, variation_id: e.variation_id })),
                choosables: p.choosables.map(g => ({ id: g.id, selected: g.selected, variation_id: g.variation_id })),
                portion_items: p.portion_items.map(e => ({ id: e.id, item_id: e.item_id, variation_id: e.variation_id })),
              }));
            }
            if (item.promotions && item.promotions.length) {
              item.promotions = item.allPromotions.filter(p => item.promotions.includes(p.id));
            }
            if (item.categories && item.categories.length) {
              item.categories = item.categories.map(c => {
                return { id: c.id, promotions: c.promotions && c.promotions.length ? c.promotions.map(p => p.id) : [] };
                // c.promotions = c.promotions && c.promotions.length ? c.promotions.map(p => p.id) : [];
                // return c;
              });
            }
            if (item.selected.variations.length) {
              item.selected.variations = item.selected.variations.map(v => {
                v.taxes = v.taxes ? v.taxes.map(t => t.id) : [];
                v.allTaxes = v.item_taxes = v.allTaxes ? v.allTaxes.map(t => t.id) : [];
                return v;
              });
            }
            item.selected.serials = i.selected.serials;
            item.taxes = item.taxes ? item.taxes.map(t => t.id) : [];
            item.allTaxes = item.item_taxes = item.allTaxes ? item.allTaxes.map(t => t.id) : [];
            item.promotions = i.promotions && i.promotions.length ? i.promotions.map(p => p.id) : [];
            item.allPromotions = item.item_promotions = i.allPromotions && i.allPromotions.length ? i.allPromotions.map(p => p.id) : [];
            return item;
          });
        }
        if (page == 'modifiers') {
          data.options = data.options.map(o => ({ id: o.id, sku: o.sku, item_id: o.item_id, quantity: o.quantity }));
        }
        if (data.date) {
          data.date = this.$moment(data.date).format(this.$moment.HTML5_FMT.DATE);
        }
        if (data.start_date) {
          data.start_date = this.$moment(data.start_date).format(this.$moment.HTML5_FMT.DATE);
        }
        if (data.expiry_date) {
          data.expiry_date = this.$moment(data.expiry_date).format(this.$moment.HTML5_FMT.DATE);
        }
        if (data.settings && data.settings.birth_date) {
          data.settings.birth_date = this.$moment(data.settings.birth_date).format(this.$moment.HTML5_FMT.DATE);
        }
        if (data.settings && data.settings.hire_date) {
          data.settings.hire_date = this.$moment(data.settings.hire_date).format(this.$moment.HTML5_FMT.DATE);
        }
        if (data.user) {
          delete data.user;
        }
        if (data.customer) {
          delete data.customer;
        }
        if (data.location) {
          delete data.location;
        }
        data = this.$form(data);
        this.$http
          .post(post, data)
          .then(res => {
            if (res.data.success) {
              this.$Notice.destroy();
              this.$Notice.success({ title: this.$tc(model) + ' ' + this.$t(msg), desc: this.$t(msg_text) });
              if (stay) {
                if (msg == 'updated') {
                  if (fnFormat) {
                    this.form = fnFormat(res.data.data, this);
                  }
                } else {
                  this.handleReset();
                }
                if (this.step) {
                  this.step = 0;
                }
                if (create) {
                  this.create();
                }
              } else {
                this.$router.push({ name: page + '.list' });
              }
            } else {
              this.$Notice.error({ title: this.$t('failed'), desc: this.$t('failed_error_text') });
            }
          })
          .catch(error => (this.errors = error))
          .finally(() => (this.saving = false));
      },
      updateCF(field, value) {
        this.form[field] = value;
        setTimeout(() => {
          this.$refs[model].validateField(field);
        }, 1000);
      },
    },
  };
}

export default Form;
