<template>
  <div class="">
    <div class="mb16">
      <Checkbox v-model="is_stock" @on-change="isStockChanged" label="different_price" :true-value="1" :false-value="0">
        {{
          $t('set_different_text', {
            values: $store.getters.stock ? $t('rack') + ', ' + $t('quantity') + ' & ' + $t('price') : $t('rack') + ' & ' + $t('price'),
          })
        }}
      </Checkbox>
    </div>
    <transition name="slide-fade">
      <div v-if="is_stock" style="margin-top: 16px; margin-bottom: 16px;">
        <span v-if="hasVariants == 1">
          <div :key="loc.value" v-for="(loc, li) in $store.getters.locations">
            <h4 style="margin: 8px 0;">#{{ li + 1 }} {{ loc.label }}</h4>
            <div class="variant-table">
              <table class="table">
                <thead>
                  <tr>
                    <th :key="'col_' + index" :style="colStyle(col)" v-for="(col, index) in variantsColumns">
                      {{ col.title }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(variation, index) in variations" :key="'variation_' + index">
                    <td><Input v-model="variation.sku" readonly /></td>
                    <td><InputNumber v-model="variation.stock[li].cost" /></td>
                    <td><InputNumber v-model="variation.stock[li].price" /></td>
                    <td v-if="$store.getters.stock">
                      <InputNumber v-model="variation.stock[li].quantity" />
                    </td>
                    <td><Input v-model="variation.stock[li].rack" /></td>
                    <template v-if="variants.length">
                      <td v-for="(va, vi) in variants" :key="'option' + vi">
                        <span v-if="va.options">
                          <Input v-model="variation.meta[va.name]" readonly />
                        </span>
                        <span v-else>
                          <Input v-model="variation[va.name]" readonly />
                        </span>
                      </td>
                    </template>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </span>
        <span v-else>
          <Row :gutter="16" v-if="isStock">
            <Col :sm="24" :md="12" :lg="12" :key="loc.value" v-for="(loc, index) in $store.getters.locations">
              <Card dis-hover style="margin-bottom: 16px;">
                <p slot="title">{{ loc.label }}</p>
                <div class="ivu-form-label-top ivu-form-inline">
                  <template v-if="$store.getters.stock">
                    <FormItem :label="$t('quantity')" prop="stock">
                      <InputNumber v-model="stock[index].quantity" />
                    </FormItem>
                  </template>
                  <FormItem :label="$t('cost')" prop="lcost">
                    <InputNumber v-model="stock[index].cost" />
                  </FormItem>
                  <FormItem :label="$t('price')" prop="lprice">
                    <InputNumber v-model="stock[index].price" />
                  </FormItem>
                  <FormItem :label="$t('rack_location')" prop="lrack">
                    <Input v-model="stock[index].rack" />
                  </FormItem>
                  <transition name="slide-fade">
                    <div v-if="stock[index].units && stock[index].units.length">
                      <Card dis-hover v-for="(unit, ui) in stock[index].units" :key="'loc_unit_' + unit.id">
                        <p slot="title">{{ unit.name }} ({{ unit.code }})</p>
                        <FormItem :label="$t('cost')">
                          <InputNumber v-model="stock[index].units[ui].cost" />
                        </FormItem>
                        <FormItem :label="$t('price')">
                          <InputNumber v-model="stock[index].units[ui].price" />
                        </FormItem>
                      </Card>
                    </div>
                  </transition>
                </div>
              </Card>
            </Col>
          </Row>
        </span>
      </div>
    </transition>
    <div>
      <Button type="primary" @click="goToStep(3)">{{ $t('next') }}</Button>
      <Button ghost type="warning" @click="goToStep(1)" style="margin-left: 8px;">{{ $t('back') }}</Button>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    istock: {
      type: Array,
      required: true,
    },
    isStock: {
      type: Number,
      required: true,
    },
    variants: {
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
    return {
      stock: [],
      option: false,
      variations: [],
      is_stock: false,
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
          minWidth: 125,
          key: 'cost',
          slot: 'cost',
          sortable: false,
          title: this.$t('cost'),
        },
        {
          minWidth: 125,
          key: 'price',
          slot: 'price',
          sortable: false,
          title: this.$t('price'),
        },
        {
          minWidth: 125,
          key: 'rack',
          slot: 'rack',
          sortable: false,
          title: this.$t('rack'),
        },
      ],
    };
  },
  created() {
    this.is_stock = this.isStock;
    // this.stock = [...this.istock];
    this.stock = JSON.parse(JSON.stringify(this.istock));
    this.variations = [...this.ivariations];
    this.variations = this.variations.map(v => {
      if (!v.stock) {
        v.stock = this.$store.getters.locations.map(l => {
          return { ...l, location_id: l.value, price: null, cost: null, quantity: null, rack: '' };
        });
      }
      return v;
    });
    if (this.$store.getters.stock) {
      this.variantsColumns.splice(3, 0, {
        minWidth: 125,
        key: 'quantity',
        slot: 'quantity',
        sortable: false,
        title: this.$t('quantity'),
      });
    }
    this.$nextTick(() => {
      if (this.variants.length > 0) {
        this.variants.map(v => {
          this.variantsColumns.splice(this.variantsColumns.length, 0, {
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
    isStockChanged(v) {
      this.$emit('is-stock-changed', v);
    },
    goToStep(step) {
      if (this.is_stock && step == 3 && this.hasVariants == 1) {
        let vQty = this.variations.reduce((a, v) => a + v.quantity, 0);
        let vStockQty = this.variations.reduce((a, v) => {
          let sQty = v.stock.reduce((sa, s) => sa + s.quantity, 0);
          return a + sQty;
        }, 0);
        if (vQty == vStockQty) {
          this.$emit('variations-changed', [...this.variations]);
          this.$emit('step-changed', step);
        } else {
          this.$Modal.confirm({
            title: this.$t('variations_qty_error'),
            content: this.$t('variations_qty_error_text'),
            okText: this.$t('auto_adjust'),
            onOk: () => {
              this.autoAdjustQty();
              this.$emit('variations-changed', [...this.variations]);
              this.$emit('step-changed', step);
            },
          });
        }
      } else {
        this.$emit('stock-changed', [...this.stock]);
        this.$emit('step-changed', step);
      }
    },
    autoAdjustQty() {
      this.variations = this.variations.map(v => {
        v.quantity = v.stock.reduce((a, s) => a + s.quantity, 0);
        return v;
      });
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
