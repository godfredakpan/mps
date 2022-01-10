<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $tc('account', 2) }}</p>
    <router-link to="/settings/accounts/add" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('add') }} {{ $tc('account') }}
    </router-link>
    <div>
      <table-component
        url="app/accounts"
        :columns="columns"
        :options="options"
        :refresh="refresh"
        :dblClickCB="showInfo"
        :bulkDelCB="deleteRecords"
      ></table-component>
    </div>
  </Card>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
export default {
  mixins: [Table('account', 'app/accounts', 'name', 'reference')],
  data() {
    return {
      columns: [
        { title: '', width: 50, key: 'id', align: 'center', fixed: 'left', render: this.renderTransactions },
        { title: this.$t('name'), sortable: true, key: 'name', sortType: 'asc', minWidth: 200 },
        { title: this.$tc('type'), sortable: true, key: 'type', width: 150 },
        { title: this.$t('reference'), className: 'reference', sortable: true, key: 'reference', width: 150 },
        { title: this.$t('balance'), sortable: false, key: 'journal', minWidth: 120, render: this.renderBalance },
        { title: this.$t('details'), sortable: true, key: 'details', ellipsis: true, minWidth: 250 },
        { title: this.$t('offline'), sortable: true, key: 'offline', width: 80, render: this.renderOfflineIcon },
        { title: this.$t('fees'), sortable: true, key: 'fees', width: 70, render: this.renderFeesIcon },
        { title: this.$t('fixed'), sortable: true, key: 'fixed', width: 80, render: this.renderFixed },
        { title: this.$t('percentage'), sortable: true, key: 'percentage', width: 125, render: this.renderPercentage },
        { title: this.$t('apply_to'), sortable: true, key: 'apply_to', minWidth: 150, render: this.renderApplyTo },
        { title: this.$t('actions'), key: 'actions', align: 'center', fixed: 'right', width: 100, render: this.renderActions },
      ],
      options: {
        orderBy: 'name',
        perPage: this.$store.state.settings.rows,
      },
    };
  },
  methods: {
    renderTransactions(h, params) {
      return h('transactions-icon-component', { props: { id: params.row.id, url: '/settings/accounts/transactions/' } });
    },
    renderFeesIcon(h, params) {
      return this.renderBoolean(h, params, 'fees');
    },
    renderOfflineIcon(h, params) {
      return this.renderBoolean(h, params, 'offline');
    },
    renderFixed(h, params) {
      return <div class="text-right">{this.formatNumber(params.row.fixed, this.$store.getters.settings.decimals)}</div>;
    },
    renderPercentage(h, params) {
      return <div class="text-right">{this.formatNumber(params.row.percentage, this.$store.getters.settings.decimals)}%</div>;
    },
    renderApplyTo(h, params) {
      if (!params.row.apply_to) {
        return '';
      } else if (params.row.apply_to == 'in') {
        return <div>{this.$t('credit')}</div>;
      } else if (params.row.apply_to == 'out') {
        return <div>{this.$t('debit')}</div>;
      } else if (params.row.apply_to == 'both') {
        return <div>{this.$t('transaction_both')}</div>;
      }
    },
    renderBalance(h, params) {
      return (
        <div class="text-right">
          {this.formatJournalBalance(params.row.journal ? params.row.journal.balance.amount : '', this.$store.getters.settings.decimals)}
        </div>
      );
    },
  },
};
</script>
