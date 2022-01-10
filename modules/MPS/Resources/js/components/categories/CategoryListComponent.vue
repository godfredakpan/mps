<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $tc('category', 2) }}</p>
    <router-link to="/settings/categories/add" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('add') }} {{ $tc('category') }}
    </router-link>
    <div>
      <table-component
        url="app/categories"
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
  mixins: [Table('category', 'app/categories', 'code', 'name')],
  data() {
    return {
      refresh: 1,
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('photo'), sortable: false, key: 'photo', width: 60, render: this.renderPhoto },
        { title: this.$t('code'), sortable: true, key: 'code', width: 200, sortType: 'asc' },
        { title: this.$t('name'), sortable: true, key: 'name', minWidth: 200 },
        { title: this.$t('slug'), sortable: true, key: 'slug', minWidth: 200 },
        { title: this.$t('parent_category'), sortable: true, key: 'name', minWidth: 200, render: this.renderParent },
        { title: this.$t('actions'), key: 'actions', align: 'center', fixed: 'right', width: 100, render: this.renderActions },
      ],
      options: {
        orderBy: 'code',
        perPage: this.$store.state.settings.rows,
      },
    };
  },
  methods: {
    renderPhoto(h, params) {
      return params.row.photo ? (
        <div style="display:flex;align-items:center;justify-center:center;">
          <img src={params.row.photo} alt="" style="max-width:100%;max-height:100%;border-radius:2px" />
        </div>
      ) : (
        ''
      );
    },
    renderParent(h, params) {
      return <div>{params.row.parent ? params.row.parent.name : ''}</div>;
    },
  },
};
</script>
