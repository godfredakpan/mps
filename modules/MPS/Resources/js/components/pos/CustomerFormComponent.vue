<template>
  <Form ref="customer" :model="form" :rules="rules" :label-width="150" class="form-responsive">
    <Row :gutter="16">
      <Col :sm="24" :md="24" :lg="24">
        <Loading v-if="loading" />
        <Alert type="error" show-icon class="mb26" v-if="errors.message">
          <div v-html="errors.message"></div>
        </Alert>

        <FormItem :label="$t('name')" prop="name" :error="errors.form.name | a2s">
          <Input v-model="form.name" />
        </FormItem>
        <FormItem :label="$tc('company')" prop="company" :error="errors.form.company | a2s">
          <Input v-model="form.company" />
        </FormItem>
        <FormItem :error="errors.form.title | a2s" :label="$t('opening_balance')" prop="opening_balance" v-if="!form.id">
          <InputNumber v-model="form.opening_balance"></InputNumber>
        </FormItem>
        <FormItem :label="$t('phone')" prop="phone" :error="errors.form.phone | a2s">
          <Input v-model="form.phone" />
        </FormItem>
        <FormItem :label="$t('email')" prop="email" :error="errors.form.email | a2s">
          <Input v-model="form.email" />
        </FormItem>
        <FormItem :label="$t('due_limit')" prop="due_limit" :error="errors.form.due_limit | a2s">
          <InputNumber v-model="form.due_limit" />
        </FormItem>

        <FormItem :label="$tc('customer_group')" prop="customer_group" :error="errors.form.customer_group | a2s">
          <Select clearable v-model="form.customer_group_id" :placeholder="$t('select_x', { x: $tc('customer_group') })">
            <Option v-for="(option, index) in customer_groups" :value="option.value" :key="index">{{ option.label }}</Option>
          </Select>
        </FormItem>
        <FormItem :label="$t('address')" prop="address" :error="errors.form.address | a2s">
          <Input type="textarea" v-model="form.address" :autosize="{ minRows: 2, maxRows: 5 }" />
        </FormItem>
        <FormItem :label="$tc('country')" prop="country" :error="errors.form.country | a2s">
          <Select v-model="form.country" placeholder filterable @on-change="countryChange">
            <Option v-for="(option, index) in countries" :value="option.value" :key="index">{{ option.label }}</Option>
          </Select>
        </FormItem>
        <FormItem :label="$tc('state')" prop="state" :error="errors.form.state | a2s">
          <Select v-model="form.state" placeholder filterable :loading="searching">
            <Option v-for="(option, index) in states" :value="option.value" :key="index">{{ option.label }}</Option>
          </Select>
        </FormItem>

        <form-custom-fields v-model="form" :attributes="attributes" @update="updateCF" />

        <FormItem class="mb0">
          <Button type="primary" :loading="saving" :disabled="saving" @click="submit()">
            <span v-if="!saving">{{ $t('submit') }}</span>
            <span v-else>{{ $t('saving') }}...</span>
          </Button>
          <Button v-if="!form.id" @click="handleReset()" style="margin-left: 8px;">{{ $t('reset') }}</Button>
        </FormItem>
      </Col>
    </Row>
  </Form>
</template>

<script>
export default {
  data() {
    return {
      saving: false,
      countries: [],
      attributes: [],
      loading: false,
      searching: false,
      customer_groups: [],
      errors: { message: '', form: {} },
      states: [{ value: '', label: this.$t('select_country') }],
      form: {
        id: '',
        name: '',
        email: '',
        phone: '',
        state: '',
        number: '',
        footer: '',
        address: '',
        country: '',
        show_tax: 0,
        due_limit: null,
        show_discount: 0,
        require_country: 0,
        customer_group_id: '',
        opening_balance: null,
      },
      rules: {
        name: [
          {
            required: true,
            trigger: 'blur',
            message: this.$t('field_is_required', { field: this.$t('name') }),
          },
        ],
        phone: [
          {
            required: false,
            trigger: 'blur',
            message: this.$t('field_is_required', { field: this.$t('phone') }),
          },
        ],
        email: [
          {
            required: false,
            trigger: 'blur',
            message: this.$t('field_is_required', { field: this.$t('email') }),
          },
        ],
        address: [
          {
            required: false,
            trigger: 'blur',
            message: this.$t('field_is_required', { field: this.$t('address') }),
          },
        ],
        country: [
          {
            trigger: 'change',
            required: this.$store.getters.require_country,
            message: this.$t('field_is_required', { field: this.$t('country') }),
          },
        ],
        state: [
          {
            trigger: 'change',
            required: this.$store.getters.require_country,
            message: this.$t('field_is_required', { field: this.$t('state') }),
          },
        ],
        customer_group_id: [
          {
            required: false,
            trigger: 'change',
            message: this.$t('field_is_required', { field: this.$tc('customer_group') }),
          },
        ],
      },
    };
  },
  created() {
    this.$http
      .get('app/customers/create')
      .then(res => (this.attributes = res.data))
      .finally(() => (this.loading = false));
    this.$http.get('app/countries').then(({ data }) => (this.countries = data));
    this.$http.get('app/customer_groups/search').then(({ data }) => (this.customer_groups = data));
  },
  methods: {
    submit() {
      this.$refs.customer.validate(valid => {
        if (valid) {
          this.saving = true;
          let msg = 'added';
          let msg_text = 'record_added';
          this.$http
            .post('app/pos/add_customer', this.form)
            .then(res => {
              if (res.data.success) {
                this.$Notice.destroy();
                this.$Notice.success({ title: this.$tc('customer') + ' ' + this.$t(msg), desc: this.$t(msg_text) });
                this.$emit('added', res.data.customer);
              } else {
                this.$Notice.error({ title: this.$t('failed'), desc: this.$t('failed_error_text') });
              }
            })
            .catch(error => (this.errors = error))
            .finally(() => (this.saving = false));
        }
      });
    },
    handleReset() {
      this.$refs.address.resetFields();
      this.saving = false;
    },
    updateCF(field, value) {
      this.form[field] = value;
      setTimeout(() => {
        this.$refs[model].validateField(field);
      }, 1000);
    },
    countryChange(code, state = '') {
      if (code) {
        this.searching = true;
        this.getStates(code, state);
        this.form.state = '';
      } else {
        this.form.state = '';
        this.form.country = '';
        this.states = [{ value: '', label: this.$t('select_country') }];
      }
    },
    getStates(country, selected) {
      this.$http
        .get('app/states', { params: { country } })
        .then(res => {
          this.states = res.data;
          if (selected) {
            this.form.state = this.states.find(state => state.value == selected).value;
          }
          this.searching = false;
        })
        .catch(err => this.$event.fire('appError', err.response));
    },
  },
};
</script>
