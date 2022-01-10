<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">
        {{ $t('x_report', { x: $tc('income', 2) }) }} ({{ datetime(new Date(reportForm.start_date)) }} -
        {{ datetime(new Date(reportForm.end_date)) }})
      </p>
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
              <div class="box static" :class="'box' + (di + 3)">
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
        />
      </div>
    </Card>
    <Modal
      width="750"
      v-model="view"
      :footer-hide="true"
      :mask-closable="false"
      class="np-header-footer"
      :title="$tc('income') + ' (' + income.reference + ')'"
    >
      <div v-if="loading" class="py16">
        <Loading />
      </div>
      <income-view-component :income="income" @remove="a => deleteAttachment(a, income)" />
    </Modal>
  </div>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
import ReportFormComponent from './ReportFormComponent';
import IncomeViewComponent from '@mpscom/incomes/IncomeViewComponent';

export default {
  components: { IncomeViewComponent, ReportFormComponent },
  mixins: [Table('income', 'app/incomes', 'reference')],
  data() {
    return {
      url: '',
      data: {},
      income: {},
      updated: 0,
      view: false,
      loading: false,
      showForm: false,
      reportForm: {},
      fields: ['date', 'reference', 'custom_fields', 'account_id', 'category_id', 'user_id', 'details', 'title'],
      columns: [
        { title: this.$t('date'), sortable: true, key: 'date', sortType: 'desc', minWidth: 125, render: this.renderDate },
        { title: this.$t('title'), sortable: true, key: 'title', minWidth: 200 },
        { title: this.$t('reference'), className: 'reference', sortable: true, key: 'reference', width: 150 },
        { title: this.$t('amount'), sortable: true, key: 'amount', maxWidth: 125, minWidth: 125, render: this.renderAmount },
        { title: '🔗', align: 'center', sortable: false, key: 'attachments', maxWidth: 50, minWidth: 125, render: this.renderAttachments },
        { title: this.$tc('category'), sortable: false, key: 'categories', minWidth: 175, render: this.renderCategory },
        { title: this.$tc('account'), sortable: false, key: 'account', minWidth: 175, render: this.renderAccount },
        { title: this.$tc('created_by'), sortable: false, key: 'user', minWidth: 175, render: this.renderUser },
        { title: this.$t('details'), sortable: true, key: 'details', ellipsis: true, minWidth: 300 },
        { title: this.$tc('field', 2), sortable: false, key: 'extra_attributes', minWidth: 250, render: this.renderExtras },
        { title: this.$t('created_at'), sortable: true, key: 'created_at', sortType: 'desc', minWidth: 175, render: this.renderDateTime },
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
  //     return 'app/reports/incomes/table?' + this.queryString(this.reportForm);
  //   },
  // },
  created() {
    this.$event.listen('location:changed', id => this.refresh++);
    this.fields.map(f => {
      this.reportForm[f] = '';
    });
    this.reportForm.end_date = '';
    this.reportForm.start_date = '';
    this.url = 'app/reports/incomes/table?' + this.queryString(this.reportForm);
    this.$http
      .get('app/reports/incomes', { params: { ...this.reportForm, date: '' } })
      .then(res => {
        this.reportForm.date = [new Date(res.data.start_date), new Date(res.data.end_date)];
        this.prepareData(res.data.incomes);
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

      this.url = 'app/reports/incomes/table?' + this.queryString(this.reportForm);
      this.refresh++;
      this.$http
        .get('app/reports/incomes', { params: { ...this.reportForm, date: '' } })
        .then(res => {
          this.reportForm.date = [new Date(res.data.start_date), new Date(res.data.end_date)];
          this.prepareData(res.data.incomes);
          this.updated++;
        })
        .finally(() => (this.loading = false));
    },
    prepareData(incomes) {
      this.reportForm.end_date = this.reportForm.date[1];
      this.reportForm.start_date = this.reportForm.date[0];
      this.data = [
        { label: this.$t('total_x', { x: this.$tc('income', 2) }), value: incomes.count },
        { label: this.$t('total_x', { x: this.$tc('amount', 2) }), value: incomes.amount },
      ];
    },
    viewModal(row) {
      this.view = true;
      if (row.id != this.income.id) {
        this.loading = true;
        this.$http
          .get(`app/incomes/${row.id}`)
          .then(res => (this.income = res.data))
          .finally(() => (this.loading = false));
      }
    },
    renderAmount(h, params) {
      return this.renderNumber(h, params, 'amount');
    },
    renderAccount(h, params) {
      return <div>{params.row.account.name}</div>;
    },
    renderLocation(h, params) {
      return <div>{params.row.location.name}</div>;
    },
    renderCategory(h, params) {
      return <div>{params.row.categories.map(c => c.name).join('')}</div>;
    },
    renderUser(h, params) {
      return <div>{params.row.user ? params.row.user.name : ''}</div>;
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
