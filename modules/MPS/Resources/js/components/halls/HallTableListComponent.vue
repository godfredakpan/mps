<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $tc('table', 2) }} {{ hall ? ' - ' + hall.label : '' }}</p>
    <span slot="extra">
      <router-link to="/settings/halls">
        <Icon type="ios-grid-outline" />
        {{ $t('list') }} {{ $tc('hall', 2) }}
      </router-link>
      <router-link :to="'/settings/halls/' + hall_id + '/tables/add'">
        <Icon type="ios-grid-outline" />
        {{ $t('add') }} {{ $tc('table') }}
      </router-link>
    </span>
    <div>
      <table-component
        :url="url"
        :columns="columns"
        :options="options"
        :dblClickCB="showInfo"
        :bulkDelCB="deleteRecords"
        :refresh="refresh"
      ></table-component>
    </div>
  </Card>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
export default {
  mixins: [Table('table', 'app/tables', 'title', 'code')],
  data() {
    return {
      url: null,
      hall: null,
      hall_id: null,
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('created_at'), sortable: true, key: 'created_at', sortType: 'asc', minWidth: 150, render: this.renderDateTime },
        { title: this.$tc('code'), sortable: true, key: 'code', width: 150 },
        { title: this.$t('title'), sortable: true, key: 'title', sortType: 'asc', minWidth: 200 },
        { title: this.$tc('hall', 2), sortable: false, key: 'halls', minWidth: 200, render: this.renderHall },
        { title: this.$t('details'), sortable: true, key: 'details', ellipsis: true, minWidth: 250 },
        { title: this.$tc('field', 2), sortable: false, key: 'extra_attributes', minWidth: 200, render: this.renderExtras },
        { title: this.$t('actions'), key: 'actions', align: 'center', fixed: 'right', width: 100, render: this.renderActions },
      ],
      options: {
        orderBy: 'created_at desc',
        perPage: this.$store.state.settings.rows,
      },
    };
  },
  created() {
    this.hall_id = this.$route.params.hall_id;
    this.url = 'app/tables?hall=' + this.hall_id;
    this.$http.get('app/halls/search?q=' + this.hall_id).then(res => (this.hall = res.data[0]));
  },
  methods: {
    renderHall(h, params) {
      return <div>{params.row.hall ? params.row.hall.title : ''}</div>;
    },
  },
};
</script>
