<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">
        {{ $t('x_report', { x: $tc('sale', 2) }) }} ({{ datetime(new Date(reportForm.start_date)) }} -
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
    <center-loading-component v-if="loading" />
    <Modal
      width="750"
      v-model="view"
      :footer-hide="true"
      :mask-closable="false"
      class="np-header-footer"
      :title="$tc('sale') + ' (' + sale.reference + ')'"
    >
      <div v-if="loading" class="py16">
        <Loading />
      </div>
      <order-view-component :record="sale" :heading="$tc('sale')" :to-text="$tc('bill_to')" @remove="a => deleteAttachment(a, sale)" />
    </Modal>
  </div>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
import ReportFormComponent from './ReportFormComponent';
import OrderViewComponent from '@mpscom/helpers/OrderViewComponent';

export default {
  mixins: [Table('sale', 'app/sales', 'reference')],
  components: { OrderViewComponent, ReportFormComponent },
  data() {
    return {
      url: '',
      data: {},
      sale: {},
      updated: 0,
      view: false,
      printData: {},
      loading: false,
      showForm: false,
      printing: false,
      printModal: false,
      reportForm: {},
      fields: ['date', 'reference', 'custom_fields', 'customer_id', 'user_id', 'draft', 'paid', 'void', 'pos', 'item_id', 'serial'],
      columns: [
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
  //     return 'app/reports/sales/table?' + this.queryString(this.reportForm);
  //   },
  // },
  created() {
    this.$event.listen('location:changed', id => this.refresh++);
    this.fields.map(f => {
      this.reportForm[f] = '';
    });
    this.reportForm.end_date = '';
    this.reportForm.start_date = '';
    this.url = 'app/reports/sales/table?' + this.queryString(this.reportForm);
    this.$http
      .get('app/reports/sales', { params: { ...this.reportForm, date: '' } })
      .then(res => {
        this.reportForm.date = [new Date(res.data.start_date), new Date(res.data.end_date)];
        this.prepareData(res.data.sales);
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

      this.url = 'app/reports/sales/table?' + this.queryString(this.reportForm);
      this.refresh++;
      this.$http
        .get('app/reports/sales', { params: { ...this.reportForm, date: '' } })
        .then(res => {
          this.reportForm.date = [new Date(res.data.start_date), new Date(res.data.end_date)];
          this.prepareData(res.data.sales);
          this.updated++;
        })
        .finally(() => (this.loading = false));
    },
    prepareData(sales) {
      this.reportForm.end_date = this.reportForm.date[1];
      this.reportForm.start_date = this.reportForm.date[0];
      this.data = [
        { label: this.$t('total_x', { x: this.$tc('sale', 2) }), value: sales.grand_total },
        { label: this.$t('total_x', { x: this.$tc('tax', 2) }), value: sales.total_tax_amount },
        { label: this.$t('total_x', { x: this.$t('recoverable_tax_amount') }), value: sales.recoverable_tax_amount },
        { label: this.$t('total_x', { x: this.$t('recoverable_tax_calculated_on') }), value: sales.recoverable_tax_calculated_on },
        { label: this.$t('total_x', { x: this.$t('discount') }), value: sales.discount_amount },
        { label: this.$t('total_x', { x: this.$t('shipping') }), value: sales.shipping },
      ];
    },
    showReceipt() {
      this.printData.type = 'receipt';
      this.printData.order = this.sale;
      this.printData.order.items = this.printData.order.items.map(item => {
        item.selected = { portions: [], variations: [], modifiers: [] };
        item.selected.portions = item.portions;
        item.selected.variations = item.variations;
        item.selected.modifiers = item.modifier_options;
        return item;
      });
      this.$Modal.confirm({
        width: 365,
        closable: true,
        scrollable: true,
        render: h => {
          return h('print-component', { props: { print: this.printData, vm: this } });
        },
        okText: this.$t('print'),
        onOk: () => {
          window.print();
        },
        onCancel: () => {
          setTimeout(() => (this.printData = {}), 200);
        },
      });
    },
    viewModal(row) {
      if (row.id != this.sale.id) {
        this.loading = true;
        this.$http
          .get(`app/sales/${row.id}`)
          .then(res => {
            this.sale = res.data;
            if (this.sale.pos == 1) {
              this.showReceipt();
            } else {
              this.view = true;
            }
          })
          .finally(() => (this.loading = false));
      } else {
        if (this.sale.pos == 1) {
          this.showReceipt();
        } else {
          this.view = true;
        }
      }
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
        if (!values.every(value => isNaN(value))) {
          const v = values.reduce((a, c) => (!isNaN(Number(c)) ? a + c : a), 0);
          sums[key] = { key, value: this.formatNumber(v) };
        }
      });

      return sums;
    },
  },
};
</script>
