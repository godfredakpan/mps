<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">
        {{ $t('x_report', { x: $tc('tax') }) }} ({{ datetime(new Date(reportForm.start_date)) }} -
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
        <Card :dis-hover="true">
          <p slot="title">{{ $tc('sale', 2) }}</p>
          <Row :gutter="16" class="sparkboxes">
            <template v-for="(d, di) in sales">
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
        </Card>
        <Card :dis-hover="true" class="mt16">
          <p slot="title">{{ $tc('purchase', 2) }}</p>
          <Row :gutter="16" class="sparkboxes">
            <template v-for="(d, di) in purchase">
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
        </Card>
      </div>
    </Card>
    <center-loading-component v-if="loading" />
  </div>
</template>

<script>
import ReportFormComponent from './ReportFormComponent';

export default {
  components: { ReportFormComponent },
  data() {
    return {
      data: {},
      sales: {},
      updated: 0,
      purchases: {},
      loading: false,
      showForm: false,
      reportForm: {},
      fields: ['date'],
    };
  },
  created() {
    this.$event.listen('location:changed', id => this.refresh++);
    this.fields.map(f => {
      this.reportForm[f] = '';
    });
    this.reportForm.end_date = '';
    this.reportForm.start_date = '';
    this.$http
      .get('app/reports/taxes', { params: { ...this.reportForm, date: '' } })
      .then(res => {
        this.reportForm.date = [new Date(res.data.start_date), new Date(res.data.end_date)];
        this.prepareData(res.data.taxes);
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

      this.refresh++;
      this.$http
        .get('app/reports/taxes', { params: { ...this.reportForm, date: '' } })
        .then(res => {
          this.reportForm.date = [new Date(res.data.start_date), new Date(res.data.end_date)];
          this.prepareData(res.data.taxes);
          this.updated++;
        })
        .finally(() => (this.loading = false));
    },
    prepareData(taxes) {
      this.reportForm.end_date = this.reportForm.date[1];
      this.reportForm.start_date = this.reportForm.date[0];
      this.sales = [
        { label: this.$t('total_x', { x: this.$tc('sale', 2) }), value: taxes[0].grand_total },
        { label: this.$t('total_x', { x: this.$tc('tax', 2) }), value: taxes[0].total_tax_amount },
        { label: this.$t('total_x', { x: this.$t('recoverable_tax_amount') }), value: taxes[0].recoverable_tax_amount },
        { label: this.$t('total_x', { x: this.$t('recoverable_tax_calculated_on') }), value: taxes[0].recoverable_tax_calculated_on },
      ];
      this.purchase = [
        { label: this.$t('total_x', { x: this.$tc('purchase', 2) }), value: taxes[1].grand_total },
        { label: this.$t('total_x', { x: this.$tc('tax', 2) }), value: taxes[1].total_tax_amount },
        { label: this.$t('total_x', { x: this.$t('recoverable_tax_amount') }), value: taxes[1].recoverable_tax_amount },
        { label: this.$t('total_x', { x: this.$t('recoverable_tax_calculated_on') }), value: taxes[1].recoverable_tax_calculated_on },
      ];
    },
  },
};
</script>
