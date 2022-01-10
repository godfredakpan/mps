<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ $tc('register', 2) }}</p>
      <router-link to="/reports" slot="extra">
        <Icon type="ios-grid-outline" />
        {{ $tc('report', 2) }}
      </router-link>
      <div>
        <table-component
          :columns="columns"
          :options="options"
          :dblClickCB="showInfo"
          url="app/reports/registers"
          :bulkDelCB="deleteRecords"
          :refresh="refresh"
        ></table-component>
      </div>
    </Card>
    <Modal v-model="recordModal" :title="$t('register_details')" :mask-closable="false" :footer-hide="true">
      <pos-register-component :record="record" :close="closeRecordModal" />
    </Modal>
  </div>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
import PosRegisterComponent from './PosRegisterComponent';
export default {
  components: { PosRegisterComponent },
  mixins: [Table('register', 'app/reports/registers', 'id', 'id')],
  data() {
    return {
      columns: [
        { title: this.$t('created_at'), sortable: true, key: 'created_at', sortType: 'asc', minWidth: 175, render: this.renderCreatedAt },
        { title: this.$tc('register'), sortable: true, key: 'register', width: 150, render: this.renderRegister },
        { title: this.$tc('user'), sortable: true, key: 'user', width: 150, render: this.renderUser },
        { title: this.$tc('cash_in_hand'), sortable: false, key: 'cash_in_hand', width: 125, render: this.renderCashInHand },
        {
          width: 175,
          sortable: false,
          key: 'total_cash_submitted',
          render: this.renderTotalCash,
          title: this.$t('x_sales', { x: this.$t('cash') }),
        },
        {
          width: 175,
          sortable: false,
          key: 'total_cheque_submitted',
          render: this.renderTotalCheque,
          title: this.$t('x_sales', { x: this.$t('cheque') }),
        },
        {
          width: 175,
          sortable: false,
          key: 'total_cc_slips_submitted',
          render: this.renderTotalCreditCard,
          title: this.$t('x_sales', { x: this.$t('credit_card') }),
        },
        {
          width: 125,
          sortable: false,
          key: 'total_gift_card_amount',
          render: this.renderTotalGiftCard,
          title: this.$t('x_sales', { x: this.$tc('gift_card') }),
        },
        {
          width: 125,
          sortable: false,
          key: 'total_other_amount',
          render: this.renderTotalOther,
          title: this.$t('x_sales', { x: this.$t('other') }),
        },
        {
          width: 125,
          sortable: false,
          title: this.$tc('expense', 2),
          key: 'total_expenses_amount',
          render: this.renderTotalExpenses,
        },
        {
          width: 125,
          sortable: false,
          key: 'total_return_orders_amount',
          title: this.$tc('return_order', 2),
          render: this.renderTotalReturnOrder,
        },
        {
          width: 125,
          sortable: false,
          key: 'total_refunds_amount',
          title: this.$tc('refund', 2),
          render: this.renderTotalRefunds,
        },
        { title: this.$t('closed_at'), sortable: true, key: 'closed_at', sortType: 'asc', minWidth: 175, render: this.renderClosedAt },
        { title: this.$t('closed_by'), sortable: true, key: 'closed_by', sortType: 'asc', minWidth: 175, render: this.renderClosedBy },
        // { title: this.$t('actions'), key: 'actions', align: 'center', fixed: 'right', width: 75, render: this.renderActions },
        {
          width: 75,
          fixed: 'right',
          key: 'actions',
          align: 'center',
          title: this.$t('actions'),
          render: (h, params) => {
            return h('actions-component', {
              props: {
                params,
                viewFn: this.viewRecord,
                record: { model: 'register', name: 'date', title: this.$tc('register') },
              },
            });
          },
        },
      ],
      options: {
        orderBy: 'created_at desc',
        perPage: this.$store.state.settings.rows,
      },
      record: null,
      recordModal: false,
    };
  },
  // created() {
  //   this.$event.listen('location:changed', id => this.refresh++);
  // },
  methods: {
    viewRecord(row) {
      this.record = row;
      this.recordModal = true;
      // window.open('/view/sale/' + row.hash);
      // Object.assign(document.createElement('a'), { target: '_blank', href: window.mpsURL + 'view/register/' + row.id }).click();
    },
    closeRecordModal() {
      this.record = null;
      this.recordModal = false;
    },
    renderUser(h, params) {
      return <div>{params.row.user.name}</div>;
    },
    renderRegister(h, params) {
      return (
        <div>
          <div>{params.row.register.name}</div>
          <div>{params.row.location.name}</div>
        </div>
      );
    },
    renderClosedAt(h, params) {
      return (
        <div>
          {params.row.closed_at
            ? this.$options.filters.formatDate(params.row.closed_at, this.$store.state.settings.dateformat + ' HH:mm A')
            : ''}
        </div>
      );
    },
    renderClosedBy(h, params) {
      return <div>{params.row.closed_by_user ? params.row.closed_by_user.name : ''}</div>;
    },
    renderCashInHand(h, params) {
      return (
        <div class="text-right">{this.$options.filters.formatNumber(params.row.cash_in_hand, this.$store.getters.settings.decimals)}</div>
      );
    },
    renderTotalOther(h, params) {
      return (
        <div class="text-right">
          {params.row.closed_at
            ? this.$options.filters.formatNumber(params.row.total_other_amount, this.$store.getters.settings.decimals)
            : ''}
        </div>
      );
    },
    renderTotalGiftCard(h, params) {
      return (
        <div class="text-right">
          {params.row.closed_at
            ? this.$options.filters.formatNumber(params.row.total_gift_card_amount, this.$store.getters.settings.decimals)
            : ''}
        </div>
      );
    },
    renderTotalExpenses(h, params) {
      return (
        <div class="text-right">
          {params.row.closed_at
            ? this.$options.filters.formatNumber(params.row.total_expenses_amount, this.$store.getters.settings.decimals)
            : ''}
        </div>
      );
    },
    renderTotalRefunds(h, params) {
      return (
        <div class="text-right">
          {params.row.closed_at
            ? this.$options.filters.formatNumber(params.row.total_refunds_amount, this.$store.getters.settings.decimals)
            : ''}
        </div>
      );
    },
    renderTotalReturnOrder(h, params) {
      return (
        <div class="text-right">
          {params.row.closed_at
            ? this.$options.filters.formatNumber(params.row.total_return_orders_amount, this.$store.getters.settings.decimals)
            : ''}
        </div>
      );
    },
    renderTotalCash(h, params) {
      return params.row.closed_at ? (
        <table class="table table-sm">
          <tr>
            <td>{this.$t('total')}:</td>
            <td class="text-right">
              {this.$options.filters.formatNumber(params.row.total_cash_amount, this.$store.getters.settings.decimals)}
            </td>
          </tr>
          <tr>
            <td>{this.$t('submitted')}:</td>
            <td class="text-right">
              {this.$options.filters.formatNumber(params.row.total_cash_submitted, this.$store.getters.settings.decimals)}
            </td>
          </tr>
        </table>
      ) : (
        ''
      );
    },
    renderTotalCheque(h, params) {
      return params.row.closed_at ? (
        <table class="table table-sm">
          <tr>
            <td>{this.$t('total')}:</td>
            <td class="text-right">
              {this.$options.filters.formatNumber(params.row.total_cheques, this.$store.getters.settings.decimals)}
            </td>
          </tr>
          <tr>
            <td>{this.$t('submitted')}:</td>
            <td class="text-right">
              {this.$options.filters.formatNumber(params.row.total_cheques_submitted, this.$store.getters.settings.decimals)}
            </td>
          </tr>
        </table>
      ) : (
        ''
      );
    },
    renderTotalCreditCard(h, params) {
      return params.row.closed_at ? (
        <table class="table table-sm">
          <tr>
            <td>{this.$t('total')}:</td>
            <td class="text-right">
              {this.$options.filters.formatNumber(params.row.total_cc_slips, this.$store.getters.settings.decimals)}
            </td>
          </tr>
          <tr>
            <td>{this.$t('submitted')}:</td>
            <td class="text-right">
              {this.$options.filters.formatNumber(params.row.total_cc_slips_submitted, this.$store.getters.settings.decimals)}
            </td>
          </tr>
        </table>
      ) : (
        ''
      );
    },
  },
};
</script>
