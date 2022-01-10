<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $tc('ingredient', 2) }}</p>
    <router-link to="/ingredients/add" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('add') }} {{ $tc('ingredient') }}
    </router-link>
    <div>
      <table-component
        url="app/ingredients"
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
  mixins: [Table('ingredient', 'app/ingredients', 'name')],
  data() {
    return {
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('created_at'), sortable: true, key: 'created_at', sortType: 'asc', minWidth: 150 },
        { title: this.$tc('code'), sortable: true, key: 'code', width: 150 },
        { title: this.$t('name'), sortable: true, key: 'name', minWidth: 200 },
        { title: this.$t('cost'), sortable: true, key: 'cost', maxWidth: 125, minWidth: 125, render: this.renderCost },
        // { title: this.$t('price'), sortable: true, key: 'price', maxWidth: 125, minWidth: 125, render: this.renderPrice },
        { title: this.$t('quantity'), sortable: true, key: 'quantity', maxWidth: 125, minWidth: 125, render: this.renderQuantity },
        { title: this.$t('details'), sortable: true, key: 'details', ellipsis: true, minWidth: 250 },
        { title: this.$tc('field', 2), sortable: false, key: 'extra_attributes', minWidth: 200, render: this.renderExtras },
        { title: this.$t('actions'), key: 'actions', align: 'center', fixed: 'right', width: 100, render: this.renderActions },
      ],
      options: {
        orderBy: 'created_at desc',
        perPage: this.$store.state.settings.rows,
      },
    };
  },
  methods: {
    renderCost(h, params) {
      return this.renderNumber(h, params, 'cost');
    },
    renderPrice(h, params) {
      return this.renderNumber(h, params, 'price');
    },
    renderQuantity(h, params) {
      return this.renderNumber(h, params, 'quantity');
    },
  },
};
</script>
