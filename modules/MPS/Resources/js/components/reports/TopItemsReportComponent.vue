<template>
  <div>
    <Card dis-hover>
      <p slot="title">
        {{ $t('x_report', { x: $t('top_items') }) }}
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
      fields: ['date', 'category_id', 'customer_id', 'user_id'],
      columns: [
        // { title: this.$t('date'), sortable: true, key: 'date', sortType: 'desc', width: 100, render: this.renderDate },
        { title: this.$t('code'), sortable: false, key: 'code', minWidth: 150 },
        { title: this.$t('name'), sortable: false, key: 'name', minWidth: 175 },
        {
          title: this.$t('sold'),
          sortable: 'custom',
          sortType: 'desc',
          key: 'sold',
          minWidth: 200,
          minWidth: 80,
          render: this.renderSold,
        },
      ],
      options: {
        orderBy: [],
        hideSearch: true,
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
    this.url = 'app/reports/items/top?' + this.queryString(this.reportForm);
    // this.reportForm.date = [new Date(res.data.start_date), new Date(res.data.end_date)];
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
      if (this.reportForm.date && this.reportForm.date[0]) {
        this.reportForm.start_date = this.$moment(this.reportForm.date[0]).format(this.datetimeFormatString());
      }
      if (this.reportForm.date && this.reportForm.date[1]) {
        this.reportForm.end_date = this.$moment(this.reportForm.date[1]).format(this.datetimeFormatString());
      }
      delete this.reportForm.date;

      this.url = 'app/reports/items/top?' + this.queryString(this.reportForm);
      this.refresh++;
    },
    renderSold(h, params) {
      return <div class="bold text-right">{this.formatNumber(params.row.sold || 0, this.$store.state.settings.quantity_decimals)}</div>;
    },
  },
};
</script>
