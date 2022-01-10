<template>
  <Card :dis-hover="true">
    <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('promo') }}</p>
    <router-link to="/settings/promos" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list') }} {{ $tc('promo', 2) }}
    </router-link>
    <div>
      <Form ref="promo" :model="form" :rules="rules" :label-width="150" class="form-responsive">
        <Row :gutter="16">
          <Col :sm="24" :md="24" :lg="24">
            <Loading v-if="loading" />
            <Alert type="error" show-icon class="mb26" v-if="errors.message">
              <div v-html="errors.message"></div>
            </Alert>
            <Row :gutter="16">
              <Col :sm="24" :md="12" :lg="12">
                <FormItem :label="$t('type')" prop="type" :error="errors.form.type | a2s">
                  <Select v-model="form.type" placeholder>
                    <Option value="simple">{{ $t('simple_discount') }}</Option>
                    <Option value="advance">{{ $t('advance_discount') }}</Option>
                    <Option value="BXGY">{{ $t('buy_x_get_y') }}</Option>
                    <Option value="SXGD">{{ $t('spend_x_get_discount') }}</Option>
                  </Select>
                </FormItem>
                <FormItem :label="$t('name')" prop="name" :error="errors.form.name | a2s">
                  <Input v-model="form.name" />
                </FormItem>
                <FormItem :label="$t('start_date')" prop="start_date" :error="errors.form.start_date | a2s">
                  <DatePicker type="date" v-model="form.start_date" format="yyyy-MM-dd" style="width: 100%;" />
                </FormItem>
                <FormItem :label="$t('end_date')" prop="end_date" :error="errors.form.end_date | a2s">
                  <DatePicker type="date" v-model="form.end_date" format="yyyy-MM-dd" style="width: 100%;" />
                </FormItem>
                <FormItem :label="$t('details')" prop="details" :error="errors.form.details | a2s">
                  <Input type="textarea" v-model="form.details" :autosize="{ minRows: 2, maxRows: 5 }" />
                </FormItem>
              </Col>
              <Col :sm="24" :md="12" :lg="12">
                <transition name="slide-fade">
                  <span v-if="form.type == 'BXGY'">
                    <FormItem prop="item_id_to_buy" :label="$t('item_to_buy')" :error="errors.item_id_to_buy | a2s">
                      <!-- <Input v-model="form.item_id_to_buy" /> -->
                      <Select remote clearable filterable v-model="form.item_id_to_buy" :loading="searching" :remote-method="searchItems">
                        <Option v-for="(option, index) in items" :value="option.value" :key="option.value">{{ option.label }}</Option>
                      </Select>
                    </FormItem>
                    <FormItem prop="quantity_to_buy" :label="$t('quantity_to_buy')" :error="errors.quantity_to_buy | a2s">
                      <InputNumber v-model="form.quantity_to_buy" />
                    </FormItem>
                    <FormItem prop="item_id_to_get" :label="$t('item_to_get')" :error="errors.item_id_to_get | a2s">
                      <!-- <Input v-model="form.item_id_to_get" /> -->
                      <Select remote clearable filterable v-model="form.item_id_to_get" :loading="searching" :remote-method="searchItems">
                        <Option v-for="(option, index) in items" :value="option.value" :key="option.value">{{ option.label }}</Option>
                      </Select>
                    </FormItem>
                    <FormItem prop="quantity_to_get" :label="$t('quantity_to_get')" :error="errors.quantity_to_get | a2s">
                      <InputNumber v-model="form.quantity_to_get" />
                    </FormItem>
                  </span>
                </transition>
                <transition name="slide-fade">
                  <span v-if="form.type == 'SXGD'">
                    <FormItem prop="amount_to_spend" :label="$t('amount_to_spend')" :error="errors.form.amount_to_spend | a2s">
                      <InputNumber v-model="form.amount_to_spend" />
                    </FormItem>
                  </span>
                </transition>
                <transition name="slide-fade">
                  <span v-if="form.type != 'BXGY'">
                    <FormItem :label="$tc('item', 2)" prop="items" :error="errors.form.items | a2s">
                      <Select remote multiple filterable v-model="form.items" :loading="searching" :remote-method="searchItems">
                        <Option v-for="(option, index) in items" :value="option.value" :key="option.value">{{ option.label }}</Option>
                      </Select>
                    </FormItem>
                    <!-- <FormItem :label="$t('combo_items')" prop="combo_items" :error="errors.form.combo_items | a2s">
                      <Input v-model="form.combo_items" />
                    </FormItem> -->
                    <FormItem :label="$tc('category', 2)" prop="categories" :error="errors.form.categories | a2s">
                      <Select multiple v-model="form.categories">
                        <Option v-for="(option, index) in categories" :value="option.value" :key="option.value">{{ option.label }}</Option>
                      </Select>
                    </FormItem>
                  </span>
                </transition>
                <transition name="slide-fade">
                  <span v-if="form.type == 'advance'">
                    <FormItem prop="quantity_to_buy" :label="$t('quantity_to_buy')" :error="errors.form.quantity_to_buy | a2s">
                      <InputNumber v-model="form.quantity_to_buy" />
                    </FormItem>
                  </span>
                </transition>
                <transition name="slide-fade">
                  <span v-if="form.type == 'simple' || form.type == 'advance' || form.type == 'SXGD'">
                    <FormItem :label="$t('discount_')" prop="discount" :error="errors.form.discount | a2s">
                      <InputNumber v-model="form.discount" />
                    </FormItem>
                    <!-- <FormItem
                      prop="discount_method"
                      :label="$t('discount_method')"
                      :error="errors.form.discount_method | a2s"
                    >
                      <Select v-model="form.discount_method" placeholder>
                        <Option value="fixed">{{ $t('fixed') }}</Option>
                        <Option value="percentage">{{ $t('percentage') }}</Option>
                      </Select>
                    </FormItem> -->
                  </span>
                </transition>
              </Col>
            </Row>
            <FormItem prop="active" :error="errors.form.active | a2s">
              <Checkbox v-model="form.active" :true-value="1" :false-value="0">
                <span>{{ $t('active') }}</span>
              </Checkbox>
            </FormItem>
            <FormItem>
              <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('promos')">
                <span v-if="!saving">{{ $t('submit') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button
                ghost
                type="primary"
                :loading="saving"
                :disabled="saving"
                style="margin-left: 8px;"
                @click="handleSubmit('promos', true)"
              >
                <span v-if="!saving">{{ $t('save_n_stay') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button v-if="!form.id" @click="handleReset()" style="margin-left: 8px">{{ $t('reset') }}</Button>
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
  data.discount = data.discount ? parseFloat(data.discount) : null;
  data.amount_to_spend = data.amount_to_spend ? parseFloat(data.amount_to_spend) : null;
  data.quantity_to_buy = data.quantity_to_buy ? parseFloat(data.quantity_to_buy) : null;
  data.quantity_to_get = data.quantity_to_get ? parseFloat(data.quantity_to_get) : null;
  if (data.items || data.item_id_to_buy) {
    vm.items = data.items.map(i => {
      return { label: i.name + ' (' + i.code + ')', value: i.id };
    });
    if (data.item_id_to_get) {
      vm.items.push({ label: data.item_to_get.name + ' (' + data.item_to_get.code + ')', value: data.item_to_get.id });
    }
  }
  if (data.categories) {
    data.categories = data.categories.map(c => c.id);
  }
  vm.form = { ...data };
  return vm.form;
};
export default {
  mixins: [Form('promo', 'app/promos', false, formatRes)],
  data() {
    return {
      logo: false,
      new_logo: false,
      searching: false,
      items: [],
      accounts: [],
      categories: [],
      form: {
        id: '',
        name: '',
        items: [],
        active: '1',
        details: '',
        end_date: '',
        discount: null,
        categories: [],
        start_date: '',
        type: 'simple',
        item_id_to_buy: '',
        item_id_to_get: '',
        quantity_to_buy: null,
        quantity_to_get: null,
        amount_to_spend: null,
        discount_method: 'percentage',
      },
      errors: { message: '', form: {} },
      rules: {
        type: [{ required: true, message: this.$t('field_is_required', { field: this.$t('type') }), trigger: 'change' }],
        name: [{ required: true, message: this.$t('field_is_required', { field: this.$t('name') }), trigger: 'blur' }],
        amount_to_spend: [
          {
            required: true,
            type: 'number',
            trigger: ['blur', 'change'],
            message: this.$t('field_is_required', { field: this.$t('amount_to_spend') }),
          },
        ],
        items: [
          {
            type: 'array',
            required: false,
            trigger: 'change',
            message: this.$t('field_is_required', { field: this.$tc('item', 2) }),
          },
        ],
        categories: [
          {
            type: 'array',
            required: false,
            trigger: 'change',
            message: this.$t('field_is_required', { field: this.$tc('category', 2) }),
          },
        ],
        discount: [
          {
            required: true,
            type: 'number',
            trigger: 'blur',
            message: this.$t('field_is_required', { field: this.$t('discount') }),
          },
        ],
        discount_method: [
          { required: true, message: this.$t('field_is_required', { field: this.$t('discount_method') }), trigger: 'blur' },
        ],
        item_id_to_buy: [{ required: true, message: this.$t('field_is_required', { field: this.$t('item_to_buy') }), trigger: 'blur' }],
        quantity_to_buy: [
          {
            required: true,
            type: 'number',
            trigger: 'blur',
            message: this.$t('field_is_required', { field: this.$t('quantity_to_buy') }),
          },
        ],
        item_id_to_get: [{ required: true, message: this.$t('field_is_required', { field: this.$t('item_to_get') }), trigger: 'blur' }],
        quantity_to_get: [
          {
            required: true,
            type: 'number',
            trigger: 'blur',
            message: this.$t('field_is_required', { field: this.$t('quantity_to_get') }),
          },
        ],
        // start_date: [{ required: true, message: this.$t('field_is_required', { field: this.$t('start_date') }), trigger: 'blur' }],
        // end_date: [{ required: true, message: this.$t('field_is_required', { field: this.$t('end_date') }), trigger: 'blur' }],
      },
    };
  },
  created() {
    this.$http
      .get('app/categories/search')
      .then(res => (this.categories = res.data))
      .finally(() => (this.loading = false));
  },
  methods: {
    fetch(id) {
      this.$http
        .get(`app/promos/${id}`)
        .then(res => (this.form = formatRes(res.data, this)))
        .then(() => {
          this.form.items = this.items.map(i => i.value);
          if (this.form.item_id_to_buy) {
            this.form.item_id_to_buy = '';
            this.form.item_id_to_get = '';
            this.$nextTick(() => {
              this.form.item_id_to_buy = this.form.item_to_buy.id;
              this.form.item_id_to_get = this.form.item_to_get.id;
            });
          }
        })
        .finally(() => (this.loading = false));
    },
    searchItems(search) {
      this.getItems(search, this);
    },
    getItems: _debounce((search, vm) => {
      vm.searching = true;
      const search_delay = vm.$store.getters.search_delay;
      vm.$http
        .get('app/items/search?select=1&q=' + search)
        .then(res => (vm.items = res.data))
        .finally(() => (vm.searching = false));
    }, search_delay || 250),
    handleSubmit(page, stay = false) {
      this.$refs.promo.validate(valid => {
        if (valid) {
          if (this.form.type == 'BXGY') {
            this.submit(page, stay);
          } else if (this.form.items.length || this.form.categories.length) {
            console.log('items');
            this.submit(page, stay);
          } else {
            this.errors.message = this.$t('select_items_or_categories');
            this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('select_items_or_categories'), duration: 10 });
          }
        } else {
          this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
        }
      });
    },
  },
};
</script>
