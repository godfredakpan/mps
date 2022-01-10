<template>
  <div>
    <Form ref="address" :model="form" :rules="rules" label-position="top" autocomplete="off">
      <Row :gutter="16">
        <Col :sm="24" :md="24" :lg="24">
          <Alert type="error" show-icon class="mb26" v-if="errors.message">
            <div v-html="errors.message"></div>
          </Alert>
          <Row :gutter="16">
            <Col :sm="24" :md="12" :lg="12">
              <FormItem :label="$t('first_name')" prop="first_name" :error="errors.form.first_name | a2s">
                <Input v-model="form.first_name" />
              </FormItem>
            </Col>
            <Col :sm="24" :md="12" :lg="12">
              <FormItem :label="$t('last_name')" prop="last_name" :error="errors.form.last_name | a2s">
                <Input v-model="form.last_name" />
              </FormItem>
            </Col>
            <Col :sm="24" :md="12" :lg="12">
              <FormItem :label="$t('email')" prop="email" :error="errors.form.email | a2s">
                <Input v-model="form.email" />
              </FormItem>
            </Col>
            <Col :sm="24" :md="12" :lg="12">
              <FormItem :label="$t('phone')" prop="phone" :error="errors.form.phone | a2s">
                <Input v-model="form.phone" />
              </FormItem>
            </Col>
            <Col :sm="24" :md="24" :lg="24">
              <FormItem :label="$tc('company')" prop="company" :error="errors.form.company | a2s">
                <Input v-model="form.company" />
              </FormItem>
            </Col>
            <Col :sm="24" :md="12" :lg="12">
              <FormItem :label="$t('house_no')" prop="house_no" :error="errors.form.house_no | a2s">
                <Input v-model="form.house_no" />
              </FormItem>
            </Col>
            <Col :sm="24" :md="12" :lg="12">
              <FormItem :label="$t('street_no')" prop="street_no" :error="errors.form.street_no | a2s">
                <Input v-model="form.street_no" />
              </FormItem>
            </Col>
            <Col :sm="24" :md="24" :lg="24">
              <FormItem :label="$t('address')" prop="address" :error="errors.form.address | a2s">
                <Input v-model="form.address" />
              </FormItem>
            </Col>
            <Col :sm="24" :md="12" :lg="12">
              <FormItem :label="$t('city')" prop="city" :error="errors.form.city | a2s">
                <Input v-model="form.city" />
              </FormItem>
            </Col>
            <Col :sm="24" :md="12" :lg="12">
              <FormItem :label="$t('postal_code')" prop="postal_code" :error="errors.form.postal_code | a2s">
                <Input v-model="form.postal_code" />
              </FormItem>
            </Col>
            <Col :sm="24" :md="12" :lg="12">
              <FormItem :label="$tc('country')" prop="country" :error="errors.form.country | a2s" autocomplete="off">
                <Select v-model="form.country" placeholder filterable @on-change="countryChange">
                  <Option v-for="(option, index) in countries" :value="option.value" :key="index">{{ option.label }}</Option>
                </Select>
              </FormItem>
            </Col>
            <Col :sm="24" :md="12" :lg="12">
              <FormItem :label="$tc('state')" prop="state" :error="errors.form.state | a2s" autocomplete="off">
                <Select v-model="form.state" placeholder filterable :loading="searching">
                  <Option v-for="(option, index) in states" :value="option.value" :key="index">{{ option.label }}</Option>
                </Select>
              </FormItem>
            </Col>
          </Row>

          <!-- <form-custom-fields v-model="form" :attributes="attributes" @update="updateCF" /> -->
          <FormItem class="mb0">
            <Button type="primary" :loading="saving" :disabled="saving" @click="submit()">
              <span v-if="!saving">{{ $t('submit') }}</span>
              <span v-else>{{ $t('saving') }}...</span>
            </Button>
            <Button @click="handleReset()" style="margin-left: 8px">{{ $t('reset') }}</Button>
          </FormItem>
        </Col>
      </Row>
    </Form>
  </div>
</template>

<script>
export default {
  props: {
    customer_id: {
      required: true,
    },
    current: {
      required: false,
    },
  },
  data() {
    return {
      saving: false,
      address: [],
      countries: [],
      attributes: [],
      searching: false,
      customer_groups: [],
      errors: { message: '', form: {} },
      states: [{ value: '', label: this.$t('select_country') }],
      form: {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        country: '',
        state: '',
        house_no: '',
        street_no: '',
        company: '',
        address: '',
        city: '',
        postal_code: '',
        default: '',
      },
      rules: {
        first_name: [
          {
            required: true,
            trigger: 'blur',
            message: this.$t('field_is_required', { field: this.$t('first_name') }),
          },
        ],
        last_name: [
          {
            required: true,
            trigger: 'blur',
            message: this.$t('field_is_required', { field: this.$t('last_name') }),
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
        house_no: [
          {
            required: true,
            trigger: 'blur',
            message: this.$t('field_is_required', { field: this.$t('house_no') }),
          },
        ],
        street_no: [
          {
            required: true,
            trigger: 'blur',
            message: this.$t('field_is_required', { field: this.$t('street_no') }),
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
            required: true,
            trigger: 'change',
            message: this.$t('field_is_required', { field: this.$t('country') }),
          },
        ],
        state: [
          {
            required: true,
            trigger: 'change',
            message: this.$t('field_is_required', { field: this.$t('state') }),
          },
        ],
        city: [
          {
            required: true,
            trigger: 'blur',
            message: this.$t('field_is_required', { field: this.$tc('city') }),
          },
        ],
        postal_code: [
          {
            required: true,
            trigger: 'blur',
            message: this.$t('field_is_required', { field: this.$tc('postal_code') }),
          },
        ],
      },
    };
  },
  created() {
    this.$http.get('app/countries').then(({ data }) => (this.countries = data));
  },
  watch: {
    current: function(address) {
      if (address) {
        this.getStates(address.country, address.state);
        this.form = address;
      } else {
        this.form.id = null;
        this.$refs.address.resetFields();
      }
    },
  },
  methods: {
    submit() {
      this.$refs.address.validate(valid => {
        if (valid) {
          this.form.customer_id = this.customer_id;
          if (this.form.id) {
            this.$http
              .put('app/customers/update_address/' + this.form.id, this.form)
              .then(res => {
                if (res.data.success) {
                  this.$Notice.destroy();
                  this.$Notice.success({ title: this.$tc('address') + ' ' + this.$t('updated'), desc: this.$t('record_updated') });
                  this.$emit('added', res.data.address);
                } else {
                  this.$Notice.error({ title: this.$t('failed'), desc: this.$t('failed_error_text') });
                }
              })
              .catch(error => (this.errors = error))
              .finally(() => (this.saving = false));
          } else {
            this.$http
              .post('app/customers/add_address', this.form)
              .then(res => {
                if (res.data.success) {
                  this.$Notice.destroy();
                  this.$Notice.success({ title: this.$tc('address') + ' ' + this.$t('added'), desc: this.$t('record_added') });
                  this.$emit('added', res.data.address);
                } else {
                  this.$Notice.error({ title: this.$t('failed'), desc: this.$t('failed_error_text') });
                }
              })
              .catch(error => (this.errors = error))
              .finally(() => (this.saving = false));
          }
        }
      });
    },
    handleReset() {
      this.$refs.address.resetFields();
      this.saving = false;
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
