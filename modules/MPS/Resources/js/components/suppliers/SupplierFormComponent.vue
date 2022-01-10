<template>
  <Card :dis-hover="true">
    <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('supplier') }}</p>
    <router-link to="/suppliers" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list') }} {{ $tc('supplier', 2) }}
    </router-link>
    <div>
      <Form ref="supplier" :model="form" :rules="rules" :label-width="150" class="form-responsive">
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
                <FormItem :label="$tc('company')" prop="company" :error="errors.form.company | a2s">
                  <Input v-model="form.company" />
                </FormItem>
                <FormItem :error="errors.form.opening_balance | a2s" :label="$t('opening_balance')" prop="opening_balance" v-if="!form.id">
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
              </Col>
              <Col :sm="24" :md="12" :lg="12">
                <Row :gutter="16" class="mb24">
                  <Col :sm="24" :md="12" :lg="24" :xl="12">
                    <FormItem :label="$t('house_no')" prop="house_no" :error="errors.form.house_no | a2s">
                      <Input v-model="form.house_no" />
                    </FormItem>
                  </Col>
                  <Col :sm="24" :md="12" :lg="24" :xl="12">
                    <FormItem :label="$t('street_no')" prop="street_no" :error="errors.form.street_no | a2s">
                      <Input v-model="form.street_no" />
                    </FormItem>
                  </Col>
                </Row>
                <FormItem :label="$t('address')" prop="address" :error="errors.form.address | a2s">
                  <Input v-model="form.address" />
                </FormItem>
                <FormItem :label="$t('city')" prop="city" :error="errors.form.city | a2s">
                  <Input v-model="form.city" />
                </FormItem>
                <FormItem :label="$t('postal_code')" prop="postal_code" :error="errors.form.postal_code | a2s">
                  <Input v-model="form.postal_code" />
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
              </Col>
            </Row>

            <form-custom-fields v-model="form" :attributes="attributes" @update="updateCF" />

            <FormItem>
              <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('suppliers')">
                <span v-if="!saving">{{ $t('submit') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button
                ghost
                type="primary"
                :loading="saving"
                :disabled="saving"
                style="margin-left: 8px;"
                @click="handleSubmit('suppliers', true)"
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
const formatRes = (data, vm) => {
  if (data.attributes) {
    vm.attributes = data.attributes;
    delete data.attributes;
  }
  data.extra_attributes = vm.formatAttributes(vm.attributes, data.extra_attributes);
  data.due_limit = data.due_limit ? parseFloat(data.due_limit) : null;
  vm.form = { ...data, ...data.extra_attributes };
  return vm.form;
};
export default {
  mixins: [Form('supplier', 'app/suppliers', true, formatRes)],
  data() {
    return {
      countries: [],
      searching: false,
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
        opening_balance: null,
      },
      rules: {
        name: [{ required: true, message: this.$t('name') + ' ' + this.$t('is_required'), trigger: 'blur' }],
        phone: [{ required: false, message: this.$t('phone') + ' ' + this.$t('is_required'), trigger: 'blur' }],
        email: [{ required: false, message: this.$t('email') + ' ' + this.$t('is_required'), trigger: 'blur' }],
        address: [{ required: false, message: this.$t('address') + ' ' + this.$t('is_required'), trigger: 'blur' }],
        country: [
          {
            required: this.$store.getters.require_country,
            message: this.$t('country') + ' ' + this.$t('is_required'),
            trigger: 'change',
          },
        ],
        state: [
          {
            required: this.$store.getters.require_country,
            message: this.$t('state') + ' ' + this.$t('is_required'),
            trigger: 'change',
          },
        ],
      },
    };
  },
  created() {
    this.$http.get('app/countries').then(countries => (this.countries = countries.data));
  },
  methods: {
    create() {
      this.$http
        .get('app/suppliers/create')
        .then(res => (this.attributes = res.data))
        .finally(() => (this.loading = false));
    },
    fetch(id) {
      this.$http
        .get(`app/suppliers/${id}`)
        .then(res => {
          // this.countryChange(res.data.country, res.data.state);
          // res.data.state = '';
          this.form = formatRes(res.data, this);
        })
        .finally(() => (this.loading = false));
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
      this.$http.get('app/states', { params: { country } }).then(res => {
        this.states = res.data;
        if (selected) {
          this.form.state = this.states.find(state => state.value == selected).value;
        }
        this.searching = false;
      });
    },
  },
};
</script>
