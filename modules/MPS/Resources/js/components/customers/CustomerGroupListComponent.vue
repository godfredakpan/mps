<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $tc('customer_group', 2) }}</p>
    <router-link to="/settings/customer_groups/add" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('add') }} {{ $tc('customer_group') }}
    </router-link>
    <div>
      <table-component
        url="app/customer_groups"
        :columns="columns"
        :options="options"
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
  mixins: [Table('customer_group', 'app/customer_groups', 'name', 'company')],
  data() {
    return {
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('name'), sortable: true, key: 'name', sortType: 'asc', minWidth: 200 },
        { title: this.$tc('company'), sortable: true, key: 'company', width: 175 },
        { title: this.$t('phone'), sortable: true, key: 'phone', width: 150 },
        { title: this.$t('email'), sortable: true, key: 'email', minWidth: 200 },
        { title: this.$t('balance'), sortable: false, key: 'journal', width: 150, render: this.renderBalance },
        // { title: '', width: 50, key: 'id', align: 'center', render: this.renderTransactions },
        { title: this.$t('address'), sortable: true, key: 'address', minWidth: 200 },
        { title: this.$t('state'), sortable: true, key: 'state_name', maxWidth: 150, minWidth: 150 },
        { title: this.$t('country'), sortable: true, key: 'country_name', maxWidth: 150, minWidth: 150 },
        { title: this.$tc('field', 2), sortable: false, key: 'extra_attributes', minWidth: 200, render: this.renderExtras },
        // { title: this.$t('actions'), key: 'actions', align: 'center', fixed: 'right', width: 100, render: this.renderActions },
        {
          width: 100,
          fixed: 'right',
          key: 'actions',
          align: 'center',
          title: this.$t('actions'),
          render: (h, params) => {
            return h('actions-dropdown-component', {
              props: {
                params,
                editFn: this.editRecord,
                listFn: this.listRecord,
                payFn: this.paymentRecord,
                deleteFn: this.deleteRecord,
                record: { model: 'item', name: 'name', title: this.$tc('customer_group') },
              },
            });
          },
        },
      ],
      options: {
        orderBy: 'name',
        perPage: this.$store.state.settings.rows,
      },
    };
  },
  methods: {
    emailRecord(row) {
      console.log('sending email for ' + row.id);
    },
    paymentRecord(row) {
      console.log('Make payment for ' + row.id);
    },
    listRecord(row) {
      this.$router.push('/customer_groups/transactions/' + row.id);
    },
    renderTransactions(h, params) {
      return h('transactions-icon-component', { props: { id: params.row.id, url: '/customer_groups/transactions/' } });
    },
    renderBalance(h, params) {
      return (
        <div class="text-right">
          {params.row.journal
            ? this.$options.filters.formatJournalBalance(params.row.journal.balance.amount, this.$store.getters.settings.decimals)
            : ''}
        </div>
      );
    },
  },
};
</script>
