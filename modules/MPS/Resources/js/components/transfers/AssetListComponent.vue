<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $tc('asset_transfer', 2) }}</p>
    <router-link to="/transfers/asset/add" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('add_x', { x: $tc('asset_transfer') }) }}
    </router-link>
    <div>
      <table-component
        :columns="columns"
        :options="options"
        :refresh="refresh"
        :dblClickCB="showInfo"
        url="app/transfers/asset"
        :bulkDelCB="deleteRecords"
      ></table-component>
    </div>
  </Card>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
export default {
  mixins: [Table('asset_transfers', 'app/transfers/asset', 'reference')],
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
        { title: this.$t('reference'), className: 'reference', sortable: true, key: 'reference', width: 150 },
        { title: this.$t('amount'), sortable: false, key: 'amount', width: 150, render: this.renderAmount },
        {
          key: 'to',
          maxWidth: 300,
          minWidth: 200,
          sortable: false,
          title: this.$tc('to_account'),
          render: this.renderToAccount,
        },
        {
          key: 'from',
          maxWidth: 300,
          minWidth: 200,
          sortable: false,
          render: this.renderFromAccount,
          title: this.$tc('from_account'),
        },
        {
          width: 175,
          key: 'user_id',
          sortable: false,
          render: this.renderUser,
          title: this.$t('created_by'),
        },
        { title: this.$t('details'), sortable: false, key: 'details', ellipsis: true, ellipsis: true, minWidth: 250, maxWidth: 400 },
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
                record: { model: 'asset_transfer', name: 'reference', title: this.$tc('asset_transfer') },
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
  methods: {
    editRecord(row) {
      this.$router.push({ name: 'transfers.asset.edit', params: { id: row.id } });
    },
    renderToAccount(h, params) {
      return <div>{params.row.to_account.name}</div>;
    },
    renderFromAccount(h, params) {
      return <div>{params.row.from_account.name}</div>;
    },
    renderAmount(h, params) {
      return this.renderNumber(h, params, 'amount');
    },
  },
};
</script>
