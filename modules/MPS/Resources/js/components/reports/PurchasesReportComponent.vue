<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">
        {{ $t('x_report', { x: $tc('purchase', 2) }) }} ({{ datetime(new Date(reportForm.start_date)) }} -
        {{ datetime(new Date(reportForm.end_date)) }})
      </p>
      <!-- <a href="#" slot="extra">
        <router-link to="/reports">
          <Icon type="md-return-left" />
          {{ $tc('report', 2) }}
        </router-link>
        <Divider type="vertical" />
        <router-link to="/reports">
          <Icon type="md-options" />
          {{ $t('form') }}
        </router-link>
      </a> -->
      <Button type="text" size="small" slot="extra" @click="showForm = !showForm">
        <Icon type="md-options" />
        {{ $t('toggle_x', { x: $tc('form', 2) }) }}
      </Button>
      <div>
        <transition
          name="fade"
          mode="out-in"
          enter-active-class="animate__animated fast animate__fadeInDown"
          leave-active-class="animate__animated faster animate__fadeOutUp"
        >
          <Card dis-hover v-if="showForm" class="mb16">
            <report-form-component @submit="handleSubmit" :fields="fields" :reportForm="reportForm" :updated="updated" />
          </Card>
        </transition>
        <Row :gutter="16" class="sparkboxes">
          <template v-for="(d, di) in data">
            <Col :sm="12" :md="6" :key="di" v-if="d.value && d.value != 0">
              <div class="box static" :class="'box' + (di + 1)">
                <div class="details">
                  <h3>{{ formatNumber(d.value) }}</h3>
                  <h4>{{ d.label }}</h4>
                </div>
                <div style="clear:both;"></div>
              </div>
            </Col>
          </template>
        </Row>
        <table-component
          :url="url"
          :stripe="false"
          :columns="columns"
          :options="options"
          :refresh="refresh"
          :dblClickCB="viewModal"
          :bulkDelCB="deleteRecords"
          :row-class-name="rowClassName"
        />
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
        :record="purchase"
        :heading="$tc('purchase')"
        :to-text="$tc('bill_to')"
        @remove="a => deleteAttachment(a, purchase)"
      />
    </Modal>
  </div>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
import ReportFormComponent from './ReportFormComponent';
import OrderViewComponent from '@mpscom/helpers/OrderViewComponent';

export default {
  components: { OrderViewComponent, ReportFormComponent },
  mixins: [Table('purchase', 'app/purchases', 'reference')],
  data() {
    return {
      url: '',
      data: {},
      purchase: {},
      updated: 0,
      view: false,
      loading: false,
      showForm: false,
      reportForm: {},
      fields: ['date', 'reference', 'custom_fields', 'supplier_id', 'user_id', 'draft', 'paid', 'void', 'item_id', 'serial'],
      columns: [
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
        { title: this.$t('item_tax'), sortable: false, key: 'item_tax_amount', width: 125, render: this.renderItemTax },
        { title: this.$t('order_tax'), sortable: false, key: 'order_tax_amount', width: 125, render: this.renderOrderTax },
        { title: this.$t('discount'), sortable: false, key: 'discount_amount', width: 125, render: this.renderDiscount },
        { title: this.$t('shipping'), sortable: false, key: 'shipping', width: 125, render: this.renderShipping },
        { title: this.$t('grand_total'), sortable: false, key: 'grand_total', width: 150, render: this.renderGrandTotal },
        { title: this.$tc('field', 2), sortable: false, key: 'extra_attributes', minWidth: 300, render: this.renderExtras },
        { title: this.$t('details'), sortable: false, key: 'details', ellipsis: true, width: 350 },
      ],
      options: {
        hideSearch: true,
        showSummary: true,
        summaryMethod: this.handleSummary,
        perPage: this.$store.state.settings.rows,
        orderBy: ['date desc', 'created_at desc'],
      },
    };
  },
  // computed: {
  //   url() {
  //     return 'app/reports/purchases/table?' + this.queryString(this.reportForm);
  //   },
  // },
  created() {
    this.$event.listen('location:changed', id => this.refresh++);
    this.fields.map(f => {
      this.reportForm[f] = '';
    });
    this.reportForm.end_date = '';
    this.reportForm.start_date = '';
    this.url = 'app/reports/purchases/table?' + this.queryString(this.reportForm);
    this.$http
      .get('app/reports/purchases', { params: { ...this.reportForm, date: '' } })
      .then(res => {
        this.reportForm.date = [new Date(res.data.start_date), new Date(res.data.end_date)];
        this.prepareData(res.data.purchases);
        this.updated++;
      })
      .finally(() => (this.loading = false));
  },
  methods: {
    handleSubmit(form) {
      this.fields.map(f => {
        this.reportForm[f] = form[f];
      });
      if (this.reportForm.date && this.reportForm.date[0]) {
        this.reportForm.start_date = this.$moment(this.reportForm.date[0]).format(this.datetimeFormatString());
      }
      if (this.reportForm.date && this.reportForm.date[1]) {
        this.reportForm.end_date = this.$moment(this.reportForm.date[1]).format(this.datetimeFormatString());
      }
      delete this.reportForm.date;

      this.url = 'app/reports/purchases/table?' + this.queryString(this.reportForm);
      this.refresh++;
      this.$http
        .get('app/reports/purchases', { params: { ...this.reportForm, date: '' } })
        .then(res => {
          this.reportForm.date = [new Date(res.data.start_date), new Date(res.data.end_date)];
          this.prepareData(res.data.purchases);
          this.updated++;
        })
        .finally(() => (this.loading = false));
    },
    prepareData(purchases) {
      this.reportForm.end_date = this.reportForm.date[1];
      this.reportForm.start_date = this.reportForm.date[0];
      this.data = [
        { label: this.$t('total_x', { x: this.$tc('purchase', 2) }), value: purchases.grand_total },
        { label: this.$t('total_x', { x: this.$tc('tax', 2) }), value: purchases.total_tax_amount },
        { label: this.$t('total_x', { x: this.$t('recoverable_tax_amount') }), value: purchases.recoverable_tax_amount },
        { label: this.$t('total_x', { x: this.$t('recoverable_tax_calculated_on') }), value: purchases.recoverable_tax_calculated_on },
        { label: this.$t('total_x', { x: this.$t('discount') }), value: purchases.discount_amount },
        { label: this.$t('total_x', { x: this.$t('shipping') }), value: purchases.shipping },
      ];
    },
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
      } else if (row.draft == 1) {
        return 'ivu-table-warning-row';
      } else if (row.paid == 1) {
        return 'ivu-table-success-row';
      }
      return '';
    },
    handleSummary({ columns, data }) {
      let cc = ['grand_total', 'total', 'item_tax_amount', 'order_tax_amount', 'discount_amount', 'shipping'];
      const sums = {};
      columns.forEach((column, index) => {
        const key = column.key;
        if (!cc.includes(key)) {
          sums[key] = { key, value: '' };
          return;
        }
        const values = data.map(item => (item.void || item.draft ? 0 : Number(this.formatDecimal(item[key]))));
        // if (!values.every(value => isNaN(value))) {
        const v = values.reduce((a, c) => (!isNaN(Number(c)) ? a + c : a), 0);
        sums[key] = { key, value: this.formatNumber(v) };
        // }
      });

      return sums;
    },
  },
};
</script>
