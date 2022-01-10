<template>
  <Row :gutter="16" class="sparkboxes">
    <Col :sm="12" :md="6" v-for="(wc, i) in chartsData" :key="wc.name">
      <div class="box" :class="'box' + (i + 1)">
        <div class="details">
          <h3>{{ getTotal(wc.data) }}</h3>
          <h4>{{ $tc(wc.name, 2) }}</h4>
        </div>
        <div class="spline">
          <highcharts :options="getChartOptions(wc)"></highcharts>
        </div>
      </div>
    </Col>
  </Row>
</template>

<script>
import { Chart } from 'highcharts-vue';
export default {
  props: {
    chartsData: {
      type: Array,
      required: true,
    },
  },
  components: {
    highcharts: Chart,
  },
  data() {
    return {
      chartOptions: {
        chart: {
          height: 80,
          borderWidth: 0,
          type: 'areaspline',
          borderColor: null,
          backgroundColor: 'transparent',
          style: {
            float: 'right',
            overflow: 'visible',
          },
          marginTop: 5,
          marginLeft: 5,
          marginRight: -10,
          skipClone: true,
          marginBottom: 5,
        },
        title: { text: '' },
        legend: { enabled: false },
        credits: { enabled: false },
        colors: ['#ffffff'],
        xAxis: {
          lineWidth: 0,
          tickWidth: 0,
          maxPadding: 1,
          categories: [],
          minPadding: 0.5,
          gridLineWidth: 0,
          endOnTick: false,
          tickPositions: [],
          startOnTick: false,
          title: { text: null },
          labels: { enabled: false },
          // categories: this.categories(),
        },
        yAxis: {
          lineWidth: 0,
          tickWidth: 0,
          maxPadding: 1,
          minPadding: 0.5,
          gridLineWidth: 0,
          endOnTick: false,
          startOnTick: false,
          tickPositions: [0],
          title: { text: null },
          labels: { enabled: false },
        },
        tooltip: {
          hideDelay: 0,
          shared: true,
          outside: true,
          shadow: false,
          useHTML: true,
          borderWidth: 0,
          backgroundColor: '#515a6e',
          style: {
            width: 'auto',
            color: '#ffffff',
            maxWidth: '250px',
          },
          headerFormat: '<span style="font-weight:900">{point.key}</span><table>',
          footerFormat: '</table>',
        },
        plotOptions: {
          series: {
            lineWidth: 2,
            shadow: false,
            threshold: null,
            animation: false,
            fillOpacity: 0,
            lineColor: '#ffffff',
            states: { hover: { lineWidth: 2 } },
            marker: {
              radius: 2,
              enabled: true,
              states: { hover: { radius: 4 } },
            },
          },
          column: {
            borderColor: 'silver',
            negativeColor: '#910000',
          },
        },
        series: [
          {
            data: [],
            name: this.$t('total'),
            // data: [100, 200, 100, 200, 100, 300, 80],
          },
        ],
      },
    };
  },
  // created() {
  //     // console.log(this.chartsData);
  // },
  // mounted() {},
  methods: {
    getTotal(data) {
      var total = 0;
      for (var key in data) {
        total += parseFloat(data[key]);
      }
      return this.formatNumber(total);
      // return this.formatDecimal(total);
    },
    getChartOptions(wc) {
      let c = JSON.parse(JSON.stringify(this.chartOptions));
      // console.log(wc);
      c.series[0].name = this.$t('total_x', { x: this.$tc(wc.name, 2) });
      // let data = wc.data;
      Object.keys(wc.data)
        .sort(function(a, b) {
          return new Date(a) - new Date(b);
        })
        .map((key, i) => {
          c.series[0].data[i] = wc.data[key];
          c.xAxis.categories[i] = this.date(key);
        });
      var vm = this;
      c.tooltip.pointFormatter = function(tooltip) {
        this.y = vm.formatNumber(this.y, 2);
        return `<tr><td style="color:${this.series.color};padding:0">${this.series.name}: </td>
                        <td style="padding:0;text-align:right"><b>${this.y}</b></td></tr>`;
        // <small>$</small>
      };
      return c;
    },
    // categories() {
    //     var days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    //     var today = new Date();
    //     var daysSorted = [];
    //     for (var i = 0; i < 7; i++) {
    //         var newDate = new Date(today.setDate(today.getDate() - 1));
    //         daysSorted.push(days[newDate.getDay()]);
    //     }
    //     return daysSorted.reverse();
    // },
  },
};
</script>
