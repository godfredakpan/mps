<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ $tc('delivery', 2) }}</p>
      <router-link to="/deliveries/add" slot="extra">
        <Icon type="ios-grid-outline" />
        {{ $t('add_x', { x: $tc('delivery') }) }}
      </router-link>
      <div>
        <table-component
          :columns="columns"
          :options="options"
          :refresh="refresh"
          :dblClickCB="viewModal"
          url="app/deliveries"
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
      :title="$tc('delivery') + ' (' + delivery.reference + ')'"
    >
      <div v-if="loading" class="py16">
        <Loading />
      </div>
      <order-view-component
        to-text=""
        :to="false"
        field="cost"
        :extra="false"
        :payment="false"
        :only-qty="true"
        :delivery="true"
        :record="delivery"
        :heading="$tc('delivery')"
        @remove="a => deleteAttachment(a, delivery)"
      />
    </Modal>
  </div>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
import OrderViewComponent from '@mpscom/helpers/OrderViewComponent';
export default {
  components: { OrderViewComponent },
  mixins: [Table('delivery', 'app/deliveries', 'reference')],
  data() {
    return {
      view: false,
      loading: false,
      delivery: { id: null, reference: null },
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
          width: 175,
          key: 'user_id',
          sortable: false,
          render: this.renderUser,
          title: this.$t('created_by'),
        },
        { title: '🔗', align: 'center', sortable: false, key: 'attachments', maxWidth: 50, minWidth: 125, render: this.renderAttachments },
        {
          width: 175,
          sortable: false,
          key: 'delivered_by',
          render: this.renderDeliveredBy,
          title: this.$t('delivered_by'),
        },
        {
          width: 175,
          sortable: false,
          key: 'delivered_at',
          render: this.renderDeliveredAt,
          title: this.$t('delivered_at'),
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
                record: { model: 'delivery', name: 'reference', title: this.$tc('delivery') },
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
      if (row.id != this.delivery.id) {
        this.loading = true;
        this.$http
          .get(`app/deliveries/${row.id}`)
          .then(res => (this.delivery = res.data))
          .finally(() => (this.loading = false));
      }
    },
    editRecord(row) {
      this.$router.push({ name: 'deliveries.edit', params: { id: row.id } });
    },
    renderDeliveredBy(h, params) {
      return <div>{params.row.delivered_by && params.row.delivered_by.name ? params.row.delivered_by.name : params.row.delivered_by}</div>;
    },
    renderDeliveredAt(h, params) {
      return (
        <div>
          {params.row.delivered_at
            ? this.$options.filters.formatDate(params.row.delivered_at, this.$store.state.settings.dateformat + ' HH:mm A')
            : ''}
        </div>
      );
    },
  },
};
</script>
