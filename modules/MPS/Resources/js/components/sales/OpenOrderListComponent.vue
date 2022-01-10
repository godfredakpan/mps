<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $tc('order', 2) }}</p>
    <router-link to="/pos" slot="extra">
      <Icon type="ios-apps" />
      {{ $t('pos') }}
    </router-link>
    <div>
      <table-component
        url="app/orders"
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
  mixins: [Table('order', 'app/orders')],
  data() {
    return {
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        {
          width: 175,
          sortable: true,
          sortType: 'desc',
          key: 'created_at',
          title: this.$t('created_at'),
          render: this.renderCreatedAt,
        },
        { title: this.$t('ref_tab'), sortable: true, key: 'oId', width: 125 },
        { title: this.$t('date'), sortable: true, key: 'date', width: 125, render: this.renderDate },
        { title: this.$t('reference'), className: 'reference', sortable: true, key: 'reference', width: 150 },
        {
          title: this.$tc('customer'),
          sortable: false,
          key: 'customer',
          maxWidth: 250,
          minWidth: 150,
          render: this.renderCustomer,
        },
        {
          title: this.$tc('location'),
          sortable: false,
          key: 'location',
          maxWidth: 300,
          minWidth: 150,
          render: this.renderLocation,
        },
        {
          width: 175,
          key: 'user_id',
          sortable: false,
          render: this.renderUser,
          title: this.$t('created_by'),
        },
        { title: this.$t('total_items'), sortable: false, key: 'total_items', width: 125, render: this.renderTotalItems },
        { title: this.$t('total_quantity'), sortable: false, key: 'total_quantity', width: 125, render: this.renderTotalQuantity },
        { title: this.$t('grand_total'), sortable: false, key: 'grand_total', width: 150, render: this.renderGrandTotal },
        { title: this.$t('details'), sortable: false, key: 'details', ellipsis: true, minWidth: 200, maxWidth: 300 },
        // { title: this.$t('actions'), key: 'actions', align: 'center', fixed: 'right', width: 100, render: this.renderActions },
        {
          width: 75,
          fixed: 'right',
          key: 'actions',
          align: 'center',
          title: this.$t('actions'),
          render: (h, params) => {
            return h('actions-component', {
              props: {
                params,
                // editFn: this.editRecord,
                deleteFn: this.deleteRecord,
                record: { model: 'order', name: 'oId' },
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
  mounted() {
    if (!this.$store.getters.superAdmin) {
      this.columns = this.columns.filter(c => c.key != 'actions');
    }
  },
  methods: {
    renderCustomer(h, params) {
      return <div>{params.row.customer.name}</div>;
    },
    renderLocation(h, params) {
      return <div>{params.row.location.name}</div>;
    },
    renderTotalItems(h, params) {
      return this.renderNumber(h, params, 'total_items');
    },
    renderTotalQuantity(h, params) {
      return this.renderNumber(h, params, 'total_quantity');
    },
    renderDiscount(h, params) {
      return this.renderNumber(h, params, 'discount_amount');
    },
    renderGrandTotal(h, params) {
      return this.renderNumber(h, params, 'grand_total', true);
    },
  },
};
</script>
