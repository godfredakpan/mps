<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $t('product_expiry_alerts', { x: $tc('item', 2) }) }}</p>
    <router-link to="/items" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list_x', { x: $tc('item', 2) }) }}
    </router-link>
    <div>
      <table-component
        url="app/alerts/expiring"
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
  mixins: [Table('item', '')],
  data() {
    return {
      columns: [
        { title: this.$t('name'), sortable: true, key: 'name', minWidth: 200 },
        { title: this.$t('code'), sortable: true, key: 'code', sortType: 'asc', width: 150 },
        { title: this.$t('quantity'), sortable: false, key: 'balance', width: 125, render: this.renderBalance },
        { title: this.$t('expiry_date'), sortable: false, key: 'expiry_date', width: 150, render: this.renderExpiry },
        { title: this.$t('batch_no'), sortable: false, key: 'batch_no', width: 150 },
      ],
      options: {
        orderBy: 'expiry_date',
        perPage: this.$store.state.settings.rows,
      },
    };
  },
  methods: {
    renderCode(h, params) {
      return <div>{params.row.item.code}</div>;
    },
    renderNmae(h, params) {
      return <div>{params.row.item.name}</div>;
    },
    renderBalance(h, params) {
      return <div class="text-center">{this.$options.filters.formatNumber(params.row.balance, this.$store.getters.settings.decimals)}</div>;
    },
    renderExpiry(h, params) {
      return <div>{this.date(params.row.expiry_date)}</div>;
    },
  },
};
</script>
