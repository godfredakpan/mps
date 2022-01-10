<template>
  <div style="order-container">
    <div class="order" v-if="salary.id">
      <div class="order-header">
        <div class="details">
          <img
            class="logo"
            :src="salary.user.location && salary.user.location.logo ? salary.user.location.logo : $store.state.settings.default_logo"
          />
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
          :value="salary.reference"
        >
          {{ $t('barcode_error') }}
        </vue-barcode>
      </div>
      <Row :gutter="16" class="border-t">
        <Col :xs="24" class="border-y py-2">
          <h3 class="text-center" style="text-transform: uppercase;">
            {{ $tc('salary') }}
          </h3>
        </Col>
      </Row>

      <Row :gutter="16" class="border-y py-2">
        <Col :xs="24" :sm="12">
          {{ $t('date') }}: <strong>{{ salary.date | date }}</strong>
          <br />
          {{ $t('reference') }}: <strong>{{ salary.reference }}</strong>
          <br />
          {{ $t('created_at') }}: <strong>{{ salary.created_at | datetime }}</strong>
        </Col>
        <Col :xs="24" :sm="12">
          {{ $t('name') }}: <strong>{{ salary.user.name }}</strong>
          <br />
          {{ $t('employee_number') }}: <strong>{{ salary.user.employee_number }}</strong>
          <br />
          {{ $t('phone') }}: <strong>{{ salary.user.phone }}</strong>
        </Col>
      </Row>
      <Row :gutter="16" class="mt16">
        <Col :xs="24" :sm="12">
          <p>{{ $tc('store') }}:</p>
          <div v-if="salary.user.location">
            <strong>{{ salary.user.location.name }}</strong
            ><br />
            {{ salary.user.location.address + ', ' + salary.user.location.state_name + ', ' + salary.user.location.country_name }}<br />
            {{ salary.user.location.phone }}<br />
            {{ salary.user.location.email }}<br />
          </div>
        </Col>
      </Row>

      <div class="table-wrapper">
        <table class="table">
          <tbody>
            <tr>
              <td>{{ $tc('account') }}</td>
              <td>{{ salary.account.name }}</td>
            </tr>
            <tr>
              <td class="bold">{{ $t('amount') }}</td>
              <td class="bold">{{ formatNumber(salary.amount) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <Card dis-hover v-if="checkExtraAttributes(salary.extra_attributes)" class="mt16">
        <span v-html="renderExtraAttributes(salary.extra_attributes, salary.attributes)"></span>
      </Card>
      <list-attachments-component :attachments="salary.attachments" @remove="deleteAttachment" class="mt16" />
      <Card dis-hover v-if="salary.details" class="mt16">{{ salary.details }}</Card>
      <p class="cgd">{{ $t('order_cgd') }}</p>
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
    salary: {
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
