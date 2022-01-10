<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">
        {{ $t('x_report', { x: $tc('payment', 2) }) }} ({{ datetime(new Date(reportForm.start_date)) }} -
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
            <Col :sm="12" :md="6" :key="di">
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
      :title="$tc('payment') + ' (' + payment.reference + ')'"
    >
      <div v-if="loading" class="py16">
        <Loading />
      </div>
      <payment-view-component :payment="payment" @remove="a => deleteAttachment(a, payment)" />
    </Modal>
  </div>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
import ReportFormComponent from './ReportFormComponent';
import PaymentViewComponent from '@mpscom/payments/PaymentViewComponent';

export default {
  mixins: [Table('payment', 'app/payments', 'reference')],
  components: { PaymentViewComponent, ReportFormComponent },
  data() {
    return {
      url: '',
      data: {},
      payment: {},
      updated: 0,
      view: false,
      loading: false,
      showForm: false,
      reportForm: {},
      fields: ['created_at', 'reference', 'custom_fields', 'customer_id', 'supplier_id', 'user_id', 'received'],
      columns: [
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
        { title: '🔗', align: 'center', sortable: false, key: 'attachments', maxWidth: 50, minWidth: 125, render: this.renderAttachments },
        { title: this.$t('gateway'), sortable: false, key: 'gateway', width: 150, render: this.renderGateway },
        {
          key: 'for',
          maxWidth: 300,
          minWidth: 200,
          sortable: false,
          title: this.$tc('account'),
          render: this.renderAccount,
        },
        {
          key: 'for',
          maxWidth: 300,
          minWidth: 200,
          sortable: false,
          render: this.renderPayable,
          title: this.$tc('created_for'),
        },
        {
          width: 175,
          key: 'user_id',
          sortable: false,
          render: this.renderUser,
          title: this.$t('created_by'),
        },
        { title: this.$t('received'), sortable: true, key: 'received', width: 100, render: this.renderIconReceived },
        { title: this.$t('details'), sortable: false, key: 'details', ellipsis: true, ellipsis: true, minWidth: 300, maxWidth: 400 },
      ],
      options: {
        hideSearch: true,
        showSummary: true,
        summaryMethod: this.handleSummary,
        perPage: this.$store.state.settings.rows,
        orderBy: ['created_at desc'],
      },
    };
  },
  created() {
    this.$event.listen('location:changed', id => this.refresh++);
    this.fields.map(f => {
      this.reportForm[f] = '';
    });
    this.reportForm.end_date = '';
    this.reportForm.start_date = '';
    this.url = 'app/reports/payments/table?' + this.queryString(this.reportForm);
    this.$http
      .get('app/reports/payments', { params: { ...this.reportForm, created_at: '' } })
      .then(res => {
        this.reportForm.created_at = [new Date(res.data.start_date), new Date(res.data.end_date)];
        this.prepareData(res.data.payments);
        this.updated++;
      })
      .finally(() => (this.loading = false));
  },
  methods: {
    handleSubmit(form) {
      this.fields.map(f => {
        this.reportForm[f] = form[f];
      });
      if (this.reportForm.created_at && this.reportForm.created_at[0]) {
        this.reportForm.start_date = this.$moment(this.reportForm.created_at[0]).format(this.datetimeFormatString());
      }
      if (this.reportForm.created_at && this.reportForm.created_at[1]) {
        this.reportForm.end_date = this.$moment(this.reportForm.created_at[1]).format(this.datetimeFormatString());
      }
      delete this.reportForm.created_at;

      this.url = 'app/reports/payments/table?' + this.queryString(this.reportForm);
      this.refresh++;
      this.$http
        .get('app/reports/payments', { params: { ...this.reportForm, date: '' } })
        .then(res => {
          this.reportForm.created_at = [new Date(res.data.start_date), new Date(res.data.end_date)];
          this.prepareData(res.data.payments);
          this.updated++;
        })
        .finally(() => (this.loading = false));
    },
    prepareData(payments) {
      this.reportForm.end_date = this.reportForm.created_at[1];
      this.reportForm.start_date = this.reportForm.created_at[0];
      this.data = [
        { label: this.$t('total_x', { x: this.$tc('payment', 2) }), value: payments.total },
        { label: this.$t('total_x', { x: this.$t('amount') }), value: payments.amount },
        { label: this.$t('total_x', { x: this.$t('received') }), value: payments.received },
        { label: this.$t('total_x', { x: this.$t('received_amount') }), value: payments.received_amount },
        { label: this.$t('total_x', { x: this.$t('due') }), value: payments.due },
        { label: this.$t('total_x', { x: this.$t('due_amount') }), value: payments.due_amount },
        { label: this.$t('customer_amount'), value: payments.customer_amount },
        { label: this.$t('supplier_amount'), value: payments.supplier_amount },
      ];
    },
    viewModal(row) {
      this.view = true;
      if (row.id != this.payment.id) {
        this.loading = true;
        this.$http
          .get(`app/payments/${row.id}`)
          .then(res => (this.payment = res.data))
          .finally(() => (this.loading = false));
      }
    },
    renderAccount(h, params) {
      return <div>{params.row.account ? params.row.account.name : ''}</div>;
    },
    gatewayColor(gateway) {
      let colors = { cash: 'success', credit_card: 'geekblue', cheque: 'green', other: 'purple' };
      if (colors[gateway]) {
        return colors[gateway];
      }
      return 'primary';
    },
    renderGateway(h, params) {
      return (
        <div class="text-center">
          {params.row.gateway ? (
            <div class={`ivu-tag ivu-tag-${this.gatewayColor(params.row.gateway)} ivu-tag-checked`}>
              <span class="ivu-tag-text ivu-tag-color-white">{this.$t(params.row.gateway)}</span>
            </div>
          ) : (
            ''
          )}
        </div>
      );
      // return <div>{this.$t(params.row.gateway)}</div>;
    },
    renderPayable(h, params) {
      return <div>{params.row.payable.name}</div>;
    },
    renderAmount(h, params) {
      return this.renderNumber(h, params, 'amount');
    },
    renderIconReceived(h, params) {
      return this.renderBoolean(h, params, 'received');
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
      let cc = ['amount'];
      const sums = {};
      columns.forEach((column, index) => {
        const key = column.key;
        if (!cc.includes(key)) {
          sums[key] = { key, value: '' };
          return;
        }
        const values = data.map(item => (item.received == 1 ? Number(this.formatDecimal(item[key])) : 0));
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
