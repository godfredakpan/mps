<template>
  <div>
    <Form ref="portionsForm" :model="form" label-position="top">
      <div class="mb16">
        <Row :gutter="16">
          <Col :sm="24" :md="12" :lg="12" v-for="(portion, i) in form.portions" :key="'portion_' + i">
            <Card dis-hover style="margin-bottom: 16px; line-height: 1em;">
              <p slot="title">{{ portion.name == 'regular' ? $t('regular') : portion.name }}</p>
              <span slot="extra">
                <Button v-if="!i" size="small" type="success" @click="option = true">
                  <Icon type="ios-options" />
                  <span class="hidden-sm">{{ $t('add_x', { x: $tc('portion') }) }}</span>
                </Button>
                <Button v-else size="small" type="error" @click="removePortion(i)" slot="extra">
                  <Icon type="ios-trash" />
                  <span class="hidden-sm">{{ $t('delete_x', { x: $tc('portion') }) }}</span>
                </Button>
                <Button v-if="!i" size="small" type="primary" @click="addItemRow()">
                  <Icon type="ios-add-circle" />
                  <span class="hidden-sm">{{ $t('add_x', { x: $tc('item') }) }}</span>
                </Button>
              </span>
              <div class="variant-table">
                <table class="table">
                  <thead>
                    <tr>
                      <th>{{ $t('name') }}</th>
                      <th>{{ $t('quantity') }}</th>
                      <th style="padding: 0; max-width: 40px; min-width: 40px; width: 40px;">
                        <icon type="md-trash" />
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr :key="'item_' + index" v-for="(item, index) in portion.portion_items">
                      <td width="70%">
                        <Select
                          remote
                          clearable
                          filterable
                          :label="item.name"
                          :loading="searching"
                          v-model="item.item_id"
                          :remote-method="searchItems"
                          @on-change="v => itemChanged(v, portion, i)"
                        >
                          <Option :value="item.id" :label="item.name" :key="'item_' + i" v-for="(item, i) in result"></Option>
                        </Select>
                      </td>
                      <td>
                        <InputNumber v-model="item.quantity" @on-change="v => itemChanged(null, portion, i)" />
                        <input type="hidden" v-model="item.cost" />
                        <input type="hidden" v-model="item.name" />
                        <input type="hidden" v-model="item.item_name" />
                      </td>
                      <td class="text-center">
                        <Icon size="16" v-if="index" class="pointer" color="#ed3f14" type="md-trash" @click="deleteItemRow(index)" />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <Alert
                class="mt16"
                type="error"
                v-if="errors.form.portions[i] && errors.form.portions[i].item && errors.form.portions[i].item.length"
                v-html="errors.form.portions[i].item.join('<br />')"
              />

              <Row :gutter="16" class="mt16">
                <Col :sm="24" :md="12" :lg="12">
                  <FormItem
                    class="input-tip"
                    :label="$t('cost')"
                    :prop="'portions.' + i + '.cost'"
                    :error="errors.form.portions[i].cost"
                    :rules="{ validator: costV, type: 'number', trigger: 'change' }"
                  >
                    <InputNumber v-model="portion.cost" />
                    <!-- @on-blur="checkPortionPrice(portion, i)"
                      @on-focus="minCost = portionCost(portion, i)"
                      @on-change="minCost = portionCost(portion, i)" -->
                    <small>{{ $t('min_cost_error', { min: portionCost(portion, i) }) }}</small>
                  </FormItem>
                </Col>
                <Col :sm="24" :md="12" :lg="12">
                  <FormItem
                    :label="$t('price')"
                    :prop="'portions.' + i + '.price'"
                    :error="errors.form.portions[i].price"
                    :rules="{ validator: priceV, type: 'number', trigger: 'change' }"
                  >
                    <InputNumber v-model="portion.price" />
                    <!-- @on-blur="checkPortionPrice(portion, i)"
                      @on-focus="minCost = portionCost(portion, i)"
                      @on-change="minCost = portionCost(portion, i)" -->
                  </FormItem>
                </Col>
              </Row>
            </Card>
          </Col>
        </Row>
      </div>
      <div class="mb16">
        <Button type="primary" :loading="saving" :disabled="saving" @click="onSubmit('items')">
          <span v-if="!saving">{{ $t('submit') }}</span>
          <span v-else>{{ $t('saving') }}...</span>
        </Button>
        <Button ghost type="primary" :loading="saving" :disabled="saving" style="margin-left: 8px;" @click="onSubmit('items', true)">
          <span v-if="!saving">{{ $t('save_n_stay') }}</span>
          <span v-else>{{ $t('saving') }}...</span>
        </Button>
        <Button type="warning" ghost @click="goToStep(0)" style="margin-left: 8px;">
          {{ $t('back') }}
        </Button>
        <Button type="error" ghost @click="handleReset()" style="margin-left: 8px;">{{ $t('reset') }}</Button>
      </div>
    </Form>
    <Modal footer-hide v-model="option" width="300" :title="$t('add_x', { x: $tc('portion') })" @on-visible-change="modalChanged">
      <Form ref="current" :model="current" :rules="portionRules" label-position="top" @submit.native.prevent="addPortion">
        <FormItem :label="$t('name')" prop="name">
          <Input v-model="current.name" :placeholder="$t('name')" element-id="portion_name" />
        </FormItem>
        <FormItem class="mb0">
          <Button type="primary" long @click="addPortion">{{ $t('add') }}</Button>
        </FormItem>
      </Form>
    </Modal>
  </div>
</template>

<script>
import _debounce from 'lodash/debounce';
export default {
  props: {
    pportions: {
      type: Array,
      required: true,
    },
    loading: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    const costV = (rule, value, callback) => {
      if (value && parseFloat(value) < parseFloat(this.minCost)) {
        callback(new Error(this.$t('min_cost_error', { min: parseFloat(this.minCost) })));
      } else {
        callback();
      }
    };
    const priceV = (rule, value, callback) => {
      if (value && parseFloat(value) < parseFloat(this.minCost)) {
        callback(new Error(this.$t('min_price_error', { min: parseFloat(this.minCost) })));
      } else {
        callback();
      }
    };

    return {
      local: [],
      result: [],
      portions: [],
      group: false,
      costV: costV,
      minCost: null,
      saving: false,
      option: false,
      priceV: priceV,
      sloading: false,
      searching: false,
      can_search: false,
      form: {},
      current: { name: '' },
      errors: { message: '', form: { portions: [{ price: null }] } },
      portionRules: {
        name: [
          {
            required: true,
            trigger: 'blur',
            message: this.$t('field_is_required', { field: this.$t('name') }),
          },
        ],
      },
      rules: {},
    };
  },
  created() {
    if (this.pportions) {
      this.pportions.map(p => {
        p.portion_items.map(e => {
          return e.item
            ? this.local.push({ ...e.item, value: e.item_id, label: e.item.name })
            : this.local.push({ ...e, value: e.item_id, label: e.name });
        });
      });
      this.result = [...this.local];

      this.form.portions = this.pportions.map((p, pi) => {
        p.cost = p.cost ? parseFloat(p.cost) : null;
        p.price = p.price ? parseFloat(p.price) : null;
        if (p.portion_items && p.portion_items.length) {
          p.portion_items = p.portion_items.map(i => {
            i.quantity = i.quantity ? parseFloat(i.quantity) : null;
            if (i.item) {
              i.id = i.item.id;
              i.sku = i.item.sku;
              i.name = i.item.name;
              i.cost = i.item.cost;
              i.item_id = i.item.id;
              // i.variants = i.item.variants;
              // i.variations = i.item.variations.map(v => ({ id: v.id, variation_id: v.id, sku: v.sku, meta: v.meta }));
              delete i.item;
            }
            return i;
          });
        }
        if (!this.errors.form.portions[pi]) {
          this.errors.form.portions[pi] = {};
        }
        this.errors.form.portions[pi].cost = '';
        this.errors.form.portions[pi].price = '';
        this.errors.form.portions[pi].item = [];
        return p;
      });
    } else {
      this.form.portions = [
        { sku: this.sku(), name: this.$t('regular'), portion_items: [{ name: '', item_id: null, quantity: 1 }], cost: null, price: null },
      ];
    }
    this.can_search = true;
  },
  methods: {
    modalChanged(v) {
      if (!v) {
        this.$refs['current'].resetFields();
      } else if (window) {
        this.$nextTick(() => {
          document.querySelector('#portion_name').focus();
        });
      }
    },
    addPortion() {
      this.$refs['current'].validate(valid => {
        if (valid) {
          if (this.current.name) {
            let regular = this.form.portions.find(p => p.name == 'regular');
            let portion = {
              cost: null,
              price: null,
              sku: this.sku(),
              name: this.current.name,
              portion_items: regular.portion_items.map(i => ({ ...i })),
            };
            // this.item_ingredients.map(i => {
            //     portion.ingredients.push({ name: i.name, ingredient_id: i.id, cost: i.cost, quantity: 1 });
            // });
            this.form.portions.push(portion);
            this.errors.form.portions.push({ price: null });
            this.$refs['current'].resetFields();
            this.option = false;
          }
        } else {
          this.$Notice.error({ title: this.$t('invalid_input'), desc: this.$t('invalid_input_error'), duration: 10 });
        }
      });
    },
    addItemRow() {
      this.form.portions = this.form.portions.map(p => {
        p.portion_items.push({ quantity: 1 });
        return p;
      });
    },
    deleteItemRow(index) {
      this.form.portions = this.form.portions.map(p => {
        p.portion_items.splice(index, 1);
        return p;
      });
    },
    removePortion(index) {
      this.form.portions.splice(index, 1);
      this.saving = true;
      this.$nextTick(() => (this.saving = false));
    },
    itemChanged(id, portion, i) {
      if (id) {
        let item = this.result.find(i => i.id == id);
        portion.portion_items = portion.portion_items.map(i => {
          if (i.item_id == id) {
            i.cost = item.cost;
            i.name = item.name;
            i.item_name = item.name;
          }
          return i;
        });
      }
      this.portionCost(portion, i);
    },
    portionCost(portion, i) {
      // this.checkPortionPrice(portion, i);
      return portion.portion_items.reduce((a, pi) => a + (pi.cost ? parseFloat(pi.cost) * parseFloat(pi.quantity) : 0), 0);
    },
    // checkPortionPrice(portion, i) {
    //   if (portion.cost && portion.price && portion.price < portion.cost && this.errors.form.portions[i] && portion.price >= this.minCost) {
    //     this.errors.form.portions[i].price = this.$t('price_tip');
    //     // } else {
    //     //   this.errors.form.portions[i].price = null;
    //   }
    // },
    searchItems(search) {
      if (this.can_search) {
        search = search.trim();
        if (search !== '' && !this.result.find(r => r.id == search || r.name == search)) {
          this.getItems(search, this);
        }
      }
    },
    getItems: _debounce((search, vm) => {
      vm.searching = true;
      const search_delay = vm.$store.getters.search_delay;
      vm.$http
        .get('app/items/search?cost=yes&q=' + search)
        .then(res => {
          vm.result = res.data;
          vm.result = [...vm.result, ...vm.local];
        })
        .finally(() => (vm.searching = false));
    }, search_delay || 250),
    goToStep(step) {
      this.$emit('portions-changed', this.form.portions);
      this.$emit('step-changed', step);
    },
    onReset() {
      this.$emit('on-reset');
      this.$emit('step-changed', 0);
    },
    onSubmit(page, stay) {
      let error = false;
      this.form.portions.map((p, i) => {
        if (p.cost === null) {
          error = true;
          this.errors.form.portions[i].cost = this.$t('field_is_required', { field: this.$t('cost') });
        }
        if (p.price === null) {
          error = true;
          this.errors.form.portions[i].price = this.$t('field_is_required', { field: this.$t('price') });
        }
        if (p.portion_items && p.portion_items.length) {
          let item_errors = [];
          p.portion_items.map(item => {
            if (!item.item_id) {
              error = true;
              item_errors.push(this.$t('field_is_required', { field: this.$tc('item') + (i + 1) }));
            }
            if (!item.quantity) {
              error = true;
              item_errors.push(this.$t('field_is_required', { field: this.$tc('item') + (i + 1) + '(' + this.$t('quantity') + ')' }));
            }
          });
          this.errors.form.portions[i].item = item_errors;
        }
      });

      if (!error) {
        this.$refs.portionsForm.validate(valid => {
          if (valid) {
            this.$emit('portions-changed', this.form.portions);
            this.$nextTick(() => {
              this.$emit('on-submit', { page, stay });
            });
          } else {
            this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
          }
        });
      }
    },
  },
};
</script>
