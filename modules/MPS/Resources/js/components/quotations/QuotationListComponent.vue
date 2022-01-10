<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ $tc('quotation', 2) }}</p>
      <router-link to="/quotations/add" slot="extra">
        <Icon type="ios-grid-outline" />
        {{ $t('add') }} {{ $tc('quotation') }}
      </router-link>
      <div>
        <table-component
          :columns="columns"
          :options="options"
          :refresh="refresh"
          url="app/quotations"
          :dblClickCB="viewModal"
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
      :title="$tc('quotation') + ' (' + quotation.reference + ')'"
    >
      <div v-if="loading" class="py16">
        <Loading />
      </div>
      <order-view-component
        :summary="false"
        :record="quotation"
        :to-text="$tc('quote_to')"
        :heading="$tc('quotation')"
        @remove="a => deleteAttachment(a, quotation)"
      />
    </Modal>
  </div>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
import OrderViewComponent from '@mpscom/helpers/OrderViewComponent';
export default {
  components: { OrderViewComponent },
  mixins: [Table('quotation', 'app/quotations', 'reference')],
  data() {
    return {
      view: false,
      quotation: {},
      loading: false,
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('date'), sortable: true, key: 'date', sortType: 'desc', width: 100, render: this.renderDate },
        { title: this.$t('reference'), className: 'reference', sortable: true, key: 'reference', minWidth: 150 },
        {
          maxWidth: 300,
          minWidth: 200,
          key: 'customer',
          sortable: false,
          title: this.$tc('customer'),
          render: this.renderCustomer,
        },
        { title: this.$t('grand_total'), sortable: false, key: 'grand_total', width: 150, render: this.renderGrandTotal },
        { title: '🔗', align: 'center', sortable: false, key: 'attachments', maxWidth: 50, minWidth: 125, render: this.renderAttachments },
        {
          width: 175,
          sortable: true,
          sortType: 'desc',
          key: 'created_at',
          title: this.$t('created_at'),
          render: this.renderCreatedAt,
        },
        {
          width: 175,
          key: 'user_id',
          sortable: false,
          render: this.renderUser,
          title: this.$t('created_by'),
        },
        { title: this.$t('total'), sortable: false, key: 'total', width: 150, render: this.renderTotal },
        { title: this.$t('item_tax'), sortable: false, key: 'item_tax', width: 125, render: this.renderItemTax },
        { title: this.$t('order_tax'), sortable: false, key: 'order_tax', width: 125, render: this.renderOrderTax },
        { title: this.$t('discount'), sortable: false, key: 'discount', width: 125, render: this.renderDiscount },
        { title: this.$t('shipping'), sortable: false, key: 'shipping', width: 125, render: this.renderShipping },
        { title: this.$t('grand_total'), sortable: false, key: 'grand_total', width: 150, render: this.renderGrandTotal },
        { title: this.$tc('field', 2), sortable: false, key: 'extra_attributes', minWidth: 300, render: this.renderExtras },
        { title: this.$t('details'), sortable: false, key: 'details', ellipsis: true, width: 350 },
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
                viewFn: this.viewModal,
                editFn: this.editRecord,
                emailFn: this.emailRecord,
                deleteFn: this.deleteRecord,
                createSaleFn: this.createSale,
                downloadFn: this.downloadRecord,
                record: { model: 'quotation', name: 'reference', title: this.$tc('quotation') },
              },
            });
          },
        },
      ],
      options: {
        perPage: this.$store.state.settings.rows,
        orderBy: ['date desc', 'created_at desc'],
      },
    };
  },
  created() {
    this.$event.listen('location:changed', id => this.refresh++);
  },
  methods: {
    viewModal(row) {
      this.view = true;
      if (row.id != this.quotation.id) {
        this.loading = true;
        this.$http
          .get(`app/quotations/${row.id}`)
          .then(res => (this.quotation = res.data))
          .finally(() => (this.loading = false));
      }
    },
    createSale(row) {
      this.$router.push(`/sales/add?quotation_id=${row.id}`);
    },
    emailRecord(row) {
      this.$http.post('app/quotations/email/' + row.id, {}).then(res => {
        if (res.data.success) {
          this.$Notice.success({ title: this.$t('email_sent'), desc: this.$t('email_sent_text') });
        } else {
          this.$Notice.error({ title: this.$tc('failed'), desc: this.$t('failed_error_text') });
        }
      });
    },
    downloadRecord(row) {
      // window.open('/download/quotations/' + row.id);
      Object.assign(document.createElement('a'), { target: '_blank', href: window.mpsURL + 'download/quotations/' + row.id }).click();
    },
    viewRecord(row) {
      // window.open('/view/quotation/' + row.hash);
      Object.assign(document.createElement('a'), { target: '_blank', href: window.mpsURL + 'view/quotation/' + row.id }).click();
    },
    renderCustomer(h, params) {
      return <div>{params.row.customer.name}</div>;
    },
    renderTotal(h, params) {
      return this.renderNumber(h, params, 'total');
    },
    renderItemTax(h, params) {
      return this.renderNumber(h, params, 'item_tax_amount');
    },
    renderOrderTax(h, params) {
      return this.renderNumber(h, params, 'order_tax_amount');
    },
    renderDiscount(h, params) {
      return this.renderNumber(h, params, 'discount_amount');
    },
    renderShipping(h, params) {
      return this.renderNumber(h, params, 'shipping');
    },
    renderGrandTotal(h, params) {
      return this.renderNumber(h, params, 'grand_total', true);
    },
    renderIconActive(h, params) {
      return this.renderUnlessIcon(h, params, 'draft');
    },
    renderIconDraft(h, params) {
      return this.renderBoolean(h, params, 'draft');
    },
    renderIconPaid(h, params) {
      return this.renderBoolean(h, params, 'paid');
    },
  },
};
</script>
