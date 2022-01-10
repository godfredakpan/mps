<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $tc('gift_card', 2) }}</p>
    <router-link to="/gift_cards/add" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('add') }} {{ $tc('gift_card') }}
    </router-link>
    <div>
      <table-component
        :columns="columns"
        :options="options"
        url="app/gift_cards"
        :dblClickCB="showInfo"
        :bulkDelCB="deleteRecords"
        :refresh="refresh"
      ></table-component>
    </div>
  </Card>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
export default {
  mixins: [Table('gift_card', 'app/gift_cards', 'name')],
  data() {
    return {
      columns: [
        // { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: '', width: 50, key: 'id', align: 'center', fixed: 'left', render: this.renderTransactions },
        { title: this.$t('created_at'), sortable: true, sortType: 'desc', key: 'created_at', minWidth: 175, render: this.renderCreatedAt },
        { title: this.$t('number'), sortable: true, key: 'number', minWidth: 200 },
        { title: this.$tc('amount'), sortable: true, key: 'amount', minWidth: 90, maxWidth: 125, render: this.renderAmount },
        { title: this.$t('balance'), sortable: true, key: 'balance', minWidth: 90, maxWidth: 125, render: this.renderBalance },
        { title: this.$t('points'), sortable: true, key: 'points', minWidth: 80, maxWidth: 125, render: this.renderPoints },
        { title: this.$tc('expiry_date'), sortable: true, key: 'expiry_date', width: 125, render: this.renderExpiryDate },
        { title: this.$tc('customer'), sortable: true, key: 'customer', minWidth: 150, maxWidth: 250, render: this.renderCustomer },
        { title: this.$t('details'), sortable: true, key: 'details', ellipsis: true, minWidth: 250 },
        // { title: this.$t('actions'), key: 'actions', align: 'center', fixed: 'right', width: 100, render: this.renderActions },
        {
          width: 100,
          fixed: 'right',
          key: 'actions',
          align: 'center',
          title: this.$t('actions'),
          render: (h, params) => {
            return h('actions-component', {
              props: {
                params,
                editFn: this.editRecord,
                deleteFn: this.deleteRecord,
                record: { model: 'gift_card', name: 'number', title: this.$tc('gift_card') },
              },
            });
          },
        },
      ],
      options: {
        orderBy: 'created_at desc',
        perPage: this.$store.state.settings.rows,
      },
    };
  },
  methods: {
    renderTransactions(h, params) {
      return h('transactions-icon-component', { props: { id: params.row.id, url: '/gift_cards/logs/' } });
    },
    renderAmount(h, params) {
      return this.renderNumber(h, params, 'amount', true);
    },
    renderBalance(h, params) {
      return this.renderNumber(h, params, 'balance', true);
    },
    renderPoints(h, params) {
      return this.renderNumber(h, params, 'points', true);
    },
    renderCustomer(h, params) {
      return <div>{params.row.customer ? params.row.customer.name : ''}</div>;
    },
    renderExpiryDate(h, params) {
      return (
        <div>
          {params.row.expiry_date ? this.$options.filters.formatDate(params.row.expiry_date, this.$store.state.settings.dateformat) : ''}
        </div>
      );
    },
  },
};
</script>
