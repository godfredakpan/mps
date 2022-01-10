<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ $tc('return_order', 2) }}</p>
      <router-link to="/return_orders/add" slot="extra">
        <Icon type="ios-grid-outline" />
        {{ $t('add') }} {{ $tc('return_order') }}
      </router-link>
      <div>
        <table-component
          url="app/return_orders"
          :columns="columns"
          :options="options"
          :dblClickCB="viewModal"
          :bulkDelCB="deleteRecords"
          :refresh="refresh"
        ></table-component>
      </div>
    </Card>
    <Modal
      width="750"
      v-model="view"
      :footer-hide="true"
      :mask-closable="false"
      class="np-header-footer"
      :title="$tc('return_order') + ' (' + return_order.reference + ')'"
    >
      <div v-if="loading" class="py16">
        <Loading />
      </div>
      <order-view-component
        :record="return_order"
        @remove="a => deleteAttachment(a, return_order)"
        :field="return_order.type == 'sale' ? 'price' : 'cost'"
        :to="return_order.type == 'sale' ? 'customer' : 'supplier'"
        :to-text="return_order.type == 'sale' ? $tc('from') : $tc('to')"
        :heading="$tc('return_order') + ' (' + $tc(return_order.type) + ')'"
      />
    </Modal>
    <Modal footer-hide v-model="paymentsModal" :width="750" @on-visible-change="paymentsModalChanged">
      <p slot="header">
        <span>
          {{ $t('list_x', { x: $tc('payment', 2) }) }}
          {{ return_order ? `(${$tc('return_order')} ${$t('reference')} ${return_order.reference})` : '' }}
        </span>
      </p>
      <Loading v-if="paymentsLoading" />
      <payments-component v-else :payments="payments"></payments-component>
    </Modal>
  </div>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
import PaymentsComponent from '../helpers/PaymentsComponent';
import OrderViewComponent from '@mpscom/helpers/OrderViewComponent';

export default {
  components: { PaymentsComponent, OrderViewComponent },
  mixins: [Table('return_order', 'app/return_orders', 'reference')],
  data() {
    return {
      view: false,
      payments: [],
      loading: false,
      return_order: {},
      paymentsModal: false,
      paymentsLoading: false,
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('date'), sortable: true, key: 'date', sortType: 'desc', width: 125, render: this.renderDate },
        { title: this.$t('reference'), className: 'reference', sortable: true, key: 'reference', minWidth: 150 },
        { title: this.$t('type'), sortable: true, key: 'type', width: 100, render: this.renderType },
        {
          maxWidth: 300,
          minWidth: 200,
          key: 'customer',
          sortable: false,
          render: this.renderPartner,
          title: this.$tc('from') + '/' + this.$tc('to'),
        },
        { title: this.$tc('register'), sortable: true, key: 'deduct_from_register', width: 100, render: this.renderIconDraft },
        // { title: this.$t('paid'), sortable: true, key: 'paid', width: 80, render: this.renderIconPaid },
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
                payFn: this.paymentRecord,
                deleteFn: this.deleteRecord,
                downloadFn: this.downloadRecord,
                listPayFn: this.ListPaymentForRecord,
                record: { model: 'return_order', name: 'reference', title: this.$tc('return_order') },
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
      if (row.id != this.return_order.id) {
        this.loading = true;
        this.$http
          .get(`app/return_orders/${row.id}`)
          .then(res => (this.return_order = res.data))
          .finally(() => (this.loading = false));
      }
    },
    emailRecord(row) {
      this.$http.post('app/return_orders/email/' + row.id, {}).then(res => {
        if (res.data.success) {
          this.$Notice.success({ title: this.$t('email_sent'), desc: this.$t('email_sent_text') });
        } else {
          this.$Notice.error({ title: this.$tc('failed'), desc: this.$t('failed_error_text') });
        }
      });
    },
    downloadRecord(row) {
      // window.open('/download/return_orders/' + row.id);
      Object.assign(document.createElement('a'), { target: '_blank', href: window.mpsURL + 'download/return_orders/' + row.id }).click();
    },
    viewRecord(row) {
      // window.open('/view/return/' + row.hash);
      Object.assign(document.createElement('a'), { target: '_blank', href: window.mpsURL + 'view/return/' + row.id }).click();
    },
    paymentRecord(row) {
      this.$router.push(`payments/add?return_id=${row.id}&customer_id=${row.customer_id}&amount=${row.grand_total}`);
    },
    ListPaymentForRecord(row) {
      this.return_order = row;
      this.paymentsModal = true;
      this.paymentsLoading = true;
      this.$http.get('app/returns/' + row.id + '/payments').then(res => {
        this.payments = res.data;
        this.paymentsLoading = false;
      });
    },
    paymentsModalChanged(opening) {
      if (!opening) {
        this.return = null;
        this.payments = [];
      }
    },
    renderPartner(h, params) {
      return <div>{params.row.type == 'sale' ? params.row.customer.name : params.row.supplier.name}</div>;
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
    renderType(h, params) {
      return params.row.type == 'sale' ? (
        <div class="ivu-tag ivu-tag-size-default ivu-tag-orange ivu-tag-checked">
          <span class="ivu-tag-text">{params.row.type}</span>
        </div>
      ) : (
        <div class="ivu-tag ivu-tag-size-default ivu-tag-blue ivu-tag-checked">
          <span class="ivu-tag-text ivu-tag-color-white">{params.row.type}</span>
        </div>
      );
    },
  },
};
</script>
