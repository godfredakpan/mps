<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $tc('brand', 2) }}</p>
    <router-link to="/settings/brands/add" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('add') }} {{ $tc('brand') }}
    </router-link>
    <div>
      <table-component
        url="app/brands"
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
  mixins: [Table('brand', 'app/brands', 'name')],
  data() {
    return {
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('photo'), sortable: false, key: 'photo', width: 60, render: this.renderPhoto },
        { title: this.$t('name'), sortable: true, key: 'name', minWidth: 200 },
        { title: this.$tc('code'), sortable: true, key: 'code', sortType: 'asc', width: 150 },
        { title: this.$t('slug'), sortable: true, key: 'slug', minWidth: 200 },
        { title: this.$tc('order'), sortable: true, key: 'order', maxWidth: 100, minWidth: 80 },
        { title: this.$t('details'), sortable: true, key: 'details', ellipsis: true, minWidth: 250 },
        // { title: this.$t('created_at'), sortable: true, key: 'created_at', minWidth: 150 },
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
    renderBase(h, params) {
      return <div>{params.row.base_brand ? params.row.base_brand.name : ''}</div>;
    },
    renderOperator(h, params) {
      return <div>{params.row.operator ? this.$t(params.row.operator) : ''}</div>;
    },
  },
};
</script>
