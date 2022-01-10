<template>
  <Row :gutter="16">
    <Col :sm="6" :md="6">
      <div class="box">
        <apexchart ref="mchart1" height="80" type="line" :options="spark1.options" :series="spark1.series"></apexchart>
      </div>
    </Col>
    <Col :sm="18" :md="18">
      <div class="box">
        <apexchart height="80" type="line" :options="spark2.options" :series="spark2.series"></apexchart>
      </div>
    </Col>
  </Row>
</template>

<script>
// import VueApexCharts from 'vue-apexcharts';
export default {
  // components: {
  //     chart: VueApexCharts,
  // },
  data() {
    return {
      spark: {
        options: {
          chart: {
            type: 'line',
            group: 'sparks',
            foreColor: '#ccc',
            sparkline: {
              enabled: true,
            },
            dropShadow: {
              top: 1,
              left: 1,
              blur: 2,
              opacity: 0.2,
              enabled: true,
            },
          },
          stroke: {
            width: 3,
            curve: 'smooth',
          },
          markers: {
            size: 0,
          },
          dataLabels: {
            enabled: false,
          },
          grid: {
            padding: {
              top: 20,
              left: 110,
              bottom: 10,
            },
            borderColor: '#535A6C',
            xaxis: {
              lines: {
                show: true,
              },
            },
          },
          colors: ['#fff'],
          tooltip: {
            theme: 'dark',
            x: {
              show: false,
            },
            y: {
              title: {
                formatter: function formatter(val) {
                  return '';
                },
              },
            },
          },
          yaxis: {
            labels: {
              minWidth: 100,
            },
          },
        },
        series: [
          {
            data: [25, 66, 41, 59, 25, 44, 12, 36, 9, 21],
          },
        ],
      },
      spark1: {},
      spark2: {},
      spark3: {},
      spark4: {},
    };
  },
  created() {
    this.spark1 = { ...this.spark };
    this.spark2 = { ...this.spark };
    this.spark3 = { ...this.spark };
    this.spark4 = { ...this.spark };
  },
  mounted() {
    this.$refs.chart1.updateSeries([
      {
        data: [25, 66, 41, 59, 25, 44, 12, 36, 9, 21],
      },
    ]);
  },
};
</script>

<style>
.box {
  padding: 25px 20px;
  background-color: #2b2d3e;
}

.shadow {
  box-shadow: 0px 1px 15px 1px rgba(69, 65, 78, 0.08);
}
.sparkboxes .box {
  padding-top: 10px;
  border-radius: 5px;
  position: relative;
  padding-bottom: 10px;
  text-shadow: 0 1px 1px 1px #666;
  box-shadow: 0px 1px 15px 1px rgba(69, 65, 78, 0.08);
}

.sparkboxes .box .details {
  color: #fff;
  position: absolute;
  transform: scale(0.7) translate(-22px, 20px);
}
.sparkboxes strong {
  top: -8px;
  z-index: 3;
  color: #fff;
  position: relative;
}

.sparkboxes .box1 {
  background-image: linear-gradient(135deg, #abdcff 10%, #0396ff 100%);
}

.sparkboxes .box2 {
  background-image: linear-gradient(135deg, #2afadf 10%, #4c83ff 100%);
}

.sparkboxes .box3 {
  background-image: linear-gradient(135deg, #ffd3a5 10%, #fd6585 100%);
}

.sparkboxes .box4 {
  background-image: linear-gradient(135deg, #ee9ae5 10%, #5961f9 100%);
}
</style>
