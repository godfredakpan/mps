<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $tc('hall', 2) }}</p>
    <router-link to="/settings/halls/add" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('add') }} {{ $tc('hall') }}
    </router-link>
    <div>
      <table-component
        url="app/halls"
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
  mixins: [Table('hall', 'app/halls', 'title')],
  data() {
    return {
      menus: [],
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('created_at'), sortable: true, key: 'created_at', sortType: 'asc', minWidth: 150, render: this.renderDateTime },
        { title: this.$tc('code'), sortable: true, key: 'code', width: 150 },
        { title: this.$t('title'), sortable: true, key: 'title', sortType: 'asc', minWidth: 200 },
        { title: this.$tc('location', 2), sortable: false, key: 'locations', minWidth: 200, render: this.renderLocation },
        { title: this.$t('details'), sortable: true, key: 'details', ellipsis: true, minWidth: 250 },
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
                menus: this.menus,
                record: { model: 'hall', name: 'title', title: this.$tc('hall') },
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
  created() {
    this.menus = [
      {
        Fn: this.editRecord,
        iconType: 'md-create',
        title: this.$t('edit_x', { x: this.$tc('hall') }),
      },
      {
        divided: true,
        Fn: this.listTables,
        iconType: 'ios-grid',
        title: this.$t('list_x', { x: this.$tc('table', 2) }),
      },
      {
        Fn: this.addTable,
        iconType: 'ios-grid-outline',
        title: this.$t('add_x', { x: this.$tc('table') }),
      },
      {
        confirm: true,
        divided: true,
        iconType: 'md-trash',
        Fn: this.deleteRecord,
        title: this.$t('delete_x', { x: this.$tc('hall') }),
      },
    ];
  },
  methods: {
    addTable(row) {
      this.$router.push({ name: 'tables.add', params: { hall_id: row.id } });
    },
    listTables(row) {
      this.$router.push({ name: 'tables.list', params: { hall_id: row.id } });
    },
    renderLocation(h, params) {
      return <div>{params.row.location ? params.row.location.name : ''}</div>;
    },
  },
};
</script>
