<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ $tc('stock_adjustment', 2) }}</p>
      <router-link to="/stock_adjustments/add" slot="extra">
        <Icon type="ios-grid-outline" />
        {{ $t('add_x', { x: $tc('stock_adjustment') }) }}
      </router-link>
      <div>
        <table-component
          :columns="columns"
          :options="options"
          :refresh="refresh"
          :dblClickCB="viewModal"
          url="app/adjustments"
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
      :title="$tc('stock_adjustment') + ' (' + adjustment.reference + ')'"
    >
      <div v-if="loading" class="py16">
        <Loading />
      </div>
      <order-view-component
        to-text=""
        :to="false"
        field="cost"
        :payment="false"
        :record="adjustment"
        :heading="$tc('stock_adjustment')"
        @remove="a => deleteAttachment(a, adjustment)"
      />
    </Modal>
  </div>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
import OrderViewComponent from '@mpscom/helpers/OrderViewComponent';
export default {
  components: { OrderViewComponent },
  mixins: [Table('stock_adjustments', 'app/adjustments', 'reference')],
  data() {
    return {
      view: false,
      loading: false,
      adjustment: { id: null, reference: null },
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('date'), sortable: true, key: 'date', sortType: 'desc', width: 100, render: this.renderDate },
        { title: this.$t('reference'), className: 'reference', sortable: true, key: 'reference', width: 150 },
        { title: this.$t('type'), sortable: true, key: 'type', width: 125, render: this.renderType },
        { title: this.$t('total'), sortable: true, key: 'grant_total', width: 150, render: this.renderGrandTotal },
        { title: '🔗', align: 'center', sortable: false, key: 'attachments', maxWidth: 50, minWidth: 125, render: this.renderAttachments },
        {
          minWidth: 175,
          key: 'user_id',
          sortable: false,
          render: this.renderUser,
          title: this.$t('created_by'),
        },
        { title: this.$t('draft'), sortable: true, key: 'draft', width: 80, render: this.renderIconDraft },
        { title: this.$t('details'), sortable: false, key: 'details', ellipsis: true, ellipsis: true, minWidth: 250, maxWidth: 600 },
        {
          width: 175,
          sortable: true,
          sortType: 'desc',
          key: 'created_at',
          title: this.$t('created_at'),
          render: this.renderCreatedAt,
        },
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
                record: { model: 'stock_adjustment', name: 'reference', title: this.$tc('stock_adjustment') },
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
      if (row.id != this.adjustment.id) {
        this.loading = true;
        this.$http
          .get(`app/adjustments/${row.id}`)
          .then(res => (this.adjustment = res.data))
          .finally(() => (this.loading = false));
      }
    },
    editRecord(row) {
      this.$router.push({ name: 'stock_adjustments.edit', params: { id: row.id } });
    },
    renderGrandTotal(h, params) {
      return this.renderNumber(h, params, 'grand_total');
    },
    renderIconDraft(h, params) {
      return this.renderBoolean(h, params, 'draft');
    },
    renderType(h, params) {
      return (
        <div class="text-center">
          <div class={`ivu-tag ivu-tag-${params.row.type == 'addition' ? 'green' : 'red'} ivu-tag-checked`}>
            <span class="ivu-tag-text ivu-tag-color-white">{this.capitalize(params.row.type)}</span>
          </div>
        </div>
      );
    },
  },
};
</script>
