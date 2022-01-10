<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ $tc('expense', 2) }}</p>
      <router-link to="/expenses/add" slot="extra">
        <Icon type="ios-grid-outline" />
        {{ $t('add') }} {{ $tc('expense') }}
      </router-link>
      <div>
        <table-component
          :url="url"
          :columns="columns"
          :options="options"
          :refresh="refresh"
          :dblClickCB="viewModal"
          :bulkDelCB="deleteRecords"
        ></table-component>
      </div>
    </Card>
    <Modal :title="$tc('expense')" width="750" v-model="view" :footer-hide="true" :mask-closable="false" class="np-header-footer">
      <div v-if="loading" class="py16">
        <Loading />
      </div>
      <expense-view-component :expense="expense" @remove="a => deleteAttachment(a, expense)" />
    </Modal>
  </div>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
import ExpenseViewComponent from './ExpenseViewComponent';
import ExpenseApprovalComponent from '@mpscom/helpers/ExpenseApprovalComponent';
export default {
  components: { ExpenseViewComponent },
  mixins: [Table('expense', 'app/expenses', 'title', 'reference')],
  data() {
    return {
      view: false,
      loading: false,
      expense: { id: null, reference: null },
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
        { title: this.$tc('recurring'), sortable: false, key: 'recurring', minWidth: 90, render: this.renderRecurring },
        { title: this.$tc('approved'), sortable: false, key: 'approved', minWidth: 90, render: this.renderApproved },
        { title: this.$tc('approval'), sortable: false, key: 'approved_by', minWidth: 175, render: this.renderApproval },
        { title: this.$t('details'), sortable: true, key: 'details', ellipsis: true, minWidth: 250 },
        { title: this.$tc('field', 2), sortable: false, key: 'extra_attributes', minWidth: 250, render: this.renderExtras },
        {
          width: 105,
          sortable: true,
          sortType: 'desc',
          key: 'start_date',
          render: this.renderStartDate,
          title: this.$t('start_date'),
        },
        { title: this.$t('repeat'), sortable: true, key: 'repeat', width: 125, render: this.renderRepeat },
        { title: this.$t('before'), sortable: true, key: 'create_before', width: 90 },
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
                record: { model: 'expense', name: 'reference', title: this.$tc('expense') },
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
  computed: {
    url() {
      return (
        'app/expenses' + (this.$route.query.require_approval ? '?require_approval=' + parseInt(this.$route.query.require_approval) : '')
      );
    },
  },
  watch: {
    '$route.query.require_approval': function(v) {
      if (v) {
        this.columns.splice(1, 0, {
          width: 50,
          title: '✓',
          fixed: 'left',
          key: 'approve',
          align: 'center',
          render: this.renderApprovalNow,
        });
      } else {
        this.columns = this.columns.filter(c => c.key != 'approve');
      }
      this.refresh++;
    },
  },
  created() {
    if (this.$route.query.require_approval) {
      this.columns.splice(1, 0, {
        title: '✓',
        width: 50,
        key: 'approve',
        align: 'center',
        fixed: 'left',
        render: this.renderApprovalNow,
      });
    } else {
      this.columns = this.columns.filter(c => c.key != 'approve');
    }
    this.$event.listen('location:changed', id => this.refresh++);
  },
  methods: {
    viewModal(row) {
      this.view = true;
      if (row.id != this.expense.id) {
        this.loading = true;
        this.$http
          .get(`app/expenses/${row.id}`)
          .then(res => (this.expense = res.data))
          .finally(() => (this.loading = false));
      }
    },
    reloadExpenses() {
      this.refresh++;
    },
    renderApprovalNow(h, params) {
      return h(ExpenseApprovalComponent, { props: { expense: params.row, updateFn: this.reloadExpenses } });
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
    renderRecurring(h, params) {
      return this.renderBoolean(h, params, 'recurring');
    },
    renderApproved(h, params) {
      return this.renderBoolean(h, params, 'approved');
    },
    renderApproval(h, params) {
      return <div>{params.row.approved_by ? params.row.approved_by.name : ''}</div>;
    },
    renderUser(h, params) {
      return <div>{params.row.user ? params.row.user.name : ''}</div>;
    },
    renderStartDate(h, params) {
      return <div>{params.row.start_date ? this.date(params.row.start_date) : ''}</div>;
    },
    renderRepeat(h, params) {
      return (
        <div class="text-center">
          {params.row.repeat ? (
            <div class={`ivu-tag ivu-tag-${this.repeatColor(params.row.repeat)} ivu-tag-checked`}>
              <span class="ivu-tag-text ivu-tag-color-white">{this.$t(params.row.repeat)}</span>
            </div>
          ) : (
            ''
          )}
        </div>
      );
    },
    repeatColor(gateway) {
      let colors = {
        daily: 'blue',
        weekly: 'magenta',
        monthly: 'primary',
        quarterly: 'geekblue',
        semiannually: 'purple',
        annually: 'success',
        biennially: 'green',
        triennially: 'orange',
      };
      if (colors[gateway]) {
        return colors[gateway];
      }
      return 'default';
    },
  },
};
</script>
