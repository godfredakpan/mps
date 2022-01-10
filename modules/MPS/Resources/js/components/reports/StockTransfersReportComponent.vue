<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">
        {{ $t('x_report', { x: $tc('stock_transfer', 2) }) }} ({{ datetime(reportForm.start_date) }} - {{ datetime(reportForm.end_date) }})
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
            <report-form-component
              :fields="fields"
              :updated="updated"
              @submit="handleSubmit"
              :reportForm="reportForm"
              :statusOptions="status_options"
            />
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
      :title="$tc('stock_transfer') + ' (' + stock_transfer.reference + ')'"
    >
      <div v-if="loading" class="py16">
        <Loading />
      </div>
      <order-view-component
        field="cost"
        :extra="false"
        :payment="false"
        :only-qty="true"
        to="to_location"
        :to-text="$t('to')"
        from="from_location"
        :record="stock_transfer"
        :heading="$tc('stock_transfer')"
        @remove="a => deleteAttachment(a, stock_transfer)"
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
  mixins: [Table('stock_transfer', 'app/transfers/stock', 'reference')],
  data() {
    return {
      url: '',
      data: {},
      updated: 0,
      view: false,
      loading: false,
      showForm: false,
      reportForm: {},
      stock_transfer: {},
      status_options: ['pending', 'transferring', 'transferred'],
      fields: ['created_at', 'reference', 'custom_fields', 'item_id', 'user_id', 'from_location', 'to_location', 'status'],
      // fields: ['created_at', 'reference', 'custom_fields', 'item_id', 'serial', 'user_id', 'from_location', 'to_location', 'draft'],
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
        {
          key: 'to',
          maxWidth: 300,
          minWidth: 200,
          sortable: false,
          title: this.$tc('to_location'),
          render: this.renderToAccount,
        },
        {
          key: 'from',
          maxWidth: 300,
          minWidth: 200,
          sortable: false,
          render: this.renderFromAccount,
          title: this.$tc('from_location'),
        },
        { title: '🔗', align: 'center', sortable: false, key: 'attachments', maxWidth: 50, minWidth: 125, render: this.renderAttachments },
        {
          width: 175,
          key: 'user_id',
          sortable: false,
          render: this.renderUser,
          title: this.$t('created_by'),
        },
        { title: this.$t('details'), sortable: false, key: 'details', ellipsis: true, ellipsis: true, minWidth: 250, maxWidth: 600 },
      ],
      options: {
        hideSearch: true,
        // showSummary: true,
        // summaryMethod: this.handleSummary,
        perPage: this.$store.state.settings.rows,
        orderBy: ['created_at desc'],
      },
    };
  },
  // computed: {
  //   url() {
  //     return 'app/reports/stock_transfers/table?' + this.queryString(this.reportForm);
  //   },
  // },
  created() {
    this.$event.listen('location:changed', id => this.refresh++);
    this.fields.map(f => {
      this.reportForm[f] = '';
    });
    this.reportForm.end_date = '';
    this.reportForm.start_date = '';
    this.url = 'app/reports/stock_transfers/table?' + this.queryString(this.reportForm);
    this.$http
      .get('app/reports/stock_transfers', { params: { ...this.reportForm, date: '' } })
      .then(res => {
        this.reportForm.created_at = [new Date(res.data.start_date), new Date(res.data.end_date)];
        this.prepareData(res.data.stock_transfers);
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

      this.url = 'app/reports/stock_transfers/table?' + this.queryString(this.reportForm);
      this.refresh++;
      this.$http
        .get('app/reports/stock_transfers', { params: { ...this.reportForm, date: '' } })
        .then(res => {
          this.reportForm.created_at = [new Date(res.data.start_date), new Date(res.data.end_date)];
          this.prepareData(res.data.stock_transfers);
          this.updated++;
        })
        .finally(() => (this.loading = false));
    },
    prepareData(stock_transfers) {
      this.reportForm.end_date = this.reportForm.created_at[1];
      this.reportForm.start_date = this.reportForm.created_at[0];
      this.data = [
        { label: this.$t('total_x', { x: this.$tc('stock_transfer', 2) }), value: stock_transfers.count },
        // { label: this.$t('total_x', { x: this.$tc('items', 2) }), value: stock_transfers.total },
      ];
    },
    viewModal(row) {
      this.view = true;
      if (row.id != this.stock_transfer.id) {
        this.loading = true;
        this.$http
          .get(`app/transfers/stock/${row.id}`)
          .then(res => (this.stock_transfer = res.data))
          .finally(() => (this.loading = false));
      }
    },
    renderToAccount(h, params) {
      return <div>{params.row.to_location.name}</div>;
    },
    renderFromAccount(h, params) {
      return <div>{params.row.from_location.name}</div>;
    },
    // handleSummary({ columns, data }) {
    //   let cc = ['grand_total'];
    //   const sums = {};
    //   columns.forEach((column, index) => {
    //     const key = column.key;
    //     if (!cc.includes(key)) {
    //       sums[key] = { key, value: '' };
    //       return;
    //     }
    //     const values = data.map(item => (item.void || item.draft ? 0 : Number(this.formatDecimal(item[key]))));
    //     if (!values.every(value => isNaN(value))) {
    //       const v = values.reduce((a, c) => (!isNaN(Number(c)) ? a + c : a), 0);
    //       sums[key] = { key, value: this.formatNumber(v) };
    //     }
    //   });

    //   return sums;
    // },
  },
};
</script>
