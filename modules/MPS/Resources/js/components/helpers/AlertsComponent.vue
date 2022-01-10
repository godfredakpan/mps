<template>
  <div v-if="alerts">
    <Row :gutter="16" style="margin-top:-16px;">
      <Col :xs="24" :sm="12" class="mt16" v-if="alerts.notifications">
        <a @click="goTo('/notifications?reload=' + new Date().getTime())">
          <Card dis-hover :class="alerts.notifications ? 'alert-hover' : 'alert-disabled'">
            <div style="text-align:center">
              <h1>{{ alerts.notifications | formatNumber(0) }}</h1>
              <h4>{{ $tc('notification', alerts.notifications) }}</h4>
            </div>
          </Card>
        </a>
      </Col>
      <Col :xs="24" :sm="12" class="mt16" v-if="alerts.payment_reviews">
        <a @click="goTo('/payments?review=1')">
          <Card dis-hover :class="alerts.payment_reviews ? 'alert-hover' : 'alert-disabled'">
            <div style="text-align:center">
              <h1>{{ alerts.payment_reviews | formatNumber(0) }}</h1>
              <h4>{{ $t('review_x', { x: $tc('payment', alerts.payment_reviews) }) }}</h4>
            </div>
          </Card>
        </a>
      </Col>
      <Col :xs="24" :sm="12" class="mt16" v-if="alerts.payments">
        <a @click="goTo('/payments?due=1')">
          <Card dis-hover :class="alerts.payments ? 'alert-hover' : 'alert-disabled'">
            <div style="text-align:center">
              <h1>{{ alerts.payments | formatNumber(0) }}</h1>
              <h4>{{ $t('due_payment_alerts', { x: $tc('payment', alerts.payments) }) }}</h4>
            </div>
          </Card>
        </a>
      </Col>
      <Col :xs="24" :sm="12" class="mt16" v-if="alerts.expenses">
        <a @click="goTo('/expenses?require_approval=1')">
          <Card dis-hover :class="alerts.expenses ? 'alert-hover' : 'alert-disabled'">
            <div style="text-align:center">
              <h1>{{ alerts.expenses | formatNumber(0) }}</h1>
              <h4>{{ $t('x_require_approval', { x: $tc('expense', alerts.expenses) }) }}</h4>
            </div>
          </Card>
        </a>
      </Col>
      <Col :xs="24" :sm="12" class="mt16" v-if="alerts.recurring_soon">
        <a @click="goTo('/sales/recurring?in_days=7')">
          <Card dis-hover :class="alerts.recurring_soon ? 'alert-hover' : 'alert-disabled'">
            <div style="text-align:center">
              <h1>{{ alerts.recurring_soon | formatNumber(0) }}</h1>
              <h4>{{ $t('coming_x', { x: $tc('recurring_sale', alerts.recurring_soon) }) }}</h4>
            </div>
          </Card>
        </a>
      </Col>

      <Col :xs="24" :sm="12" class="mt16" v-if="alerts.quantity_alert">
        <a @click="goTo('/reports/quantity')">
          <Card dis-hover :class="alerts.quantity_alert ? 'alert-hover' : 'alert-disabled'">
            <div style="text-align:center">
              <h1>{{ alerts.quantity_alert | formatNumber(0) }}</h1>
              <h4>{{ $t('product_quantity_alerts', { x: $tc('item', alerts.quantity_alert) }) }}</h4>
            </div>
          </Card>
        </a>
      </Col>
      <Col :xs="24" :sm="12" class="mt16" v-if="alerts.expiry_alert">
        <a @click="goTo('/reports/expiring')">
          <Card dis-hover :class="alerts.expiry_alert ? 'alert-hover' : 'alert-disabled'">
            <div style="text-align:center">
              <h1>{{ alerts.expiry_alert | formatNumber(0) }}</h1>
              <h4>{{ $t('product_expiry_alerts', { x: $tc('item', alerts.expiry_alert) }) }}</h4>
            </div>
          </Card>
        </a>
      </Col>

      <Col :xs="24" :sm="12" class="mt16" v-if="alerts.customers">
        <a @click="goTo('/customers?due_limit=80')">
          <Card dis-hover :class="alerts.customers ? 'alert-hover' : 'alert-disabled'">
            <div style="text-align:center">
              <h1>{{ alerts.customers | formatNumber(0) }}</h1>
              <h4>{{ $t('reaching_due_limit', { x: $tc('customer', alerts.customers) }) }}</h4>
            </div>
          </Card>
        </a>
      </Col>
      <Col :xs="24" :sm="12" class="mt16" v-if="alerts.suppliers">
        <a @click="goTo('/suppliers?due_limit=80')">
          <Card dis-hover :class="alerts.suppliers ? 'alert-hover' : 'alert-disabled'">
            <div style="text-align:center">
              <h1>{{ alerts.suppliers | formatNumber(0) }}</h1>
              <h4>{{ $t('reaching_due_limit', { x: $tc('supplier', alerts.suppliers) }) }}</h4>
            </div>
          </Card>
        </a>
      </Col>
    </Row>
  </div>
</template>

<script>
export default {
  props: {
    alerts: {
      required: true,
    },
    close: {
      required: true,
    },
  },
  methods: {
    goTo(url) {
      this.close();
      if (this.$route.fullPath != url) {
        this.$router.push(url);
      }
    },
  },
};
</script>

<style>
.alert-hover:hover {
  color: #2b6cb0 !important;
  border-color: #bee3f8 !important;
  background-color: #ebf8ff !important;
}
.alert-disabled:hover {
  cursor: default;
}
</style>
