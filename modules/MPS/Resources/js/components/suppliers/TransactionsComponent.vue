<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">
        {{ $t('transactions') }} ({{ datetime(new Date(reportForm.start_date)) }} - {{ datetime(new Date(reportForm.end_date)) }})
      </p>
      <span slot="extra">
        <router-link to="/suppliers">
          <Icon type="ios-grid-outline" />
          {{ $t('list') }} {{ $tc('supplier', 2) }}
        </router-link>
        <Button type="text" size="small" slot="extra" @click="showForm = !showForm">
          <Icon type="md-options" />
          {{ $t('toggle_x', { x: $tc('form', 2) }) }}
        </Button>
      </span>
      <div>
        <transition
          name="fade"
          mode="out-in"
          enter-active-class="animate__animated fast animate__fadeInDown"
          leave-active-class="animate__animated faster animate__fadeOutUp"
        >
          <Card dis-hover v-if="showForm" class="mb16">
            <report-form-component :fields="fields" :updated="updated" @submit="handleSubmit" :reportForm="reportForm" />
          </Card>
        </transition>
        <Row :gutter="16" class="sparkboxes">
          <template v-for="(d, di) in data">
            <Col :sm="12" :md="12" :key="di">
              <div class="box static" :class="'box' + (di + 5)">
                <div class="details">
                  <h3>{{ formatJournalBalance(d.value) }}</h3>
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
          :dblClickCB="showInfo"
          :row-class-name="rowClassName"
        ></table-component>
      </div>
    </Card>
    <Modal width="750" :footer-hide="true" v-model="view_payment" :title="$tc('payment')" class="np-header-footer">
      <div v-if="loading" class="py16">
        <Loading />
      </div>
      <payment-view-component :payment="payment" />
    </Modal>
  </div>
</template>

<script>
import ReportFormComponent from '@mpscom/reports/ReportFormComponent';
import PaymentViewComponent from '@mpscom/payments/PaymentViewComponent';

export default {
  components: { PaymentViewComponent, ReportFormComponent },
  data() {
    return {
      url: '',
      data: {},
      refresh: 1,
      updated: 0,
      reportForm: {},
      loading: false,
      showForm: false,
      payment: {},
      view_payment: false,
      fields: ['created_at', 'user_id'],
      columns: [
        {
          type: 'expand',
          width: 50,
          render: (h, params) => {
            return h('pre', { style: { margin: '0' } }, JSON.stringify(params.row.subject, null, '  '));
          },
        },
        { title: this.$t('created_at'), sortable: true, key: 'created_at', sortType: 'asc', width: 175, render: this.renderCreatedAt },
        { title: this.$tc('account'), sortable: false, key: 'account', render: this.renderAccount },
        { title: this.$tc('supplier'), sortable: false, key: 'supplier', render: this.renderSupplier },
        { title: this.$tc('debit'), sortable: true, key: 'debit', width: 150, render: this.renderDebit },
        { title: this.$tc('credit'), sortable: true, key: 'credit', width: 150, render: this.renderCredit },
        { title: this.$t('type'), sortable: false, key: 'type', width: 200, render: this.renderType },
      ],
      options: {
        hideSearch: true,
        showSummary: true,
        orderBy: 'created_at desc',
        summaryMethod: this.handleSummary,
        perPage: this.$store.state.settings.rows,
      },
    };
  },
  created() {
    this.fields.map(f => {
      this.reportForm[f] = '';
    });
    this.url = `app/suppliers/transactions/${this.$route.params.id}/table`;
    this.$http
      .get(`app/suppliers/transactions/${this.$route.params.id}`, { params: { ...this.reportForm, date: '' } })
      .then(res => {
        this.prepareData(res.data.transactions);
        this.reportForm.end_date = new Date(res.data.end_date);
        this.reportForm.start_date = new Date(res.data.start_date);
        this.reportForm.created_at = [new Date(res.data.start_date), new Date(res.data.end_date)];
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
      // delete this.reportForm.date;
      delete this.reportForm.created_at;

      this.url = `app/suppliers/transactions/${this.$route.params.id}/table?${this.queryString(this.reportForm)}`;
      this.refresh++;
      this.$http
        .get(`app/suppliers/transactions/${this.$route.params.id}`, { params: { ...this.reportForm, date: '' } })
        .then(res => {
          this.prepareData(res.data.transactions);
          this.reportForm.created_at = [new Date(res.data.start_date), new Date(res.data.end_date)];
          this.updated++;
        })
        .finally(() => (this.loading = false));
    },
    prepareData(transactions) {
      this.data = [
        { label: this.$t('start_balance'), value: transactions.start_balance }, // if money then add .amount
        { label: this.$t('close_balance'), value: transactions.close_balance }, // if money then add .amount
      ];
    },
    showInfo(row) {
      if (row.subject_type && row.subject_type.includes('Payment')) {
        this.viewPayment(row.subject_id);
        // } else if (row.subject_type && row.subject_type.includes('Income')) {
        //   this.viewIncome(row.subject_id);
      }
    },
    viewPayment(id) {
      this.view_payment = true;
      if (id != this.payment.id) {
        this.loading = true;
        this.$http
          .get(`app/payments/${id}`)
          .then(res => (this.payment = res.data))
          .finally(() => (this.loading = false));
      }
    },
    renderType(h, params) {
      const bt = params.row.type.split('_');
      const first = bt[0];
      const first_color = first == 'opening' ? 'success' : 'primary';
      const second = bt[1];
      const second_color = second == 'created' ? 'success' : second == 'updated' ? 'warning' : second == 'deleted' ? 'error' : 'default';
      return (
        <div class="text-center">
          <div class={`ivu-tag ivu-tag-${first_color} ivu-tag-checked`}>
            <span class="ivu-tag-text ivu-tag-color-white">{this.capitalize(first)}</span>
          </div>
          <div class={`ivu-tag ivu-tag-${second_color} ivu-tag-checked`}>
            <span class={`ivu-tag-text ${second_color == 'default' ? '' : 'ivu-tag-color-white'}`}>{this.capitalize(second)}</span>
          </div>
        </div>
      );
    },
    renderSupplier(h, params) {
      return (
        <div>{params.row.journal.morphed ? params.row.journal.morphed.name + ' (' + params.row.journal.morphed.company + ')' : ''}</div>
      );
    },
    renderAccount(h, params) {
      return <div>{params.row.subject && params.row.subject.account ? params.row.subject.account.name : ''}</div>;
    },
    renderCredit(h, params) {
      return (
        <div class="text-right">{this.$options.filters.formatJournalBalance(params.row.credit, this.$store.getters.settings.decimals)}</div>
      );
    },
    renderDebit(h, params) {
      return (
        <div class="text-right">{this.$options.filters.formatJournalBalance(params.row.debit, this.$store.getters.settings.decimals)}</div>
      );
    },
    renderCreatedAt(h, params) {
      return <div>{this.$options.filters.formatDate(params.row.created_at, this.$store.state.settings.dateformat + ' HH:mm A')}</div>;
    },
    rowClassName(row, index) {
      if (row.debit > 0) {
        return 'ivu-table-warning-row';
      } else if (row.credit > 0) {
        return 'ivu-table-success-row';
      }
      return '';
    },
    handleSummary({ columns, data }) {
      let cc = ['debit', 'credit'];
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
          sums[key] = { key, value: this.formatJournalBalance(v) };
        }
      });

      return sums;
    },
  },
};
</script>
