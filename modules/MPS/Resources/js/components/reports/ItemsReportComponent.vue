<template>
  <div>
    <Card dis-hover>
      <p slot="title">
        {{ $t('x_report', { x: $tc('item', 2) }) }}
        <span v-if="dates[0] && dates[1]"> ({{ datetime(new Date(dates[0])) }} - {{ datetime(new Date(dates[1])) }}) </span>
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
        <table-component :url="url" :columns="columns" sub-data="table" :options="options" :refresh="refresh" />
      </div>
    </Card>
  </div>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
import ReportFormComponent from './ReportFormComponent';

export default {
  components: { ReportFormComponent },
  mixins: [Table('item', 'app/items', 'reference')],
  data() {
    return {
      url: '',
      item: {},
      dates: [],
      updated: 0,
      view: false,
      loading: false,
      reportForm: {},
      showForm: false,
      fields: ['created_at', 'date', 'category_id'],
      columns: [
        // { title: this.$t('date'), sortable: true, key: 'date', sortType: 'desc', width: 100, render: this.renderDate },
        { title: this.$t('code'), sortable: true, sortType: 'asc', key: 'code', minWidth: 150 },
        { title: this.$t('name'), sortable: true, key: 'name', minWidth: 175 },
        { title: this.$t('sold'), sortable: false, key: 'sold', minWidth: 175, width: 150, render: this.renderSold },
        {
          minWidth: 175,
          sortable: false,
          key: 'sold_amount',
          render: this.renderSoldAmount,
          title: this.$t('x_amount', { x: this.$t('sold') }),
        },
        { title: this.$t('purchased'), sortable: false, key: 'purchased', minWidth: 175, width: 150, render: this.renderPurchased },
        {
          minWidth: 175,
          sortable: false,
          key: 'purchased_amount',
          render: this.renderPurchasedAmount,
          title: this.$t('x_amount', { x: this.$t('purchased') }),
        },
        { title: this.$tc('field', 2), sortable: false, key: 'extra_attributes', minWidth: 300, render: this.renderExtras },
        { title: this.$t('created_at'), sortable: true, key: 'created_at', width: 175, render: this.renderCreatedAt },
      ],
      options: {
        hideSearch: true,
        showSummary: true,
        orderBy: ['name asc'],
        summaryMethod: this.handleSummary,
        perPage: this.$store.state.settings.rows,
      },
    };
  },
  created() {
    this.fields.map(f => {
      this.reportForm[f] = '';
    });
    this.reportForm.end_date = '';
    this.reportForm.start_date = '';
    this.$event.listen('table:loaded', this.setDates);
    this.url = 'app/reports/items?' + this.queryString(this.reportForm);
  },
  methods: {
    setDates(dates) {
      this.dates = dates;
    },
    handleSubmit(form) {
      this.fields.map(f => {
        this.reportForm[f] = form[f];
      });
      this.reportForm.end_date = '';
      this.reportForm.start_date = '';
      this.reportForm.end_created_at = '';
      this.reportForm.start_created_at = '';
      if (this.reportForm.date && this.reportForm.date[0]) {
        this.reportForm.start_date = this.$moment(this.reportForm.date[0]).format(this.datetimeFormatString());
      }
      if (this.reportForm.date && this.reportForm.date[1]) {
        this.reportForm.end_date = this.$moment(this.reportForm.date[1]).format(this.datetimeFormatString());
      }
      if (this.reportForm.created_at && this.reportForm.created_at[0]) {
        this.reportForm.start_created_at = this.$moment(this.reportForm.created_at[0]).format(this.datetimeFormatString());
      }
      if (this.reportForm.created_at && this.reportForm.created_at[1]) {
        this.reportForm.end_created_at = this.$moment(this.reportForm.created_at[1]).format(this.datetimeFormatString());
      }
      delete this.reportForm.date;
      delete this.reportForm.created_at;

      this.url = 'app/reports/items?' + this.queryString(this.reportForm);
      this.refresh++;
    },
    renderGrandTotal(h, params) {
      return this.renderNumber(h, params, 'grand_total', true);
    },
    renderSold(h, params) {
      let sold = 0;
      if (params.row.sale_items.length) {
        // params.row.sale_items.map(si => (sold += parseFloat(si.quantity)));
        sold = params.row.sale_items.reduce((a, c) => a + parseFloat(c.quantity), 0);
      }
      return this.renderMyNumber(sold);
    },
    renderSoldAmount(h, params) {
      let amount = 0;
      if (params.row.sale_items.length) {
        amount = params.row.sale_items.reduce((a, c) => a + parseFloat(c.subtotal), 0);
      }
      return this.renderMyNumber(amount);
    },
    renderPurchased(h, params) {
      let purchased = 0;
      if (params.row.purchase_items.length) {
        // params.row.purchase_items.map(si => (purchased += parseFloat(si.quantity)));
        purchased = params.row.purchase_items.reduce((a, c) => a + parseFloat(c.quantity), 0);
      }
      return this.renderMyNumber(purchased);
    },
    renderPurchasedAmount(h, params) {
      let amount = 0;
      if (params.row.purchase_items.length) {
        amount = params.row.purchase_items.reduce((a, c) => a + parseFloat(c.subtotal), 0);
      }
      return this.renderMyNumber(amount);
    },
    handleSummary({ columns, data }) {
      let cc = ['sold', 'sold_amount', 'purchased', 'purchased_amount'];
      const sums = {};
      columns.forEach((column, index) => {
        const key = column.key;
        if (!cc.includes(key)) {
          sums[key] = { key, value: '' };
          return;
        }

        const v = data.reduce((ai, item) => {
          if (key == 'sold') {
            return item.sale_items.length ? ai + parseFloat(item.sale_items.reduce((a, c) => a + parseFloat(c.quantity), 0)) : ai;
          } else if (key == 'purchased') {
            return item.purchase_items.length ? ai + parseFloat(item.purchase_items.reduce((a, c) => a + parseFloat(c.quantity), 0)) : ai;
          } else if (key == 'sold_amount') {
            return item.sale_items.length ? ai + parseFloat(item.sale_items.reduce((a, c) => a + parseFloat(c.subtotal), 0)) : ai;
          } else if (key == 'purchased_amount') {
            return item.purchase_items.length ? ai + parseFloat(item.purchase_items.reduce((a, c) => a + parseFloat(c.subtotal), 0)) : ai;
          }
        }, 0);
        sums[key] = { key, value: this.formatNumber(v) };
      });

      return sums;
    },
  },
};
</script>
