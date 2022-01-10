<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ $tc('salary', 2) }}</p>
      <span slot="extra">
        <router-link to="/users/salaries/add" slot="extra">
          <Icon type="ios-grid-outline" />
          {{ $t('add') }} {{ $tc('salary') }}
        </router-link>
        <Button type="primary" :loading="loading" :disabled="loading" size="small" @click="generateSalaries()">
          <Icon type="ios-sync" />
          {{ $t('generate_x', { x: $tc('salary', 2) }) }}
        </Button>
      </span>
      <div>
        <Alert v-html="message.replaceAll('\n', '<br />')" v-if="message" />
        <table-component
          :url="url"
          :columns="columns"
          :options="options"
          :refresh="refresh"
          :dblClickCB="viewModal"
          :bulkDelCB="deleteRecords"
        />
      </div>
    </Card>
    <Modal
      width="750"
      v-model="view"
      :footer-hide="true"
      :mask-closable="false"
      class="np-header-footer"
      :title="$tc('salary') + ' (' + salary.reference + ')'"
    >
      <div v-if="loading" class="py16">
        <Loading />
      </div>
      <salary-view-component :salary="salary" @remove="a => deleteAttachment(a, salary)" />
    </Modal>
  </div>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
import SalaryViewComponent from './SalaryViewComponent';
export default {
  components: { SalaryViewComponent },
  mixins: [Table('salary', 'app/salaries', 'name')],
  data() {
    return {
      salary: {},
      view: false,
      message: null,
      loading: false,
      url: 'app/salaries',
      columns: [
        { title: this.$t('date'), sortable: true, sortType: 'desc', key: 'date', width: 100, render: this.renderDate },
        { title: this.$t('created_at'), sortable: true, key: 'created_at', width: 175, render: this.renderCreatedAt },
        { title: this.$t('amount'), sortable: true, key: 'amount', minWidth: 100, render: this.renderAmount },
        { title: this.$t('advance'), sortable: true, key: 'advance', width: 100, render: this.renderAdvance },
        { title: this.$t('type'), sortable: true, key: 'type', width: 100, render: this.renderType },
        { title: this.$t('status'), sortable: true, key: 'status', width: 100, render: this.renderStatus },
        { title: this.$tc('user'), sortable: true, key: 'user', minWidth: 175, render: this.renderUser },
        { title: this.$tc('account'), sortable: true, key: 'account', minWidth: 175, render: this.renderAccount },
        { title: this.$t('details'), sortable: false, key: 'details', ellipsis: true, width: 350 },
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
                record: { model: 'salary', name: 'reference' },
              },
            });
          },
        },
      ],
      options: {
        orderBy: 'date desc',
        perPage: this.$store.state.settings.rows,
      },
    };
  },
  created() {
    this.url = 'app/salaries' + (this.$route.query.user_id ? '?user_id=' + this.$route.query.user_id : '');
  },
  methods: {
    viewModal(row) {
      this.view = true;
      if (row.id != this.salary.id) {
        this.loading = true;
        this.$http
          .get(`app/salaries/${row.id}`)
          .then(res => (this.salary = res.data))
          .finally(() => (this.loading = false));
      }
    },
    renderAmount(h, params) {
      return this.renderNumber(h, params, 'amount');
    },
    renderAdvance(h, params) {
      return this.renderBoolean(h, params, 'advance');
    },
    renderType(h, params) {
      return (
        <div class="text-center">
          {params.row.type ? (
            <div class={`ivu-tag ivu-tag-${params.row.type == 'salary' ? 'primary' : 'purple'} ivu-tag-checked`}>
              <span class="ivu-tag-text ivu-tag-color-white">{this.$tc(params.row.type)}</span>
            </div>
          ) : (
            ''
          )}
        </div>
      );
    },
    renderStatus(h, params) {
      return (
        <div class="text-center">
          {params.row.status ? (
            <div class={`ivu-tag ivu-tag-${params.row.status == 'paid' ? 'success' : 'warning'} ivu-tag-checked`}>
              <span class="ivu-tag-text ivu-tag-color-white">{this.$t(params.row.status)}</span>
            </div>
          ) : (
            ''
          )}
        </div>
      );
    },
    renderAccount(h, params) {
      return <div>{params.row.account ? params.row.account.name : ''}</div>;
    },
    renderUser(h, params) {
      return <div>{params.row.user ? params.row.user.name : ''}</div>;
    },
    generateSalaries() {
      this.loading = true;
      this.$http
        .post('/app/salaries/generate')
        .then(res => {
          if (res.data.success) {
            this.message = res.data.message;
            this.$Notice.success({ title: this.$t('success'), desc: res.data.message || this.$t('record_updated'), duration: 10 });
          }
        })
        .catch(error => {
          this.$Notice.error({ title: this.$t('failed'), desc: error.message, duration: 10 });
        })
        .finally(() => (this.loading = false));
    },
  },
};
</script>
