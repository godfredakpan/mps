<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $t('product_quantity_alerts', { x: $tc('item', 2) }) }}</p>
    <router-link to="/items" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list_x', { x: $tc('item', 2) }) }}
    </router-link>
    <div>
      <table-component
        url="app/alerts/quantity"
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
        { title: this.$t('total_quantity'), sortable: false, key: 'location_stock', width: 125, render: this.renderAllStock },
        { title: this.$t('location_quantity'), sortable: false, key: 'stock', width: 150, render: this.renderStock },
        { title: this.$t('alert_quantity'), sortable: false, key: 'alert_quantity', width: 150, render: this.renderAlertQuantity },
        { title: this.$t('rack_location'), sortable: false, key: 'location', width: 125, render: this.renderRack },
      ],
      options: {
        orderBy: 'code',
        perPage: this.$store.state.settings.rows,
      },
    };
  },
  created() {
    this.$event.listen('location:changed', id => this.refresh++);
  },
  methods: {
    renderAllStock(h, params) {
      return (
        <div class="text-center">
          {this.$options.filters.formatNumber(
            params.row.stock.reduce((a, s) => a + parseFloat(s.quantity), 0),
            this.$store.getters.settings.decimals
          )}
        </div>
      );
    },
    renderStock(h, params) {
      return (
        <div class="text-center">
          {params.row.location_stock.length > 0 && params.row.location_stock[0].quantity
            ? this.$options.filters.formatNumber(params.row.location_stock[0].quantity, this.$store.getters.settings.decimals)
            : 0}
        </div>
      );
    },
    renderAlertQuantity(h, params) {
      return (
        <div class="text-center">{this.$options.filters.formatNumber(params.alert_quantity, this.$store.getters.settings.decimals)}</div>
      );
    },
    renderRack(h, params) {
      return (
        <div>
          {params.row.location_stock.length > 0 && params.row.location_stock[0].rack
            ? params.row.location_stock[0].rack
            : params.row.location}
        </div>
      );
    },
  },
};
</script>
