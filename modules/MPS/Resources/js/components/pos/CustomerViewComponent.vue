<template>
  <div class="layout">
    <Layout>
      <Content class="cv-main">
        <Header :style="{ position: 'absolute', width: '100%', padding: '0 16px' }">
          <div class="logo">{{ $store.getters.settings.name }}</div>
        </Header>
        <div class="promo-contents">
          <h1 class="mt16 mb16">{{ $t('thank_you_for_shopping') }}</h1>
          <h3 class="mb16">
            {{ $t('cv_feedback_text') }}
          </h3>
          <h1 class="mt16 text-muted">Modern Point of Sale Solution</h1>
          <h3 class="text-muted">by Tecdiary</h3>
        </div>
        <Row class="content" v-if="order">
          <Col class="pos-order">
            <div class="above-order-items">
              <div class="order-info">
                <span class="details customer_selection" style="margin-right: 20px;">
                  {{ $t('ref_tab') }}: <strong>{{ order.oId }}</strong>
                </span>
                <span class="other">
                  <fullscreen-component v-model="isFullscreen" class="hidden-sm" />
                </span>
              </div>
              <div class="order-sub-details">
                <span v-if="order.user" class="details">
                  {{ $t('served_by') }}: <strong>{{ order.user.name }}</strong>
                </span>
                <span v-else class="details">
                  {{ $t('served_by') }}:
                  <strong>{{
                    $store.getters.user && $store.getters.user.acting_user ? $store.getters.user.acting_user.name : $store.getters.user.name
                  }}</strong>
                </span>
                <span class="other">
                  <strong>{{ orderTotalItems }}</strong> {{ $tc('item', orderTotalItems) }} <strong>{{ orderTotalQuantity }}</strong>
                  {{ $t('quantity') }}
                </span>
              </div>
            </div>
            <div class="pos-order-items">
              <div class="order-items">
                <div class="header">
                  <span class="index">#</span>
                  <span class="details">{{ $t('decs') }}</span>
                  <span class="quantity">{{ $t('qty') }}</span>
                  <span class="subtotal">{{ $t('subtotal') }}</span>
                </div>
                <div class="pos-order-scroll scroll-x" ref="orderItems">
                  <div class="order-item" :key="index + '_' + row.id" v-for="(row, index) in order.items">
                    <span class="item">
                      <span class="index align-top"> #{{ index + 1 }} </span>
                      <span class="detail">
                        <strong>{{ row.name }}</strong>
                        <br />
                        <span>
                          <span
                            v-if="order.customer && order.customer.customer_group"
                            v-html="
                              order.customer.customer_group.name +
                                ' (<small>' +
                                $t('discount') +
                                ': ' +
                                order.customer.customer_group.discount +
                                '%</small>)<br />'
                            "
                          ></span>
                          <span
                            v-if="
                              row.selected.variations &&
                                row.selected.portions &&
                                !row.selected.portions.length &&
                                !row.selected.variations.length
                            "
                          >
                            <span v-if="row.discount_amount == 0">
                              @{{ row.price | formatNumber($store.state.settings.decimals) }}
                              <span v-if="row.total_tax_amount > 0">
                                + {{ row.total_tax_amount | formatNumber($store.state.settings.decimals) }}
                              </span>
                            </span>
                            <span v-else>
                              @<del>{{ row.price | formatNumber($store.state.settings.decimals) }}</del>
                              {{ (row.price - row.discount_amount) | formatNumber($store.state.settings.decimals) }}
                              <span v-if="row.total_tax_amount > 0">
                                + {{ row.total_tax_amount | formatNumber($store.state.settings.decimals) }}
                              </span>
                            </span>
                          </span>
                        </span>
                      </span>
                      <span class="quantity bold">
                        {{ row.quantity | formatQtyDecimal($store.state.settings.quantity_decimals) }}
                      </span>
                      <span class="subtotal">
                        <span v-if="row.selected.portions && !row.selected.portions.length">
                          {{ ((row.price + row.total_tax_amount) * row.quantity) | formatNumber($store.state.settings.decimals) }}
                        </span>
                      </span>
                    </span>
                    <template v-if="row.selected.variations && row.selected.variations.length">
                      <div class="combo-item variation" :key="'svi_' + svi" v-for="(sv, svi) in row.selected.variations">
                        <span class="index"></span>
                        <span class="details leading-medium">
                          <small>#{{ svi + 1 }}</small>
                          <span v-html="metaString(sv.meta)"></span>
                          <br />
                          <span v-if="sv.discount_amount == 0">
                            @{{ sv.price | formatNumber($store.state.settings.decimals) }} +
                            {{ calcItemTax(sv) | formatNumber($store.state.settings.decimals) }}
                          </span>
                          <span v-else>
                            @<del>{{ sv.price | formatNumber($store.state.settings.decimals) }}</del>
                            {{ (sv.price - sv.discount_amount) | formatNumber($store.state.settings.decimals) }}
                            <span v-if="calcItemTax(sv) > 0"> + {{ calcItemTax(sv) | formatNumber($store.state.settings.decimals) }} </span>
                          </span>
                        </span>
                        <span class="quantity bold">
                          {{ sv.quantity | formatQtyDecimal($store.state.settings.quantity_decimals) }}
                        </span>
                        <span class="subtotal"> </span>
                      </div>
                    </template>
                    <template v-if="row.selected.portions.length">
                      <div class="combo" v-for="(sp, spi) in row.selected.portions" :key="'spi_' + spi">
                        <div class="combo" v-for="p in row.portions.filter(ip => ip.id == sp.id)" :key="'pi_' + p.id">
                          <div class="combo-item variation">
                            <span class="index"></span>
                            <span class="details leading-medium">
                              <p>
                                {{ spi + 1 }}. {{ $tc('portion') }}:
                                <strong>{{ p.name == 'regular' ? $t('regular') : p.name }}</strong>
                                <br />
                                <span v-if="sp.discount_amount == 0">
                                  @{{ sp.price | formatNumber($store.state.settings.decimals) }} +
                                  {{ calcItemTax(sp) | formatNumber($store.state.settings.decimals) }}
                                </span>
                                <span v-else>
                                  @<del>{{ sp.price | formatNumber($store.state.settings.decimals) }}</del>
                                  {{ (sp.price - sp.discount_amount) | formatNumber($store.state.settings.decimals) }}
                                  <span v-if="calcItemTax(sp) > 0">
                                    + {{ calcItemTax(sp) | formatNumber($store.state.settings.decimals) }}
                                  </span>
                                </span>
                              </p>
                            </span>
                            <span class="quantity bold">
                              {{ sp.quantity | formatQtyDecimal($store.state.settings.quantity_decimals) }}
                            </span>
                            <span class="subtotal">
                              {{ formatNumber((sp.price - sp.discount_amount + sp.total_tax_amount) * sp.quantity) }}
                            </span>
                          </div>
                          <div :key="'ei_' + ei" class="combo-item bt0" style="padding-top: 1px;" v-for="(e, ei) in p.essentials">
                            <span class="index"></span>
                            <span class="details"> #{{ ei + 1 }} {{ e.item.name }} </span>
                            <span class="quantity input">
                              {{ parseFloat(e.quantity * sp.quantity) }}
                            </span>
                            <span class="subtotal"></span>
                          </div>
                          <template v-for="(g, gi) in p.choosables">
                            <div
                              class="combo-item bt0"
                              style="padding-top: 1px;"
                              :key="'gii_' + gii + '_' + gi"
                              v-for="(gitem, gii) in g.items"
                            >
                              <template v-if="getPortionChoosable(sp, g.id, gitem.item_id)">
                                <span class="index"></span>
                                <span class="details">
                                  #{{ gi + 1 + p.essentials.length }}
                                  {{ gitem.item.name }}
                                </span>
                                <span class="quantity input">
                                  {{ parseFloat(gitem.quantity * sp.quantity) }}
                                  <!-- {{ parseFloat(gitem.quantity * row.quantity) }} -->
                                </span>
                                <span class="subtotal"></span>
                              </template>
                            </div>
                          </template>
                        </div>
                      </div>
                    </template>
                    <template v-if="row.selected.modifiers.length">
                      <div class="combo-item bg">
                        <span class="index"></span>
                        <span class="details">
                          <p style="font-weight: bold;">{{ $tc('modifier', 2) }}</p>
                        </span>
                        <span class="quantity">&nbsp;</span>
                        <span class="subtotal">&nbsp;</span>
                      </div>
                      <div :sm="24" :md="12" :lg="12" :key="'mi_' + mi" v-for="(m, mi) in row.selected.modifiers">
                        <div class="combo-item" style="padding-top: 5px;">
                          <span class="index"></span>
                          <span class="details leading-medium">
                            {{ mi + 1 }}. <strong>{{ m.option }}</strong>
                            <br />
                            {{ m.title }}
                            @{{ formatNumber(m.price - m.discount_amount) }}
                          </span>
                          <span class="quantity bold">
                            {{ m.quantity | formatQtyDecimal($store.state.settings.quantity_decimals) }}
                          </span>
                          <span class="subtotal"> </span>
                        </div>
                      </div>
                    </template>
                  </div>
                </div>
              </div>
            </div>
            <div class="below-order-items">
              <div class="order-total">
                <h3 class="total" v-show="orderTotalItems != 0">
                  {{ $t('payable_amount') }}
                  <span class="amount">
                    <span v-if="orderOriginalTotal != orderPayableAmount">
                      <del>
                        {{ orderOriginalTotal | formatNumber($store.state.settings.decimals) }}
                      </del>
                    </span>
                    <span>
                      <span v-if="orderDiscount">
                        <span style="font-weight: normal;">
                          ({{ orderTotalAmount | formatNumber($store.state.settings.decimals) }} -
                          {{ orderDiscount | formatNumber($store.state.settings.decimals) }})
                        </span>
                        {{ orderPayableAmount | formatNumber($store.state.settings.decimals) }}
                      </span>
                      <span v-else>
                        {{ orderPayableAmount | formatNumber($store.state.settings.decimals) }}
                      </span>
                    </span>
                  </span>
                </h3>
              </div>
            </div>
          </Col>
        </Row>
      </Content>
    </Layout>
  </div>
</template>
<script>
import PosOrderItemsComponent from './PosOrderItemsComponent';
import FullscreenComponent from '@mpscom/helpers/FullscreenComponent';

export default {
  components: {
    FullscreenComponent,
    PosOrderItemsComponent,
  },
  data() {
    return {
      updated: 0,
      unsub: null,
      loading: true,
      location: null,
      isFullscreen: false,
      order: this.$storage.read('order'),
    };
  },
  computed: {
    isMobile() {
      return window && document.documentElement.clientWidth <= 768;
    },
    cols() {
      let col = 3;
      if (this.$store.getters.settings.show_discount == 1) {
        col++;
      }
      if (this.$store.getters.settings.show_tax == 1) {
        col++;
      }
      return col;
    },
  },
  watch: {},
  created() {
    // Set Title
    if (window && this.$route.meta.title) {
      document.title = this.$route.meta.title + ' - ' + this.$store.getters.settings.name;
    }

    this.unsub = setInterval(() => {
      this.updated++;
      this.order = this.$storage.read('order');
      this.$nextTick(() => {
        if (this.$refs.orderItems && this.$refs.orderItems.scrollHeight) {
          this.$refs.orderItems.scrollTop = this.$refs.orderItems.scrollHeight;
        }
      });
    }, 2000);
  },
  beforeDestory() {
    if (this.unsub) {
      clearInterval(this.unsub);
    }
  },
  computed: {
    orderTotalItems() {
      return this.order && this.order.items ? this.order.items.length : 0;
    },
    orderOriginalTotal() {
      if (this.order && this.order.discount_method != 'order') {
        let items = [...this.order.items];
        let total = items
          .map(i => {
            let item = { allTaxes: [...i.allTaxes], quantity: i.quantity, selected: {} };
            item.price = i.price;
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
      return this.order && this.order.discount_method == 'order'
        ? this.formatDecimal((this.orderTotalAmount * this.order.discount) / 100)
        : 0;
    },
    orderTotalQuantity() {
      return this.order ? this.order.items.map(item => item.quantity).reduce((total, curr) => total + curr, 0) : 0;
    },
    orderPayableAmount() {
      return this.order ? this.formatDecimal(this.orderTotalAmount - this.orderDiscount) : 0;
    },
    paymentChange() {
      return this.paymentForm.amount > this.orderTotalAmount ? this.paymentForm.amount - this.orderTotalAmount : 0;
    },
    totalItemTax: function() {
      let tax = this.order.items.reduce((tax, item) => {
        item = this.doTaxCalculation(item);
        let rowTax = item.taxes.reduce((a, t) => a + parseFloat(t.amount) * parseFloat(item.quantity), 0);
        return parseFloat(tax + rowTax);
      }, 0);
      return this.formatDecimal(tax);
    },
    orderTotalAmount: function() {
      let items = [...this.order.items];
      return this.formatDecimal(
        this.order.items.reduce((a, item) => {
          return a + (item ? this.calcRowTotal(item) : 0);
        }, 0)
      );
    },
  },
  methods: {
    toggleFullScreen() {
      this.$event.fire('expand:view');
    },
    calcRowOriginalTotal(item) {
      let modifiers_price = 0;
      if (item.selected.modifiers.length) {
        modifiers_price += item.selected.modifiers.reduce((a, m) => {
          let price = parseFloat(m.price);
          m.taxes = this.calculateTaxes(item.allTaxes, price);
          m.total_tax_amount = this.calcItemTax(m);
          return a + parseFloat(m.quantity) * (price + parseFloat(m.total_tax_amount));
        }, 0);
      }

      if (item.selected.variations.length || item.selected.portions.length) {
        if (item.selected.variations.length) {
          modifiers_price += item.selected.variations.reduce((a, v) => {
            let price = parseFloat(v.price ? v.price : item.price);
            v.taxes = this.calculateTaxes(item.allTaxes, price);
            v.total_tax_amount = this.calcItemTax(v);
            return a + parseFloat(v.quantity) * (price + parseFloat(v.total_tax_amount));
          }, 0);
        }
        if (item.selected.portions.length) {
          modifiers_price += item.selected.portions.reduce((a, p) => {
            let price = parseFloat(p.price);
            p.taxes = this.calculateTaxes(item.allTaxes, price);
            p.total_tax_amount = this.calcItemTax(p);
            return a + parseFloat(p.quantity) * (price + parseFloat(p.total_tax_amount));
          }, 0);
        }
        return modifiers_price;
      }

      item.taxes = this.calculateTaxes(item.allTaxes, item.price);
      item.total_tax_amount = this.calcItemTax(item);
      return modifiers_price + parseFloat(item.quantity) * (parseFloat(item.price) + item.total_tax_amount);
    },
    calcRowTotal(item, selected = false) {
      let modifiers_price = 0;
      if (item.selected.modifiers.length) {
        modifiers_price += item.selected.modifiers.reduce(
          (a, m) =>
            a +
            parseFloat(m.quantity) *
              (parseFloat(m.price) -
                parseFloat(m.price && m.discount_amount ? m.discount_amount : 0) +
                parseFloat(m.price ? m.total_tax_amount : 0)),
          0
        );
      }
      item.total_tax_amount = item ? this.calcItemTax(item) : 0;
      if (!selected && (item.selected.variations.length || item.selected.portions.length)) {
        if (item.selected.variations.length) {
          modifiers_price += item.selected.variations.reduce(
            (a, v) =>
              a +
              parseFloat(v.quantity) *
                (parseFloat(v.price ? v.price : item.price) -
                  parseFloat(v.price && v.discount_amount ? v.discount_amount : 0) +
                  parseFloat(v.price ? v.total_tax_amount : 0)),
            0
          );
        }
        if (item.selected.portions.length) {
          modifiers_price += item.selected.portions.reduce(
            (a, p) =>
              a +
              parseFloat(p.quantity) *
                (parseFloat(p.price) -
                  parseFloat(p.price && p.discount_amount ? p.discount_amount : 0) +
                  parseFloat(p.price ? p.total_tax_amount : 0)),
            0
          );
        }
        return modifiers_price;
      }
      return (
        modifiers_price +
        this.formatDecimal(
          parseFloat(selected ? selected.quantity : item.quantity) *
            (parseFloat(selected ? selected.price : item.price) -
              parseFloat(selected ? selected.discount_amount : item.discount_amount ? item.discount_amount : 0) +
              (selected ? selected.total_tax_amount : item.total_tax_amount))
        )
      );
    },
    getPortionChoosable(p, gId, iId) {
      let group = p.choosables.find(g => g.id == gId);
      return group.selected == iId;
    },
    calcItemTax(item) {
      return item.taxes.reduce((a, t) => a + parseFloat(t.amount), 0);
    },
    calculateTaxes(taxes, amount) {
      taxes = this.applicableTaxes(taxes);
      let c_taxes = taxes.filter(t => t.compound == true);
      let nc_taxes = taxes.filter(t => t.compound == false);
      let non_compound = nc_taxes.map(t => ({
        ...t,
        value: t.id,
        on: parseFloat(amount),
        amount: this.formatDecimal((parseFloat(amount) * parseFloat(t.rate)) / 100),
      }));
      let tax_amount = non_compound.reduce((a, nct) => a + parseFloat(nct.amount), 0);
      let compound = c_taxes.map(t => ({
        ...t,
        value: t.id,
        on: parseFloat(amount) + parseFloat(tax_amount),
        amount: this.formatDecimal(((parseFloat(amount) + parseFloat(tax_amount)) * parseFloat(t.rate)) / 100),
      }));
      return [...non_compound, ...compound];
    },
    applicableTaxes(taxes) {
      return taxes.filter(t => {
        let location = this.$store.getters.location;
        if (t.state && this.order.customer && location) {
          let same = this.order.customer.state == location.state;
          return t.same ? same : !same;
        }
        return true;
      });
    },
  },
};
</script>

<style lang="scss" scoped>
.cv-main {
  min-width: 450px;
  position: relative;
  background: #f5f7f9;
}
.promo-contents {
  left: 0;
  top: 0px;
  display: flex;
  padding: 0 10px;
  min-height: 100vh;
  position: absolute;
  text-align: center;
  align-items: center;
  flex-direction: column;
  justify-content: center;
  min-width: calc(100vw - 450px);
  max-width: calc(100vw - 450px);
  @media (max-width: 650px) {
    display: none;
  }
}
.content {
  width: 100%;
  min-width: 450px;
  min-height: 100vh;
}
.logo {
  color: #fff;
  font-size: 16px;
  font-weight: bold;
}

@import 'scss/order-items.scss';
.pos-order {
  top: 0px;
  z-index: 10;
  width: 450px;
  margin-left: auto;
  min-height: 100vh;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
  @media (max-width: 650px) {
    width: 100%;
    margin-left: 0;
    max-width: 100%;
  }
  .index.align-top {
    align-self: flex-start;
  }
  .detail {
    flex: 1;
  }
}
.pos-order-scroll {
  height: calc(100vh - 134px);
}
.quantity {
  text-align: center;
}
</style>
