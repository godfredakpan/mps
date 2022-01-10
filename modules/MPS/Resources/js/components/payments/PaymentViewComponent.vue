<template>
  <div style="order-container">
    <div class="order" v-if="payment.id">
      <div class="order-header">
        <div class="details">
          <img class="logo" :src="payment.location && payment.location.logo ? payment.location.logo : $store.state.settings.default_logo" />
          <div class="header-text">
            <ul>
              <li>
                <strong>{{ $store.getters.settings.name }} ({{ $store.getters.settings.short_name }})</strong>
              </li>
              <li>
                <strong>{{ $store.getters.settings.company }}</strong>
              </li>
              <li>{{ $store.getters.settings.email }} <span class="text-muted">|</span> {{ $store.getters.settings.phone }}</li>
              <li>{{ $store.getters.settings.address }}</li>
            </ul>
          </div>
        </div>
        <vue-barcode
          :width="1"
          :margin="0"
          :height="50"
          :fontSize="12"
          :margin-top="0"
          format="code39"
          class="barcode"
          :margin-bottom="2"
          :value="payment.reference"
        >
          {{ $t('barcode_error') }}
        </vue-barcode>
      </div>
      <Row :gutter="16" class="border-t">
        <Col :xs="24" class="border-y py-2">
          <h3 class="text-center" style="text-transform: uppercase;">
            {{ payment.received == 1 ? $tc('payment_note') : $tc('payment_request') }}
          </h3>
        </Col>
      </Row>
      <Row :gutter="16" class="border-y py-2">
        <Col :xs="24" :sm="12">
          {{ $t('date') }}: <strong>{{ payment.created_at | date }}</strong>
          <br />
          <span v-if="payment.user">
            {{ $t('created_by') }}: <strong>{{ payment.user.name }}</strong>
          </span>
        </Col>
        <Col :xs="24" :sm="12">
          {{ $t('reference') }}: <strong>{{ payment.reference }}</strong>
        </Col>
      </Row>
      <Row :gutter="16" class="mt16">
        <Col :xs="24" :sm="12">
          <p>{{ $tc('location') }}:</p>
          <div v-if="payment.location">
            <strong>{{ payment.location.name }}</strong
            ><br />
            <span v-if="payment.location.address">{{ payment.location.address }}</span>
            <span v-if="payment.location.state_name">{{ payment.location.state_name }}</span>
            <span v-if="payment.location.country_name">{{ payment.location.country_name }}</span
            ><br />
            <span v-if="payment.location.phone">{{ payment.location.phone }}</span
            ><br />
            <span v-if="payment.location.email">{{ payment.location.email }}</span
            ><br />
          </div>
        </Col>
        <Col :xs="24" :sm="12">
          <p>{{ payment.payable_type.includes('Customer') ? $tc('from') : $tc('to') }}:</p>
          <div v-if="payment.payable">
            <strong>{{ payment.payable.name }}</strong
            ><br />
            <span v-if="payment.payable.address">{{ payment.payable.address }}</span>
            <span v-if="payment.payable.state_name">{{ payment.payable.state_name }}</span>
            <span v-if="payment.payable.country_name">{{ payment.payable.country_name }}</span
            ><br />
            <span v-if="payment.payable.phone">{{ payment.payable.phone }}</span
            ><br />
            <span v-if="payment.payable.email">{{ payment.payable.email }}</span
            ><br />
          </div>
        </Col>
      </Row>
      <div class="table-wrapper">
        <table class="table">
          <tbody>
            <tr v-if="payment.account">
              <td>{{ $tc('account') }}</td>
              <td>{{ payment.account.name }}</td>
            </tr>
            <tr>
              <td class="bold">
                {{ payment.received == 1 ? $tc('amount_received') : $tc('amount_requested') }}
              </td>
              <td class="bold">{{ formatNumber(payment.amount) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <Card dis-hover v-if="checkExtraAttributes(payment.extra_attributes)" class="mt16">
        <span v-html="renderExtraAttributes(payment.extra_attributes, payment.attributes)"></span>
      </Card>
      <list-attachments-component :attachments="payment.attachments" @remove="deleteAttachment" class="mt16" />
      <Card dis-hover v-if="payment.details" class="mt16">{{ payment.details }}</Card>
      <p class="cgd">{{ $t('order_cgd') }}</p>
    </div>

    <div class="np text-center">
      <Button class="mt16 mx8" type="primary" :loading="loading" :disabled="loading" icon="md-print" @click="print()">
        {{ $t('print') }}
      </Button>
      <Button
        type="primary"
        class="mt16 mx8"
        :loading="loading"
        :disabled="loading"
        @click="review(payment)"
        icon="ios-information-circle"
        v-if="payment.review == 1 && !payment.reviewed_by && payment.received != 1 && review"
      >
        {{ $t('accept_x', { x: $tc('payment') }) }}
      </Button>
    </div>
  </div>
</template>

<script>
import VueBarcode from 'vue-barcode';

import ListAttachmentsComponent from '@mpscom/helpers/ListAttachmentsComponent';
export default {
  props: {
    payment: {
      type: Object,
      required: true,
    },
    review: {
      type: Function,
      required: false,
    },
  },
  components: {
    VueBarcode,
    ListAttachmentsComponent,
  },
  data() {
    return {
      loading: false,
    };
  },
  methods: {
    print() {
      window.print();
    },
    deleteAttachment(attachment) {
      this.$emit('remove', attachment);
    },
  },
};
</script>
