<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ $tc('purchase', 2) }}</p>
      <router-link to="/purchases/add" slot="extra">
        <Icon type="ios-grid-outline" />
        {{ $t('add') }} {{ $tc('purchase') }}
      </router-link>
      <div>
        <table-component
          url="app/purchases"
          :columns="columns"
          :options="options"
          :refresh="refresh"
          :dblClickCB="viewModal"
          :bulkDelCB="deleteRecords"
          :row-class-name="rowClassName"
        ></table-component>
      </div>
    </Card>
    <Modal
      width="750"
      v-model="view"
      :footer-hide="true"
      :mask-closable="false"
      class="np-header-footer"
      :title="$tc('purchase') + ' (' + purchase.reference + ')'"
    >
      <div v-if="loading" class="py16">
        <Loading />
      </div>
      <order-view-component
        field="cost"
        to="supplier"
        :record="purchase"
        :heading="$tc('purchase')"
        :to-text="$tc('order_to')"
        @remove="a => deleteAttachment(a, purchase)"
      />
    </Modal>
    <Modal footer-hide v-model="paymentsModal" :width="750" @on-visible-change="paymentsModalChanged">
      <p slot="header">
        <span>
          {{ $t('list_x', { x: $tc('payment', 2) }), }}
          {{ purchase ? `(${$tc('purchase')} ${$t('reference')} ${purchase.reference})` : '' }}
        </span>
      </p>
      <Loading v-if="paymentsLoading" />
      <payments-component v-else :payments="payments"></payments-component>
    </Modal>
  </div>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
import PaymentsComponent from '@mpscom/helpers/PaymentsComponent';
import OrderViewComponent from '@mpscom/helpers/OrderViewComponent';
export default {
  components: { PaymentsComponent, OrderViewComponent },
  mixins: [Table('purchase', 'app/purchases', 'reference')],
  data() {
    return {
      view: false,
      payments: [],
      purchase: {},
      loading: false,
      paymentsModal: false,
      paymentsLoading: false,
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('date'), sortable: true, key: 'date', sortType: 'desc', width: 100, render: this.renderDate },
        { title: this.$t('reference'), className: 'reference', sortable: true, key: 'reference', minWidth: 150 },
        {
          maxWidth: 300,
          minWidth: 200,
          key: 'supplier',
          sortable: false,
          title: this.$tc('supplier'),
          render: this.renderSupplier,
        },
        { title: this.$t('draft'), sortable: true, key: 'draft', width: 80, render: this.renderIconDraft },
        { title: this.$t('paid'), sortable: true, key: 'paid', width: 75, render: this.renderIconPaid },
        { title: this.$t('void'), sortable: true, key: 'void', width: 75, render: this.renderIconVoid },
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
        { title: this.$t('item_tax'), sortable: false, key: 'item_tax', width: 150, render: this.renderItemTax },
        { title: this.$t('order_tax'), sortable: false, key: 'order_tax', width: 150, render: this.renderOrderTax },
        { title: this.$t('discount'), sortable: false, key: 'discount', width: 150, render: this.renderDiscount },
        { title: this.$t('shipping'), sortable: false, key: 'shipping', width: 150, render: this.renderShipping },
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
                payFn: this.paymentRecord,
                emailFn: this.emailRecord,
                duplicateFn: this.duplicate,
                deleteFn: this.deleteRecord,
                toggleVoidFn: this.toggleVoid,
                downloadFn: this.downloadRecord,
                listPayFn: this.ListPaymentForRecord,
                record: { model: 'purchase', name: 'reference', title: this.$tc('purchase') },
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
      if (row.id != this.purchase.id) {
        this.loading = true;
        this.$http
          .get(`app/purchases/${row.id}`)
          .then(res => (this.purchase = res.data))
          .finally(() => (this.loading = false));
      }
    },
    duplicate(row) {
      this.$router.push(`/purchases/add?purchase_id=${row.id}`);
    },
    toggleVoid(row) {
      this.$http
        .delete(`app/purchases/${row.id}/toggle_void`)
        .then(res => {
          if (res.data.success) {
            this.refresh++;
            this.$Notice.success({
              title: this.$t('success'),
              desc: this.$t('order_marked_x_text', { x: res.data.void == 1 ? this.$t('void') : this.$t('valid') }),
            });
          } else {
            this.$Notice.error({ title: this.$tc('failed'), desc: this.$t('failed_error_text') });
          }
        })
        .finally(() => this.$Modal.remove());
    },
    emailRecord(row) {
      this.$http.post('app/purchases/email/' + row.id, {}).then(res => {
        if (res.data.success) {
          this.$Notice.success({ title: this.$t('email_sent'), desc: this.$t('email_sent_text') });
        } else {
          this.$Notice.error({ title: this.$tc('failed'), desc: this.$t('failed_error_text') });
        }
      });
    },
    downloadRecord(row) {
      // window.open('/download/purchases/' + row.id);
      Object.assign(document.createElement('a'), { target: '_blank', href: window.mpsURL + 'download/purchases/' + row.id }).click();
    },
    viewRecord(row) {
      // window.open('/view/purchase/' + row.hash);
      Object.assign(document.createElement('a'), { target: '_blank', href: window.mpsURL + 'view/purchase/' + row.hash }).click();
    },
    paymentRecord(row) {
      this.$router.push(`/payments/add?purchase_id=${row.id}&supplier_id=${row.supplier_id}&amount=${row.grand_total}`);
    },
    ListPaymentForRecord(row) {
      this.purchase = row;
      this.paymentsModal = true;
      this.paymentsLoading = true;
      this.$http.get('app/purchases/' + row.id + '/payments').then(res => {
        this.payments = res.data;
        this.paymentsLoading = false;
      });
    },
    paymentsModalChanged(opening) {
      if (!opening) {
        this.purchase = null;
        this.payments = [];
      }
    },
    renderSupplier(h, params) {
      return <div>{params.row.supplier.name}</div>;
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
    renderIconVoid(h, params) {
      return this.renderBoolean(h, params, 'void');
    },
    rowClassName(row, index) {
      if (row.void == 1) {
        return 'ivu-table-error-row';
      }
      return '';
    },
  },
};
</script>
