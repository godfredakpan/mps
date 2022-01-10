<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ $tc('income', 2) }}</p>
      <router-link to="/incomes/add" slot="extra">
        <Icon type="ios-grid-outline" />
        {{ $t('add') }} {{ $tc('income') }}
      </router-link>
      <div>
        <table-component
          url="app/incomes"
          :columns="columns"
          :options="options"
          :dblClickCB="viewModal"
          :bulkDelCB="deleteRecords"
          :refresh="refresh"
        ></table-component>
      </div>
    </Card>
    <Modal :title="$tc('income')" width="750" v-model="view" :footer-hide="true" :mask-closable="false" class="np-header-footer">
      <div v-if="loading" class="py16">
        <Loading />
      </div>
      <income-view-component :income="income" @remove="a => deleteAttachment(a, income)" />
    </Modal>
  </div>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
import IncomeViewComponent from './IncomeViewComponent';
export default {
  components: { IncomeViewComponent },
  mixins: [Table('income', 'app/incomes', 'title', 'reference')],
  data() {
    return {
      view: false,
      loading: false,
      income: { id: null, reference: null },
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('date'), sortable: true, key: 'date', sortType: 'desc', minWidth: 125, render: this.renderDate },
        { title: this.$t('title'), sortable: true, key: 'title', minWidth: 200 },
        { title: this.$t('reference'), className: 'reference', sortable: true, key: 'reference', width: 150 },
        { title: this.$t('amount'), sortable: true, key: 'amount', maxWidth: 125, minWidth: 125, render: this.renderAmount },
        { title: '🔗', align: 'center', sortable: false, key: 'attachments', maxWidth: 50, minWidth: 125, render: this.renderAttachments },
        { title: this.$tc('category'), sortable: false, key: 'categories', minWidth: 175, render: this.renderCategory },
        { title: this.$tc('account'), sortable: false, key: 'account', minWidth: 175, render: this.renderAccount },
        // { title: this.$tc('location'), sortable: false, key: 'location', minWidth: 175, render: this.renderLocation },
        { title: this.$tc('created_by'), sortable: false, key: 'user', minWidth: 175, render: this.renderUser },
        { title: this.$t('details'), sortable: true, key: 'details', ellipsis: true, minWidth: 300 },
        { title: this.$tc('field', 2), sortable: false, key: 'extra_attributes', minWidth: 250, render: this.renderExtras },
        { title: this.$t('created_at'), sortable: true, key: 'created_at', sortType: 'desc', minWidth: 175, render: this.renderDateTime },
        {
          width: 100,
          fixed: 'right',
          key: 'actions',
          align: 'center',
          title: this.$t('actions'),
          render: (h, params) => {
            return h('actions-component', {
              props: {
                params,
                viewFn: this.viewModal,
                editFn: this.editRecord,
                deleteFn: this.deleteRecord,
                record: { model: 'income', name: 'reference', title: this.$tc('income') },
              },
            });
          },
        },
      ],
      options: {
        perPage: this.$store.state.settings.rows,
        orderBy: ['date desc', 'created_at desc'],
      },
    };
  },
  created() {
    this.$event.listen('location:changed', id => this.refresh++);
  },
  methods: {
    viewModal(row) {
      this.view = true;
      if (row.id != this.income.id) {
        this.loading = true;
        this.$http
          .get(`app/incomes/${row.id}`)
          .then(res => (this.income = res.data))
          .finally(() => (this.loading = false));
      }
    },
    renderAmount(h, params) {
      return this.renderNumber(h, params, 'amount');
    },
    renderAccount(h, params) {
      return <div>{params.row.account.name}</div>;
    },
    renderLocation(h, params) {
      return <div>{params.row.location.name}</div>;
    },
    renderCategory(h, params) {
      return <div>{params.row.categories.map(c => c.name).join('')}</div>;
    },
    renderUser(h, params) {
      return <div>{params.row.user ? params.row.user.name : ''}</div>;
    },
  },
};
</script>
