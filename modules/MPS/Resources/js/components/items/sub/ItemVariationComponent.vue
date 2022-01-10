<template>
  <div>
    <div class="mb16">
      <Checkbox v-model="has_variants" @on-change="hasVariantChanged" label="price_variants" :true-value="1" :false-value="0">
        {{ $t('item_has_variants') }}
      </Checkbox>
    </div>
    <transition name="slide-fade">
      <div v-if="has_variants == 1" style="margin-top: 16px;">
        <Row :gutter="16">
          <Col :sm="24">
            <Card dis-hover style="margin-bottom: 16px; line-height: 1em;">
              <p slot="title">{{ $tc('variant') }}</p>
              <ButtonGroup size="small" slot="extra">
                <Button size="small" type="success" @click="option = true">
                  <Icon type="ios-options" />
                  <span class="hidden-sm">{{ $t('add_x', { x: $tc('option') }) }}</span>
                </Button>
                <Button size="small" type="warning" @click="doAllVariations">
                  <Icon type="ios-shuffle" />
                  <span class="hidden-sm">{{ $t('generate') }}</span>
                </Button>
                <Button size="small" type="primary" @click="addVariantRow">
                  <Icon type="ios-list-box-outline" />
                  <span class="hidden-sm">{{ $t('add_x', { x: $t('row') }) }}</span>
                </Button>
              </ButtonGroup>
              <div class="variants-form">
                <h-scroll-indicator-component
                  height="1px"
                  color="#2b85e4"
                  background="#ffffff"
                  selector=".variant-table"
                ></h-scroll-indicator-component>
                <div class="table-responsive variant-table">
                  <table class="table">
                    <thead>
                      <tr>
                        <th :key="'col_' + index" :style="colStyle(col)" v-for="(col, index) in variantsColumns">
                          <span v-if="col.key == 'del'">
                            <Icon size="16" type="md-trash" />
                          </span>
                          <span v-else>{{ col.title }}</span>
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(variation, index) in variations" :key="'variation_' + index">
                        <td><Input v-model="variation.sku" readonly /></td>
                        <td><Input v-model="variation.code" /></td>
                        <td><InputNumber v-model="variation.cost" /></td>
                        <td><InputNumber v-model="variation.price" /></td>
                        <td v-if="$store.getters.stock">
                          <InputNumber v-model="variation.quantity" />
                        </td>
                        <td><Input v-model="variation.rack" /></td>
                        <td><InputNumber v-model="variation.weight" /></td>
                        <td>
                          <Input v-model="variation.dimensions" :placeholder="$t('dimensions_tip')" />
                        </td>
                        <template v-if="variations.length">
                          <td v-for="(va, vi) in variants" :key="'option' + vi">
                            <span v-if="va.options">
                              <Select v-model="variation.meta[va.name]">
                                <Option :key="opt" :value="opt" v-for="opt in va.options">{{ opt }}</Option>
                              </Select>
                            </span>
                            <span v-else>
                              <Input v-model="variation[va.name]" />
                            </span>
                          </td>
                        </template>
                        <td class="text-center">
                          <Icon size="16" class="pointer" color="#ed3f14" type="md-trash" @click="removeVariant(index)" />
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </Card>
          </Col>
        </Row>
      </div>
    </transition>
    <div>
      <Button type="primary" @click="goToStep(2)">{{ $t('next') }}</Button>
      <Button ghost type="warning" @click="goToStep(0)" style="margin-left: 8px;">{{ $t('back') }}</Button>
    </div>

    <Modal footer-hide v-model="option" width="300" :title="$t('add_x', { x: $tc('variant') })" @on-visible-change="modalChanged">
      <Form ref="current" :model="current" :rules="variantRules" label-position="top">
        <FormItem :label="$tc('variant_name')" prop="name">
          <Input v-model="current.name" :placeholder="$t('variant_name_tip')" element-id="variant_name" />
        </FormItem>
        <FormItem :label="$tc('variant_options')" prop="options">
          <Input v-model="current.options" :placeholder="$t('variant_options_tip')" />
        </FormItem>
        <FormItem class="mb0">
          <Button type="primary" long @click="addVariantOption">{{ $t('add') }}</Button>
        </FormItem>
      </Form>
    </Modal>
  </div>
</template>

<script>
import HScrollIndicatorComponent from '@mpscom/helpers/HScrollIndicatorComponent';
export default {
  components: { HScrollIndicatorComponent },
  props: {
    ivariants: {
      type: Array,
      required: true,
    },
    ivariations: {
      type: Array,
      required: true,
    },
    hasVariants: {
      required: true,
    },
  },
  data() {
    const variantV = (rule, value, callback) => {
      if (this.variants.find(v => v.name == value)) {
        callback(new Error(this.$t('variant_exists')));
      } else {
        callback();
      }
    };
    return {
      option: false,
      variants: [],
      variations: [],
      has_variants: 0,
      current: { name: '', options: '' },
      variantsColumns: [
        {
          key: 'sku',
          slot: 'sku',
          width: 150,
          sortable: false,
          title: this.$t('sku'),
        },
        {
          key: 'code',
          slot: 'code',
          minWidth: 150,
          sortable: false,
          title: this.$t('code'),
        },
        {
          width: 125,
          key: 'cost',
          slot: 'cost',
          sortable: false,
          title: this.$t('cost'),
        },
        {
          width: 125,
          key: 'price',
          slot: 'price',
          sortable: false,
          title: this.$t('price'),
        },
        {
          width: 125,
          key: 'rack',
          slot: 'rack',
          sortable: false,
          title: this.$t('rack'),
        },
        {
          width: 125,
          key: 'weight',
          slot: 'weight',
          sortable: false,
          title: this.$t('weight'),
        },
        {
          width: 150,
          key: 'dimensions',
          slot: 'dimensions',
          sortable: false,
          title: this.$t('dimensions'),
        },
        {
          width: 50,
          key: 'del',
          slot: 'del',
          title: ' ',
          align: 'center',
          sortable: false,
          // render: this.addVariant,
        },
      ],
      variantRules: {
        name: [
          { validator: variantV, trigger: 'blur' },
          { required: true, message: this.$t('field_is_required', { field: this.$t('variant_name') }), trigger: 'blur' },
        ],
      },
    };
  },
  created() {
    this.has_variants = this.hasVariants;
    this.variants = [...this.ivariants];
    this.variations = [...this.ivariations];
    if (this.$store.getters.stock) {
      this.variantsColumns.splice(4, 0, {
        width: 125,
        key: 'quantity',
        slot: 'quantity',
        sortable: false,
        title: this.$t('quantity'),
      });
    }
    this.$nextTick(() => {
      if (this.variants.length > 0) {
        this.variants.map(v => {
          this.variantsColumns.splice(this.variantsColumns.length - 1, 0, {
            minWidth: 150,
            sortable: false,
            key: v.name,
            slot: v.name,
            title: v.name,
          });
        });
      }
    });
  },
  methods: {
    modalChanged(v) {
      if (!v) {
        this.$refs['current'].resetFields();
      } else if (window) {
        this.$nextTick(() => {
          document.querySelector('#variant_name').focus();
        });
      }
    },
    hasVariantChanged(v) {
      this.$emit('has-variants-changed', v);
    },
    goToStep(step) {
      if (step == 2) {
        this.$emit('variants-changed', [...this.variants]);
        this.$emit('variations-changed', [...this.variations]);
      }
      this.$emit('step-changed', step);
    },
    addVariantOption() {
      this.$refs['current'].validate(valid => {
        if (valid) {
          let current = { ...this.current };
          if (current.name) {
            this.variantsColumns.splice(this.variantsColumns.length - 1, 0, {
              minWidth: 150,
              sortable: false,
              key: current.name,
              slot: current.name,
              title: current.name,
            });
            if (current.options) {
              current.options = current.options.split('|');
            }

            let variations = [...this.variations];
            if (current.options && current.options.length > 0) {
              current.options.map((opt, oi) => {
                if (oi) {
                  let sku = this.sku(true);
                  let va = {
                    sku: sku,
                    code: '',
                    cost: null,
                    price: null,
                    quantity: null,
                    weight: null,
                    dimensions: '',
                    del: null,
                    meta: [],
                  };
                  va.meta[current.name] = opt;
                  variations.push(va);
                } else {
                  variations = variations.map(fv => {
                    fv.meta[current.name] = opt;
                    return fv;
                  });
                }
              });
            } else {
              variations = variations.map(fv => {
                fv.meta[current.name] = '';
                return fv;
              });
            }
            this.variations = [...variations];
            this.variants = [...this.variants, current];
            // this.$emit('variants-changed', [...this.variants]);
            // this.$emit('variations-changed', [...this.variations]);
            this.option = false;
            setTimeout(() => {
              this.current.name = '';
              this.current.options = '';
              this.$refs['current'].resetFields();
            }, 50);
          }
        } else {
          this.$Notice.error({ title: this.$t('invalid_variant'), desc: this.$t('invalid_variant_error'), duration: 10 });
        }
      });
    },
    doAllVariations() {
      if (this.variants.length > 1) {
        this.$Modal.confirm({
          title: this.$t('generate_variations'),
          content: this.$t('generate_variations_text'),
          onOk: () => {
            let vs = [];
            let variations = [];
            this.variants.map(v => {
              let va = {};
              va[v.name] = v.options;
              vs.push(va);
            });
            this.variants.map(v => {
              vs.map(opts => {
                if (opts[v.name] && opts[v.name].length > 0) {
                  opts[v.name].map(a => {
                    this.variants.map(vv => {
                      vs.map(vopts => {
                        if (vopts[vv.name] && vopts[vv.name].length > 0) {
                          vopts[vv.name].map(aa => {
                            if (vv.name != v.name && a != aa) {
                              if (!variations.find(fv => fv.meta[v.name] == a && fv.meta[vv.name] == aa)) {
                                let va = {
                                  sku: this.sku(true),
                                  code: '',
                                  cost: null,
                                  price: null,
                                  quantity: null,
                                  weight: null,
                                  dimensions: '',
                                  del: null,
                                  meta: [],
                                };
                                va.meta[v.name] = a;
                                va.meta[vv.name] = aa;
                                variations.push(va);
                              }
                            }
                          });
                        }
                      });
                    });
                  });
                }
              });
            });
            this.variations = [...variations];
            // this.$emit('variations-changed', [...this.variations]);
          },
        });
      } else {
        this.$Modal.info({
          title: this.$t('invalid_variants'),
          content: this.$t('invalid_variants_error'),
        });
      }
    },
    addVariantRow() {
      let va = {
        sku: this.sku(),
        code: '',
        cost: null,
        price: null,
        quantity: null,
        weight: null,
        dimensions: '',
        del: null,
        meta: [],
      };
      va.stock = this.$store.getters.locations.map(l => {
        return { ...l, location_id: l.value, price: null, cost: null, quantity: null, rack: '' };
      });
      this.variants.map(v => (va[v.name] = ''));
      this.variations.splice(this.variations.length, 0, va);
      // this.$emit('variants-changed', [...this.variants]);
      // this.$emit('variations-changed', [...this.variations]);
    },
    removeVariant(index) {
      this.variations.splice(index, 1);
    },
    colStyle(col) {
      let style = {};
      if (col.width) {
        style.width = col.width + 'px';
        style.minWidth = col.width + 'px';
      }
      if (col.minWidth) {
        style.minWidth = col.minWidth + 'px';
      }
      if (col.maxWidth) {
        style.maxWidth = col.maxWidth + 'px';
      }
      return style;
    },
  },
};
</script>
