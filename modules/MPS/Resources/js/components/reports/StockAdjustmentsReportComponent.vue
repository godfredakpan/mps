<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">
        {{ $t('x_report', { x: $tc('stock_adjustment', 2) }) }} ({{ datetime(new Date(reportForm.start_date)) }} -
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
        />
      </div>
    </Card>
    <Modal
      width="750"
      v-model="view"
      :footer-hide="true"
      :mask-closable="false"
      class="np-header-footer"
      :title="$tc('stock_adjustment') + ' (' + stock_adjustment.reference + ')'"
    >
      <div v-if="loading" class="py16">
        <Loading />
      </div>
      <order-view-component
        to-text=""
        :to="false"
        field="cost"
        :payment="false"
        :record="stock_adjustment"
        :heading="$tc('stock_adjustment')"
        @remove="a => deleteAttachment(a, stock_adjustment)"
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
  mixins: [Table('stock_adjustment', 'app/stock_adjustments', 'reference')],
  data() {
    return {
      url: '',
      data: {},
      updated: 0,
      view: false,
      loading: false,
      showForm: false,
      reportForm: {},
      stock_adjustment: {},
      fields: ['date', 'reference', 'custom_fields', 'item_id', 'serial', 'user_id', 'details', 'draft'],
      columns: [
        { title: this.$t('date'), sortable: true, key: 'date', sortType: 'desc', width: 100, render: this.renderDate },
        { title: this.$t('reference'), className: 'reference', sortable: true, key: 'reference', width: 150 },
        { title: this.$t('type'), sortable: true, key: 'type', width: 125, render: this.renderType },
        { title: this.$t('total'), sortable: true, key: 'grand_total', width: 150, render: this.renderGrandTotal },
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
  //     return 'app/reports/adjustments/table?' + this.queryString(this.reportForm);
  //   },
  // },
  created() {
    this.$event.listen('location:changed', id => this.refresh++);
    this.fields.map(f => {
      this.reportForm[f] = '';
    });
    this.reportForm.end_date = '';
    this.reportForm.start_date = '';
    this.url = 'app/reports/adjustments/table?' + this.queryString(this.reportForm);
    this.$http
      .get('app/reports/adjustments', { params: { ...this.reportForm, date: '' } })
      .then(res => {
        this.reportForm.date = [new Date(res.data.start_date), new Date(res.data.end_date)];
        this.prepareData(res.data.stock_adjustments);
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

      this.url = 'app/reports/adjustments/table?' + this.queryString(this.reportForm);
      this.refresh++;
      this.$http
        .get('app/reports/adjustments', { params: { ...this.reportForm, date: '' } })
        .then(res => {
          this.reportForm.date = [new Date(res.data.start_date), new Date(res.data.end_date)];
          this.prepareData(res.data.stock_adjustments);
          this.updated++;
        })
        .finally(() => (this.loading = false));
    },
    prepareData(stock_adjustments) {
      this.reportForm.end_date = this.reportForm.date[1];
      this.reportForm.start_date = this.reportForm.date[0];
      this.data = [
        { label: this.$t('total_x', { x: this.$tc('stock_adjustment', 2) }), value: stock_adjustments.grand_total },
        { label: this.$t('total_x', { x: this.$tc('tax', 2) }), value: stock_adjustments.total_tax_amount },
        { label: this.$t('total_x', { x: this.$tc('additions') }), value: stock_adjustments.additions_total },
        { label: this.$t('total_x', { x: this.$tc('damages') }), value: stock_adjustments.damages_total },
        { label: this.$t('total_x', { x: this.$tc('subtractions') }), value: stock_adjustments.subtractions_total },
      ];
    },
    viewModal(row) {
      this.view = true;
      if (row.id != this.stock_adjustment.id) {
        this.loading = true;
        this.$http
          .get(`app/adjustments/${row.id}`)
          .then(res => (this.stock_adjustment = res.data))
          .finally(() => (this.loading = false));
      }
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
    handleSummary({ columns, data }) {
      let cc = ['grand_total'];
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
