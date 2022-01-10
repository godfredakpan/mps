<template>
  <div>
    <p v-if="!payments || payments.length < 1">
      {{ $t('no_payment_added') }}
    </p>
    <div v-else class="bordered">
      <Table stripe :columns="columns1" :data="payments"></Table>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    payments: {
      required: true,
    },
  },
  data() {
    return {
      columns1: [
        {
          width: 175,
          sortable: false,
          sortType: 'desc',
          key: 'created_at',
          title: this.$t('created_at'),
          render: this.renderCreatedAt,
        },
        { title: this.$t('reference'), className: 'reference', sortable: false, key: 'reference', width: 150 },
        { title: this.$t('amount'), sortable: false, key: 'amount', minWidth: 90, maxWidth: 150, render: this.renderAmount },
        { title: this.$t('received'), sortable: false, key: 'received', width: 100, render: this.renderIconReceived },
        { title: this.$t('gateway'), sortable: false, key: 'gateway', minWidth: 100, maxWidth: 150 },
        {
          title: this.$tc('account'),
          sortable: false,
          key: 'for',
          maxWidth: 300,
          minWidth: 150,
          render: this.renderAccount,
        },
        {
          title: this.$tc('created_for'),
          sortable: false,
          key: 'for',
          maxWidth: 300,
          minWidth: 150,
          render: this.renderPayable,
        },
        {
          title: this.$t('payment_amount'),
          sortable: false,
          key: 'amount',
          minWidth: 150,
          maxWidth: 175,
          render: this.renderPaymentAmount,
        },
        {
          width: 175,
          key: 'user_id',
          sortable: false,
          render: this.renderUser,
          title: this.$t('created_by'),
        },
      ],
    };
  },
  methods: {
    renderAccount(h, params) {
      return <div>{params.row.account.name}</div>;
    },
    renderPayable(h, params) {
      return <div>{params.row.payable.name}</div>;
    },
    renderUser(h, params) {
      return <div>{params.row.user.name}</div>;
    },
    renderAmount(h, params) {
      return (
        <div class="text-right bold">
          {this.$options.filters.formatNumber(params.row.pivot.amount, this.$store.getters.settings.decimals)}
        </div>
      );
    },
    renderPaymentAmount(h, params) {
      return (
        <div class="text-right bold">{this.$options.filters.formatNumber(params.row.amount, this.$store.getters.settings.decimals)}</div>
      );
    },
    renderCreatedAt(h, params) {
      return <div>{this.$options.filters.formatDate(params.row.created_at, this.$store.state.settings.dateformat + ' HH:mm A')}</div>;
    },
    renderIconReceived(h, params) {
      return (
        <div style="text-align:center;">
          {params.row.received == 1 ? (
            <i class="ivu-icon ivu-icon-md-checkmark" style="font-size: 16px; color: #19be6b;" />
          ) : (
            <i class="ivu-icon ivu-icon-md-close" style="font-size: 16px; color: #ed4014;" />
          )}
        </div>
      );
    },
  },
};
</script>
