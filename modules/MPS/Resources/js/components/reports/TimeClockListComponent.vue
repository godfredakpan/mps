<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $tc('time_clock', 2) }}</p>
    <router-link to="/reports" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $tc('report', 2) }}
    </router-link>
    <div>
      <table-component
        :url="url"
        :columns="columns"
        :options="options"
        :refresh="refresh"
        :dblClickCB="showInfo"
        :bulkDelCB="deleteRecords"
      />
    </div>
  </Card>
</template>

<script>
import Table from '@mpsjs/mixins/Table';

export default {
  mixins: [Table('time_clock', 'app/reports/time_clocks', 'id', 'id')],
  data() {
    return {
      url: 'app/reports/time_clocks',
      columns: [
        { title: this.$t('in'), sortable: true, key: 'in', minWidth: 150, render: this.renderIn },
        { title: this.$t('out'), sortable: true, key: 'out', minWidth: 150, render: this.renderOut },
        { title: this.$t('hours'), sortable: false, key: 'hours', width: 100, render: this.rendersHours },
        { title: this.$tc('rate'), sortable: false, key: 'rate', width: 100, render: this.renderRate },
        { title: this.$tc('amount'), sortable: false, key: 'amount', width: 100, render: this.rendersAmount },
        { title: this.$tc('user'), sortable: true, key: 'user', minWidth: 150, render: this.renderUser },
        { title: this.$tc('location'), sortable: true, key: 'location', minWidth: 150, render: this.renderLocation },
        { title: this.$t('details'), sortable: true, key: 'details', ellipsis: true, minWidth: 250 },
        { title: this.$t('created_at'), sortable: true, key: 'created_at', sortType: 'asc', minWidth: 150, render: this.renderCreatedAt },
      ],
      options: {
        orderBy: 'created_at desc',
        perPage: this.$store.state.settings.rows,
      },
    };
  },
  created() {
    // this.$event.listen('location:changed', id => this.refresh++);
    this.url = '/app/reports/time_clocks' + (this.$route.query.user_id ? '?user_id=' + this.$route.query.user_id : '');
  },
  methods: {
    renderLocation(h, params) {
      return <div>{params.row.location.name}</div>;
    },
    renderUser(h, params) {
      return <div>{params.row.user.name}</div>;
    },
    rendersHours(h, params) {
      if (!params.row.out) {
        return '';
      }
      let hours = parseFloat(Math.abs(new Date(params.row.in) - new Date(params.row.out)) / (1000 * 60 * 60));
      return <div class="text-right">{this.$options.filters.formatNumber(hours, this.$store.state.settings.decimals)}</div>;
    },
    rendersAmount(h, params) {
      if (!params.row.out) {
        return '';
      }
      let amount = parseFloat(Math.abs(new Date(params.row.in) - new Date(params.row.out)) / (1000 * 60 * 60)) * params.row.rate;
      return <div class="text-right">{this.$options.filters.formatNumber(amount, this.$store.state.settings.decimals)}</div>;
    },
    renderIn(h, params) {
      return (
        <div>
          {params.row.in ? this.$options.filters.formatDate(params.row.in, this.$store.state.settings.dateformat + ' HH:mm A') : ''}
        </div>
      );
    },
    renderOut(h, params) {
      return (
        <div>
          {params.row.out ? this.$options.filters.formatDate(params.row.out, this.$store.state.settings.dateformat + ' HH:mm A') : ''}
        </div>
      );
    },
    renderRate(h, params) {
      return <div class="text-right">{this.$options.filters.formatNumber(params.row.rate, this.$store.getters.settings.decimals)}</div>;
    },
  },
};
</script>
