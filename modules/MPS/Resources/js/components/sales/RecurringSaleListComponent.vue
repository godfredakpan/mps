<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ $tc('recurring_sale', 2) }}</p>
      <router-link to="/sales/recurring/add" slot="extra">
        <Icon type="ios-grid-outline" />
        {{ $t('add') }} {{ $tc('recurring_sale') }}
      </router-link>
      <div>
        <table-component
          :url="url"
          :columns="columns"
          :options="options"
          :dblClickCB="viewModal"
          :bulkDelCB="deleteRecords"
          :refresh="refresh"
        ></table-component>
      </div>
    </Card>
    <Modal
      width="750"
      v-model="view"
      :footer-hide="true"
      :mask-closable="false"
      class="np-header-footer"
      :title="$tc('recurring_sale') + ' (' + recurring_sale.reference + ')'"
    >
      <div v-if="loading" class="py16">
        <Loading />
      </div>
      <order-view-component
        :summary="false"
        :recurring="true"
        :record="recurring_sale"
        :to-text="$tc('bill_to')"
        :heading="$tc('recurring_sale')"
        @remove="a => deleteAttachment(a, recurring_sale)"
      />
    </Modal>
  </div>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
import OrderViewComponent from '@mpscom/helpers/OrderViewComponent';
export default {
  components: { OrderViewComponent },
  mixins: [Table('recurring_sale', 'app/recurring_sales', 'reference')],
  data() {
    return {
      view: false,
      loading: false,
      recurring_sale: {},
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        {
          width: 105,
          sortable: true,
          sortType: 'desc',
          key: 'start_date',
          render: this.renderStartDate,
          title: this.$t('start_date'),
        },
        { title: this.$t('repeat'), sortable: true, key: 'repeat', width: 125, render: this.renderRepeat },
        { title: this.$t('reference'), className: 'reference', sortable: true, key: 'reference', width: 150 },
        { title: this.$t('before'), sortable: true, key: 'create_before', width: 90 },
        {
          title: this.$tc('customer'),
          sortable: false,
          key: 'customer',
          maxWidth: 300,
          minWidth: 200,
          render: this.renderCustomer,
        },
        { title: this.$t('draft'), sortable: true, key: 'draft', width: 80, render: this.renderIconDraft },
        { title: this.$t('grand_total'), sortable: false, key: 'grand_total', width: 150, render: this.renderGrandTotal },
        { title: '🔗', align: 'center', sortable: false, key: 'attachments', maxWidth: 50, minWidth: 125, render: this.renderAttachments },
        {
          width: 175,
          sortable: true,
          sortType: 'desc',
          key: 'created_at',
          title: this.$t('created_at'),
          render: this.renderCreatedAt,
        },
        {
          width: 175,
          sortable: true,
          sortType: 'desc',
          key: 'last_created_at',
          title: this.$t('last_created_at'),
          render: this.renderLastCreatedAt,
        },
        {
          width: 175,
          key: 'user_id',
          sortable: false,
          render: this.renderUser,
          title: this.$t('created_by'),
        },
        { title: this.$t('total'), sortable: false, key: 'total', width: 150, render: this.renderTotal },
        { title: this.$t('item_tax'), sortable: false, key: 'item_tax', width: 150, render: this.renderItemTax },
        { title: this.$t('order_tax'), sortable: false, key: 'order_tax', width: 150, render: this.renderOrderTax },
        { title: this.$t('discount'), sortable: false, key: 'discount', width: 150, render: this.renderDiscount },
        { title: this.$t('shipping'), sortable: false, key: 'shipping', width: 150, render: this.renderShipping },
        { title: this.$t('grand_total'), sortable: false, key: 'grand_total', width: 150, render: this.renderGrandTotal },
        { title: this.$t('details'), sortable: false, key: 'details', ellipsis: true, minWidth: 200, maxWidth: 300 },
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
                // emailFn: this.emailRecord,
                deleteFn: this.deleteRecord,
                record: { model: 'recurring_sale', name: 'reference', title: this.$tc('recurring_sale') },
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
      return 'app/recurring_sales' + (this.$route.query.in_days ? '?in_days=' + parseInt(this.$route.query.due) : '');
    },
  },
  watch: {
    '$route.query.in_days': function() {
      this.refresh++;
    },
  },
  created() {
    this.$event.listen('location:changed', id => this.refresh++);
  },
  methods: {
    viewModal(row) {
      this.view = true;
      if (row.id != this.recurring_sale.id) {
        this.loading = true;
        this.$http
          .get(`app/recurring_sales/${row.id}`)
          .then(res => (this.recurring_sale = res.data))
          .finally(() => (this.loading = false));
      }
    },
    emailRecord(row) {
      console.log('sending email for ' + row.id);
    },
    viewRecord(row) {
      // window.open('/view/recurring_sale/' + row.hash);
      Object.assign(document.createElement('a'), {
        target: '_blank',
        href: window.mpsURL + 'view/recurring_sale/' + row.hash,
      }).click();
    },
    renderCustomer(h, params) {
      return <div>{params.row.customer.name}</div>;
    },
    renderStartDate(h, params) {
      return <div>{this.$options.filters.formatDate(params.row.start_date, this.$store.state.settings.dateformat)}</div>;
    },
    renderLastCreatedAt(h, params) {
      return (
        <div>
          {params.row.last_created_at
            ? this.$options.filters.formatDate(params.row.last_created_at, this.$store.state.settings.dateformat + ' HH:mm A')
            : ''}
        </div>
      );
    },
    renderTotal(h, params) {
      return this.renderNumber(h, params, 'total');
    },
    renderItemTax(h, params) {
      return this.renderNumber(h, params, 'item_tax_amount');
    },
    renderOrderTax(h, params) {
      return this.renderNumber(h, params, 'order_tax_amount');
    },
    renderDiscount(h, params) {
      return this.renderNumber(h, params, 'discount_amount');
    },
    renderShipping(h, params) {
      return this.renderNumber(h, params, 'shipping');
    },
    renderGrandTotal(h, params) {
      return this.renderNumber(h, params, 'grand_total', true);
    },
    renderIconActive(h, params) {
      return this.renderUnlessIcon(h, params, 'draft');
    },
    renderIconDraft(h, params) {
      return this.renderBoolean(h, params, 'draft');
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
    renderRepeat(h, params) {
      return (
        <div class="text-center">
          <div class={`ivu-tag ivu-tag-${this.repeatColor(params.row.repeat)} ivu-tag-checked`}>
            <span class="ivu-tag-text ivu-tag-color-white">{this.$t(params.row.repeat)}</span>
          </div>
        </div>
      );
      // return <div>{this.$t(params.row.gateway)}</div>;
    },
  },
};
</script>
