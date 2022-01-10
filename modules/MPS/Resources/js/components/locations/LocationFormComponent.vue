<template>
  <Card :dis-hover="true">
    <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('location') }}</p>
    <router-link to="/settings/locations" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list') }} {{ $tc('location', 2) }}
    </router-link>
    <div>
      <Form ref="location" :model="form" :rules="rules" :label-width="150" class="form-responsive" autocomplete="off">
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
                <FormItem :label="$t('phone')" prop="phone" :error="errors.form.phone | a2s">
                  <Input v-model="form.phone" />
                </FormItem>
                <FormItem :label="$t('email')" prop="email" :error="errors.form.email | a2s">
                  <Input v-model="form.email" />
                </FormItem>
                <FormItem prop="account_id" :error="errors.form.account_id | a2s" :label="$t('default_x', { x: $tc('account') })">
                  <Select v-model="form.account_id" placeholder>
                    <template v-if="accounts.length > 0">
                      <Option :key="index" :value="option.value" v-for="(option, index) in accounts">{{ option.label }}</Option>
                    </template>
                  </Select>
                </FormItem>
                <FormItem :label="$tc('account', 2)" prop="accounts" :error="errors.form.accounts | a2s">
                  <Select v-model="form.accounts" multiple :placeholder="$t('select_x', { x: $tc('account', 2) })">
                    <template v-if="accounts.length > 0">
                      <Option :key="index" :value="option.value" v-for="(option, index) in accounts">{{ option.label }}</Option>
                    </template>
                  </Select>
                </FormItem>
                <FormItem :label="$t('color')" prop="color" :error="errors.form.color | a2s">
                  <ColorPicker v-model="form.color" recommend />
                </FormItem>
                <FormItem :label="$tc('logo')" prop="logo">
                  <Upload :before-upload="handleUpload" :max-size="500" action>
                    <Button icon="ios-cloud-upload-outline">{{ $t('select_logo_to_upload') }}</Button>
                  </Upload>
                  <div v-if="new_logo" class="primary">{{ $t('selected_file') }}: {{ form.logo.name }}</div>
                  <img v-if="new_logo || logo" :src="new_logo ? new_logo : logo" alt="logo" style="max-width: 250px; max-height: 80px;" />
                </FormItem>
              </Col>
              <Col :sm="24" :md="12" :lg="12">
                <FormItem :label="$t('house_no')" prop="house_no" :error="errors.form.house_no | a2s">
                  <Input v-model="form.house_no" />
                </FormItem>
                <FormItem :label="$t('street_no')" prop="street_no" :error="errors.form.street_no | a2s">
                  <Input v-model="form.street_no" />
                </FormItem>
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
                  <Select v-model="form.country" placeholder filterable @on-change="countryChange" autocomplete="none">
                    <Option v-for="(option, index) in countries" :value="option.value" :key="index">{{ option.label }}</Option>
                  </Select>
                </FormItem>
                <FormItem :label="$tc('state')" prop="state" :error="errors.form.state | a2s">
                  <Select v-model="form.state" placeholder filterable :loading="searching" autocomplete="none">
                    <Option v-for="(option, index) in states" :value="option.value" :key="index">{{ option.label }}</Option>
                  </Select>
                </FormItem>
              </Col>
              <Col :sm="24" :md="12" :lg="24" :xl="12">
                <FormItem :label="$t('receipt_header')" prop="header" :error="errors.form.header | a2s">
                  <Input type="textarea" v-model="form.header" :autosize="{ minRows: 2, maxRows: 5 }" />
                </FormItem>
              </Col>
              <Col :sm="24" :md="12" :lg="24" :xl="12">
                <FormItem :label="$t('receipt_footer')" prop="footer" :error="errors.form.footer | a2s">
                  <Input type="textarea" v-model="form.footer" :autosize="{ minRows: 2, maxRows: 5 }" />
                </FormItem>
              </Col>
            </Row>
            <Card :dis-hover="true">
              <p slot="title">{{ $tc('register', 2) }}</p>
              <a slot="extra" @click="handleAdd">
                {{ $t('add_x', { x: $tc('register') }) }}
              </a>
              <Row :gutter="16">
                <Col :sm="24" :md="12" :lg="12" v-for="(r, i) in this.form.registers" :key="'reg_' + i">
                  <Card :dis-hover="true" :class="{ mt16: i > 1 }">
                    <p slot="title">{{ $tc('register') }} {{ r.code ? r.code : i + 1 }}</p>
                    <a slot="extra" @click="handleRemove(i)">
                      {{ $t('remove') }}
                    </a>
                    <FormItem :label="$t('code')" prop="reg_code">
                      <Input v-model="r.code" />
                    </FormItem>
                    <FormItem :label="$t('name')" prop="reg_name">
                      <Input v-model="r.name" />
                    </FormItem>
                  </Card>
                </Col>
              </Row>
            </Card>

            <form-custom-fields v-model="form" :attributes="attributes" @update="updateCF" />

            <FormItem>
              <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('locations')">
                <span v-if="!saving">{{ $t('submit') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button
                ghost
                type="primary"
                :loading="saving"
                :disabled="saving"
                style="margin-left: 8px;"
                @click="handleSubmit('locations', true)"
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
export default {
  mixins: [Form('location', 'app/locations', true)],
  data() {
    return {
      logo: false,
      accounts: [],
      countries: [],
      new_logo: false,
      searching: false,
      form: {
        id: '',
        name: '',
        color: '',
        email: '',
        phone: '',
        state: '',
        header: '',
        footer: '',
        address: '',
        country: '',
        attributes: {},
        company_id: '',
        registers: [{ id: '', code: '', name: '' }],
      },
      states: [{ value: '', label: this.$t('select_country') }],
      rules: {
        name: [{ required: true, message: this.$t('field_is_required', { field: this.$t('name') }), trigger: 'blur' }],
        phone: [{ required: true, message: this.$t('field_is_required', { field: this.$t('phone') }), trigger: 'blur' }],
        email: [{ required: true, message: this.$t('field_is_required', { field: this.$t('email') }), trigger: 'blur' }],
        // address: [{ required: true, message: this.$t('field_is_required', { field: this.$t('address') }), trigger: 'blur' }],
        account_id: [{ required: true, message: this.$t('field_is_required', { field: this.$tc('account') }), trigger: 'change' }],
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
    this.$http.get('app/accounts/search').then(res => (this.accounts = res.data));
    this.$http.get('app/countries').then(countries => (this.countries = countries.data));
  },
  methods: {
    create() {
      this.$http
        .get('app/locations/create')
        .then(res => (this.attributes = res.data))
        .finally(() => (this.loading = false));
    },
    fetch(id) {
      this.$http
        .get(`app/locations/${id}`)
        .then(res => {
          this.attributes = res.data.attributes;
          delete res.data.attributes;
          this.logo = res.data.logo;
          delete res.data.logo;
          this.countryChange(res.data.country, res.data.state);
          res.data.state = '';
          res.data.color = res.data.color || '';
          this.form = { ...res.data, ...res.data.extra_attributes };
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
    handleUpload(file) {
      var reader = new FileReader();
      reader.addEventListener('load', () => (this.new_logo = reader.result), false);
      this.form['logo'] = file;
      reader.readAsDataURL(file);
      return false;
    },
    onSelect(name) {
      if (name) {
        if (name == 'mail_settings') {
          this.email = true;
        } else if (name == 'payment_settings') {
          this.payment = true;
        } else {
          this.$router.push({ name });
        }
      }
    },
    handleAdd() {
      this.form.registers.push({ id: '', code: '', name: '' });
    },
    handleRemove(i) {
      this.form.registers.splice(i, 1);
    },
  },
};
</script>
