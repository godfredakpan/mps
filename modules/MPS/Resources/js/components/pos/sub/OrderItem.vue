<template>
  <!-- <div class="order-item" @dblclick="toggleShow" v-touch:longtap="toggleShow"> -->
  <div class="order-item" v-touch:longtap="toggleShow">
    <!-- <div v-if="show" class="item">
        <span class="index"> #{{ index }} </span>
        <span class="details">
            <Input
                v-model="item.comment"
                type="textarea"
                :autosize="{ minRows: 1, maxRows: 5 }"
                @on-blur="changed"
                :placeholder="$t('item_comment')"
            ></Input>
        </span>
        <span class="quantity" style="text-align:right;">
            <Button type="error" @click="deleteItem">
                <Icon type="ios-trash" size="18"></Icon>
            </Button>
        </span>
    </div> -->
    <span class="item">
      <span class="index">
        #{{ index }}
        <!-- TODO make optional -->
        <span><br /><Icon type="md-close" size="16" class="pointer" color="#ed4014" @click="deleteItem"/></span>
      </span>
      <span class="details pointer" @click="toggleShow">
        {{ item.name }} <br />
        <span v-if="group" v-html="group.name + ' (<small>' + $t('discount') + ': ' + group.discount + '%</small>)<br />'"></span>
        <span v-if="item.discount_amount == 0">
          @<strong>
            {{ item.price | formatNumber($store.state.settings.decimals) }} +
            {{ totalItemTax | formatNumber($store.state.settings.decimals) }}
          </strong>
        </span>
        <span v-else>
          @<del>{{ item.price | formatNumber($store.state.settings.decimals) }}</del>
          <strong>
            {{ (item.price - item.discount_amount) | formatNumber($store.state.settings.decimals) }} +
            {{ totalItemTax | formatNumber($store.state.settings.decimals) }}
          </strong>
        </span>
      </span>
      <span class="quantity">
        <InputNumber v-model="item.quantity" @on-change="changed"></InputNumber>
      </span>
      <span class="subtotal">
        {{ subTotal | formatNumber($store.state.settings.decimals) }}
      </span>
    </span>
    <Modal v-model="show" :title="$t('edit') + ' - ' + item.name" :width="300" :footer-hide="true" @on-visible-change="editClose">
      <Form ref="editItem" :model="form" :rules="rules">
        <FormItem :label="$t('price')" prop="price" v-if="item.changeable">
          <InputNumber v-model="form.price"></InputNumber>
        </FormItem>
        <FormItem :label="$t('quantity')" prop="quantity">
          <InputNumber v-model="form.quantity"></InputNumber>
        </FormItem>
        <FormItem :label="$tc('tax', 2)" prop="taxes" v-if="item.changeable">
          <Select v-model="form.allTaxes" multiple style="width: 100%;">
            <Option v-for="option in $store.getters.taxes" :value="option.id" :key="'tax_' + option.id">
              {{ option.name }}
            </Option>
          </Select>
        </FormItem>
        <FormItem :label="$t('discount_')" prop="discount">
          <InputNumber v-model="form.discount"></InputNumber>
        </FormItem>
        <FormItem :label="$tc('promo', 2)" v-if="item.allPromotions && item.allPromotions.length > 0">
          <Select v-model="form.promotions" multiple style="width: 100%;">
            <Option v-for="(option, index) in item.allPromotions" :value="index" :key="'tax_' + index + '_' + option.id">
              {{ option.name }}
            </Option>
          </Select>
        </FormItem>
        <FormItem :label="$t('comment')">
          <Input type="textarea" v-model="form.comment" :placeholder="$t('item_comment')" :autosize="{ minRows: 2, maxRows: 5 }"></Input>
        </FormItem>
        <FormItem>
          <Button long type="primary" @click="update()">{{ $t('update') }}</Button>
        </FormItem>
      </Form>
      <!-- TODO make optional -->
      <Button long type="error" @click="deleteItem()">{{ $t('remove_from_order') }}</Button>
    </Modal>
  </div>
</template>
<script>
export default {
  props: {
    group: { required: true },
    ct: { type: Function, required: true },
    item: { type: Object, required: true },
    index: { type: Number, required: true },
  },
  data() {
    const discountV = (rule, value, callback) => {
      if (value && value > this.item.max_discount) {
        callback(new Error(this.$t('max_discount_error', { percent: parseFloat(this.item.max_discount) })));
      } else {
        callback();
      }
    };
    const priceV = (rule, value, callback) => {
      if (value && (value > this.item.max_price || value < this.item.min_price)) {
        callback(new Error(this.$t('mm_price_error', { min: parseFloat(this.item.min_price), max: parseFloat(this.item.max_price) })));
      } else {
        callback();
      }
    };
    return {
      show: false,
      form: { price: '', discount: '', comment: '' },
      rules: {
        price: [
          {
            required: true,
            type: 'number',
            message: this.$t('field_is_required', { field: this.$t('price') }),
            trigger: ['change', 'blur'],
          },
          { validator: priceV, trigger: ['change', 'blur'] },
        ],
        quantity: [
          {
            required: true,
            type: 'number',
            message: this.$t('field_is_required', { field: this.$t('quantity') }),
            trigger: ['change', 'blur'],
          },
        ],
        discount: [{ validator: discountV, trigger: ['change', 'blur'] }],
      },
    };
  },
  computed: {
    totalItemTax: function() {
      let amount = this.item.price - parseFloat(this.item.discount_amount ? this.item.discount_amount : 0);
      this.item.quantity = +this.item.quantity;
      this.item.taxes = this.ct(this.item.allTaxes, amount);
      return this.item.taxes.reduce((a, t) => a + parseFloat(t.amount) * parseFloat(this.item.quantity), 0);
    },
    subTotal() {
      this.item.total_tax_amount = this.item.taxes.reduce((a, t) => a + parseFloat(t.amount), 0);
      return parseFloat(
        this.$options.filters.formatDecimal(
          parseFloat(this.item.quantity) *
            (parseFloat(this.item.price) -
              parseFloat(this.item.discount_amount ? this.item.discount_amount : 0) +
              this.item.total_tax_amount),
          2
        )
      );
    },
  },
  created() {
    this.form.taxes = this.item.taxes;
    this.form.allTaxes = this.item.allTaxes.map(t => t.id);
    this.item.discount = this.item.discount ? parseFloat(this.item.discount) : null;
    this.item.discount_amount = this.item.discount_amount ? parseFloat(this.item.discount_amount) : 0;
    this.resetForm();
  },
  methods: {
    update() {
      this.$refs.editItem.validate(valid => {
        if (valid) {
          this.item.price = parseFloat(this.form.price);
          this.item.allTaxes = this.$store.getters.taxes.filter(t => {
            if (this.form.allTaxes.includes(t.id)) {
              return t;
            }
          });
          let promotions = this.item.promotions;
          this.item.comment = this.form.comment;
          this.item.quantity = this.form.quantity;
          this.item.promotions = this.form.promotions;
          this.item.discount = this.form.discount ? parseFloat(this.form.discount) : null;
          this.item.discount_amount = parseFloat(
            this.$options.filters.formatDecimal((this.item.price * this.item.discount) / 100, this.$store.state.settings.decimals)
          );

          this.item.allPromotions.map(p => {
            if (promotions.includes(p.id) && p.type == 'BXGY') {
              this.$store.commit('delPromotionItem', { id: p.item_id_to_get, promo_item_id: this.item.id });
            }
            if (this.item.promotions.includes(p.id)) {
              this.item.discount_amount += parseFloat(
                this.$options.filters.formatDecimal(
                  (parseFloat(this.item.price - this.item.discount_amount) * parseFloat(p.discount)) / 100,
                  this.$store.state.settings.decimals
                )
              );
              if (p.type == 'BXGY' && this.item.quantity >= p.quantity_to_buy) {
                this.$event.fire('addOrderRowByItemID', { id: p.item_id_to_get, item_id: this.item.id });
              }
            }
          });
          let amount = this.item.price - parseFloat(this.item.discount_amount ? this.item.discount_amount : 0);
          this.item.taxes = this.ct(this.item.allTaxes, amount);
          // this.aTO(this.item, this.item.quantity, true, true);
          this.changed();
          this.show = false;
        } else {
          this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
        }
      });
    },
    calcTaxes() {
      if (this.item.tax_included == 1) {
      } else {
        this.item.tax_amount;
      }
    },
    resetForm() {
      this.form.discount = this.item.discount;
      this.form.promotions = this.item.promotions;
      this.form.comment = this.item.comment || '';
      this.form.price = parseFloat(this.item.price);
      this.form.allPromotions = this.item.allPromotions;
      this.form.quantity = parseFloat(this.item.quantity);
    },
    editClose(reset) {
      if (!reset) {
        this.resetForm();
        this.$refs.editItem.resetFields();
      }
    },
    toggleShow() {
      if (this.item.promo) {
        this.$Notice.error({ title: this.$t('not_allowed'), desc: this.$t('not_allowed_error'), duration: 5 });
        return false;
      }
      this.resetForm();
      this.show = !this.show;
    },
    changed() {
      this.$store.commit('setOrderRow', this.item);
      // this.$event.fire('order:row:changed', this.row);
    },
    // calcRowTotal(item) {
    //     item.total_tax_amount = item.taxes.reduce((a, t) => a + parseFloat(t.amount), 0);
    //     return parseFloat(
    //         this.$options.filters.formatDecimal(
    //             parseFloat(item.quantity) *
    //                 (parseFloat(item.price) - parseFloat(item.discount_amount ? item.discount_amount : 0) + item.total_tax_amount),
    //             2
    //         )
    //     );
    // },
    deleteItem() {
      if (this.$store.getters.superAdmin) {
        if (this.item.promotions && this.item.promotions.length) {
          this.item.promotions = this.item.allPromotions ? this.item.allPromotions.filter(p => this.item.promotions.includes(p.id)) : [];
          this.item.promotions.map(p => {
            if (p.type == 'BXGY' && this.item.quantity >= p.quantity_to_buy) {
              this.$store.commit('delPromotionItem', { id: p.item_id_to_get, promo_item_id: this.item.id });
            }
          });
        }
        this.$store.commit('delOrderRow', this.item);
        this.show = false;
      } else {
        this.$Modal.confirm({
          width: 260,
          okText: this.$t('ok'),
          cancelText: this.$t('cancel'),
          title: this.$t('pin_required'),
          render: h => {
            return h('Input', {
              props: {
                autofocus: true,
                value: this.value,
                placeholder: this.$t('type_pin'),
              },
              on: {
                input: val => {
                  this.value = val;
                },
              },
            });
          },
        });
      }
    },
  },
};
</script>

<style lang="scss" scoped>
@import 'resources/sass/variables.sass';
.order-item {
  padding: 10px;
  border-bottom: 1px solid $border;
  &:nth-child(even) {
    background: #f8f8f9;
  }
  &:last-child {
    border-bottom: 0;
  }
  .item {
    display: flex;
    flex-direction: columns;
    align-items: center;
    justify-content: center;
    .index {
      width: 20px;
      font-weight: bold;
      margin-right: 10px;
      // align-self: flex-start;
    }
    .details {
      flex: 1;
    }
    .quantity {
      width: 70px;
      .ivu-input-number {
        width: 100% !important;
      }
    }
    .subtotal {
      width: 100px;
      padding-right: 10px;
      text-align: right;
      font-weight: bold;
    }
  }
}
.ivu-btn-text:focus {
  box-shadow: none;
}
</style>
