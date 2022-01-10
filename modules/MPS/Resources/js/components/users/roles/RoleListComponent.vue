<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $tc('role', 2) }}</p>
    <router-link to="/users/roles/add" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('add') }} {{ $tc('role') }}
    </router-link>
    <div>
      <table-component
        url="app/roles"
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
  mixins: [Table('role', 'app/roles', 'name')],
  data() {
    return {
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('created_at'), sortable: true, key: 'created_at', width: 175, render: this.renderCreatedAt },
        { title: this.$t('name'), sortable: true, key: 'name', minWidth: 200 },
        {
          width: 100,
          fixed: 'right',
          key: 'actions',
          align: 'center',
          title: this.$t('actions'),
          render: (h, params) => {
            return h('actions-component', {
              props: {
                params,
                editFn: this.editRecord,
                deleteFn: this.deleteRecord,
                record: { model: 'role', name: 'name' },
                trailsFn: id => {
                  this.$router.push(`/users/roles/${id}/permissions`);
                },
              },
            });
          },
        },
      ],
      options: {
        orderBy: 'name',
        perPage: this.$store.state.settings.rows,
      },
    };
  },
  methods: {},
};
</script>
