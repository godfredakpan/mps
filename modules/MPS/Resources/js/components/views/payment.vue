<template>
  <div class="fullpage">
    <div v-if="loading" class="preloaderapp" ref="preloaderapp">
      <div>
        <div class="spin spin-large spin-default">
          <div class="spin-main">
            <div v-if="$store.state.settings.loader == 'circle'" class="spin-text">
              <div class="circle">
                <svg viewBox="25 25 50 50" class="rotating-circle">
                  <circle cx="50" cy="50" r="20" fill="none" stroke-width="5" stroke-miterlimit="10" class="rotating-circle-path"></circle>
                </svg>
              </div>
            </div>
            <div v-else>
              <span class="spin-dot"></span>
              <div class="spin-text"></div>
            </div>
          </div>
        </div>
        <div class="error" style="text-align:center;margin-top:2rem;font-size:1.2rem;color:#3F4448;display:none">
          <svg
            version="1.1"
            xmlns="http://www.w3.org/2000/svg"
            xmlns:xlink="http://www.w3.org/1999/xlink"
            x="0px"
            y="0px"
            viewBox="0 0 451.74 451.74"
            style="width:100px;height:100px;"
          >
            <path
              style="fill:#E24C4B;"
              d="M446.324,367.381L262.857,41.692c-15.644-28.444-58.311-28.444-73.956,0L5.435,367.381 c-15.644,28.444,4.267,64,36.978,64h365.511C442.057,429.959,461.968,395.825,446.324,367.381z"
            />
            <path style="fill:#FFFFFF;" d="M225.879,63.025l183.467,325.689H42.413L225.879,63.025L225.879,63.025z" />
            <g>
              <path
                style="fill:#3F4448;"
                d="M196.013,212.359l11.378,75.378c1.422,8.533,8.533,15.644,18.489,15.644l0,0 c8.533,0,17.067-7.111,18.489-15.644l11.378-75.378c2.844-18.489-11.378-34.133-29.867-34.133l0,0 C207.39,178.225,194.59,193.87,196.013,212.359z"
              />
              <circle style="fill:#3F4448;" cx="225.879" cy="336.092" r="17.067" />
            </g></svg
          ><br />
          <span>Network Error<br /></span>
          <span style="color:#E24C4B">Unable to load application.</span>
        </div>
      </div>
    </div>
    <div v-else class="order-view">
      <div class="np" v-if="$route.query.error" style="margin:16px;">
        <Alert type="error">
          {{ $route.query.error }}
        </Alert>
      </div>
      <payment-view-component :payment="payment" />
    </div>
    <!-- Forms -->
    <div v-if="!payment.received && !payment.review" class="np payment-form">
      <Card dis-hover>
        <div v-if="$store.getters.gateway == 'Stripe'">
          <Card dis-hover>
            <p slot="title">
              <Icon type="ios-card" size="18"></Icon>
              {{ $t('pay_with_cc') }} ({{ $store.getters.gateway }})
            </p>
            <stripe-card-elements
              ref="elementsRef"
              @token="tokenCreated"
              class="stripe-element"
              @loading="saving = $event"
              locale="window.user_locale"
              :amount="payment.amount * 100"
              :pk="$store.getters.public_key"
            />
            <!-- <stripe-checkout ref="checkoutRef" :pk="publishableKey" :successUrl="successUrl" :cancelUrl="cancelUrl">
          <template slot="checkout-button">
            <button @click="checkout">Shut up and take my money!</button>
          </template>
        </stripe-checkout> -->
            <Button type="primary" size="large" class="bold" long @click="submit">{{ $t('pay') }}</Button>
          </Card>
        </div>
        <div
          v-if="
            $store.getters.gateway == 'PayPal_Rest' ||
              $store.getters.gateway == 'PayPal_Pro' ||
              $store.getters.gateway == 'AuthorizeNetApi_Api'
          "
        >
          <Card dis-hover>
            <p slot="title">
              <Icon type="ios-card" size="18"></Icon>
              {{ $t('pay_with_cc') }} ({{
                $store.getters.gateway == 'PayPal_Rest'
                  ? 'PayPal Rest'
                  : $store.getters.gateway == 'PayPal_Pro'
                  ? 'PayPal Pro'
                  : $store.getters.gateway == 'AuthorizeNetApi_Api'
                  ? 'Authorize.net'
                  : ''
              }})
            </p>
            <Form ref="cardForm" :model="form" :rules="rules" label-position="top">
              <Row :gutter="16">
                <Col :sm="24" :md="24" :lg="24">
                  <Loading v-if="loading" />
                  <Alert type="error" show-icon class="mb26" v-if="errors.message">
                    <div v-html="errors.message"></div>
                  </Alert>
                  <Row :gutter="16">
                    <Col :sm="24" :md="12" :lg="12">
                      <FormItem :label="$t('firstName')" prop="firstName" :error="errors.firstName | a2s">
                        <Input v-model="form.firstName" />
                      </FormItem>
                    </Col>
                    <Col :sm="24" :md="12" :lg="12">
                      <FormItem :label="$t('lastName')" prop="lastName" :error="errors.lastName | a2s">
                        <Input v-model="form.lastName" />
                      </FormItem>
                    </Col>
                    <Col :sm="24" :md="24" :lg="24">
                      <FormItem :label="$t('billing_x', { x: $t('address') })" prop="billingAddress1" :error="errors.billingAddress1 | a2s">
                        <Input v-model="form.billingAddress1" />
                      </FormItem>
                    </Col>
                    <Col :sm="24" :md="12" :lg="12">
                      <FormItem :label="$t('billing_x', { x: $t('city') })" prop="billing_city" :error="errors.billing_city | a2s">
                        <Input v-model="form.billing_city" />
                      </FormItem>
                    </Col>
                    <Col :sm="24" :md="12" :lg="12">
                      <FormItem
                        :label="$t('billing_x', { x: $t('postal_code') })"
                        prop="billing_postal_code"
                        :error="errors.billing_postal_code | a2s"
                      >
                        <Input v-model="form.billing_postal_code" />
                      </FormItem>
                    </Col>
                    <Col :sm="24" :md="12" :lg="12">
                      <FormItem :label="$t('billing_x', { x: $t('state') })" prop="billing_state" :error="errors.billing_state | a2s">
                        <Input v-model="form.billing_state" />
                      </FormItem>
                    </Col>
                    <Col :sm="24" :md="12" :lg="12">
                      <FormItem :label="$t('billing_x', { x: $t('country') })" prop="billing_country" :error="errors.billing_country | a2s">
                        <Input v-model="form.billing_country" />
                      </FormItem>
                    </Col>
                    <Col :sm="24" :md="12" :lg="12">
                      <FormItem :label="$t('card_number')" prop="number" :error="errors.number | a2s">
                        <Input v-model="form.number" />
                      </FormItem>
                    </Col>
                    <Col :sm="24" :md="12" :lg="12">
                      <Row :gutter="16">
                        <Col :sm="24" :md="14" :lg="16">
                          <FormItem :label="$t('expiry_date')" prop="expiry_date" :error="errors.expiry_date | a2s">
                            <DatePicker type="month" v-model="form.expiry_date" placeholder="YYYY-MM" style="width: 100%" />
                          </FormItem>
                        </Col>
                        <Col :sm="24" :md="10" :lg="8">
                          <FormItem :label="$t('cvv')" prop="cvv" :error="errors.cvv | a2s">
                            <InputNumber v-model="form.cvv" />
                          </FormItem>
                        </Col>
                      </Row>
                    </Col>
                  </Row>

                  <FormItem class="mb0">
                    <Button long size="large" type="primary" :loading="saving" :disabled="saving" @click="handleSubmit()">
                      <span v-if="!saving">{{ $t('submit') }}</span>
                      <span v-else>{{ $t('saving') }}...</span>
                    </Button>
                  </FormItem>
                </Col>
              </Row>
            </Form>
          </Card>
        </div>
        <div v-if="$store.getters.payment.paypal">
          <Divider dashed />
          <a class="paypal" :href="$store.getters.payment.moduleURL + '/paypal/' + payment.hash">
            {{ $t('pay_with') }} <img src="/images/paypal-logo.svg" alt="PayPal" style="margin-left:8px;max-height:22px;" />
          </a>
        </div>
      </Card>
    </div>
  </div>
</template>

<script>
import { StripeElementCard } from '@vue-stripe/vue-stripe';
import PaymentViewComponent from '@mpscom/payments/PaymentViewComponent';

export default {
  components: { PaymentViewComponent, StripeElementCard },
  data() {
    const cardNumber = (rule, value, callback) => {
      if (!value || value == '') {
        callback(new Error(this.$t('field_is_required', { field: this.$t('card_number') })));
      } else if (value.length != 16) {
        callback(new Error(this.$t('card_number_error')));
      } else {
        callback();
      }
    };
    return {
      token: null,
      payment: {},
      charge: null,
      saving: false,
      loading: true,
      errors: { message: '', form: {} },
      form: {
        firstName: '',
        lastName: '',
        billingAddress1: '',
        billing_city: '',
        billing_postal_code: '',
        billing_state: '',
        billing_country: '',
        number: null,
        expiry_date: null,
        cvv: null,
      },
      rules: {
        firstName: [{ required: true, message: this.$t('field_is_required', { field: this.$t('firstName') }), trigger: 'blue' }],
        lastName: [{ required: true, message: this.$t('field_is_required', { field: this.$t('lastName') }), trigger: 'blue' }],
        billingAddress1: [
          {
            required: true,
            trigger: 'blue',
            message: this.$t('field_is_required', { field: this.$t('billing_x', { x: this.$t('address') }) }),
          },
        ],
        billing_city: [
          {
            required: true,
            trigger: 'blue',
            message: this.$t('field_is_required', { field: this.$t('billing_x', { x: this.$t('city') }) }),
          },
        ],
        billing_postal_code: [
          {
            required: true,
            trigger: 'blue',
            message: this.$t('field_is_required', { field: this.$t('billing_x', { x: this.$t('postal_code') }) }),
          },
        ],
        billing_state: [
          {
            required: true,
            trigger: 'blue',
            message: this.$t('field_is_required', { field: this.$t('billing_x', { x: this.$t('state') }) }),
          },
        ],
        billing_country: [
          {
            required: true,
            trigger: 'blue',
            message: this.$t('field_is_required', { field: this.$t('billing_x', { x: this.$t('country') }) }),
          },
        ],
        number: [{ required: true, validator: cardNumber, trigger: 'blur' }],
        cvv: [{ required: true, type: 'number', message: this.$t('field_is_required', { field: this.$t('cvv') }), trigger: 'change' }],
        expiry_date: [
          {
            type: 'date',
            required: true,
            trigger: 'change',
            message: this.$t('field_is_required', { field: this.$t('expiry_date') }),
          },
        ],
      },
    };
  },
  // created() {
  //   this.getPayment(this.$route.params.hash);
  // },
  watch: {
    '$route.params.hash': {
      handler: function(hash) {
        this.getPayment(hash);
      },
      deep: true,
      immediate: true,
    },
  },
  methods: {
    getPayment(hash) {
      if (hash) {
        this.loading = true;
        this.$http
          .get(`/views/payment/${hash}`)
          .then(res => {
            this.payment = res.data;
            this.$nextTick(() => {
              document.title = this.$tc('payment') + ' ' + this.payment.reference;
            });
          })
          .catch(err => {
            this.$Notice.error({ title: this.$t('not_found'), desc: this.$t('not_found_text') });
            this.$router.push('/views');
          })
          .finally(() => (this.loading = false));
      } else {
        this.$router.push('/views');
      }
    },
    handleSubmit() {
      this.$refs.cardForm.validate(valid => {
        if (valid) {
          this.saving = true;
          let form = { ...this.form, expiryMonth: this.form.expiry_date.getMonth(), expiryYear: this.form.expiry_date.getFullYear() };
          this.$http
            .post(`app/pay/${this.$store.getters.gateway}/${this.payment.hash}`, form)
            .then(res => {
              if (res.data.success) {
                if (res.data.redirect) {
                  Window.location.href = res.data.redirect;
                  this.$Notice.success({
                    title: this.$t('success'),
                    desc: this.$t('payment_redirect_text'),
                  });
                } else {
                  this.$Notice.success({
                    title: this.$t('success'),
                    desc: this.$t('payment_charged_text'),
                  });
                  this.payment = res.data.payment;
                }
              }
            })
            .catch(err => console.log(err))
            .finally(() => (this.saving = false));
        } else {
          this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
        }
      });
    },
    submit() {
      this.saving = true;
      this.$refs.elementsRef.submit();
    },
    tokenCreated(token) {
      this.token = token;
      this.charge = {
        source: token.id,
        amount: this.payment.amount * 100,
        description: this.payment.details,
      };
      this.sendTokenToServer(this.charge);
    },
    sendTokenToServer(charge) {
      // Send to charge to your backend server to be processed
      // Documentation here: https://stripe.com/docs/api/charges/create
      this.$http
        .post('app/pay/Stripe/' + this.payment.hash, charge)
        .then(res => {
          console.log(res);
          if (res.data.success) {
            if (res.data.redirect) {
              Window.location.href = res.data.redirect;
              this.$Notice.success({
                title: this.$t('success'),
                desc: this.$t('payment_redirect_text'),
              });
            } else {
              this.$Notice.success({
                title: this.$t('success'),
                desc: this.$t('payment_charged_text'),
              });
              this.payment = res.data.payment;
            }
          }
        })
        .catch(err => console.log(err))
        .finally(() => (this.saving = false));
    },
    // checkout() {
    //   this.$refs.checkoutRef.redirectToCheckout();
    // },
  },
};
</script>

<style>
.payment-form {
  width: 100%;
  margin: 16px;
  max-width: 750px;
}
.stripe-element .StripeElement {
  margin: 0;
  border-radius: 5px;
  box-shadow: none !important;
  border: 1px solid #eeeeee !important;
}
.paypal {
  color: #fff;
  width: 100%;
  display: flex;
  cursor: pointer;
  height: 2.25rem;
  font-size: 0.9rem;
  text-decoration: none;
  border-radius: 4px;
  align-items: center;
  background: #2d8cf0;
  justify-content: center;
  border: 1px solid #2d8cf0;
}
.btn-primary:hover,
.paypal:hover {
  color: #fff;
  background: #3273dc;
}
</style>
