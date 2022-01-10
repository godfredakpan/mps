<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $tc('tax', 2) }}</p>
    <router-link to="/settings/taxes/add" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('add') }} {{ $tc('tax') }}
    </router-link>
    <div>
      <table-component
        url="app/taxes"
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
  mixins: [Table('tax', 'app/taxes', 'code', 'name')],
  data() {
    return {
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('code'), sortable: true, key: 'code', sortType: 'asc', minWidth: 120 },
        { title: this.$t('name'), sortable: true, key: 'name', minWidth: 150 },
        { title: this.$t('number'), sortable: true, key: 'number', minWidth: 150 },
        { title: this.$t('rate'), sortable: true, key: 'rate', minWidth: 120, render: this.renderRate },
        { title: this.$t('compound'), sortable: true, key: 'compound', width: 120, render: this.renderIconCompound },
        { title: this.$t('recoverable'), sortable: true, key: 'recoverable', width: 125, render: this.renderIconRecoverable },
        { title: this.$t('state'), sortable: true, key: 'state', width: 100, render: this.renderIconState },
        { title: this.$t('description'), sortable: true, key: 'description', minWidth: 200 },
        { title: this.$t('actions'), key: 'actions', align: 'center', fixed: 'right', width: 100, render: this.renderActions },
      ],
      options: {
        orderBy: 'name',
        perPage: this.$store.state.settings.rows,
      },
    };
  },
  methods: {
    renderRate(h, params) {
      return this.renderNumber(h, params, 'rate');
    },
    renderIconCompound(h, params) {
      return this.renderBoolean(h, params, 'compound');
    },
    renderIconRecoverable(h, params) {
      return this.renderBoolean(h, params, 'recoverable');
    },
    renderIconState(h, params) {
      return (
        <div style="text-align:center;">
          {params.row['state'] == 1 ? (
            params.row['same'] == 1 ? (
              this.$t('same')
            ) : (
              this.$t('different')
            )
          ) : (
            <i class="ivu-icon ivu-icon-md-close" style="font-size: 16px; color: #ed4014;" />
          )}
        </div>
      );
    },
  },
};
</script>
