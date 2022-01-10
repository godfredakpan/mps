<template>
  <Card :dis-hover="true">
    <template v-if="$store.getters.staff">
      <p slot="title">{{ $t('dashboard') }}</p>
      <!-- <a href="#" slot="extra">
        <Dropdown style="margin: 0 -5px 0 20px">
            <a href="javascript:void(0)" style="padding-right: 10px">
                <Icon type="logo-buffer" size="18" />
            </a>
            <DropdownMenu slot="list">
                <DropdownItem>驴打滚</DropdownItem>
            </DropdownMenu>
        </Dropdown>
        <Dropdown style="margin: 0 -5px 0 20px" placement="bottom-end">
            <a href="javascript:void(0)" style="padding-right: 10px">
                <Icon type="md-menu" size="18" />
            </a>
            <DropdownMenu slot="list">
                <DropdownItem>驴打滚</DropdownItem>
            </DropdownMenu>
        </Dropdown>
    </a> -->
      <p style="margin-bottom: 1rem;">
        {{ $t('dashboard_chart_heading_text') }}
      </p>
      <div id="charts">
        <weekly-chart v-if="WeeklyChartData" :chartsData="WeeklyChartData"></weekly-chart>
        <div id="containter">
          <Row :gutter="16">
            <Col :sm="24" :md="12" :lg="12">
              <Select v-model="month" style="width: 100%; margin-bottom: 1rem;">
                <Option v-for="m in months" :value="m" :key="m">{{ (m + new Date().getFullYear()) | month }}</Option>
              </Select>
            </Col>
            <Col :sm="24" :md="12" :lg="12">
              <Select v-model="year" style="width: 100%; margin-bottom: 1rem;">
                <Option v-for="y in years" :value="y" :key="y">{{ y }}</Option>
              </Select>
            </Col>
          </Row>
          <div id="month">
            <h4 class="text-center">{{ $t('daily_overview_chart') }}</h4>
            <highcharts :options="monthChartOptions"></highcharts>
          </div>
          <div id="year">
            <h4 class="text-center">{{ $t('monthly_overview_chart') }}</h4>
            <highcharts :options="yearChartOptions"></highcharts>
          </div>
        </div>
      </div>
    </template>
    <template v-else>
      <p>Are you lost? Please <a href="/">click here</a> to go to home page.</p>
    </template>
  </Card>
</template>
<script>
import { Chart } from 'highcharts-vue';
import WeeklyChart from './charts/WeeklyChart.vue';
export default {
  components: { highcharts: Chart, WeeklyChart },
  data() {
    return {
      year: new Date().getFullYear(),
      month: new Date().getMonth(),
      WeeklyChartData: null,
      yearChartOptions: {},
      monthChartOptions: {},
      months: Array.apply(0, Array(12)).map((_, i) => this.monthToLocale(moment().month(i))),
      chartOptions: {
        title: { text: '' },
        chart: { type: 'column' },
        credits: { enabled: false },
        yAxis: { min: 0, title: { text: '' } },
        xAxis: { categories: this.months, crosshair: true },
        colors: ['#2d8cf0', '#17233d', '#19be6b', '#ff9900'],
        plotOptions: { column: { pointPadding: 0.2, borderWidth: 0 } },
        legend: {
          itemStyle: { color: '#515a6e' },
          itemHoverStyle: { color: '#363e4f' },
        },
        tooltip: {
          headerFormat: '<span style="font-weight:900;">{point.key}</span><table>',
          // pointFormat:
          //     '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
          //     '<td style="padding:0;text-align:right"><b><small>$</small>{point.y:.2f}</b></td></tr>',
          footerFormat: '</table>',
          shared: true,
          useHTML: true,
          hideDelay: 0,
          shadow: false,
          borderWidth: 0,
          backgroundColor: '#eeeeee',
          style: {
            width: 'auto',
            maxWidth: '250px',
          },
        },
        series: [
          { name: 'Sales', data: [] },
          { name: 'Purchases', data: [] },
          { name: 'Incomes', data: [] },
          { name: 'Expenses', data: [] },
        ],
      },
    };
  },
  computed: {
    // months() {
    //   return Array.apply(0, Array(12)).map((_, i) => this.monthToLocale(moment().month(i)));
    // },
    years() {
      let years = [];
      let date = new Date();
      let year = date.getFullYear();
      for (let y = 2019; y <= year; y++) {
        years.push(y);
      }
      return years;
    },
  },
  watch: {
    month: function(m) {
      if (this.$store.getters.staff) {
        let month = 0;
        this.months.map((mn, mi) => (mn == m ? (month = mi + 1) : mn));
        this.$http
          .get(`/app/dashboard/month?month=${month}&year=${this.year}`)
          .then(res => this.setMonthChart(res.data.chart.month))
          .catch(err => console.log(err));
      }
    },
    year: function(y) {
      if (this.$store.getters.staff) {
        this.$http
          .get('/app/dashboard?year=' + y)
          .then(res => {
            this.setYearChart(res.data.chart.year);
            this.setMonthChart(res.data.chart.month);
          })
          .catch(err => console.log(err));
      }
    },
  },
  created() {
    this.month = this.months[new Date().getMonth()];
    this.yearChartOptions = JSON.parse(JSON.stringify(this.chartOptions));
    this.monthChartOptions = JSON.parse(JSON.stringify(this.chartOptions));

    if (this.$store.getters.staff) {
      this.getChartsData();
      this.$event.listen('location:changed', () => {
        if (this.$route.name == 'dashboard') {
          this.getChartsData();
        }
      });
    }
  },
  methods: {
    getChartsData() {
      this.$http
        .get('/app/dashboard')
        .then(res => {
          this.WeeklyChartData = [];
          for (let c in res.data.week) {
            let chart = { name: c, data: res.data.week[c] };
            this.WeeklyChartData.push(chart);
          }
          this.setYearChart(res.data.chart.year);
          this.setMonthChart(res.data.chart.month);
        })
        .catch(err => console.log(err));
    },
    setYearChart(chart) {
      let series = [];
      let categories = [];
      for (let m in chart) {
        let elem = { name: m, data: [] };
        let data = { ...chart[m] };
        Object.keys(data)
          .sort(function(a, b) {
            return new Date('' + a + '-01') - new Date('' + b + '-01');
          })
          .map((k, i) => {
            elem.data[i] = data[k];
          });
        series.push(elem);
      }
      this.yearChartOptions.series = series;
      this.yearChartOptions.xAxis.categories = this.months.map(m => this.$options.filters.monthYear(m + ' ' + this.year));

      // Object.keys(chart.sale)
      //   .sort((a, b) => new Date(a) - new Date(b))
      //   .map(k => {
      //     let day = this.dateDay(new Date(k));
      //     categories.push(day);
      //   });
      // this.monthChartOptions.series = series;
      // this.monthChartOptions.chart.type = 'line';
      // this.monthChartOptions.xAxis.categories = categories;

      var vm = this;
      var tc = x => this.$tc(x, 2);
      this.yearChartOptions.legend.labelFormatter = function() {
        return tc(this.name);
      };
      this.yearChartOptions.tooltip.pointFormatter = function(tooltip) {
        this.y = vm.$options.filters.formatNumber(this.y, 2);
        return `<tr><td style="color:${this.series.color};padding:0 10px 0 0;font-weight:bold;">${tc(
          this.series.name
        )}: </td><td style="color:${this.series.color};padding:0;text-align:right"><b>${this.y}</b></td></tr>`;
      };
    },
    setMonthChart(chart) {
      let series = [];
      let categories = [];
      for (let m in chart) {
        let data = { ...chart[m] };
        let elem = { name: m, data: [] };
        Object.keys(data)
          .sort(function(a, b) {
            return new Date(a) - new Date(b);
          })
          .map((k, i) => {
            elem.data[i] = data[k];
          });
        series.push(elem);
      }
      Object.keys(chart.sale)
        .sort((a, b) => new Date(a) - new Date(b))
        .map(k => {
          let day = this.dateDay(new Date(k));
          categories.push(day);
        });
      this.monthChartOptions.series = series;
      this.monthChartOptions.chart.type = 'line';
      this.monthChartOptions.xAxis.categories = categories;

      var vm = this;
      var tc = x => this.$tc(x, 2);
      this.monthChartOptions.legend.labelFormatter = function() {
        return tc(this.name);
      };
      this.monthChartOptions.tooltip.pointFormatter = function(tooltip) {
        this.y = vm.$options.filters.formatNumber(this.y, 2);
        return `<tr><td style="color:${this.series.color};padding:0 10px 0 0;font-weight:bold;">${tc(
          this.series.name
        )}: </td><td style="color:${this.series.color};padding:0;text-align:right"><b>${this.y}</b></td></tr>`;
      };
    },
  },
};
</script>

<style>
#containter {
  width: 100%;
  display: block;
  min-height: 416px;
  margin-top: 1rem;
  /*position: relative;*/
}
#containter #chart {
  width: 100%;
  height: 100%;
  /*margin-top: 16px;
    position: absolute;*/
}
.highcharts-container {
  width: 100% !important;
  height: 100% !important;
}
.highcharts-container svg {
  width: 100% !important;
  display: flex !important;
}
</style>
