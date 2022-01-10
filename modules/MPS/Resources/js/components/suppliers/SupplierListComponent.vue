<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ $tc('supplier', 2) }}</p>
      <router-link to="/suppliers/add" slot="extra">
        <Icon type="ios-grid-outline" />
        {{ $t('add') }} {{ $tc('supplier') }}
      </router-link>
      <div>
        <table-component
          :url="url"
          :columns="columns"
          :options="options"
          :dblClickCB="showInfo"
          :bulkDelCB="deleteRecords"
          :refresh="refresh"
        ></table-component>
      </div>
    </Card>
    <pre style="font-size:10px">{{ $t('supplier_balance_tip') }}</pre>
  </div>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
export default {
  mixins: [Table('supplier', 'app/suppliers', 'name', 'company')],
  data() {
    return {
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('name'), sortable: true, key: 'name', sortType: 'asc', minWidth: 200 },
        { title: this.$tc('company'), sortable: true, key: 'company', width: 175 },
        { title: this.$t('phone'), sortable: true, key: 'phone', maxWidth: 125, minWidth: 125 },
        { title: this.$t('email'), sortable: true, key: 'email', minWidth: 200 },
        { title: this.$t('balance'), sortable: false, key: 'journal', width: 150, render: this.renderBalance },
        // { title: '', width: 50, key: 'id', align: 'center', render: this.renderTransactions },
        { title: this.$t('address'), sortable: true, key: 'address', minWidth: 200, render: this.renderAddress },
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
                record: { model: 'item', name: 'name', title: this.$tc('customer') },
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
  computed: {
    url() {
      return 'app/suppliers' + (this.$route.query.due_limit ? '?due_limit=' + parseInt(this.$route.query.due_limit) : '');
    },
  },
  watch: {
    '$route.query.due_limit': function() {
      this.refresh++;
    },
  },
  methods: {
    paymentRecord(row) {
      this.$router.push(
        `/payments/add?supplier_id=${row.id}${row.journal.balance.amount < 0 ? '&amount=' + (0 - row.journal.balance.amount / 100) : ''}`
      );
    },
    listRecord(row) {
      this.$router.push('/suppliers/transactions/' + row.id);
    },
    renderTransactions(h, params) {
      return h('transactions-icon-component', { props: { id: params.row.id, url: '/suppliers/transactions/' } });
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
