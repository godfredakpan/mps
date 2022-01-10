<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $tc('promo', 2) }}</p>
    <router-link to="/settings/promos/add" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('add') }} {{ $tc('promo') }}
    </router-link>
    <div>
      <table-component
        url="app/promos"
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
  mixins: [Table('promo', 'app/promos', 'reference')],
  data() {
    return {
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        {
          width: 175,
          sortable: true,
          sortType: 'desc',
          key: 'created_at',
          title: this.$t('created_at'),
          render: this.renderCreatedAt,
        },
        { title: this.$t('type'), sortable: true, key: 'type', width: 100 },
        { title: this.$t('name'), sortable: true, key: 'name', width: 150 },
        { title: this.$t('active'), sortable: true, key: 'active', width: 80, render: this.renderIconActive },
        { title: this.$t('start_date'), sortable: true, key: 'start_date', width: 110, render: this.renderDate },
        { title: this.$t('end_date'), sortable: true, key: 'end_date', width: 110, render: this.renderDate },
        { title: this.$t('discount'), sortable: true, key: 'discount', width: 110, render: this.renderDiscount },
        // { title: this.$t('discount_method'), sortable: true, key: 'discount_method', width: 150 },
        { title: this.$t('item_to_buy'), sortable: true, key: 'item_to_buy', width: 150, render: this.renderItem2Buy },
        { title: this.$t('quantity_to_buy'), sortable: true, key: 'quantity_to_buy', width: 140, render: this.renderQty2Buy },
        { title: this.$t('item_to_get'), sortable: true, key: 'item_to_get', width: 150, render: this.renderItem2Get },
        { title: this.$t('quantity_to_get'), sortable: true, key: 'quantity_to_get', width: 140, render: this.renderQty2Get },
        { title: this.$t('details'), sortable: false, key: 'details', ellipsis: true, ellipsis: true, minWidth: 250, maxWidth: 400 },
        { title: this.$t('actions'), key: 'actions', align: 'center', fixed: 'right', width: 100, render: this.renderActions },
      ],
      options: {
        orderBy: 'created_at desc',
        perPage: this.$store.state.settings.rows,
      },
    };
  },
  methods: {
    renderDiscount(h, params) {
      return this.renderNumber(h, params, 'discount', false, false, '%');
    },
    renderItem2Buy(h, params) {
      return <div>{params.row.item_to_buy ? params.row.item_to_buy.name : ''}</div>;
    },
    renderItem2Get(h, params) {
      return <div>{params.row.item_to_get ? params.row.item_to_get.name : ''}</div>;
    },
    renderQty2Buy(h, params) {
      return params.row.quantity_to_buy ? this.renderNumber(h, params, 'quantity_to_buy') : '';
    },
    renderQty2Get(h, params) {
      return params.row.quantity_to_get ? this.renderNumber(h, params, 'quantity_to_get') : '';
    },
    renderIconActive(h, params) {
      return this.renderBoolean(h, params, 'active');
    },
  },
};
</script>
