<template>
  <div style="order-container">
    <div class="order" v-if="income.id">
      <div class="order-header">
        <div class="details">
          <img class="logo" :src="income.location && income.location.logo ? income.location.logo : $store.state.settings.default_logo" />
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
          :value="income.reference"
        >
          {{ $t('barcode_error') }}
        </vue-barcode>
      </div>
      <Row :gutter="16" class="border-t">
        <Col :xs="24" class="border-y py-2">
          <h3 class="text-center" style="text-transform: uppercase;">
            {{ $tc('income') }}
          </h3>
        </Col>
      </Row>

      <Row :gutter="16" class="border-y py-2">
        <Col :xs="24" :sm="12">
          {{ $t('date') }}: <strong>{{ income.date | date }}</strong>
          <br />
          {{ $t('reference') }}: <strong>{{ income.reference }}</strong>
        </Col>
        <Col :xs="24" :sm="12">
          {{ $t('created_at') }}: <strong>{{ income.created_at | datetime }}</strong>
          <br />
          {{ $t('created_by') }}: <strong>{{ income.user.name }}</strong>
        </Col>
      </Row>
      <Row :gutter="16" class="mt16">
        <Col :xs="24" :sm="12">
          <p>{{ $tc('store') }}:</p>
          <div v-if="income.location">
            <strong>{{ income.location.name }}</strong
            ><br />
            <span v-if="income.location.address">{{ income.location.address }}</span>
            <span v-if="income.location.state_name">{{ income.location.state_name }}</span>
            <span v-if="income.location.country_name">{{ income.location.country_name }}</span
            ><br />
            <span v-if="income.location.phone">{{ income.location.phone }}</span
            ><br />
            <span v-if="income.location.email">{{ income.location.email }}</span
            ><br />
          </div>
        </Col>
      </Row>

      <div class="table-wrapper">
        <table class="table">
          <tbody>
            <tr>
              <td>{{ $t('title') }}</td>
              <td>{{ income.title }}</td>
            </tr>
            <tr>
              <td>{{ $tc('account') }}</td>
              <td>{{ income.account.name }}</td>
            </tr>
            <tr>
              <td>{{ $tc('category') }}</td>
              <td>{{ income.categories[0].name }}</td>
            </tr>
            <tr>
              <td class="bold">{{ $t('amount') }}</td>
              <td class="bold">{{ formatNumber(income.amount) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <Card dis-hover v-if="checkExtraAttributes(income.extra_attributes)" class="mt16">
        <span v-html="renderExtraAttributes(income.extra_attributes, income.attributes)"></span>
      </Card>
      <list-attachments-component :attachments="income.attachments" @remove="deleteAttachment" class="mt16" />
      <Card dis-hover v-if="income.details" class="mt16">{{ income.details }}</Card>
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
    income: {
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
