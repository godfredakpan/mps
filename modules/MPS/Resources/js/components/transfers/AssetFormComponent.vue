<template>
  <Card :dis-hover="true">
    <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('asset_transfer') }}</p>
    <router-link to="/transfers/asset" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list') }} {{ $tc('asset_transfer', 2) }}
    </router-link>
    <div>
      <Form ref="asset_transfer" :model="form" :rules="rules" :label-width="150" class="form-responsive">
        <Row :gutter="16">
          <Col :sm="24" :md="24" :lg="24">
            <Loading v-if="loading" />
            <Alert type="error" show-icon class="mb26" v-if="errors.message">
              <div v-html="errors.message"></div>
            </Alert>
            <Row :gutter="16">
              <Col :sm="24" :md="12" :lg="12">
                <FormItem :label="$t('from_account')" prop="from" :error="errors.form.from | a2s">
                  <Select v-model="form.from" placeholder>
                    <template v-if="accounts.length > 0">
                      <Option :key="index" :value="option.value" v-for="(option, index) in accounts">{{ option.label }}</Option>
                    </template>
                  </Select>
                </FormItem>
                <FormItem :label="$t('to_account')" prop="to" :error="errors.form.to | a2s">
                  <Select v-model="form.to" placeholder>
                    <template v-if="accounts.length > 0">
                      <Option :key="index" :value="option.value" v-for="(option, index) in accounts">{{ option.label }}</Option>
                    </template>
                  </Select>
                </FormItem>
                <FormItem :label="$tc('reference')" prop="reference" :error="errors.form.reference | a2s">
                  <Input v-model="form.reference" />
                </FormItem>
                <FormItem :label="$t('amount')" prop="amount" :error="errors.form.amount | a2s">
                  <InputNumber v-model="form.amount"></InputNumber>
                </FormItem>
                <FormItem :label="$t('details')" prop="details" :error="errors.form.details | a2s">
                  <Input type="textarea" v-model="form.details" :autosize="{ minRows: 4, maxRows: 8 }" />
                </FormItem>
              </Col>
              <Col :sm="24" :md="12" :lg="12"></Col>
            </Row>

            <form-custom-fields v-model="form" :attributes="attributes" @update="updateCF" />

            <FormItem>
              <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('transfers.asset')">
                <span v-if="!saving">{{ $t('submit') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button
                ghost
                type="primary"
                :loading="saving"
                :disabled="saving"
                style="margin-left: 8px;"
                @click="handleSubmit('transfers.asset', true)"
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
const formatRes = (data, vm) => {
  data.amount = parseFloat(data.amount);
  vm.form = { ...data };
  return vm.form;
};
export default {
  mixins: [Form('asset_transfer', 'app/transfers/asset', false, formatRes)],
  data() {
    const toAccount = (rule, value, callback) => {
      if (!value || value == '') {
        callback(new Error(this.$t('field_is_required', { field: this.$t('to_account') })));
      } else if (value == this.form.from) {
        callback(new Error(this.$t('to_account_same_error')));
      } else {
        callback();
      }
    };
    return {
      accounts: [],
      searching: false,
      form: {
        id: '',
        title: '',
        details: '',
        amount: null,
        reference: '',
        to: '',
        from: '',
      },
      rules: {
        // title: [{ required: true, message: this.$t('field_is_required', { field: this.$t('title') }), trigger: 'blur' }],
        // to: [{ required: true, message: this.$t('field_is_required', { field: this.$tc('account') }), trigger: 'change' }],
        from: [{ required: true, message: this.$t('field_is_required', { field: this.$tc('from_account') }), trigger: 'change' }],
        to: [{ required: true, validator: toAccount, trigger: 'change' }],
        amount: [
          {
            required: true,
            type: 'number',
            trigger: ['change', 'blur'],
            message: this.$t('field_is_required', { field: this.$tc('amount') }),
          },
        ],
      },
    };
  },
  created() {
    if (this.$route.query.amount) {
      this.max_amount = this.form.amount = parseFloat(this.$route.query.amount);
    }
    this.$http.get('app/accounts/search').then(res => (this.accounts = res.data));
  },
  methods: {
    fetch(id) {
      this.$http
        .get(`app/transfers/asset/${id}`)
        .then(res => formatRes(res.data, this))
        .finally(() => (this.loading = false));
    },
  },
};
</script>
