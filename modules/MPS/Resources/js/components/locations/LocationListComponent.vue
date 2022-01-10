<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $tc('location') }}</p>
    <router-link to="/settings/locations/add" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('add_x', { x: $tc('location') }) }}
    </router-link>
    <div>
      <table-component
        url="app/locations"
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
  mixins: [Table('location', 'app/locations', 'name')],
  data() {
    return {
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('name'), sortable: true, key: 'name', sortType: 'asc', minWidth: 200 },
        { title: this.$t('phone'), sortable: true, key: 'phone', width: 150 },
        { title: this.$t('email'), sortable: true, key: 'email', minWidth: 200 },
        { title: this.$tc('account'), sortable: false, key: 'account', minWidth: 175, render: this.renderAccount },
        { title: this.$t('address'), sortable: true, key: 'address', minWidth: 200, render: this.renderAddress },
        { title: this.$t('state'), sortable: true, key: 'state_name', maxWidth: 150, minWidth: 150 },
        { title: this.$t('country'), sortable: true, key: 'country_name', maxWidth: 150, minWidth: 150 },
        { title: this.$t('color'), sortable: true, key: 'color', width: 100, render: this.renderColor },
        { title: this.$t('receipt_header'), sortable: true, key: 'header', minWidth: 200 },
        { title: this.$t('receipt_footer'), sortable: true, key: 'footer', minWidth: 200 },
        { title: this.$tc('field', 2), sortable: false, key: 'extra_attributes', minWidth: 200, render: this.renderExtras },
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
                viewFn: this.viewModal,
                editFn: this.editRecord,
                deleteFn: this.deleteRecord,
                record: { model: 'location', name: 'name', title: this.$tc('location') },
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
  methods: {
    renderAccount(h, params) {
      return <div>{params.row.account.name}</div>;
    },
    renderColor(h, params) {
      let styles = { color: params.row.color, background: params.row.color, borderRadius: '4px' };
      return <div style={styles}>{params.row.color}</div>;
    },
  },
};
</script>
