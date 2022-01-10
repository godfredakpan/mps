<template>
  <Card :dis-hover="true">
    <p slot="title">
      {{ $t('item_trails') }} ({{ datetime(new Date(reportForm.start_date)) }} - {{ datetime(new Date(reportForm.end_date)) }})
    </p>
    <span slot="extra">
      <router-link to="/items">
        <Button type="text" ghost>
          <Icon type="ios-grid-outline" />
          {{ $t('list') }} {{ $tc('item', 2) }}
        </Button>
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
        :row-class-name="rowClassName"
      ></table-component>
    </div>
  </Card>
</template>

<script>
import inflection from 'inflection';
import ReportFormComponent from '@mpscom/reports/ReportFormComponent';

export default {
  components: { ReportFormComponent },
  data() {
    return {
      url: '',
      data: {},
      refresh: 1,
      updated: 0,
      reportForm: {},
      loading: false,
      showForm: false,
      fields: ['created_at', 'location_id'],
      columns: [
        {
          minWidth: 175,
          sortable: true,
          sortType: 'desc',
          key: 'created_at',
          title: this.$t('created_at'),
          render: this.renderCreatedAt,
        },
        { title: this.$t('type'), sortable: true, key: 'memo', width: 150 },
        // { title: this.$t('type'), sortable: true, key: 'type', width: 225, render: this.renderType },
        { title: this.$tc('item'), sortable: false, key: 'item', minWidth: 250, render: this.renderItem },
        { title: this.$t('quantity'), sortable: false, key: 'quantity', minWidth: 125, render: this.renderQuantity },
        { title: this.$tc('unit'), sortable: false, key: 'unit', minWidth: 150, render: this.renderUnit },
        { title: this.$tc('location'), sortable: false, key: 'location', minWidth: 150, render: this.renderLocation },
        // { title: this.$tc('portion'), sortable: false, key: 'portion', minWidth: 150, render: this.renderPortion },
        { title: this.$tc('variation'), sortable: false, key: 'variation', minWidth: 250, render: this.renderVariation },
        // { title: this.$t('memo'), sortable: true, key: 'memo', minWidth: 200 },
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
    this.url = `app/items/trails/${this.$route.params.id}/table`;
    this.$event.listen('location:changed', id => this.refresh++);
    this.$http
      .get(`app/items/trails/${this.$route.params.id}`, { params: { ...this.reportForm, date: '' } })
      .then(res => {
        this.prepareData(res.data.stock);
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

      this.url = `app/items/trails/${this.$route.params.id}/table?${this.queryString(this.reportForm)}`;
      this.refresh++;
      this.$http
        .get(`app/items/trails/${this.$route.params.id}`, { params: { ...this.reportForm, date: '' } })
        .then(res => {
          this.prepareData(res.data.stock);
          this.reportForm.created_at = [new Date(res.data.start_date), new Date(res.data.end_date)];
          this.updated++;
        })
        .finally(() => (this.loading = false));
    },
    prepareData(stock) {
      this.data = [
        { label: this.$t('start_stock'), value: stock.start_stock },
        { label: this.$t('close_stock'), value: stock.close_stock },
      ];
    },
    renderLocation(h, params) {
      return <div>{params.row.location ? params.row.location.name : ''}</div>;
    },
    renderItem(h, params) {
      return <div>{params.row.item ? params.row.item.name : ''}</div>;
    },
    renderUnit(h, params) {
      return <div>{params.row.unit ? params.row.unit.name : ''}</div>;
    },
    renderPortion(h, params) {
      return <div>{params.row.portion ? params.row.portion.name : ''}</div>;
    },
    renderVariation(h, params) {
      return <div>{params.row.variation ? params.row.variation.code : ''}</div>;
    },
    renderCreatedAt(h, params) {
      return <div>{this.$options.filters.formatDate(params.row.created_at, this.$store.state.settings.dateformat + ' HH:mm A')}</div>;
    },
    renderQuantity(h, params) {
      return (
        <div class="text-center">{this.$options.filters.formatNumber(params.row.quantity, this.$store.getters.settings.decimals)}</div>
      );
    },
    renderType(h, params) {
      return <div>{inflection.humanize(params.row.type)}</div>;
      // return <div>{inflection.titleize(params.row.type)}</div>;
    },
    rowClassName(row, index) {
      if (row.quantity < 0) {
        return 'ivu-table-warning-row';
      } else if (row.quantity > 0) {
        return 'ivu-table-success-row';
      }
      return '';
    },
    handleSummary({ columns, data }) {
      let cc = ['quantity'];
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
