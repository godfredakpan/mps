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
  mixins: [Table('customer_group', 'app/customer_groups', 'name', 'code')],
  data() {
    return {
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('name'), sortable: true, key: 'name', sortType: 'asc', minWidth: 150 },
        { title: this.$t('code'), sortable: true, key: 'code', minWidth: 150 },
        { title: this.$tc('discount'), sortable: true, key: 'discount', minWidth: 125, render: this.renderDiscount },
        { title: this.$t('actions'), key: 'actions', align: 'center', fixed: 'right', width: 100, render: this.renderActions },
      ],
      options: {
        orderBy: 'name',
        perPage: this.$store.state.settings.rows,
      },
    };
  },
  methods: {
    renderDiscount(h, params) {
      return (
        <div class="text-right">{this.$options.filters.formatDecimal(params.row.discount, this.$store.getters.settings.decimals)}%</div>
      );
    },
  },
};
</script>
