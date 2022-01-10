<template>
  <div>
    <Form ref="form" :model="form" label-position="top">
      <Row :gutter="16">
        <Col :sm="24" :md="24" :lg="24">
          <FormItem :label="$t('quantity')" class="text-center">
            <InputNumber size="large" v-model="form.quantity" />
            <!-- <input-number-component v-model="form.quantity"></input-number-component> -->
          </FormItem>
        </Col>
        <template v-if="item.serials && item.serials.length">
          <Col :sm="24" :md="24" :lg="24">
            <FormItem
              :label="$tc('serial_number', 2)"
              prop="serials"
              :rules="[
                {
                  required: true,
                  type: 'array',
                  size: form.quantity,
                  message: $t('field_are_required', { field: $tc('serial_number', 2) }),
                  trigger: 'change',
                },
              ]"
              v-if="item.serials.length"
            >
              <Select v-model="form.serials" multiple style="width: 100%;">
                <Option :value="serial.id" :key="'serial_' + si" v-for="(serial, si) in item.serials">
                  {{ serial.number }}
                </Option>
              </Select>
            </FormItem>
          </Col>
        </template>
        <template v-if="item.portions && item.portions.length">
          <Col :sm="24" :md="24" :lg="24">
            <Card dis-hover class="cardp">
              <p slot="title">
                <Icon type="ios-option"></Icon>
                {{ $tc('portion') }}:
                {{ item && form.portion_id ? ' (' + (form.portion.name == 'regular' ? $t('regular') : form.portion.name) + ')' : '' }}
              </p>
              <Row>
                <template v-if="item.portions.length > 1">
                  <Col :sm="24" :md="24" :lg="24">
                    <!-- <Select v-model="form.portion_id">
                      <Option :value="p.id" :key="'pi_' + pi" v-for="(p, pi) in item.portions" @on-change="portionChanged">
                        {{ p.name == 'regular' ? $t('regular') : p.name }}
                      </Option>
                    </Select> -->
                    <RadioGroup class="mb16" type="button" v-model="form.portion_id" @on-change="portionChanged">
                      <Radio :label="p.id" :key="'pi_' + pi" v-for="(p, pi) in item.portions">
                        {{ p.name == 'regular' ? $t('regular') : p.name }}
                      </Radio>
                    </RadioGroup>
                  </Col>
                </template>
                <Col :sm="24" :md="24" :lg="24" v-if="form.portion_id">
                  <template v-if="form.portion.portion_items && form.portion.portion_items.length">
                    <Divider dashed orientation="left" style="margin-top: 0;">
                      <small style="color: #aaa;">
                        {{ $tc('item', form.portion.portion_items.length) }}
                      </small>
                    </Divider>
                    <p style="margin-bottom: 8px; font-weight: bold;">
                      {{ form.portion.portion_items.map(e => e.item.name).join(', ') }}
                    </p>
                  </template>
                  <span v-if="form.portion.essentials && form.portion.essentials.length">
                    <Divider dashed orientation="left" style="margin-top: 0;">
                      <small style="color: #aaa;">
                        {{ $t('essential_items') }}
                      </small>
                    </Divider>
                    <p style="margin-bottom: 8px; font-weight: bold;">
                      {{ form.portion.essentials.map(e => e.item.name).join(', ') }}
                    </p>
                  </span>
                  <span v-if="form.portion.choosables && form.portion.choosables.length">
                    <Divider dashed orientation="left">
                      <small style="color: #aaa;">
                        {{ $t('choosable_items') }}
                      </small>
                    </Divider>
                    <!-- <h3 style="margin-bottom:8px;">
                    <strong>{{ $t('choosable_items') }}:</strong>
                  </h3> -->
                    <div :key="'gi_' + gi" v-for="(g, gi) in form.portion.choosables">
                      <!-- <Col :sm="24" :md="12" :lg="12"> -->
                      <FormItem
                        :label="g.name"
                        :prop="'portion.choosables.' + gi + '.selected'"
                        :rules="{
                          required: true,
                          trigger: 'change',
                          message: $t('select_x', { x: $tc('item') }),
                        }"
                      >
                        <RadioGroup v-model="g.selected" vertical>
                          <Radio
                            false-value=""
                            :key="'gii_' + gii"
                            :label="option.item_id"
                            :true-value="option.item_id"
                            v-for="(option, gii) in g.items"
                          >
                            {{ option.item.name }}
                          </Radio>
                        </RadioGroup>
                      </FormItem>
                      <!-- <strong>{{ g.name }}:</strong>
                      {{ g.items.map(e => e.item.name).join(', ') }}
                    </Col> -->
                    </div>
                  </span>
                  <template v-if="form.portion.portion_items && form.portion.portion_items.length">
                    <div :key="'eiv_' + ei" v-for="(e, ei) in form.portion.portion_items">
                      <template v-if="e.item.variants && e.item.variants.length">
                        <div class="mb16" :key="'t_' + ei">
                          <span class="bold">{{ e.item.name }} ({{ $tc('variation') }})</span>
                        </div>
                        <Row :gutter="16" :key="'r_' + ei">
                          <Col :xs="24" :sm="12" v-for="(v, i) in e.item.variants" :key="'pcsv_' + i">
                            <FormItem
                              :label="v.name"
                              :key="'picsvf_' + v.id"
                              :prop="'portion.portion_items[' + ei + '].' + v.name"
                              :rules="{
                                required: true,
                                trigger: 'change',
                                message: $t('select_x', { x: $tc('variant') }),
                              }"
                            >
                              <Select v-model="e[v.name]" @on-change="selectPortionItemVariant(e.id)">
                                <Option :key="opt" :value="opt" v-for="opt in v.options">{{ opt }}</Option>
                              </Select>
                            </FormItem>
                          </Col>
                        </Row>
                      </template>
                    </div>
                  </template>
                  <template v-if="form.portion.essentials && form.portion.essentials.length">
                    <div :key="'eiv_' + ei" v-for="(e, ei) in form.portion.essentials">
                      <template v-if="e.item.variants && e.item.variants.length">
                        <div class="mb16" :key="'t_' + ei">
                          <span class="bold">{{ e.item.name }} ({{ $tc('variation') }})</span>
                        </div>
                        <Row :gutter="16" :key="'r_' + ei">
                          <Col :xs="24" :sm="12" v-for="(v, i) in e.item.variants" :key="'pcsv_' + i">
                            <FormItem
                              :label="v.name"
                              :key="'pcsvf_' + v.id"
                              :prop="'portion.essentials[' + ei + '].' + v.name"
                              :rules="{
                                required: true,
                                trigger: 'change',
                                message: $t('select_x', { x: $tc('variant') }),
                              }"
                            >
                              <Select v-model="e[v.name]" @on-change="selectEssentialVariant(e.id)">
                                <Option :key="opt" :value="opt" v-for="opt in v.options">{{ opt }}</Option>
                              </Select>
                            </FormItem>
                          </Col>
                        </Row>
                      </template>
                    </div>
                  </template>
                  <template v-if="form.portion.choosables && form.portion.choosables.length">
                    <div :key="'giv_' + gi" v-for="(g, gi) in form.portion.choosables">
                      <template v-for="(option, optionI) in g.items">
                        <template v-if="g.selected && g.selected == option.item.id && option.item.variants && option.item.variants.length">
                          <div class="mb16" :key="'t_' + gi">
                            <span class="bold">{{ g.name }}</span
                            >: {{ option.item.name }} ({{ $tc('variation') }})
                          </div>
                          <Row :gutter="16" :key="'r_' + gi">
                            <Col :xs="24" :sm="12" v-for="(v, i) in option.item.variants" :key="'pcsv_' + i">
                              <FormItem
                                :label="v.name"
                                :key="'pcsvf_' + v.id"
                                :prop="'portion.choosables[' + gi + '].items[' + optionI + '].' + v.name"
                                :rules="{
                                  required: true,
                                  trigger: 'change',
                                  message: $t('select_x', { x: $tc('variant') }),
                                }"
                              >
                                <Select v-model="g.items[optionI][v.name]" @on-change="selectChoosableVariant(g.id)">
                                  <Option :key="opt" :value="opt" v-for="opt in v.options">{{ opt }}</Option>
                                </Select>
                              </FormItem>
                            </Col>
                          </Row>
                        </template>
                      </template>
                    </div>
                  </template>
                </Col>
              </Row>
            </Card>
          </Col>
        </template>
        <template v-if="item.variants && item.variants.length">
          <Col :sm="24" :md="24" :lg="24">
            <Divider dashed orientation="left">
              <small style="color: #aaa;">
                {{ $tc('variant', 2) }}
              </small>
            </Divider>
          </Col>
          <Col :sm="24" :md="12" :lg="12" v-for="(v, i) in item.variants" :key="'sv_' + i">
            <FormItem
              :label="v.name"
              :prop="'meta.' + v.name"
              :rules="{
                required: true,
                trigger: 'change',
                message: $t('select_x', { x: $tc('variant') }),
              }"
            >
              <Select v-model="form.meta[v.name]" @on-change="selectVariant">
                <Option :key="opt" :value="opt" v-for="opt in v.options">{{ opt }}</Option>
              </Select>
            </FormItem>
          </Col>
          <Col :sm="24" :md="12" :lg="12" v-if="item.variants.length % 2 == 1"></Col>
          <Col :sm="24" :md="24" :lg="24">
            <Alert type="info" show-icon class="mb16" v-if="show">
              <strong
                ><span v-if="purchase">{{ this.$t('current_stock') }}</span
                ><span v-else>{{ this.$t('available_x', { x: this.$t('quantity') }) }}</span
                >: {{ this.qty }}</strong
              >
            </Alert>
          </Col>
        </template>
        <template v-if="!purchase && item.modifiers && item.modifiers.length">
          <Col :sm="24" :md="24" :lg="24">
            <Divider dashed orientation="left">
              <small style="color: #aaa;">
                {{ $tc('modifier', 2) }}
              </small>
            </Divider>
          </Col>
          <Col :sm="24" :md="12" :lg="12" v-for="(m, mi) in item.modifiers" :key="'mi_' + mi">
            <FormItem :label="m.title" prop="modifiers">
              <span v-if="m.show_as == 'radio'">
                <RadioGroup vertical v-model="m.selected">
                  <Radio :key="opt.id" :label="opt.id" v-for="opt in m.options">{{ modOptLabel(opt) }}</Radio>
                </RadioGroup>
              </span>
              <span v-else-if="m.show_as == 'checkbox'">
                <CheckboxGroup vertical v-model="m.selected">
                  <Checkbox :key="opt.id" :label="opt.id" v-for="opt in m.options">{{ modOptLabel(opt) }}</Checkbox>
                </CheckboxGroup>
              </span>
              <span v-else-if="m.show_as == 'select'">
                <Select v-model="m.selected">
                  <Option :key="opt.id" :value="opt.id" v-for="opt in m.options">{{ modOptLabel(opt) }}</Option>
                </Select>
              </span>
              <span v-else-if="m.show_as == 'select_multiple'">
                <Select v-model="m.selected" multiple>
                  <Option :key="opt.id" :value="opt.id" v-for="opt in m.options">{{ modOptLabel(opt) }}</Option>
                </Select>
              </span>
            </FormItem>
          </Col>
        </template>
      </Row>
    </Form>
    <div class="text-right">
      <Button long type="primary" :disabled="form.quantity < 1" @click="submit">
        {{ this.$t('add') }}
      </Button>
    </div>
  </div>
</template>

<script>
import _isEqual from 'lodash/isEqual';
import InputNumberComponent from './InputNumberComponent';
export default {
  components: { InputNumberComponent },
  props: {
    item: { required: true },
    purchase: {
      type: Boolean,
      default: false,
      required: false,
    },
  },
  data() {
    return {
      show: false,
      form: { quantity: 1, portion_id: null, variation_id: null, meta: {}, portion: null, modifiers: [], serials: [] },
    };
  },
  watch: {
    item: function(val) {
      this.setPortion();
    },
  },
  mounted() {
    this.setPortion();
  },
  methods: {
    setPortion() {
      this.form = { quantity: 1, portion_id: null, variation_id: null, meta: {}, portion: null, modifiers: [], serials: [] };
      this.$nextTick(() => {
        // this.form.modifiers = [...this.item.modifiers];
        if (this.item.portions && this.item.portions.length) {
          let portion = this.item.portions.find(p => p.name == 'regular');
          // let portion = this.item.portions.find(p => p.name == this.$t('regular'));
          this.form.portion = { ...portion };
          this.form.portion_id = portion.id;
          this.form.portion.essentials.map(e => {
            if (e.variation_id) {
              const v = e.item.variations.find(v => v.id == e.variation_id);
              for (const [key, value] of Object.entries(v.meta)) {
                e[key] = value;
              }
            }
          });
          this.form.portion.choosables.map(c => {
            c.items.map(i => {
              if (i.variation_id) {
                const v = i.item.variations.find(v => v.id == i.variation_id);
                for (const [key, value] of Object.entries(v.meta)) {
                  c[key] = value;
                }
              }
            });
          });
        }
      });
    },
    // mofiderSelected(modifier, value) {
    //     console.log(modifier, value);
    // },
    modOptLabel(opt) {
      return opt.item.name + ' (+' + this.formatDecimal(opt.item.price) + ')';
    },
    selectChoosableVariant(cId) {
      this.form.portion.choosables = this.form.portion.choosables.map(c => {
        if (c.selected && c.id == cId) {
          c.items = c.items.map(ci => {
            if (ci.item.id == c.selected) {
              let meta = {};
              if (ci && ci.item.variants) {
                ci.item.variants.map(v => (ci[v.name] ? (meta[v.name] = ci[v.name]) : ''));
                let variation = ci.item.variations.find(v => _isEqual(v.meta, meta));
                ci.variation_id = variation ? variation.id : null;
              }
            }
            return ci;
          });
        }
        return c;
      });
    },
    selectPortionItemVariant(pIId) {
      this.form.portion.portion_items = this.form.portion.portion_items.map(pi => {
        if (pi.id == pIId) {
          let meta = {};
          pi.item.variants.map(v => (pi[v.name] ? (meta[v.name] = pi[v.name]) : ''));
          let variation = pi.item.variations.find(v => _isEqual(v.meta, meta));
          pi.variation_id = variation ? variation.id : null;
        }
        return pi;
      });
    },
    selectEssentialVariant(eId) {
      this.form.portion.essentials = this.form.portion.essentials.map(e => {
        if (e.id == eId) {
          let meta = {};
          e.item.variants.map(v => (e[v.name] ? (meta[v.name] = e[v.name]) : ''));
          let variation = e.item.variations.find(v => _isEqual(v.meta, meta));
          e.variation_id = variation ? variation.id : null;
        }
        return e;
      });
    },
    selectVariant() {
      let variation = this.item.variations.find(v => _isEqual(v.meta, this.form.meta));
      if (variation) {
        this.qty = this.formatDecimal(variation.location_stock[0].quantity);
        this.show = true;
        this.form.variation_id = variation.id;
      }
    },
    portionChanged(v) {
      let portion = v ? this.item.portions.find(p => p.id == v) : {};
      this.form.portion = { ...portion };
    },
    submit() {
      this.$refs.form.validate(valid => {
        if (valid) {
          if (this.item.serials && this.item.serials.length && this.form.serials.length != this.form.quantity) {
            this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
            return false;
          }

          if (this.item.modifiers && this.item.modifiers.length) {
            this.item.modifiers.map(m => {
              if (m.selected) {
                if (m.show_as == 'checkbox' || m.show_as == 'select_multiple') {
                  m.selected.map(s => {
                    m.options.map(o => {
                      if (s == o.id) {
                        this.form.modifiers.push({
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
                      this.form.modifiers.push({
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
          if (this.form.portion) {
            let portion = {
              choosables: [],
              essentials: [],
              portion_items: [],
              id: this.form.portion.id,
              name: this.form.portion.name,
              quantity: this.form.quantity,
              cost: this.form.portion.cost,
              price: this.form.portion.price,
            };
            this.form.portion.portion_items.map(e => {
              let variation = e.item.variations.find(v => v.id == e.variation_id);
              portion.portion_items.push({
                id: e.id,
                item_id: e.item.id,
                variants: e.item.variants,
                variation_id: e.variation_id,
                variations: e.item.variations,
                meta: variation ? variation.meta : null,
              });
            });
            this.form.portion.essentials.map(e => {
              let variation = e.item.variations.find(v => v.id == e.variation_id);
              portion.essentials.push({
                id: e.id,
                item_id: e.item.id,
                variants: e.item.variants,
                variation_id: e.variation_id,
                variations: e.item.variations,
                meta: variation ? variation.meta : null,
              });
            });
            this.form.portion.choosables.map(g => {
              let selected_g = g.items.find(i => i.item_id == g.selected);
              let variation = selected_g.item.variations.find(v => v.id == selected_g.variation_id);
              portion.choosables.push({
                id: g.id,
                selected: g.selected,
                variants: selected_g.item.variants,
                variation_id: selected_g.variation_id,
                variations: selected_g.item.variations,
                meta: variation ? variation.meta : null,
              });
            });
            this.form.selected_portion = portion;
          } else {
            this.form.selected_portion = null;
          }
          if (this.form.variation_id) {
            let selectedVariation = this.item.variations ? this.item.variations.find(v => v.id == this.form.variation_id) : null;
            let variation = selectedVariation
              ? {
                  id: selectedVariation.id,
                  meta: selectedVariation.meta,
                  quantity: this.form.quantity,
                  cost: selectedVariation.location_stock[0].cost,
                  price: selectedVariation.location_stock[0].price,
                  available: this.formatDecimal(selectedVariation.location_stock[0].quantity),
                }
              : null;
            this.form.selected_variation = selectedVariation ? variation : null;
          } else {
            this.form.selected_variation = null;
          }

          // console.log(this.form.portion);
          // let portion = this.form.portion;
          // let modifiers = this.item.modifiers;
          // let variation_id = this.form.variation_id;
          // console.log(portion, modifiers, variation_id);
          // let variation = this.item.variations ? this.item.variations.find(v => _isEqual(v.meta, this.form.meta)) : null;
          this.$emit('on-submit', this.form);
        } else {
          this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
        }
      });
    },
  },
};
</script>

<style lang="scss">
.cardp {
  margin-bottom: 16px;
  .ivu-card-body {
    padding-bottom: 0;
  }
}
.ivu-radio-group-button .ivu-radio-wrapper {
  padding: 5px 20px;
  height: auto !important;
  border-radius: 0 !important;
}
.ivu-form .ivu-form-item-label {
  font-size: 14px;
  font-weight: bold;
}
</style>
