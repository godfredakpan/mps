<template>
  <div class="order register" v-if="record">
    <Alert v-if="!record.closed_at">
      {{ $tc('register_is_not_closed') }}
    </Alert>
    <List border>
      <ListItem class="table-wrapper">
        <table class="table mt-0">
          <tr>
            <td class="bold bg-gray" style="border-radius: 5px 0 0 0;">{{ $tc('register') }}</td>
            <td class="bold bg-gray" style="border-radius: 0 5px 0 0;">{{ record.register.name }}</td>
          </tr>
          <tr>
            <td>{{ $t('opened_at') }}</td>
            <td class="bold">{{ record.created_at | datetime }}</td>
          </tr>
          <tr v-if="record.closed_at">
            <td>{{ $t('closed_at') }}</td>
            <td class="bold">{{ record.closed_at | datetime }}</td>
          </tr>
          <tr v-if="record.closed_by">
            <td>{{ $t('closed_by') }}</td>
            <td class="bold">{{ record.closed_by_user.name }}</td>
          </tr>
          <tr>
            <td>{{ $t('total_sales_amount') }}</td>
            <td class="bold" style="border-radius: 0 0 5px 0;">
              {{ total_sales | formatNumber($store.state.settings.decimals) }}
            </td>
          </tr>
        </table>
      </ListItem>
    </List>
    <List border class="mt16">
      <ListItem class="table-wrapper mt-0">
        <table class="table">
          <tr>
            <td>{{ $t('cash_in_hand') }}</td>
            <td class="bold text-right">{{ record.cash_in_hand | formatNumber($store.state.settings.decimals) }}</td>
          </tr>
          <tr>
            <td>{{ $t('x_amount', { x: $t('cash') }) }}</td>
            <td class="bold text-right">{{ record.total_cash_amount | formatNumber($store.state.settings.decimals) }}</td>
          </tr>
          <tr>
            <td>{{ $t('x_amount', { x: $t('credit_card') }) }}</td>
            <td class="bold text-right">{{ record.total_cc_slips_amount | formatNumber($store.state.settings.decimals) }}</td>
          </tr>
          <tr>
            <td>{{ $t('x_amount', { x: $t('cheque') }) }}</td>
            <td class="bold text-right">{{ record.total_cheques_amount | formatNumber($store.state.settings.decimals) }}</td>
          </tr>
          <tr>
            <td>{{ $t('x_amount', { x: $t('other') }) }}</td>
            <td class="bold text-right">{{ record.total_other_amount | formatNumber($store.state.settings.decimals) }}</td>
          </tr>
          <tr>
            <td>{{ $t('x_amount', { x: $tc('gift_card') }) }}</td>
            <td class="bold text-right">{{ record.total_gift_card_amount | formatNumber($store.state.settings.decimals) }}</td>
          </tr>
          <tr>
            <td>{{ $t('x_amount', { x: $tc('return_order', 2) }) }}</td>
            <td class="bold text-right">{{ record.total_return_orders_amount | formatNumber($store.state.settings.decimals) }}</td>
          </tr>
          <tr>
            <td>{{ $t('x_amount', { x: $tc('refund') }) }}</td>
            <td class="bold text-right">{{ record.total_refunds_amount | formatNumber($store.state.settings.decimals) }}</td>
          </tr>
          <tr>
            <td>{{ $t('x_amount', { x: $tc('expense', 2) }) }}</td>
            <td class="bold text-right">{{ record.total_expenses_amount | formatNumber($store.state.settings.decimals) }}</td>
          </tr>
          <tr>
            <td class="bold bg-gray" style="border-radius: 0 0 0 5px;">{{ $t('total_amount') }}</td>
            <td class="bold text-right bg-gray" style="border-radius: 0 0 5px 0;">
              {{ total | formatNumber($store.state.settings.decimals) }}
            </td>
          </tr>
        </table>
      </ListItem>
    </List>
  </div>
</template>
<script>
export default {
  props: {
    close: { required: true },
    record: { required: true },
  },
  computed: {
    total() {
      return this.record
        ? parseFloat(this.record.total_cash_amount) +
            parseFloat(this.record.total_cc_slips_amount) +
            parseFloat(this.record.total_cheques_amount) +
            parseFloat(this.record.total_other_amount) +
            parseFloat(this.record.total_gift_card_amount) -
            parseFloat(this.record.total_refunds_amount) -
            parseFloat(this.record.total_expenses_amount)
        : 0;
    },
    total_sales() {
      return this.record
        ? parseFloat(this.record.total_cash_amount) +
            parseFloat(this.record.total_cc_slips_amount) +
            parseFloat(this.record.total_cheques_amount) +
            parseFloat(this.record.total_other_amount) +
            parseFloat(this.record.total_gift_card_amount)
        : 0;
    },
  },
};
</script>

<style>
.register .table-wrapper {
  width: 100%;
  padding: 0 !important;
}
.register .table th,
.register .table td {
  padding: 5px 10px;
  border-bottom: 1px solid #e8eaec;
}
</style>
