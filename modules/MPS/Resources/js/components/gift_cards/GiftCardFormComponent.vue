<template>
  <Card :dis-hover="true">
    <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('gift_card') }}</p>
    <router-link to="/gift_cards" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list') }} {{ $tc('gift_card', 2) }}
    </router-link>
    <div>
      <Form ref="gift_card" :model="form" :rules="rules" :label-width="150" class="form-responsive">
        <Row :gutter="16">
          <Col :sm="24" :md="24" :lg="24">
            <Loading v-if="loading" />
            <Alert type="error" show-icon class="mb26" v-if="errors.message">
              <div v-html="errors.message"></div>
            </Alert>
            <Row :gutter="16">
              <Col :sm="24" :md="18" :lg="12">
                <template v-if="!form.id">
                  <FormItem :label="$tc('number')" prop="number" :error="errors.form.number | a2s">
                    <Input v-model="form.number">
                      <Button slot="append" icon="md-refresh" @click="generateNumber"></Button>
                    </Input>
                  </FormItem>
                </template>
                <template v-else>
                  <FormItem :label="$tc('number')">
                    <Alert class="mb0">
                      {{ form.number }}
                    </Alert>
                  </FormItem>
                </template>
                <FormItem :label="$t('amount')" prop="amount" :error="errors.form.amount | a2s">
                  <InputNumber v-model="form.amount" />
                </FormItem>
                <FormItem :label="$tc('expiry_date')" prop="expiry_date" :error="errors.form.expiry_date | a2s">
                  <DatePicker type="date" v-model="form.expiry_date" format="yyyy-MM-dd" style="width: 100%;" />
                </FormItem>
                <FormItem :label="$tc('customer')" :error="errors.form.customer_id | a2s">
                  <Select
                    remote
                    clearable
                    filterable
                    :loading="searching"
                    v-model="form.customer_id"
                    @on-change="customerChanged"
                    :remote-method="searchCustomers"
                    :placeholder="$t('type_to_search', { x: $tc('customer') })"
                  >
                    <Option v-for="(option, index) in customers" :value="option.value" :key="index + option.value">{{
                      option.label
                    }}</Option>
                  </Select>
                </FormItem>
                <transition
                  mode="out-in"
                  name="slide-fade"
                  enter-active-class="animate__animated faster animate__fadeInDown"
                  leave-active-class="animate__animated fastest animate__fadeOutDown"
                >
                  <template v-if="customer && customer.points">
                    <div>
                      <FormItem :label="$t('points')" prop="points" :error="errors.form.points | a2s">
                        <InputNumber v-model="form.points" />
                      </FormItem>
                      <FormItem>
                        <Alert class="mb0">
                          {{ $t('available_x', { x: $t('points') }) }}: {{ formatNumber(customer.points - form.points) }}
                        </Alert>
                      </FormItem>
                    </div>
                  </template>
                </transition>
                <FormItem :label="$t('details')" prop="details" :error="errors.form.details | a2s">
                  <Input type="textarea" v-model="form.details" :autosize="{ minRows: 2, maxRows: 8 }" />
                </FormItem>
              </Col>
              <Col :sm="24" :md="6" :lg="12"> </Col>
            </Row>
            <FormItem>
              <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('gift_cards')">
                <span v-if="!saving">{{ $t('submit') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button
                ghost
                type="primary"
                :loading="saving"
                :disabled="saving"
                style="margin-left: 8px;"
                @click="handleSubmit('gift_cards', true)"
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
  data.amount = data.amount ? parseFloat(data.amount) : null;
  data.points = data.points ? parseFloat(data.points) : null;
  data.expiry_date = data.expiry_date ? new Date(data.expiry_date) : null;
  vm.form = { ...data };
  return vm.form;
};
export default {
  mixins: [Form('gift_card', 'app/gift_cards', false, formatRes)],
  data() {
    const pointsV = (rule, value, callback) => {
      if (value && this.customer && this.customer.points && parseFloat(value) > parseFloat(this.customer.points)) {
        callback(new Error(this.$t('low_points_error')));
      } else {
        callback();
      }
    };
    return {
      customer: {},
      customers: [],
      searching: false,
      form: { id: '', number: '', amount: null, customer_id: null, points: null, expiry_date: null, details: '' },
      rules: {
        number: [
          { required: true, type: 'number', message: this.$t('field_is_required', { field: this.$t('number') }), trigger: 'change' },
        ],
        amount: [
          { required: true, type: 'number', message: this.$t('field_is_required', { field: this.$t('amount') }), trigger: 'change' },
        ],
        points: [{ validator: pointsV, type: 'number', trigger: 'change' }],
      },
    };
  },
  created() {
    var d = new Date();
    var day = d.getDate();
    var month = d.getMonth();
    var year = d.getFullYear();
    this.form.expiry_date = new Date(year + 2, month, day);
  },
  methods: {
    fetch(id) {
      this.$http
        .get(`app/gift_cards/${id}`)
        .then(res => formatRes(res.data, this))
        .finally(() => (this.loading = false));
    },
    generateNumber() {
      this.form.number = Math.floor(1000000000 + Math.random() * 9000000000);
    },
    customerChanged(v) {
      this.customer = this.customers.find(c => c.value == v);
      if (!this.customer.points) {
        this.form.points = null;
      }
    },
    searchCustomers(search) {
      if (search !== '' && !this.customers.find(c => c.label == search)) {
        this.getCustomers(search, this);
      }
    },
    getCustomers: _debounce((search, vm) => {
      vm.searching = true;
      const search_delay = vm.$store.getters.search_delay;
      vm.$http
        .get('app/customers/search?q=' + search)
        .then(res => {
          vm.customers = res.data;
          if (vm.form.customer_id) {
            vm.customer = vm.customers.find(c => c.value == vm.form.customer_id);
            vm.customer.points = parseFloat(vm.customer.points) + parseFloat(vm.form.points);
          }
        })
        .finally(() => (vm.searching = false));
    }, search_delay || 250),
  },
};
</script>
