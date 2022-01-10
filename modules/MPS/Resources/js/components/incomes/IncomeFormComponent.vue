<template>
  <Card :dis-hover="true">
    <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('income') }}</p>
    <router-link to="/incomes" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list') }} {{ $tc('income', 2) }}
    </router-link>
    <div>
      <Form ref="income" :model="form" :rules="rules" :label-width="150" class="form-responsive">
        <Row :gutter="16">
          <Col :sm="24" :md="24" :lg="24">
            <Loading v-if="loading" />
            <Alert type="error" show-icon class="mb26" v-if="errors.message">
              <div v-html="errors.message"></div>
            </Alert>
            <Row :gutter="16">
              <Col :sm="24" :md="12" :lg="12">
                <FormItem :label="$t('date')" prop="date" :error="errors.form.date | a2s">
                  <DatePicker type="date" v-model="form.date" format="yyyy-MM-dd" style="width: 100%;" />
                </FormItem>
                <FormItem :label="$t('title')" prop="title" :error="errors.form.title | a2s">
                  <Input v-model="form.title" />
                </FormItem>
                <FormItem :label="$t('amount')" prop="amount" :error="errors.form.amount | a2s">
                  <InputNumber v-model="form.amount"></InputNumber>
                </FormItem>
              </Col>
              <Col :sm="24" :md="12" :lg="12" class="sm-pt-24">
                <FormItem :label="$tc('reference')" prop="reference" :error="errors.form.reference | a2s">
                  <Input v-model="form.reference" />
                </FormItem>
                <FormItem :label="$tc('account')" prop="account_id" :error="errors.form.account_id | a2s">
                  <Select v-model="form.account_id" placeholder>
                    <Option v-if="accounts.length > 0" v-for="(option, index) in accounts" :value="option.value" :key="index">{{
                      option.label
                    }}</Option>
                  </Select>
                </FormItem>
                <FormItem :label="$tc('category')" prop="category_id" :error="errors.form.category_id | a2s">
                  <Select v-model="form.category_id" placeholder>
                    <Option :key="index" :value="option.id" v-if="categories.length > 0" v-for="(option, index) in categories">
                      {{ option.name }}
                    </Option>
                  </Select>
                </FormItem>
              </Col>
            </Row>

            <form-custom-fields v-model="form" :attributes="attributes" @update="updateCF" />
            <attachments-component :error="errors.form.attachments | a2s" @selected="handleUpload" @clear="clearAttachments">
              <list-attachments-component :attachments="attachments" @remove="deleteAttachment" />
            </attachments-component>
            <FormItem :label="$t('details')" prop="details" :error="errors.form.details | a2s">
              <Input type="textarea" v-model="form.details" :autosize="{ minRows: 2, maxRows: 5 }" />
            </FormItem>

            <FormItem>
              <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('incomes')">
                <span v-if="!saving">{{ $t('submit') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button
                ghost
                type="primary"
                :loading="saving"
                :disabled="saving"
                style="margin-left: 8px;"
                @click="handleSubmit('incomes', true)"
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
import Attachment from '@mpsjs/mixins/Attachment';
const formatRes = (data, vm) => {
  if (data.attributes) {
    vm.attributes = data.attributes;
    delete data.attributes;
  }
  data.extra_attributes = vm.formatAttributes(vm.attributes, data.extra_attributes);
  data.amount = parseFloat(data.amount);
  data.category_id = data.categories[0].id;
  vm.attachments = data.attachments && data.attachments.length ? [...data.attachments] : [];
  vm.form = { ...data, ...data.extra_attributes };
  return vm.form;
};
export default {
  mixins: [Attachment, Form('income', 'app/incomes', true, formatRes)],
  data() {
    return {
      accounts: [],
      categories: [],
      attributes: [],
      form: { id: '', date: new Date(), title: '', reference: '', amount: null, details: '', account_id: '', category_id: '' },
      rules: {
        title: [{ required: true, message: this.$t('field_is_required', { field: this.$t('title') }), trigger: 'blur' }],
        reference: [{ required: false, message: this.$t('field_is_required', { field: this.$t('reference') }), trigger: 'blur' }],
        account_id: [{ required: true, message: this.$t('field_is_required', { field: this.$t('account') }), trigger: 'change' }],
        category_id: [{ required: true, message: this.$t('field_is_required', { field: this.$t('category') }), trigger: 'change' }],
        date: [
          {
            type: 'date',
            required: true,
            trigger: 'change',
            message: this.$t('field_is_required', { field: this.$t('date') }),
          },
        ],
        amount: [
          {
            required: true,
            type: 'number',
            message: this.$t('field_is_required', { field: this.$t('amount') }),
            trigger: 'blur',
          },
        ],
      },
    };
  },
  created() {
    this.$http.get('app/accounts/search').then(res => (this.accounts = res.data));
    // this.$http.get('app/categories/search').then(res => (this.categories = res.data));
    this.$http.get('app/all_categories/search').then(res => (this.categories = this.flattenDeep(res.data)));
  },
  methods: {
    create() {
      this.$http
        .get('app/incomes/create')
        .then(res => (this.attributes = res.data))
        .finally(() => (this.loading = false));
    },
    fetch(id) {
      this.$http
        .get(`app/incomes/${id}`)
        .then(res => formatRes(res.data, this))
        .finally(() => (this.loading = false));
    },
  },
};
</script>
