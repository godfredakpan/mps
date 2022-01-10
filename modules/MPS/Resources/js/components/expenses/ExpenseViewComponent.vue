<template>
  <div style="order-container">
    <div class="order" v-if="expense.id">
      <div class="order-header">
        <div class="details">
          <img class="logo" :src="expense.location && expense.location.logo ? expense.location.logo : $store.state.settings.default_logo" />
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
          :value="expense.reference"
        >
          {{ $t('barcode_error') }}
        </vue-barcode>
      </div>
      <Row :gutter="16" class="border-t">
        <Col :xs="24" class="border-y py-2">
          <h3 class="text-center" style="text-transform: uppercase;">
            {{ $tc('expense') }}
          </h3>
        </Col>
      </Row>

      <Row :gutter="16" class="border-y py-2">
        <Col :xs="24" :sm="12">
          {{ $t('date') }}: <strong>{{ expense.date | date }}</strong>
          <br />
          {{ $t('reference') }}: <strong>{{ expense.reference }}</strong>
          <br />
          {{ $t('created_by') }}: <strong>{{ expense.user.name }}</strong>
        </Col>
        <Col :xs="24" :sm="12">
          {{ $t('created_at') }}: <strong>{{ expense.created_at | datetime }}</strong>
          <br />
          {{ $t('approval') }}: <strong v-if="expense.approved_by">{{ expense.approved_by.name | capitalize }}</strong>
          <br />
          {{ $tc('approved') }}: <span v-html="booleanView(expense.approved)"></span>
        </Col>
      </Row>
      <Row :gutter="16" class="mt16">
        <Col :xs="24" :sm="12">
          <p>{{ $tc('store') }}:</p>
          <div v-if="expense.location">
            <strong>{{ expense.location.name }}</strong
            ><br />
            <span v-if="expense.location.address">{{ expense.location.address }}</span>
            <span v-if="expense.location.state_name">{{ expense.location.state_name }}</span>
            <span v-if="expense.location.country_name">{{ expense.location.country_name }}</span
            ><br />
            <span v-if="expense.location.phone">{{ expense.location.phone }}</span
            ><br />
            <span v-if="expense.location.email">{{ expense.location.email }}</span
            ><br />
          </div>
        </Col>
      </Row>
      <div class="table-wrapper">
        <table class="table">
          <tbody>
            <tr>
              <td>{{ $t('title') }}</td>
              <td>{{ expense.title }}</td>
            </tr>
            <tr>
              <td>{{ $tc('account') }}</td>
              <td>{{ expense.account.name }}</td>
            </tr>
            <tr>
              <td>{{ $tc('category') }}</td>
              <td>{{ expense.categories[0].name }}</td>
            </tr>
            <tr>
              <td class="bold">{{ $t('amount') }}</td>
              <td class="bold">{{ formatNumber(expense.amount) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <Card dis-hover v-if="checkExtraAttributes(expense.extra_attributes)" class="mt16">
        <span v-html="renderExtraAttributes(expense.extra_attributes, expense.attributes)"></span>
      </Card>
      <list-attachments-component :attachments="expense.attachments" @remove="deleteAttachment" class="mt16" />
      <Card dis-hover v-if="expense.details" class="mt16">{{ expense.details }}</Card>
      <!-- <Row :gutter="16" class="mt16">
      <Col :xs="24" :sm="12">
      </Col>
      <Col :xs="24" :sm="12" v-if="expense.user">
        {{ $t('created_by') }}: <strong>{{ expense.user.name }}</strong>
      </Col>
    </Row>
    <Row :gutter="16" class="mt16">
      <Col :xs="24" :sm="12">
        {{ $t('approval') }}: <strong v-if="expense.approved_by">{{ expense.approved_by.name | capitalize }}</strong>
      </Col>
      <Col :xs="24" :sm="12"> {{ $tc('approved') }}: <span v-html="booleanView(expense.approved)"></span> </Col>
    </Row> -->
    </div>

    <div class="np text-center">
      <Button class="mt16 mx8" type="primary" :loading="loading" :disabled="loading" icon="md-print" @click="print()">
        {{ $t('print') }}
      </Button>
    </div>
  </div>
</template>

<script>
import VueBarcode from 'vue-barcode';
import ListAttachmentsComponent from '@mpscom/helpers/ListAttachmentsComponent';
export default {
  props: {
    expense: {
      type: Object,
      required: true,
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
