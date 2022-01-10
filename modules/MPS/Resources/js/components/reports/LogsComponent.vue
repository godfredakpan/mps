<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $tc('logs') }}</p>
    <div>
      <table-component
        url="app/logs"
        :columns="columns"
        :options="options"
        :refresh="refresh"
        :dblClickCB="showInfo"
        :row-class-name="rowClassName"
      />
    </div>
  </Card>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
export default {
  mixins: [Table('log', 'app/logs')],
  data() {
    return {
      columns: [
        // { type: 'selection', width: 50, align: 'center' },
        {
          width: 50,
          fixed: 'left',
          type: 'expand',
          render: (h, params) => {
            return h('pre', { style: { margin: '0 10px' } }, JSON.stringify(params.row.properties, null, '  '));
          },
        },
        { title: this.$tc('created_at'), sortable: true, key: 'created_at', width: 175, sortType: 'desc', render: this.renderCreatedAt },
        { title: this.$tc('log_name'), sortable: true, key: 'log_name', width: 125 },
        { title: this.$tc('description'), sortable: true, key: 'description', minWidth: 250 },
        { title: this.$tc('by'), sortable: false, key: 'user', width: 200, render: this.renderUser },
        // { title: this.$tc('subject_type'), sortable: true, key: 'subject_type', minWidth: 250 },
        // { title: this.$tc('subject_id'), sortable: true, key: 'subject_id', minWidth: 275 },
      ],
      options: {
        orderBy: 'created_at desc',
        perPage: this.$store.state.settings.rows,
      },
    };
  },
  methods: {
    rowClassName(row, index) {
      if (row.description.includes('deleted')) {
        return 'ivu-table-error-row';
      } else if (row.description.includes('updated')) {
        return 'ivu-table-warning-row';
      } else if (row.description.includes('created')) {
        return 'ivu-table-success-row';
      }
      return '';
    },
  },
};
</script>
