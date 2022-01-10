<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $tc('report', 2) }}</p>
    <a href="#" slot="extra">
      <Dropdown style="margin: 0 -5px 0 20px;" placement="bottom-end" @on-click="onSelect">
        <a href="javascript:void(0)" style="padding-right: 10px;">
          <Icon type="md-menu" size="18"></Icon>
        </a>
        <DropdownMenu slot="list">
          <template v-if="report_routes.length > 0">
            <template v-for="(route, ri) in report_routes">
              <template v-if="ri && !route.meta.hideInMenu">
                <DropdownItem :name="route.name" :key="route.name" :divided="route.meta.divided">
                  {{ route.meta.title }}
                </DropdownItem>
              </template>
            </template>
          </template>
        </DropdownMenu>
      </Dropdown>
    </a>
    <div style="margin-bottom:-16px;">
      <Row :gutter="16" class="sparkboxes">
        <template v-for="(d, di) in data">
          <Col :sm="12" :md="6" :key="di">
            <div class="box dark static">
              <div class="details">
                <h3>{{ parseInt(d.value) }}</h3>
                <h4>{{ d.label }}</h4>
              </div>
              <div style="clear:both;"></div>
            </div>
          </Col>
        </template>
      </Row>
      <h3 class="mt16 mb16">{{ $t('links') }}</h3>
      <Row :gutter="16" class="sparkboxes">
        <template v-for="(r, ri) in report_routes">
          <Col :sm="12" :md="6" :key="ri" v-if="ri && !r.meta.hideInMenu">
            <router-link :to="r.path" class="box-hover">
              <div class="box static">
                <div class="details">
                  <h3>{{ r.meta.title }}</h3>
                </div>
                <div style="clear:both;"></div>
              </div>
            </router-link>
          </Col>
        </template>
      </Row>
    </div>
  </Card>
</template>

<script>
import { routes } from '@mpsjs/routes/index';

export default {
  data() {
    return {
      data: {},
      report_routes: [],
    };
  },
  created() {
    this.report_routes = this.routes = routes(this.$store.state.user_language)[0].children.find(r => r.path == 'reports').children;
    this.$http
      .get('app/reports/totals')
      .then(res => {
        this.prepareData(res.data.totals);
        this.updated++;
      })
      .finally(() => (this.loading = false));
  },
  methods: {
    prepareData(totals) {
      this.data = [
        { label: this.$t('total_x', { x: this.$tc('sale', 2) }), value: totals.sales },
        { label: this.$t('total_x', { x: this.$tc('purchase', 2) }), value: totals.purchases },
        { label: this.$t('total_x', { x: this.$tc('income', 2) }), value: totals.incomes },
        { label: this.$t('total_x', { x: this.$tc('expense', 2) }), value: totals.expenses },
        { label: this.$t('total_x', { x: this.$tc('quotation', 2) }), value: totals.quotations },
        { label: this.$t('total_x', { x: this.$tc('return_order', 2) }), value: totals.return_orders },
        { label: this.$t('total_x', { x: this.$tc('stock_adjustment', 2) }), value: totals.stock_adjustments },
        { label: this.$t('total_x', { x: this.$tc('stock_transfer', 2) }), value: totals.stock_transfers },
        { label: this.$t('total_x', { x: this.$tc('customer', 2) }), value: totals.customers },
        { label: this.$t('total_x', { x: this.$tc('supplier', 2) }), value: totals.suppliers },
        { label: this.$t('total_x', { x: this.$tc('category', 2) }), value: totals.categories },
        { label: this.$t('total_x', { x: this.$tc('brand', 2) }), value: totals.brands },
        // { label: this.$t('total_x', { x: this.$tc('item', 2) }), value: totals.items },
        { label: this.$t('total_x', { x: this.$tc('item', 2) }), value: totals.standard_items },
        { label: this.$t('total_x', { x: this.$tc('service', 2) }), value: totals.services },
        { label: this.$t('total_x', { x: this.$tc('recipe', 2) }), value: totals.recipes },
        { label: this.$t('total_x', { x: this.$tc('combo', 2) }), value: totals.combos },
      ];
    },
    onSelect(name) {
      if (name) {
        if (name == 'mail_settings') {
          this.email = true;
        } else if (name == 'scale_barcode') {
          this.scale_barcode_show = true;
        } else if (name == 'payment_settings') {
          this.payment = true;
        } else {
          this.$router.push({ name });
        }
      }
    },
  },
};
</script>
