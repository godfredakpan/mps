<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ $tc('sale', 2) }}</p>
      <router-link to="/sales/add" slot="extra">
        <Icon type="ios-grid-outline" />
        {{ $t('add') }} {{ $tc('sale') }}
      </router-link>
      <div>
        <table-component
          url="app/sales"
          :columns="columns"
          :options="options"
          :refresh="refresh"
          :dblClickCB="viewModal"
          :bulkDelCB="deleteRecords"
          :row-class-name="rowClassName"
        ></table-component>
      </div>
    </Card>
    <Modal
      width="750"
      v-model="view"
      :footer-hide="true"
      :mask-closable="false"
      class="np-header-footer"
      :title="$tc('sale') + ' (' + sale.reference + ')'"
    >
      <div v-if="loading" class="py16">
        <Loading />
      </div>
      <order-view-component :record="sale" :heading="$tc('sale')" :to-text="$tc('bill_to')" @remove="a => deleteAttachment(a, sale)" />
    </Modal>
    <Modal footer-hide v-model="paymentsModal" :width="750" @on-visible-change="paymentsModalChanged">
      <p slot="header">
        <span>
          {{ $t('list_x', { x: $tc('payment', 2) }) }}
          {{ sale ? `(${$tc('sale')} ${$t('reference')} ${sale.reference})` : '' }}
        </span>
      </p>
      <Loading v-if="loadingPayments" />
      <payments-component v-else :payments="payments"></payments-component>
    </Modal>
    <Modal :title="$tc('delivery')" v-model="deliveryModal" :footer-hide="true" :mask-closable="false">
      <div v-if="loadingDelivery" class="py16">
        <Loading />
      </div>
      <delivery-view-component :delivery="delivery" @remove="a => deleteAttachment(a, delivery)" />
    </Modal>
  </div>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
import PaymentsComponent from '@mpscom/helpers/PaymentsComponent';
import OrderViewComponent from '@mpscom/helpers/OrderViewComponent';
import DeliveryViewComponent from '@mpscom/deliveries/DeliveryViewComponent';

export default {
  components: { DeliveryViewComponent, PaymentsComponent, OrderViewComponent },
  mixins: [Table('sale', 'app/sales', 'reference')],
  data() {
    return {
      sale: {},
      view: false,
      delivery: {},
      payments: [],
      printData: {},
      loading: false,
      printing: false,
      printModal: false,
      deliveryModal: false,
      paymentsModal: false,
      loadingDelivery: false,
      loadingPayments: false,
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('date'), sortable: true, key: 'date', sortType: 'desc', width: 100, render: this.renderDate },
        { title: this.$t('reference'), className: 'reference', sortable: true, key: 'reference', minWidth: 150 },
        {
          maxWidth: 300,
          minWidth: 200,
          key: 'customer',
          sortable: false,
          title: this.$tc('customer'),
          render: this.renderCustomer,
        },
        { title: this.$t('draft'), sortable: true, key: 'draft', width: 80, render: this.renderIconDraft },
        { title: this.$t('paid'), sortable: true, key: 'paid', width: 75, render: this.renderIconPaid },
        { title: this.$t('void'), sortable: true, key: 'void', width: 75, render: this.renderIconVoid },
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
          key: 'user_id',
          sortable: false,
          render: this.renderUser,
          title: this.$t('created_by'),
        },
        { title: this.$t('total'), sortable: false, key: 'total', width: 150, render: this.renderTotal },
        { title: this.$t('item_tax'), sortable: false, key: 'item_tax', width: 125, render: this.renderItemTax },
        { title: this.$t('order_tax'), sortable: false, key: 'order_tax', width: 125, render: this.renderOrderTax },
        { title: this.$t('discount'), sortable: false, key: 'discount', width: 125, render: this.renderDiscount },
        { title: this.$t('shipping'), sortable: false, key: 'shipping', width: 125, render: this.renderShipping },
        { title: this.$t('grand_total'), sortable: false, key: 'grand_total', width: 150, render: this.renderGrandTotal },
        { title: this.$tc('field', 2), sortable: false, key: 'extra_attributes', minWidth: 300, render: this.renderExtras },
        { title: this.$t('details'), sortable: false, key: 'details', ellipsis: true, width: 350 },
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
                payFn: this.paymentRecord,
                emailFn: this.emailRecord,
                deliveryFn: this.deliveryFn,
                duplicateFn: this.duplicate,
                deleteFn: this.deleteRecord,
                receiptFn: this.viewReceipt,
                toggleVoidFn: this.toggleVoid,
                downloadFn: this.downloadRecord,
                listPayFn: this.ListPaymentForRecord,
                listDeliveriesFn: this.listDeliveriesFn,
                viewDeliveriesFn: this.viewDeliveriesFn,
                // viewLink: '/view/sale/' + params.row.hash,
                // payFn: params.row.paid ? null : this.paymentRecord,
                record: { model: 'sale', name: 'reference', title: this.$tc('sale') },
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
  mounted() {
    if (this.$store.state.settings.pos_server == 1) {
      this.$connect();
      this.$socket.addEventListener('error', event => {
        console.error('WebSocket error: ', event);
        this.$Notice.error({ title: this.$t('error'), desc: this.$t('pos_server_error'), duration: 10 });
      });
      this.$socket.addEventListener('message', event => {
        this.$Notice.info({ title: event.data, duration: 5 });
      });
    }
  },
  beforeDestory() {
    if (this.$store.state.settings.pos_server == 1) {
      this.$socket.removeEventListener('error', () => {
        console.log('Socket error listener removed.');
      });
      this.$socket.removeEventListener('message', () => {
        console.log('Socket message listener removed.');
      });
      this.$disconnect();
    }
  },
  methods: {
    viewModal(row) {
      this.view = true;
      if (row.id != this.sale.id) {
        this.loading = true;
        this.$http
          .get(`app/sales/${row.id}`)
          .then(res => (this.sale = res.data))
          .finally(() => (this.loading = false));
      }
    },
    showReceipt(data) {
      if (data.pos) {
        this.sale = data;
        this.printData.type = 'receipt';
        this.printData.order = this.sale;
        this.printData.order.items = this.printData.order.items.map(item => {
          item.selected = { portions: [], variations: [], modifiers: [] };
          item.selected.portions = item.portions;
          item.selected.variations = item.variations;
          item.selected.modifiers = item.modifier_options;
          return item;
        });
        this.$Modal.confirm({
          width: 365,
          closable: true,
          scrollable: true,
          render: h => {
            return h('print-component', { props: { print: this.printData, vm: this } });
          },
          okText: this.$t('print'),
          onOk: () => {
            if (this.$store.state.settings.pos_server == 1) {
              if (this.$socket.readyState != 1) {
                let n = 0;
                this.$connect();
                this.$socket.addEventListener('error', event => {
                  console.error('WebSocket error: ', event);
                  this.$Notice.error({ title: this.$t('error'), desc: this.$t('pos_server_error'), duration: 10 });
                });
                this.$socket.addEventListener('message', event => {
                  this.$Notice.info({ title: event.data, duration: 5 });
                });
                if (n == 0) {
                  this.$Notice.info({ desc: this.$t('pos_server_reconnect_text'), duration: 5 });
                  setTimeout(() => this.printReceipt(), 1000);
                  n++;
                }
                return false;
              }
              this.printReceipt();
            } else {
              window.print();
            }
          },
          onCancel: () => {
            setTimeout(() => (this.printData = {}), 200);
          },
        });
      }
    },
    printReceipt() {
      let order = this.sale;
      let items = null;
      let totals = null;
      let headers = null;
      let footers = null;
      let notice = null;
      let table_headers = null;
      let store_details = {};
      let user = order.user ? order.user : this.$store.getters.user;
      let location = order.location ? order.location : this.$store.getters.location;
      store_details['name'] = location.name || location.label;
      let details = [
        [this.$t('order_id'), order.orderId],
        [this.$t('reference'), order.reference],
        [this.$t('date'), this.date(order.date)],
        [this.$t('created_by'), user.name + '(' + user.username + ')'],
        [this.$t('created_at'), this.datetime(order.created_at)],
        [this.$t('printed_at'), this.datetime(new Date())],
      ];
      items = order.items.map(item => {
        let rows = ['feed'];
        let name = item.code + '\n' + item.name + (item.alt_name ? '\n' + item.alt_name : '') + (item.comment ? '\n' + item.comment : '');
        if (item.variations && item.variations.length) {
          rows.push({ type: 'text', text: name });
          item.variations.map(sv => {
            rows.push({
              type: 'item',
              name: ' ' + this.metaString(sv.meta, true, true),
              price: this.formatNumber(parseFloat(sv.pivot.net_price) + parseFloat(sv.pivot.tax_amount)),
              qty: this.formatQuantity(sv.pivot.quantity),
              subtotal: this.formatNumber(sv.pivot.total),
            });
          });
        } else if (item.portions && item.portions.length) {
          rows.push({ type: 'text', text: name });
          item.portions.map(p => {
            rows.push('feed');
            rows.push({
              type: 'item',
              name: this.$tc('portion') + ': ' + this.$tc(p.name),
              price: this.formatNumber(parseFloat(p.pivot.price) - parseFloat(p.pivot.discount_amount)),
              qty: this.formatQuantity(p.pivot.quantity),
              subtotal: this.formatNumber(p.pivot.total),
            });
            if (p.portion_items && p.portion_items.length) {
              p.portion_items.map(pi => {
                rows.push({
                  type: 'item',
                  name: ' ' + pi.item.name + (pi.meta ? '\n(' + this.metaString(pi.meta, true, true) + ')' : ''),
                  price: ' ',
                  qty: this.formatQuantity(parseFloat(pi.quantity) * parseFloat(p.pivot.quantity)),
                  subtotal: ' ',
                });
              });
            }
            if (p.essentials && p.essentials.length) {
              p.essentials.map(pe => {
                rows.push({
                  type: 'item',
                  name: ' ' + pe.item.name + (pe.meta ? '\n(' + this.metaString(pe.meta, true, true) + ')' : ''),
                  price: ' ',
                  qty: this.formatQuantity(parseFloat(pe.quantity) * parseFloat(p.pivot.quantity)),
                  subtotal: ' ',
                });
              });
            }
            if (p.choosables && p.choosables.length) {
              p.choosables.map(pc => {
                pc.items.map(pci => {
                  if (p.pivot.choosables.find(c => c.id == pc.id && pci.item_id == c.item_id)) {
                    rows.push({ type: 'text', text: pc.name });
                    rows.push({
                      type: 'item',
                      name: ' ' + pci.item.name + (pc.meta ? '\n(' + this.metaString(pc.meta, true, true) + ')' : ''),
                      price: ' ',
                      qty: this.formatQuantity(parseFloat(pci.quantity) * parseFloat(p.pivot.quantity)),
                      subtotal: ' ',
                    });
                  }
                });
              });
            }
          });
        } else {
          rows.push({
            name,
            type: 'item',
            price: this.formatNumber(item.unit_price),
            qty: this.formatQuantity(item.quantity),
            subtotal: this.formatNumber(item.subtotal),
          });
        }
        if (item.modifier_options && item.modifier_options.length) {
          item.modifier_options.map(m => {
            rows.push({ type: 'text', text: m.modifier.title });
            rows.push({
              type: 'item',
              name: ' ' + m.item.name,
              price: this.formatNumber(parseFloat(m.pivot.net_price) + parseFloat(m.pivot.tax_amount)),
              qty: this.formatQuantity(m.pivot.quantity),
              subtotal: this.formatNumber(m.pivot.total),
            });
          });
        }
        return rows;
      });

      headers = [this.$store.state.settings.header, location.header];
      store_details['email'] = location.email;
      store_details['phone'] = location.phone;
      store_details['company'] = location.company;
      store_details['address'] = location.address;
      store_details['state'] = location.state_name;
      store_details['country'] = location.country_name;

      details.unshift([this.$tc('id'), order.id]);
      details.push([
        this.$tc('customer'),
        order.customer.name +
          (order.customer.company ? ' (' + order.customer.company + ')' : '') +
          (order.customer.email ? ' ' + order.customer.email : '') +
          (order.customer.phone ? ' ' + order.customer.phone : ''),
      ]);

      totals = [{ label: this.$t('total'), value: this.formatNumber(order.total) }];
      if (order.order_discount_amount) {
        totals.push({ label: this.$t('order_discount'), value: this.formatNumber(order.order_discount_amount) });
      }
      totals.push({ label: this.$t('tax_amount'), value: this.formatNumber(order.total_tax_amount) });
      totals.push({ label: this.$t('grand_total'), value: this.formatNumber(order.grand_total) });
      let total_paid = order && order.payments ? order.payments.reduce((a, p) => a + parseFloat(p.amount), 0) : 0;
      totals.push({ label: this.$t('paid'), value: this.formatNumber(total_paid) });
      totals.push({ label: this.$t('balance_due'), value: this.formatNumber(parseFloat(order.grand_total) - total_paid) });
      notice = this.$t('order_cgd');

      table_headers = [{ label: this.$t('name') }, { label: this.$t('price') }, { label: this.$t('qty') }, { label: this.$t('subtotal') }];
      footers = [this.$store.state.settings.footer, location.footer];

      let data = {
        logo: location.logo || this.$store.state.settings.default_logo,
        heading: this.$store.state.settings.name,
        store_heading: this.$tc('location'),
        store_details,
        type: this.$tc('receipt'),
        headers,
        info: details,
        table_headers,
        items,
        totals,
        footers,
        notice,
      };
      this.$nextTick(() => {
        if (this.$socket.readyState == 1) {
          this.$socket.send(JSON.stringify({ type: 'print-json-receipt', data }));
        } else {
          this.$Notice.error({ title: this.$t('error'), desc: this.$t('pos_server_connection_error'), duration: 10 });
        }
      });
    },
    viewReceipt(row) {
      if (row.id != this.sale.id) {
        this.loading = true;
        this.$http
          .get(`app/sales/${row.id}`)
          .then(res => this.showReceipt(res.data))
          .finally(() => (this.loading = false));
      } else {
        this.showReceipt(this.sale);
      }
    },
    duplicate(row) {
      this.$router.push(`/sales/add?sale_id=${row.id}`);
    },
    toggleVoid(row) {
      this.$http
        .delete(`app/sales/${row.id}/toggle_void`)
        .then(res => {
          if (res.data.success) {
            this.refresh++;
            this.$Notice.success({
              title: this.$t('success'),
              desc: this.$t('order_marked_x_text', { x: res.data.void == 1 ? this.$t('void') : this.$t('valid') }),
            });
          } else {
            this.$Notice.error({ title: this.$tc('failed'), desc: this.$t('failed_error_text') });
          }
        })
        .finally(() => this.$Modal.remove());
    },
    emailRecord(row) {
      this.$http.post('app/sales/email/' + row.id, {}).then(res => {
        if (res.data.success) {
          this.$Notice.success({ title: this.$t('email_sent'), desc: this.$t('email_sent_text') });
        } else {
          this.$Notice.error({ title: this.$tc('failed'), desc: this.$t('failed_error_text') });
        }
      });
    },
    downloadRecord(row) {
      // window.open('/download/sales/' + row.id);
      Object.assign(document.createElement('a'), { target: '_blank', href: window.mpsURL + 'download/sales/' + row.id }).click();
    },
    viewRecord(row) {
      // window.open('/view/sale/' + row.hash);
      Object.assign(document.createElement('a'), { target: '_blank', href: window.mpsURL + 'view/sale/' + row.hash }).click();
    },
    paymentRecord(row) {
      this.$router.push(`/payments/add?sale_id=${row.id}&customer_id=${row.customer_id}&amount=${row.grand_total}`);
    },
    deliveryFn(row) {
      if (row.draft == 1) {
        this.$Notice.warning({ title: this.$tc('draft'), desc: this.$t('order_draft_text') });
      }
      if (row.deliveries.length) {
        this.$router.push(`deliveries/edit/${row.deliveries[0].id}`);
      } else {
        this.$router.push(`deliveries/add?sale_id=${row.id}&customer_id=${row.customer_id}`);
      }
    },
    viewDeliveriesFn(row) {
      this.deliveryModal = true;
      if (row.deliveries[0].id != this.delivery.id) {
        this.loadingDelivery = true;
        this.$http
          .get(`app/deliveries/${row.deliveries[0].id}`)
          .then(res => (this.delivery = res.data))
          .finally(() => (this.loadingDelivery = false));
      }
    },
    listDeliveriesFn(row) {
      this.$router.push(`deliveries?sale_id=${row.id}`);
    },
    ListPaymentForRecord(row) {
      this.sale = row;
      this.paymentsModal = true;
      this.loadingPayments = true;
      this.$http.get('app/sales/' + row.id + '/payments').then(res => {
        this.payments = res.data;
        this.loadingPayments = false;
      });
    },
    paymentsModalChanged(opening) {
      if (!opening) {
        this.sale = {};
        this.payments = [];
      }
    },
    renderCustomer(h, params) {
      return <div>{params.row.customer.name}</div>;
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
    renderIconPaid(h, params) {
      return this.renderBoolean(h, params, 'paid');
    },
    renderIconVoid(h, params) {
      return this.renderBoolean(h, params, 'void');
    },
    rowClassName(row, index) {
      if (row.void == 1) {
        return 'ivu-table-error-row';
      }
      return '';
    },
  },
};
</script>
