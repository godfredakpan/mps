<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $tc('field', 2) }}</p>
    <router-link to="/settings/fields/add" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('add') }} {{ $tc('field') }}
    </router-link>
    <div>
      <table-component
        url="app/fields"
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
  mixins: [Table('field', 'app/fields', 'slug', 'name')],
  data() {
    return {
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('name'), sortable: true, key: 'name', sortType: 'asc', minWidth: 150 },
        { title: this.$t('slug'), sortable: true, key: 'slug', minWidth: 120 },
        { title: this.$t('type'), sortable: true, key: 'type', minWidth: 100 },
        { title: this.$t('options'), sortable: true, key: 'options', minWidth: 200 },
        { title: this.$tc('order'), sortable: true, key: 'order', width: 100 },
        { title: this.$t('required'), sortable: true, key: 'required', width: 100, render: this.renderIcon },
        { title: this.$t('description'), sortable: true, key: 'description', minWidth: 200 },
        { title: this.$t('entities'), sortable: false, key: 'entities', minWidth: 200, render: this.renderEntities },
        { title: this.$t('actions'), key: 'actions', align: 'center', fixed: 'right', width: 100, render: this.renderActions },
      ],
      options: {
        orderBy: 'name',
        perPage: this.$store.state.settings.rows,
      },
    };
  },
  methods: {
    renderIcon(h, params) {
      return this.renderBoolean(h, params, 'required');
    },
    renderEntities(h, params) {
      return <div>{params.row.entities.map(e => this.capitalize(e)).join(', ')}</div>;
    },
  },
};
</script>
