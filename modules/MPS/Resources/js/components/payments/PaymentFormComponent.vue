<template>
  <Card :dis-hover="true">
    <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('payment') }}</p>
    <router-link to="/payments" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list') }} {{ $tc('payment', 2) }}
    </router-link>
    <div>
      <Form ref="payment" :model="form" :rules="rules" :label-width="150" class="form-responsive">
        <Row :gutter="16">
          <Col :sm="24" :md="24" :lg="24">
            <Loading v-if="loading" />
            <Alert type="error" show-icon class="mb26" v-if="errors.message">
              <div v-html="errors.message"></div>
            </Alert>
            <Row :gutter="16">
              <Col :sm="24" :xl="12">
                <FormItem :label="$t('type')" prop="type" :error="errors.form.type | a2s">
                  <Select v-model="form.type" placeholder="">
                    <Option value="customer">{{ $tc('customer') }}</Option>
                    <Option value="supplier">{{ $tc('supplier') }}</Option>
                  </Select>
                </FormItem>
                <FormItem prop="customer_id" :label="$tc('customer')" v-if="form.type == 'customer'" :error="errors.form.customer_id | a2s">
                  <Select
                    remote
                    clearable
                    filterable
                    :loading="searching"
                    v-model="form.customer_id"
                    :remote-method="searchCustomers"
                    :placeholder="$t('type_to_search_x', { x: $tc('customer') })"
                  >
                    <Option :value="option.value" :key="index + option.value" v-for="(option, index) in customers">{{
                      option.label
                    }}</Option>
                  </Select>
                </FormItem>
                <FormItem prop="supplier_id" :label="$tc('supplier')" v-if="form.type == 'supplier'" :error="errors.form.supplier_id | a2s">
                  <Select
                    remote
                    clearable
                    filterable
                    :loading="searching"
                    v-model="form.supplier_id"
                    :remote-method="searchSuppliers"
                    :placeholder="$t('type_to_search_x', { x: $tc('supplier') })"
                  >
                    <Option v-for="(option, index) in suppliers" :value="option.value" :key="index + option.value">{{
                      option.label
                    }}</Option>
                  </Select>
                </FormItem>
                <FormItem :label="$tc('reference')" prop="reference" :error="errors.form.reference | a2s">
                  <Input v-model="form.reference" />
                </FormItem>
                <FormItem :label="$tc('account')" prop="account_id" :error="errors.form.account_id | a2s">
                  <Select v-model="form.account_id" placeholder>
                    <template v-if="accounts.length > 0">
                      <Option :key="index" :value="option.value" v-for="(option, index) in accounts">
                        {{ option.label }}
                      </Option>
                    </template>
                  </Select>
                </FormItem>
                <FormItem :label="$t('amount')" prop="amount" :error="errors.form.amount | a2s">
                  <InputNumber v-model="form.amount"></InputNumber>
                </FormItem>
                <FormItem :label="$t('gateway')" prop="gateway" :error="errors.form.gateway">
                  <Select v-model="form.gateway" placeholder="" @on-change="updatePaymentFormFocus()">
                    <Option value="">{{ $t('requesting') }}</Option>
                    <Option v-if="form.type == 'customer' && $store.getters.gateway" :value="$store.getters.gateway">
                      {{ $t($store.getters.gateway) }}
                    </Option>
                    <Option value="cash">{{ $t('cash') }}</Option>
                    <Option value="cheque">{{ $t('cheque') }}</Option>
                    <Option value="gift_card">{{ $tc('gift_card') }}</Option>
                    <Option value="credit_card">{{ $t('credit_card') }} ({{ $t('record') }})</Option>
                    <Option value="other">{{ $t('other') }}</Option>
                  </Select>
                </FormItem>
                <transition name="slide-fade">
                  <FormItem v-if="form.gateway == 'Stripe'" class="mb0">
                    <stripe-card-elements
                      ref="stripeCard"
                      class="stripe-card"
                      :amount="form.amount"
                      @token="tokenCreated"
                      @loading="loading = $event"
                      :pk="$store.state.payment.public_key"
                    ></stripe-card-elements>
                  </FormItem>
                  <FormItem
                    v-else-if="form.gateway == 'PayPal_Pro' || form.gateway == 'PayPal_Rest' || form.gateway == 'AuthorizeNetApi_Api'"
                  >
                    <Card dis-hover>
                      <Form ref="cardForm" :model="form" :rules="rules" label-position="top" class="form-top">
                        <Row :gutter="16">
                          <Col :sm="24" :md="12" :lg="12">
                            <FormItem :label="$tc('first_name')" prop="firstName" :error="errors.form.firstName | a2s">
                              <Input v-model="form.firstName" />
                            </FormItem>
                          </Col>
                          <Col :sm="24" :md="12" :lg="12">
                            <FormItem :label="$tc('last_name')" prop="lastName" :error="errors.form.lastName | a2s">
                              <Input v-model="form.lastName" />
                            </FormItem>
                          </Col>
                        </Row>
                        <Row :gutter="16">
                          <Col :sm="24" :md="16" :lg="16">
                            <FormItem :label="$tc('billing_address')" prop="billingAddress1" :error="errors.form.billingAddress1 | a2s">
                              <Input v-model="form.billingAddress1" />
                            </FormItem>
                          </Col>
                          <Col :sm="24" :md="8" :lg="8">
                            <FormItem :label="$tc('billing_city')" prop="billingCity" :error="errors.form.billingCity | a2s">
                              <Input v-model="form.billingCity" />
                            </FormItem>
                          </Col>
                        </Row>
                        <Row :gutter="16">
                          <Col :sm="24" :md="8" :lg="8">
                            <FormItem :label="$tc('billing_postcode')" prop="billingPostcode" :error="errors.form.billingPostcode | a2s">
                              <Input v-model="form.billingPostcode" />
                            </FormItem>
                          </Col>
                          <Col :sm="24" :md="8" :lg="8">
                            <FormItem :label="$tc('billing_state')" prop="billingState" :error="errors.form.billingState | a2s">
                              <Input v-model="form.billingState" />
                            </FormItem>
                          </Col>
                          <Col :sm="24" :md="8" :lg="8">
                            <FormItem :label="$tc('billing_country')" prop="billingCountry" :error="errors.form.billingCountry | a2s">
                              <Input v-model="form.billingCountry" />
                            </FormItem>
                          </Col>
                        </Row>
                        <FormItem :label="$tc('card_number')" prop="card_number" :error="errors.form.card_number | a2s">
                          <Input v-model="form.card_number" />
                        </FormItem>
                        <Row :gutter="16">
                          <Col :sm="24" :md="16" :lg="16">
                            <FormItem :label="$t('expiry_date')" prop="expiry_date" :error="errors.form.expiry_date">
                              <DatePicker type="month" format="MM/yyyy" style="width: 100%;" v-model="form.expiry_date"></DatePicker>
                            </FormItem>
                          </Col>
                          <Col :sm="24" :md="8" :lg="8">
                            <FormItem :label="$tc('cvv')" prop="cvv" :error="errors.form.cvv | a2s">
                              <Input v-model="form.cvv" />
                            </FormItem>
                          </Col>
                        </Row>
                      </Form>
                    </Card>
                  </FormItem>
                  <span v-else-if="form.gateway == 'credit_card'">
                    <FormItem :error="errors.form.swipe" id="swipe_holder">
                      <Input v-model="form.swipe" :placeholder="$t('swipe_card_text')" element-id="credit_card" />
                    </FormItem>
                    <FormItem :label="$t('card_holder')" prop="card_holder" :error="errors.form.card_holder">
                      <Input v-model="form.card_holder" />
                    </FormItem>
                    <FormItem :label="$t('card_number')" prop="card_number" :error="errors.form.card_number">
                      <Input v-model="form.card_number" />
                    </FormItem>
                    <FormItem :label="$t('expiry_date')" prop="expiry_date" :error="errors.form.expiry_date">
                      <DatePicker type="month" format="MM/yyyy" style="width: 100%;" v-model="form.expiry_date"></DatePicker>
                    </FormItem>
                    <!-- <FormItem :label="$t('cvv')" prop="cvv" :error="errors.form.cvv">
                      <Input v-model="form.cvv" element-id="cvv" />
                    </FormItem> -->
                  </span>
                  <span v-else-if="form.gateway == 'gift_card'">
                    <FormItem :label="$t('gift_card_number')" prop="gift_card_number" :error="errors.form.gift_card_number">
                      <!-- <Input v-model="form.gift_card_number" element-id="gift_card" /> -->
                      <AutoComplete
                        icon="ios-search"
                        style="width:100%"
                        element-id="gift_card"
                        @on-search="searchGiftCard"
                        @on-select="giftCardSelected"
                        v-model="form.gift_card_number"
                      >
                        <Option v-for="(card, ci) in gift_cards" :value="card.number" :key="'gift_card_' + ci">{{ card.number }}</Option>
                      </AutoComplete>
                      <span v-if="gift_card && gift_card.number">
                        {{ $t('balance') }}: {{ gift_card.balance | formatNumber($store.state.settings.decimals) }}
                      </span>
                    </FormItem>
                  </span>
                  <span v-else-if="form.gateway == 'cheque'">
                    <FormItem :label="$t('cheque_number')" prop="cheque_number" :error="errors.form.cheque_number">
                      <Input v-model="form.cheque_number" element-id="cheque" />
                    </FormItem>
                  </span>
                </transition>

                <attachments-component :error="errors.form.attachments | a2s" @selected="handleUpload" @clear="clearAttachments">
                  <list-attachments-component :attachments="attachments" @remove="deleteAttachment" />
                </attachments-component>
                <FormItem :label="$t('details')" prop="details" :error="errors.form.details | a2s">
                  <Input type="textarea" v-model="form.details" :autosize="{ minRows: 2, maxRows: 5 }" />
                </FormItem>
              </Col>
              <Col :sm="24" :md="12" :lg="12"></Col>
            </Row>

            <form-custom-fields v-model="form" :attributes="attributes" @update="updateCF" />

            <FormItem>
              <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('payments')">
                <span v-if="!saving">{{ $t('submit') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button
                ghost
                type="primary"
                :loading="saving"
                :disabled="saving"
                style="margin-left: 8px;"
                @click="handleSubmit('payments', true)"
              >
                <span v-if="!saving">{{ $t('save_n_stay') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button v-if="!form.id" @click="handleReset()" style="margin-left: 8px;">{{ $t('reset') }}</Button>
            </FormItem>
          </Col>
        </Row>
      </Form>
    </div>
  </Card>
</template>

<script>
import Form from '@mpsjs/mixins/Form';
import _debounce from 'lodash/debounce';
import Attachment from '@mpsjs/mixins/Attachment';
import { StripeElementCard } from '@vue-stripe/vue-stripe';

const formatRes = (data, vm) => {
  data.amount = parseFloat(data.amount);
  // data.received = data.received == 1 ? true : false;
  let type = 'customer';
  let supplier_id = '';
  let customer_id = '';
  let payable = { id: data.payable.id, name: data.payable.name, value: data.payable.id, label: data.payable.name };
  var str_pos = data.payable_type.indexOf('Supplier');
  if (str_pos > -1) {
    type = 'supplier';
    vm.suppliers = [payable];
    supplier_id = data.payable.id;
  } else {
    type = 'customer';
    vm.customers = [payable];
    customer_id = data.payable.id;
  }
  vm.attachments = data.attachments && data.attachments.length ? [...data.attachments] : [];
  vm.form = { ...data, type, customer_id, supplier_id };
  return vm.form;
};
export default {
  components: { StripeElementCard },
  mixins: [Attachment, Form('payment', 'app/payments', false, formatRes)],
  data() {
    const amountV = (rule, value, callback) => {
      if (!value || value == '') {
        callback(new Error(this.$t('field_is_required', { field: this.$t('amount') })));
      } else if (value && this.max_amount && value > this.max_amount) {
        callback(
          new Error(
            this.$t('payment_amount_error', {
              min: 0,
              max: this.max_amount,
            })
          )
        );
      } else {
        callback();
      }
    };
    const checlGiftCardBalance = (rule, value, callback) => {
      if (!value) {
        callback();
      }
      if (this.form.amount && this.form.gateway == 'gift_card' && parseFloat(this.gift_card.balance) < this.form.amount) {
        callback(new Error(this.$t('gift_card_payment_error')));
      } else {
        callback();
      }
    };
    return {
      accounts: [],
      customers: [],
      suppliers: [],
      gift_cards: [],
      gift_card: null,
      searching: false,
      max_amount: null,
      form: {
        id: '',
        title: '',
        details: '',
        amount: null,
        reference: '',
        account_id: '',
        customer_id: '',
        supplier_id: '',
        sale_id: '',
        purchase_id: '',
        type: 'customer',
        gateway: '',
        token: null,
        token_id: null,
        firstName: '',
        lastName: '',
        billingAddress1: '',
        billingCity: '',
        billingPostcode: '',
        billingState: '',
        billingCountry: '',
      },
      rules: {
        title: [{ required: true, message: this.$t('field_is_required', { field: this.$t('title') }), trigger: 'blur' }],
        // reference: [{ required: true, message: this.$t('field_is_required', { field: this.$t('reference') }), trigger: 'blur' }],
        account_id: [{ required: true, message: this.$t('field_is_required', { field: this.$tc('account') }), trigger: 'change' }],
        customer_id: [{ required: true, message: this.$t('field_is_required', { field: this.$tc('customer') }), trigger: 'change' }],
        supplier_id: [{ required: true, message: this.$t('field_is_required', { field: this.$tc('supplier') }), trigger: 'change' }],
        amount: [{ required: true, type: 'number', validator: amountV, trigger: 'blur' }],
        gateway: [{ message: this.$t('field_is_required', { field: this.$t('gateway') }), trigger: 'change' }],
        cheque_number: [{ required: true, message: this.$t('field_is_required', { field: this.$t('cheque_number') }), trigger: 'blur' }],
        gift_card_number: [
          { required: true, message: this.$t('field_is_required', { field: this.$t('gift_card_number') }), trigger: 'blur' },
          { validator: checlGiftCardBalance, trigger: ['change', 'blur'] },
        ],
        card_holder: [{ required: true, message: this.$t('field_is_required', { field: this.$t('card_holder') }), trigger: 'blur' }],
        card_number: [{ required: true, message: this.$t('field_is_required', { field: this.$t('card_number') }), trigger: 'blur' }],
        expiry_date: [
          { required: true, type: 'date', message: this.$t('field_is_required', { field: this.$t('expiry_date') }), trigger: 'blur' },
        ],
        cvv: [{ required: true, message: this.$t('field_is_required', { field: this.$t('cvv') }), trigger: 'blur' }],
        firstName: [{ required: true, message: this.$t('field_is_required', { field: this.$t('first_name') }), trigger: 'blur' }],
        lastName: [{ required: true, message: this.$t('field_is_required', { field: this.$t('last_name') }), trigger: 'blur' }],
        billingAddress1: [
          { required: true, message: this.$t('field_is_required', { field: this.$t('billing_address') }), trigger: 'blur' },
        ],
        billingCity: [{ required: true, message: this.$t('field_is_required', { field: this.$t('billing_city') }), trigger: 'blur' }],
        billingPostcode: [
          { required: true, message: this.$t('field_is_required', { field: this.$t('billing_postcode') }), trigger: 'blur' },
        ],
        billingState: [{ required: true, message: this.$t('field_is_required', { field: this.$t('billing_state') }), trigger: 'blur' }],
        billingCountry: [{ required: true, message: this.$t('field_is_required', { field: this.$t('billing_country') }), trigger: 'blur' }],
      },
    };
  },
  watch: {
    'paymentForm.swipe': function(newVal, oldVal) {
      if (newVal && newVal.charAt(0) == '%') {
        setTimeout(() => {
          var card = newVal.split('^');
          let name = card && card[1] ? card[1].split('/') : '';
          let lastName = name ? name[0].trim().split('.')[0] : '';
          let firstName = name && name[1] ? name[1].trim() : '';
          let ed1 = card && card[2] ? card[2].substring(2, 4) + '/' + card[2].substring(0, 2) : '';
          let edf = card && card[2] ? card[2].split(';') : '';
          let eds = edf && edf[1] ? edf[1].split('=') : null;
          let ed = eds && eds[1] ? eds[1].trim() : null;
          let ed2 = ed ? ed.substring(2, 4) + '/' + ed.substring(0, 2) : ed1;
          this.form.card_number = card[0].substring(2);
          this.form.expiry_date = ed2 || ed1;
          this.form.card_holder = `${firstName} ${lastName}`;
          this.$nextTick(function() {
            this.form.swipe = '';
            document.querySelector('#cvv').focus();
          });
        }, 400);
      } else if (newVal) {
        this.errors.payment.swipe = this.$t('unknown_value');
        document.querySelector('#swipe_holder').classList.add('ivu-form-item-error');
      } else {
        this.errors.payment.swipe = '';
        document.querySelector('#swipe_holder').classList.remove('ivu-form-item-error');
      }
    },
  },
  created() {
    if (this.$route.query.sale_id) {
      this.form.sale_id = this.$route.query.sale_id;
    }
    if (this.$route.query.customer_id) {
      this.form.type = 'customer';
      this.form.customer_id = this.$route.query.customer_id;
    }
    if (this.$route.query.purchase_id) {
      this.form.purchase_id = this.$route.query.purchase_id;
    }
    if (this.$route.query.supplier_id) {
      this.form.type = 'supplier';
      this.form.supplier_id = this.$route.query.supplier_id;
    }
    if (this.$route.query.amount) {
      this.max_amount = this.form.amount = parseFloat(this.$route.query.amount);
    }
    this.$http.get('app/accounts/search').then(res => (this.accounts = res.data));
  },
  methods: {
    fetch(id) {
      this.$http
        .get(`app/payments/${id}`)
        .then(res => formatRes(res.data, this))
        .finally(() => (this.loading = false));
    },
    handleSubmit(page, stay = false) {
      this.$refs.payment.validate(valid => {
        if (valid) {
          if (this.form.gateway == 'PayPal_Pro' || this.form.gateway == 'PayPal_Rest' || this.form.gateway == 'AuthorizeNetApi_Api') {
            this.$refs.cardForm.validate(valid => {
              if (valid) {
                this.loading = true;
                this.submit(page, stay);
              } else {
                this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
              }
            });
          } else {
            this.loading = true;
            if (this.form.gateway == 'Stripe' && !this.form.token_id) {
              this.$refs.stripeCard.submit();
            } else {
              this.submit(page, stay);
            }
          }
        } else {
          this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
        }
      });
    },
    tokenCreated(token) {
      this.form.token = token;
      this.form.token_id = token.id;
      this.submit();
    },
    searchCustomers(search) {
      if (search !== '' && !this.customers.find(c => c.label == search)) {
        this.getCustomers(search, this);
      }
    },
    getCustomers: _debounce((search, vm) => {
      vm.searching = true;
      const search_delay = vm.$store.getters.search_delay;
      vm.$http
        .get('app/customers/search?q=' + search)
        .then(res => (vm.customers = res.data))
        .finally(() => (vm.searching = false));
    }, search_delay || 250),
    searchSuppliers(search) {
      if (search !== '' && !this.suppliers.find(c => c.label == search)) {
        this.getSuppliers(search, this);
      }
    },
    getSuppliers: _debounce((search, vm) => {
      vm.searching = true;
      const search_delay = vm.$store.getters.search_delay;
      vm.$http
        .get('app/suppliers/search?q=' + search)
        .then(res => (vm.suppliers = res.data))
        .finally(() => (vm.searching = false));
    }, search_delay || 250),
    updatePaymentFormFocus() {
      if (
        this.form.gateway &&
        this.form.gateway != 'cash' &&
        this.form.gateway != 'other' &&
        this.form.gateway != 'Stripe' &&
        this.form.gateway != this.$store.getters.payment.gateway
      ) {
        this.$refs.payment.fields.map(field => {
          if (
            field.prop != 'type' &&
            field.prop != 'amount' &&
            field.prop != 'gateway' &&
            field.prop != 'details' &&
            field.prop != 'account_id' &&
            field.prop != 'reference' &&
            field.prop != 'customer_id' &&
            field.prop != 'supplier_id'
          ) {
            field.resetField() || true;
          }
        });
        setTimeout(
          () => (document.querySelector('#' + this.form.gateway) ? document.querySelector('#' + this.form.gateway).focus() : ''),
          100
        );
      }
    },
    giftCardSelected(n) {
      this.gift_card = this.gift_cards.find(c => c.number == n);
    },
    searchGiftCard(n) {
      this.getGiftCard(n, this);
    },
    getGiftCard: _debounce((search, vm) => {
      vm.searching = true;
      const search_delay = vm.$store.getters.search_delay;
      vm.$http
        .get('app/gift_cards/search?q=' + search)
        .then(res => {
          if (res.data.length == 1) {
            vm.gift_card = res.data[0];
            vm.form.gift_card_number = res.data[0].number;
          } else {
            vm.gift_cards = res.data;
          }
        })
        .finally(() => (vm.searching = false));
    }, search_delay || 250),
  },
};
</script>

<style>
.stripe-card .StripeElement {
  box-shadow: none !important;
  border-radius: 4px !important;
  border: 1px solid #dcdee2 !important;
}
</style>
