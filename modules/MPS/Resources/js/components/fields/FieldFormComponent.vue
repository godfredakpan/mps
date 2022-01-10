<template>
  <Card :dis-hover="true">
    <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('field') }}</p>
    <router-link to="/settings/fields" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list') }} {{ $tc('field', 2) }}
    </router-link>
    <div>
      <Form ref="field" :model="form" :rules="rules" :label-width="150" class="form-responsive">
        <Row :gutter="16">
          <Col :sm="24" :md="24" :lg="24">
            <Loading v-if="loading" />
            <Alert type="error" show-icon class="mb26" v-if="errors.message">
              <div v-html="errors.message"></div>
            </Alert>
            <Row :gutter="16">
              <Col :sm="24" :md="12" :lg="12">
                <FormItem :label="$t('name')" prop="name" :error="errors.name | a2s">
                  <Input v-model="form.name" />
                </FormItem>
                <FormItem :label="$t('slug')" prop="slug" :error="errors.slug | a2s">
                  <Input v-model="form.slug" />
                </FormItem>
                <FormItem :label="$t('entities')" prop="entities" :error="errors.entitise | a2s">
                  <Select v-model="form.entities" multiple>
                    <!-- <Option value="asset_transfer">{{ $tc('asset_transfer') }}</Option> -->
                    <Option value="customer">{{ $tc('customer') }}</Option>
                    <Option value="delivery">{{ $tc('delivery') }}</Option>
                    <Option value="expense">{{ $tc('expense') }}</Option>
                    <Option value="income">{{ $tc('income') }}</Option>
                    <Option value="item">{{ $tc('item') }}</Option>
                    <Option value="location">{{ $tc('location') }}</Option>
                    <Option value="payment">{{ $tc('payment') }}</Option>
                    <Option value="purchase">{{ $tc('purchase') }}</Option>
                    <Option value="quotation">{{ $tc('quotation') }}</Option>
                    <Option value="return_order">{{ $tc('return_order') }}</Option>
                    <Option value="sale">{{ $tc('sale') }}</Option>
                    <Option value="stock_adjustment">{{ $tc('stock_adjustment') }}</Option>
                    <Option value="stock_transfer">{{ $tc('stock_transfer') }}</Option>
                    <Option value="supplier">{{ $tc('supplier') }}</Option>
                  </Select>
                </FormItem>
                <FormItem :label="$t('details')" prop="details" :error="errors.form.details | a2s">
                  <Input type="textarea" v-model="form.details" :autosize="{ minRows: 2, maxRows: 5 }" />
                </FormItem>
                <FormItem :label="$t('order_no')" prop="order" :error="errors.form.order | a2s">
                  <InputNumber v-model="form.order"></InputNumber>
                </FormItem>
              </Col>
              <Col :sm="24" :md="12" :lg="12"></Col>
            </Row>
            <FormItem :label="$t('type')" prop="type" :error="errors.type | a2s">
              <RadioGroup v-model="form.type">
                <Radio label="text" true-value="text">
                  <span>{{ $t('text') }}</span>
                </Radio>
                <Radio label="number" true-value="number">
                  <span>{{ $t('number') }}</span>
                </Radio>
                <Radio label="select" true-value="select">
                  <span>{{ $t('select') }}</span>
                </Radio>
                <Radio label="textarea" true-value="textarea">
                  <span>{{ $t('textarea') }}</span>
                </Radio>
                <Radio label="checkbox" true-value="checkbox">
                  <span>{{ $t('checkbox') }}</span>
                </Radio>
                <Radio label="radio" true-value="radio">
                  <span>{{ $t('radio') }}</span>
                </Radio>
                <Radio label="date" true-value="date">
                  <span>{{ $t('date') }}</span>
                </Radio>
                <Radio label="datetime" true-value="datetime">
                  <span>{{ $t('datetime') }}</span>
                </Radio>
              </RadioGroup>
            </FormItem>
            <transition name="slide-fade">
              <Row :gutter="16" v-if="reqiue_options">
                <Col :sm="24" :md="12" :lg="12">
                  <FormItem :label="$t('options')" prop="options" :error="errors.form.options | a2s">
                    <Input v-model="form.options" placeholder="Option 1|Option 2|Option 3" />
                    <small>{{ $t('opts_separated_by_pipe') }}</small>
                  </FormItem>
                </Col>
                <Col :sm="24" :md="12" :lg="12"></Col>
              </Row>
            </transition>
            <FormItem prop="required">
              <Checkbox v-model="form.required" :true-value="1" :false-value="0">
                <span>{{ $t('required') }}</span>
              </Checkbox>
            </FormItem>
            <FormItem>
              <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('fields')">
                <span v-if="!saving">{{ $t('submit') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button
                ghost
                type="primary"
                :loading="saving"
                :disabled="saving"
                style="margin-left: 8px;"
                @click="handleSubmit('fields', true)"
              >
                <span v-if="!saving">{{ $t('save_n_stay') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button type="warning" ghost @click="handleReset()" style="margin-left: 8px;">{{ $t('reset') }}</Button>
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
  mixins: [Form('field', 'app/fields')],
  data() {
    return {
      form: {
        id: '',
        name: '',
        slug: '',
        type: '',
        order: null,
        required: 0,
        description: '',
        options: [],
        entities: [
          'customer',
          'delivery',
          'expense',
          'income',
          'item',
          'location',
          'payment',
          'purchase',
          'quotation',
          'return_order',
          'sale',
          'stock_adjustment',
          'stock_transfer',
          'supplier',
        ],
      },
      rules: {
        name: [{ required: true, message: this.$t('field_is_required', { field: this.$t('name') }), trigger: 'blur' }],
        slug: [
          { required: true, message: this.$t('field_is_required', { field: this.$t('slug') }), trigger: 'blur' },
          {
            required: true,
            pattern: /^([a-zA-Z0-9-_]{2,25})$/,
            message: this.$t('alpha_dash_error'),
            trigger: ['blur', 'change'],
          },
        ],
        type: [{ required: true, message: this.$t('field_is_required', { field: this.$t('type') }), trigger: 'change' }],
        entities: [{ required: true, type: 'array', min: 1, message: this.$t('select_entities'), trigger: 'change' }],
        options: [{ required: true, message: this.$t('options') + ' ' + this.$t('are_required'), trigger: 'blur' }],
      },
    };
  },
  computed: {
    reqiue_options() {
      return this.form.type == 'select' || this.form.type == 'checkbox' || this.form.type == 'radio';
    },
  },
  methods: {
    fetch(id) {
      this.$http
        .get(`app/fields/${id}`)
        .then(res => {
          delete res.data.created_at;
          delete res.data.updated_at;
          res.data.order = parseInt(res.data.order);
          res.data.required = parseInt(res.data.required);
          this.form = res.data;
        })
        .catch(err => {
          this.$event.fire('appError', err.response);
        })
        .finally(() => (this.loading = false));
    },
  },
};
</script>
