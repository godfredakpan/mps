<template>
  <Form ref="reportForm" :model="form" :rules="rules" label-position="top">
    <Row :gutter="16">
      <Col :sm="12" :md="12" v-if="fields.includes('date')">
        <FormItem :label="$t('date')">
          <DatePicker style="width:100%;" v-model="form.date" split-panels type="datetimerange" :options="dateOptions" />
        </FormItem>
      </Col>
      <Col :sm="12" :md="12" v-if="fields.includes('created_at')">
        <FormItem :label="$t('created_at')">
          <DatePicker style="width:100%;" type="datetimerange" split-panels :options="dateOptions" v-model="form.created_at" />
        </FormItem>
      </Col>
      <Col :sm="12" :md="6" v-if="fields.includes('reference')">
        <FormItem :label="$t('reference')">
          <Input v-model="form.reference" />
        </FormItem>
      </Col>
      <Col :sm="12" :md="6" v-if="fields.includes('status') && statusOptions.length">
        <FormItem :label="$t('status')">
          <Select v-model="form.status" placeholder>
            <Option :value="opt" v-for="opt in statusOptions" :key="opt">{{ $t(opt) }}</Option>
          </Select>
        </FormItem>
      </Col>
      <Col :sm="12" :md="6" v-if="fields.includes('custom_fields')">
        <FormItem :label="$tc('field', 2)">
          <Input v-model="form.custom_fields" />
        </FormItem>
      </Col>
      <Col :sm="12" :md="6" v-if="fields.includes('location_id')">
        <FormItem :label="$tc('location')">
          <Select v-model="form.location_id" placeholder>
            <template v-if="locations.length > 0">
              <Option :key="index" :value="option.value" v-for="(option, index) in locations">
                {{ option.label }}
              </Option>
            </template>
          </Select>
        </FormItem>
      </Col>
      <Col :sm="12" :md="6" v-if="fields.includes('from_location')">
        <FormItem :label="$t('from_location')">
          <Select v-model="form.from_location_id" placeholder>
            <template v-if="locations.length > 0">
              <Option :key="index" :value="option.value" v-for="(option, index) in locations">
                {{ option.label }}
              </Option>
            </template>
          </Select>
        </FormItem>
      </Col>
      <Col :sm="12" :md="6" v-if="fields.includes('to_location')">
        <FormItem :label="$t('to_location')">
          <Select v-model="form.to_location_id" placeholder>
            <template v-if="locations.length > 0">
              <Option :key="index" :value="option.value" v-for="(option, index) in locations">
                {{ option.label }}
              </Option>
            </template>
          </Select>
        </FormItem>
      </Col>
      <Col :sm="12" :md="6" v-if="fields.includes('account_id')">
        <FormItem :label="$tc('account')">
          <Select remote clearable filterable :loading="searching" v-model="form.account_id" placeholder>
            <Option v-for="(option, index) in accounts" :value="option.value" :key="index + option.value">{{ option.label }}</Option>
          </Select>
        </FormItem>
      </Col>
      <Col :sm="12" :md="6" v-if="fields.includes('category_id')">
        <FormItem :label="$tc('category')">
          <Select
            remote
            clearable
            filterable
            :loading="searching"
            v-model="form.category_id"
            :remote-method="searchCategories"
            :placeholder="$t('type_to_search')"
          >
            <Option v-for="(option, index) in categories" :value="option.value" :key="index + option.value">{{ option.label }}</Option>
          </Select>
        </FormItem>
      </Col>
      <Col :sm="12" :md="6" v-if="fields.includes('customer_id')">
        <FormItem :label="$tc('customer')">
          <Select
            remote
            clearable
            filterable
            :loading="searching"
            v-model="form.customer_id"
            :remote-method="searchCustomers"
            :placeholder="$t('type_to_search')"
          >
            <Option v-for="(option, index) in customers" :value="option.value" :key="index + option.value">{{ option.label }}</Option>
          </Select>
        </FormItem>
      </Col>
      <Col :sm="12" :md="6" v-if="fields.includes('supplier_id')">
        <FormItem :label="$tc('supplier')">
          <Select
            remote
            clearable
            filterable
            :loading="searching"
            v-model="form.supplier_id"
            :remote-method="searchSuppliers"
            :placeholder="$t('type_to_search')"
          >
            <Option v-for="(option, index) in suppliers" :value="option.value" :key="index + option.value">{{ option.label }}</Option>
          </Select>
        </FormItem>
      </Col>
      <Col :sm="12" :md="6" v-if="fields.includes('user_id')">
        <FormItem :label="$t('created_by')">
          <Select
            remote
            clearable
            filterable
            :loading="searching"
            v-model="form.user_id"
            :remote-method="searchUsers"
            :placeholder="$t('type_to_search')"
          >
            <Option v-for="(option, index) in users" :value="option.value" :key="index + option.value">{{ option.label }}</Option>
          </Select>
        </FormItem>
      </Col>
      <Col :sm="12" :md="6" v-if="fields.includes('item_id')">
        <FormItem :label="$tc('item')">
          <Input v-model="form.item_id" />
        </FormItem>
      </Col>
      <Col :sm="12" :md="6" v-if="fields.includes('serial')">
        <FormItem :label="$tc('serial_number')">
          <Input v-model="form.serial" />
        </FormItem>
      </Col>
      <Col :sm="12" :md="6" v-if="fields.includes('title')">
        <FormItem :label="$t('title')">
          <Input v-model="form.title" />
        </FormItem>
      </Col>
      <Col :sm="12" :md="6" v-if="fields.includes('details')">
        <FormItem :label="$t('details')">
          <Input v-model="form.details" />
        </FormItem>
      </Col>
    </Row>
    <Row :gutter="16">
      <Col :sm="12" :md="6" v-if="fields.includes('approved')">
        <FormItem :label="$tc('approved')">
          <RadioGroup v-model="form.approved">
            <Radio label="">
              <span>{{ $t('all') }}</span>
            </Radio>
            <Radio label="1">
              <span>{{ $t('yes') }}</span>
            </Radio>
            <Radio label="0">
              <span>{{ $t('no') }}</span>
            </Radio>
          </RadioGroup>
        </FormItem>
      </Col>
      <Col :sm="12" :md="6" v-if="fields.includes('draft')">
        <FormItem :label="$tc('draft')">
          <RadioGroup v-model="form.draft">
            <Radio label="">
              <span>{{ $t('all') }}</span>
            </Radio>
            <Radio label="1">
              <span>{{ $t('yes') }}</span>
            </Radio>
            <Radio label="0">
              <span>{{ $t('no') }}</span>
            </Radio>
          </RadioGroup>
        </FormItem>
      </Col>
      <Col :sm="12" :md="6" v-if="fields.includes('paid')">
        <FormItem :label="$tc('paid')">
          <RadioGroup v-model="form.paid">
            <Radio label="">
              <span>{{ $t('all') }}</span>
            </Radio>
            <Radio label="1">
              <span>{{ $t('yes') }}</span>
            </Radio>
            <Radio label="0">
              <span>{{ $t('no') }}</span>
            </Radio>
          </RadioGroup>
        </FormItem>
      </Col>
      <Col :sm="12" :md="6" v-if="fields.includes('void')">
        <FormItem :label="$tc('void')">
          <RadioGroup v-model="form.void">
            <Radio label="">
              <span>{{ $t('all') }}</span>
            </Radio>
            <Radio label="1">
              <span>{{ $t('yes') }}</span>
            </Radio>
            <Radio label="0">
              <span>{{ $t('no') }}</span>
            </Radio>
          </RadioGroup>
        </FormItem>
      </Col>
      <Col :sm="12" :md="6" v-if="fields.includes('pos')">
        <FormItem :label="$tc('pos')">
          <RadioGroup v-model="form.pos">
            <Radio label="">
              <span>{{ $t('all') }}</span>
            </Radio>
            <Radio label="1">
              <span>{{ $t('yes') }}</span>
            </Radio>
            <Radio label="0">
              <span>{{ $t('no') }}</span>
            </Radio>
          </RadioGroup>
        </FormItem>
      </Col>
    </Row>
    <FormItem class="mb0">
      <Button type="primary" @click="handleSubmit()">{{ $t('submit') }}</Button>
      <Button @click="handleReset()" style="margin-left: 8px">{{ $t('reset') }}</Button>
    </FormItem>
  </Form>
</template>

<script>
import _debounce from 'lodash/debounce';
export default {
  props: {
    fields: {
      type: Array,
      required: true,
    },
    reportForm: {
      type: Object,
      default: {},
    },
    updated: {
      type: Number,
      default: 0,
    },
    statusOptions: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      form: {
        date: [],
        paid: '',
        void: '',
        draft: '',
        title: '',
        status: '',
        serial: '',
        details: '',
        item_id: '',
        user_id: '',
        reference: '',
        account_id: '',
        category_id: '',
        customer_id: '',
        location_id: '',
        supplier_id: '',
        custom_fields: '',
        to_location_id: '',
        from_location_id: '',
      },
      oForm: {},
      rules: {},
      users: [],
      accounts: [],
      locations: [],
      categories: [],
      customers: [],
      suppliers: [],
      searching: false,
      dateOptions: {
        shortcuts: [
          {
            text: this.$t('today'),
            value() {
              const today = new Date();
              return [new Date(today.setHours(0, 0, 0, 0)), new Date(today.setHours(23, 59, 59, 999))];
            },
          },
          // {
          //   text: this.$t('month'),
          //   value() {
          //     const date = new Date();
          //     const start = new Date(date.getFullYear(), date.getMonth(), 1);
          //     const end = new Date(date.getFullYear(), date.getMonth() + 1, 0);
          //     return [new Date(start.setHours(0, 0, 0, 0)), new Date(end.setHours(23, 59, 59, 999))];
          //   },
          // },
          {
            text: this.$t('yesterday'),
            value() {
              const yesterday = new Date();
              yesterday.setTime(yesterday.getTime() - 3600 * 1000 * 24);
              return [new Date(yesterday.setHours(0, 0, 0, 0)), new Date(yesterday.setHours(23, 59, 59, 999))];
            },
          },
          {
            text: this.$t('x_days', { x: 7 }),
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
              return [new Date(start.setHours(0, 0, 0, 0)), new Date(end.setHours(23, 59, 59, 999))];
            },
          },
          {
            text: this.$t('x_days', { x: 30 }),
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
              return [new Date(start.setHours(0, 0, 0, 0)), new Date(end.setHours(23, 59, 59, 999))];
            },
          },
          {
            text: this.$t('x_days', { x: 60 }),
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(start.getTime() - 3600 * 1000 * 24 * 60);
              return [new Date(start.setHours(0, 0, 0, 0)), new Date(end.setHours(23, 59, 59, 999))];
            },
          },
          {
            text: this.$t('x_days', { x: 90 }),
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
              return [new Date(start.setHours(0, 0, 0, 0)), new Date(end.setHours(23, 59, 59, 999))];
            },
          },
          {
            text: this.$t('x_days', { x: 180 }),
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(start.getTime() - 3600 * 1000 * 24 * 180);
              return [new Date(start.setHours(0, 0, 0, 0)), new Date(end.setHours(23, 59, 59, 999))];
            },
          },
          {
            text: this.$t('this_x', { x: this.$t('year') }),
            value() {
              const date = new Date();
              const start = new Date(date.getFullYear(), 0, 1);
              const end = new Date(date.getFullYear(), date.getMonth() + 1, 0);
              return [new Date(start.setHours(0, 0, 0, 0)), new Date(end.setHours(23, 59, 59, 999))];
            },
          },
          {
            text: this.$t('last_x', { x: this.$t('year') }),
            value() {
              const date = new Date();
              const start = new Date(date.getFullYear() - 1, 0, 1);
              const end = new Date(date.getFullYear(), 0, 0);
              return [new Date(start.setHours(0, 0, 0, 0)), new Date(end.setHours(23, 59, 59, 999))];
            },
          },
        ],
      },
    };
  },
  watch: {
    updated: {
      deep: true,
      handler() {
        this.fields.map(f => {
          this.form[f] = this.reportForm[f];
        });
      },
    },
  },
  created() {
    this.oForm = { ...this.form };
    this.$http
      .get('app/accounts/search')
      .then(res => {
        this.accounts = res.data;
        this.$http
          .get('app/locations/search')
          .then(res => (this.locations = res.data))
          .finally(() => (this.loading = false));
      })
      .finally(() => (this.searching = false));
  },
  mounted() {
    this.fields.map(f => {
      this.form[f] = this.reportForm[f];
    });
  },
  methods: {
    handleSubmit() {
      this.$emit('submit', this.form);
      return false;
    },
    handleReset() {
      this.form = { ...this.oForm };
      this.$refs.reportForm.resetFields();
      this.$emit('submit', this.form);
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
        .then(res => (vm.customers = res.data))
        .finally(() => (vm.searching = false));
    }, search_delay || 250),
    searchSuppliers(search) {
      if (search !== '' && !this.suppliers.find(c => c.label == search)) {
        this.getSuppliers(search, this);
      }
    },
    getSuppliers: _debounce((search, vm) => {
      vm.searching = true;
      const search_delay = vm.$store.getters.search_delay;
      vm.$http
        .get('app/suppliers/search?q=' + search)
        .then(res => (vm.suppliers = res.data))
        .finally(() => (vm.searching = false));
    }, search_delay || 250),
    searchUsers(search) {
      if (search !== '' && !this.users.find(c => c.label == search)) {
        this.getUsers(search, this);
      }
    },
    getUsers: _debounce((search, vm) => {
      vm.searching = true;
      const search_delay = vm.$store.getters.search_delay;
      vm.$http
        .get('app/users/search?q=' + search)
        .then(res => (vm.users = res.data))
        .finally(() => (vm.searching = false));
    }, search_delay || 250),
    searchCategories(search) {
      if (search !== '' && !this.categories.find(c => c.label == search)) {
        this.getCategories(search, this);
      }
    },
    getCategories: _debounce((search, vm) => {
      vm.searching = true;
      const search_delay = vm.$store.getters.search_delay;
      vm.$http
        .get('app/categories/search?q=' + search)
        .then(res => (vm.categories = res.data))
        .finally(() => (vm.searching = false));
    }, search_delay || 250),
  },
};
</script>
