<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $tc('gift_card_log', 2) }}</p>
    <router-link to="/gift_cards" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list_x', { x: $tc('gift_card', 2) }) }}
    </router-link>
    <div>
      <table-component :url="url" :columns="columns" :options="options" :refresh="refresh"></table-component>
    </div>
  </Card>
</template>

<script>
export default {
  data() {
    return {
      refresh: 1,
      columns: [
        // { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('created_at'), sortable: true, sortType: 'desc', key: 'created_at', width: 175, render: this.renderCreatedAt },
        { title: this.$tc('amount'), sortable: true, key: 'amount', minWidth: 100, maxWidth: 150, render: this.renderAmount },
        { title: this.$t('description'), sortable: true, key: 'description', minWidth: 250 },
        // { title: this.$t('actions'), key: 'actions', align: 'center', fixed: 'right', width: 100, render: this.renderActions },
      ],
      options: {
        orderBy: 'created_at desc',
        perPage: this.$store.state.settings.rows,
      },
    };
  },
  computed: {
    url() {
      return `app/gift_cards/logs/${this.$route.params.id ? this.$route.params.id : ''}`;
    },
  },
  methods: {
    renderCreatedAt(h, params) {
      return <div>{this.$options.filters.formatDate(params.row.created_at, this.$store.state.settings.dateformat + ' HH:mm A')}</div>;
    },
    renderAmount(h, params) {
      return <div class="text-right">{this.$options.filters.formatNumber(params.row.amount, this.$store.getters.settings.decimals)}</div>;
    },
  },
};
</script>
