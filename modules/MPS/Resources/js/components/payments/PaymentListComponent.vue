<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ $tc('payment', 2) }}</p>
      <router-link to="/payments/add" slot="extra">
        <Icon type="ios-grid-outline" />
        {{ $t('add_x', { x: $tc('payment') }) }}
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
    <Modal
      width="750"
      v-model="view"
      :footer-hide="true"
      :mask-closable="false"
      class="np-header-footer"
      :title="$tc('payment') + ' (' + payment.reference + ')'"
    >
      <div v-if="loading" class="py16">
        <Loading />
      </div>
      <payment-view-component :payment="payment" :review="reviewRecord" @remove="a => deleteAttachment(a, payment)" />
    </Modal>
  </div>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
import PaymentViewComponent from './PaymentViewComponent';
export default {
  components: { PaymentViewComponent },
  mixins: [Table('payment', 'app/payments', 'reference')],
  data() {
    return {
      view: false,
      loading: false,
      payment: {},
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
        { title: this.$t('reference'), className: 'reference', sortable: true, key: 'reference', width: 150 },
        { title: this.$t('amount'), sortable: false, key: 'amount', width: 150, render: this.renderAmount },
        { title: '🔗', align: 'center', sortable: false, key: 'attachments', maxWidth: 50, minWidth: 125, render: this.renderAttachments },
        { title: this.$t('gateway'), sortable: false, key: 'gateway', width: 150, render: this.renderGateway },
        {
          key: 'for',
          maxWidth: 300,
          minWidth: 200,
          sortable: false,
          title: this.$tc('account'),
          render: this.renderAccount,
        },
        {
          key: 'for',
          maxWidth: 300,
          minWidth: 200,
          sortable: false,
          render: this.renderPayable,
          title: this.$tc('created_for'),
        },
        {
          width: 175,
          key: 'user_id',
          sortable: false,
          render: this.renderUser,
          title: this.$t('created_by'),
        },
        { title: this.$t('received'), sortable: true, key: 'received', width: 100, render: this.renderIconReceived },
        { title: this.$t('details'), sortable: false, key: 'details', ellipsis: true, ellipsis: true, minWidth: 250, maxWidth: 400 },
        {
          width: 100,
          fixed: 'right',
          key: 'actions',
          align: 'center',
          title: this.$t('actions'),
          render: (h, params) => {
            return h('actions-dropdown-component', {
              props: {
                params,
                viewFn: this.viewModal,
                editFn: this.editRecord,
                emailFn: this.emailRecord,
                reviewFn: this.reviewRecord,
                deleteFn: this.deleteRecord,
                downloadFn: this.downloadRecord,
                record: { model: 'payment', name: 'reference', title: this.$tc('payment') },
              },
            });
          },
        },
      ],
      options: {
        orderBy: 'created_at desc',
        perPage: this.$store.state.settings.rows,
      },
    };
  },
  computed: {
    url() {
      return (
        'app/payments' +
        (this.$route.query.review
          ? '?review=' + parseInt(this.$route.query.review)
          : this.$route.query.due
          ? '?due=' + parseInt(this.$route.query.due)
          : '')
      );
    },
  },
  watch: {
    '$route.query.due': function() {
      this.refresh++;
    },
    '$route.query.review': function() {
      this.refresh++;
    },
  },
  created() {
    this.$event.listen('location:changed', id => this.refresh++);
  },
  methods: {
    viewModal(row) {
      this.view = true;
      if (row.id != this.payment.id) {
        this.loading = true;
        this.$http
          .get(`app/payments/${row.id}`)
          .then(res => (this.payment = res.data))
          .finally(() => (this.loading = false));
      }
    },
    emailRecord(row) {
      this.$http.post('app/payments/email/' + row.id, {}).then(res => {
        if (res.data.success) {
          this.$Notice.success({ title: this.$t('email_sent'), desc: this.$t('email_sent_text') });
        } else {
          this.$Notice.error({ title: this.$tc('failed'), desc: this.$t('failed_error_text') });
        }
      });
    },
    downloadRecord(row) {
      // window.open('/download/payments/' + row.id);
      Object.assign(document.createElement('a'), { target: '_blank', href: window.mpsURL + 'download/payments/' + row.id }).click();
    },
    reviewRecord(row) {
      if (row.review == 1 && !row.reviewed_by && !row.received) {
        this.$http.post('app/payments/review/' + row.id, {}).then(res => {
          if (res.data.success) {
            this.refresh++;
            this.view = false;
            this.payment = {};
            this.$Notice.success({ title: this.$t('success'), desc: this.$t('review_payment_text') });
          } else {
            this.$Notice.error({ title: this.$tc('failed'), desc: this.$t('failed_error_text') });
          }
        });
      }
    },
    viewRecord(row) {
      // window.open('/view/payment/' + row.hash);
      Object.assign(document.createElement('a'), { target: '_blank', href: window.mpsURL + 'view/payment/' + row.hash }).click();
    },
    renderAccount(h, params) {
      return <div>{params.row.account ? params.row.account.name : ''}</div>;
    },
    gatewayColor(gateway) {
      let colors = { cash: 'success', credit_card: 'geekblue', cheque: 'green', other: 'purple' };
      if (colors[gateway]) {
        return colors[gateway];
      }
      return 'primary';
    },
    renderGateway(h, params) {
      return (
        <div class="text-center">
          {params.row.gateway ? (
            <div class={`ivu-tag ivu-tag-${this.gatewayColor(params.row.gateway)} ivu-tag-checked`}>
              <span class="ivu-tag-text ivu-tag-color-white">{this.$t(params.row.gateway)}</span>
            </div>
          ) : (
            ''
          )}
        </div>
      );
      // return <div>{this.$t(params.row.gateway)}</div>;
      // <div class="ivu-tag ivu-tag-size-default ivu-tag-default ivu-tag-border ivu-tag-checked"><span class="ivu-tag-text ivu-tag-color-default"></span></div>
    },
    renderPayable(h, params) {
      return <div>{params.row.payable.name}</div>;
    },
    renderAmount(h, params) {
      return this.renderNumber(h, params, 'amount');
    },
    renderIconReceived(h, params) {
      return this.renderBoolean(h, params, 'received');
    },
  },
};
</script>
