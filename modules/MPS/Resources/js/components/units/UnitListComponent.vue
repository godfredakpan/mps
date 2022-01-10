<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $tc('uom', 2) }}</p>
    <router-link to="/settings/units/add" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('add') }} {{ $tc('unit') }}
    </router-link>
    <div>
      <table-component
        url="app/units"
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
  mixins: [Table('unit', 'app/units', 'name', 'code')],
  data() {
    return {
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('name'), sortable: true, sortType: 'asc', key: 'name', minWidth: 200 },
        { title: this.$tc('code'), sortable: true, key: 'code', width: 150 },
        { title: this.$t('base_unit'), sortable: true, key: 'base_unit', maxWidth: 150, minWidth: 200, render: this.renderBase },
        { title: this.$t('operator'), sortable: false, key: 'operator', width: 125, render: this.renderOperator },
        { title: this.$t('operation_value'), sortable: false, key: 'operation_value', width: 175 },
        { title: this.$t('created_at'), sortable: true, key: 'created_at', minWidth: 175, maxWidth: 200, render: this.renderCreatedAt },
        // { title: this.$t('details'), sortable: true, key: 'details', ellipsis: true, minWidth: 250 },
        { title: this.$t('actions'), key: 'actions', align: 'center', fixed: 'right', width: 100, render: this.renderActions },
      ],
      options: {
        orderBy: 'name',
        perPage: this.$store.state.settings.rows,
      },
    };
  },
  methods: {
    renderBase(h, params) {
      return <div>{params.row.base_unit ? params.row.base_unit.name : ''}</div>;
    },
    renderOperator(h, params) {
      return <div>{params.row.operator ? this.$t(params.row.operator) : ''}</div>;
    },
  },
};
</script>
