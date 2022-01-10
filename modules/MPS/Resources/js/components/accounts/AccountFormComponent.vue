<template>
  <Card :dis-hover="true">
    <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('account') }}</p>
    <router-link to="/settings/accounts" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list') }} {{ $tc('account', 2) }}
    </router-link>
    <div>
      <Form ref="account" :model="form" :rules="rules" :label-width="150" class="form-responsive">
        <Row :gutter="16">
          <Col :sm="24" :md="24" :lg="24">
            <Loading v-if="loading" />
            <Alert type="error" show-icon class="mb26" v-if="errors.message">
              <div v-html="errors.message"></div>
            </Alert>
            <Row :gutter="16">
              <Col :sm="24" :md="12" :lg="12">
                <FormItem :label="$t('name')" prop="name" :error="errors.form.name | a2s">
                  <Input v-model="form.name" />
                </FormItem>
                <FormItem :label="$t('type')" prop="type" :error="errors.form.type | a2s">
                  <Input v-model="form.type" />
                </FormItem>
                <FormItem :label="$tc('reference')" prop="reference" :error="errors.form.reference | a2s">
                  <Input v-model="form.reference" />
                </FormItem>
                <FormItem v-if="!form.id" prop="opening_balance" :label="$t('opening_balance')" :error="errors.form.opening_balance | a2s">
                  <InputNumber v-model="form.opening_balance"></InputNumber>
                </FormItem>
                <FormItem :label="$t('details')" prop="details" :error="errors.form.details | a2s">
                  <Input type="textarea" v-model="form.details" :autosize="{ minRows: 2, maxRows: 5 }" />
                </FormItem>
              </Col>
              <Col :sm="24" :md="12" :lg="12"></Col>
            </Row>
            <FormItem class="mb0" prop="offline" :error="errors.form.offline | a2s">
              <Checkbox v-model="form.offline" true-value="1" false-value="0">
                <span>{{ $t('show_in_offline') }}</span>
              </Checkbox>
            </FormItem>
            <FormItem prop="fees" :error="errors.form.fees | a2s">
              <Checkbox v-model="form.fees" true-value="1" false-value="0">
                <span>{{ $t('transaction_fees') }}</span>
              </Checkbox>
            </FormItem>

            <transition
              name="fade"
              mode="out-in"
              enter-active-class="animate__animated fast animate__fadeInDown"
              leave-active-class="animate__animated faster animate__fadeOutUp"
            >
              <Row :gutter="16" v-if="form.fees == 1">
                <Col :sm="24" :md="12" :lg="12">
                  <FormItem :label="$t('fixed')" prop="fixed" :error="errors.form.fixed | a2s">
                    <InputNumber v-model="form.fixed" />
                  </FormItem>
                  <FormItem :label="$t('percentage')" prop="percentage" :error="errors.form.percentage | a2s">
                    <InputNumber v-model="form.percentage" :formatter="value => `${value}%`" :parser="value => value.replace('%', '')" />
                  </FormItem>
                  <FormItem :label="$t('apply_to')" prop="apply_to" :error="errors.form.apply_to | a2s">
                    <Select v-model="form.apply_to" placeholder>
                      <Option value="in">{{ $t('credit') }}</Option>
                      <Option value="out">{{ $t('debit') }}</Option>
                      <Option value="both">{{ $t('transaction_both') }}</Option>
                    </Select>
                  </FormItem>
                </Col>
                <Col :sm="24" :md="12" :lg="12"></Col>
              </Row>
            </transition>
            <FormItem>
              <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('accounts')">
                <span v-if="!saving">{{ $t('submit') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button
                ghost
                type="primary"
                :loading="saving"
                :disabled="saving"
                style="margin-left: 8px;"
                @click="handleSubmit('accounts', true)"
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
export default {
  mixins: [Form('account', 'app/accounts')],
  data() {
    return {
      accounts: [],
      categories: [],
      form: {
        id: '',
        name: '',
        type: '',
        reference: '',
        details: '',
        offline: '',
        fees: '',
        fixed: null,
        percentage: null,
        apply_to: 'both',
        opening_balance: null,
      },
      rules: {
        name: [{ required: true, message: this.$t('field_is_required', { field: this.$t('name') }), trigger: 'blur' }],
        reference: [{ required: true, message: this.$t('field_is_required', { field: this.$t('reference') }), trigger: 'blur' }],
        type: [{ required: true, message: this.$t('field_is_required', { field: this.$t('type') }), trigger: 'blur' }],
        opening_balance: [
          {
            required: true,
            type: 'number',
            message: this.$t('field_is_required', { field: this.$t('opening_balance') }),
            trigger: 'blur',
          },
        ],
      },
    };
  },
  created() {
    this.$http.get('app/accounts/search').then(res => (this.accounts = res.data));
    this.$http.get('app/categories/search').then(res => (this.categories = res.data));
  },
  methods: {
    fetch(id) {
      this.$http.get(`app/accounts/${id}`).then(res => {
        this.form = res.data;
        this.loading = false;
      });
    },
  },
};
</script>
