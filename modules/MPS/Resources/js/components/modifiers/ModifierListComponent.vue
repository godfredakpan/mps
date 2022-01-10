<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $tc('modifier', 2) }}</p>
    <router-link to="/modifiers/add" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('add') }} {{ $tc('modifier') }}
    </router-link>
    <div>
      <table-component
        :columns="columns"
        :options="options"
        :refresh="refresh"
        url="app/modifiers"
        :dblClickCB="showInfo"
        :bulkDelCB="deleteRecords"
      ></table-component>
    </div>
  </Card>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
export default {
  mixins: [Table('modifier', 'app/modifiers', 'title', 'reference')],
  data() {
    return {
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('created_at'), sortable: true, key: 'created_at', sortType: 'asc', width: 175, render: this.renderCreatedAt },
        { title: this.$t('code'), sortable: true, key: 'code', minWidth: 150 },
        { title: this.$t('title'), sortable: true, key: 'title', minWidth: 200 },
        { title: this.$tc('show_as'), sortable: true, key: 'show_as', width: 200, render: this.renderShowAs },
        // { title: this.$t('options'), sortable: true, key: 'options', minWidth: 450, render: this.renderOptions },
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
  methods: {
    renderShowAs(h, params) {
      return <div>{this.$t(params.row.show_as)}</div>;
    },
    renderOptions(h, params) {
      let opts = [];
      if (params.row.options && params.row.options.length) {
        opts = params.row.options.map(o => {
          return `${this.$t('name')}: ${o.name},
                            ${this.$t('cost')}: ${o.cost},
                            ${this.$t('price')}: ${o.price},
                            ${this.$t('quantity')}: ${o.quantity}`;
        });
      }
      console.log(opts);
      return <div>{opts}</div>;
    },
  },
};
</script>
