<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ $tc('stock_transfer', 2) }}</p>
      <router-link to="/transfers/stock/add" slot="extra">
        <Icon type="ios-grid-outline" />
        {{ $t('add_x', { x: $tc('stock_transfer') }) }}
      </router-link>
      <div>
        <table-component
          :columns="columns"
          :options="options"
          :refresh="refresh"
          :dblClickCB="viewModal"
          url="app/transfers/stock"
          :bulkDelCB="deleteRecords"
        ></table-component>
      </div>
    </Card>
    <Modal
      width="750"
      v-model="view"
      :footer-hide="true"
      :mask-closable="false"
      class="np-header-footer"
      :title="$tc('stock_transfer') + ' (' + stock.reference + ')'"
    >
      <div v-if="loading" class="py16">
        <Loading />
      </div>
      <order-view-component
        field="cost"
        :extra="false"
        :record="stock"
        :payment="false"
        :only-qty="true"
        to="to_location"
        :to-text="$t('to')"
        from="from_location"
        :heading="$tc('stock_transfer')"
        @remove="a => deleteAttachment(a, stock)"
      />
    </Modal>
  </div>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
import OrderViewComponent from '@mpscom/helpers/OrderViewComponent';
export default {
  components: { OrderViewComponent },
  mixins: [Table('stock_transfers', 'app/transfers/stock', 'reference')],
  data() {
    return {
      view: false,
      loading: false,
      stock: { id: null, reference: null },
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
        {
          key: 'to',
          maxWidth: 300,
          minWidth: 200,
          sortable: false,
          title: this.$tc('to_location'),
          render: this.renderToAccount,
        },
        {
          key: 'from',
          maxWidth: 300,
          minWidth: 200,
          sortable: false,
          render: this.renderFromAccount,
          title: this.$tc('from_location'),
        },
        { title: '🔗', align: 'center', sortable: false, key: 'attachments', maxWidth: 50, minWidth: 125, render: this.renderAttachments },
        {
          width: 175,
          key: 'user_id',
          sortable: false,
          render: this.renderUser,
          title: this.$t('created_by'),
        },
        { title: this.$t('details'), sortable: false, key: 'details', ellipsis: true, ellipsis: true, minWidth: 250, maxWidth: 600 },
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
                record: { model: 'stock_transfer', name: 'reference', title: this.$tc('stock_transfer') },
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
    viewModal(row) {
      this.view = true;
      if (row.id != this.stock.id) {
        this.loading = true;
        this.$http
          .get(`app/transfers/stock/${row.id}`)
          .then(res => (this.stock = res.data))
          .finally(() => (this.loading = false));
      }
    },
    editRecord(row) {
      this.$router.push({ name: 'transfers.stock.edit', params: { id: row.id } });
    },
    renderToAccount(h, params) {
      return <div>{params.row.to_location.name}</div>;
    },
    renderFromAccount(h, params) {
      return <div>{params.row.from_location.name}</div>;
    },
  },
};
</script>
