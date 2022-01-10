<template>
  <Card :dis-hover="true">
    <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('customer_group') }}</p>
    <router-link to="/settings/customer_groups" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list') }} {{ $tc('customer_group', 2) }}
    </router-link>
    <div>
      <Form ref="customer_group" :model="form" :rules="rules" :label-width="150" class="form-responsive">
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
                <FormItem :error="errors.form.title | a2s" :label="$t('opening_balance')" prop="opening_balance" v-if="!form.id">
                  <InputNumber v-model="form.opening_balance"></InputNumber>
                </FormItem>
                <FormItem :label="$t('phone')" prop="phone" :error="errors.form.phone | a2s">
                  <Input v-model="form.phone" />
                </FormItem>
                <FormItem :label="$t('email')" prop="email" :error="errors.form.email | a2s">
                  <Input v-model="form.email" />
                </FormItem>
              </Col>
              <Col :sm="24" :md="12" :lg="12">
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
              </Col>
            </Row>

            <form-custom-fields v-model="form" :attributes="attributes" @update="updateCF" />

            <FormItem>
              <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('customer_groups')">
                <span v-if="!saving">{{ $t('submit') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button
                ghost
                type="primary"
                :loading="saving"
                :disabled="saving"
                style="margin-left: 8px;"
                @click="handleSubmit('customer_groups', true)"
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
  mixins: [Form('customer_group', 'app/customer_groups', true)],
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
        show_discount: 0,
        require_country: 0,
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
            required: true,
            trigger: 'blur',
            message: this.$t('field_is_required', { field: this.$t('phone') }),
          },
        ],
        email: [
          {
            required: true,
            trigger: 'blur',
            message: this.$t('field_is_required', { field: this.$t('email') }),
          },
        ],
        address: [
          {
            required: true,
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
      },
    };
  },
  created() {
    this.$http.get('app/countries').then(countries => (this.countries = countries.data));
  },
  methods: {
    create() {
      this.$http
        .get('app/customer_groups/create')
        .then(res => (this.attributes = res.data))
        .finally(() => (this.loading = false));
    },
    fetch(id) {
      this.$http
        .get(`app/customer_groups/${id}`)
        .then(res => {
          this.attributes = res.data.attributes;
          delete res.data.attributes;
          this.countryChange(res.data.country, res.data.state);
          res.data.state = '';
          this.form = res.data;
          this.loading = false;
        })
        .catch(err => this.$event.fire('appError', err.response));
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
