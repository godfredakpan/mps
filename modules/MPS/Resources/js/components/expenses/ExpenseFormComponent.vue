<template>
  <Card :dis-hover="true">
    <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('expense') }}</p>
    <router-link to="/expenses" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list') }} {{ $tc('expense', 2) }}
    </router-link>
    <div>
      <Form ref="expense" :model="form" :rules="rules" :label-width="150" class="form-responsive">
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
                  <Input v-model="form.title"></Input>
                </FormItem>
                <FormItem :label="$t('amount')" prop="amount" :error="errors.form.amount | a2s">
                  <InputNumber v-model="form.amount"></InputNumber>
                </FormItem>
              </Col>
              <Col :sm="24" :md="12" :lg="12" class="sm-pt-24">
                <FormItem :label="$tc('reference')" prop="reference" :error="errors.form.reference | a2s">
                  <Input v-model="form.reference"></Input>
                </FormItem>
                <FormItem :label="$tc('account')" prop="account_id" :error="errors.form.account_id | a2s">
                  <Select v-model="form.account_id" placeholder>
                    <Option :value="option.value" :key="index" v-if="accounts.length > 0" v-for="(option, index) in accounts">{{
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

            <Row :gutter="16">
              <Col :sm="24" :md="12" :lg="12">
                <FormItem
                  prop="approved_by_id"
                  v-if="!$store.getters.superAdmin"
                  :label="$tc('require_approval_by')"
                  :error="errors.form.approved_by_id | a2s"
                >
                  <Select v-model="form.approved_by_id" placeholder>
                    <template v-if="accounts.length > 0">
                      <Option :key="index" :value="option.value" v-for="(option, index) in admins">
                        {{ option.label }}
                      </Option>
                    </template>
                  </Select>
                </FormItem>
              </Col>
            </Row>

            <form-custom-fields v-model="form" :attributes="attributes" @update="updateCF" />
            <attachments-component :error="errors.form.attachments | a2s" @selected="handleUpload" @clear="clearAttachments">
              <list-attachments-component :attachments="attachments" @remove="deleteAttachment" />
            </attachments-component>
            <FormItem :label="$t('details')" prop="details" :error="errors.form.details | a2s">
              <Input type="textarea" v-model="form.details" :autosize="{ minRows: 2, maxRows: 5 }"></Input>
            </FormItem>

            <FormItem prop="recurring">
              <Checkbox v-model="form.recurring" true-value="1" false-value="0">
                <span>{{ $t('recurring_expense_text') }}</span>
              </Checkbox>
            </FormItem>

            <transition
              mode="out-in"
              name="slide-in"
              enter-active-class="animate__animated faster animate__fadeInDown"
              leave-active-class="animate__animated fastest animate__fadeOutDown"
            >
              <div v-if="form.recurring == 1">
                <Row :gutter="16">
                  <Col :sm="24" :md="12" :lg="12">
                    <FormItem :label="$t('start_date')" prop="start_date" :error="errors.form.start_date | a2s">
                      <DatePicker type="date" v-model="form.start_date" format="yyyy-MM-dd" style="width: 100%;" />
                    </FormItem>
                  </Col>
                  <Col :sm="24" :md="12" :lg="12">
                    <FormItem :label="$t('repeat')" prop="repeat" :error="errors.form.repeat | a2s">
                      <Select v-model="form.repeat" placeholder="">
                        <Option value="daily">{{ $t('daily') }}</Option>
                        <Option value="weekly">{{ $t('weekly') }}</Option>
                        <Option value="monthly">{{ $t('monthly') }}</Option>
                        <Option value="quarterly">{{ $t('quarterly') }}</Option>
                        <Option value="semiannually">{{ $t('semiannually') }}</Option>
                        <Option value="annually">{{ $t('annually') }}</Option>
                        <Option value="biennially">{{ $t('biennially') }}</Option>
                        <Option value="triennially">{{ $t('triennially') }}</Option>
                      </Select>
                    </FormItem>
                  </Col>
                </Row>
                <Row :gutter="16">
                  <Col :sm="24" :md="12" :lg="12">
                    <FormItem prop="approved_by_id" :label="$tc('require_approval_by')" :error="errors.form.approved_by_id | a2s">
                      <Select v-model="form.approved_by_id" placeholder>
                        <template v-if="accounts.length > 0">
                          <Option :key="index" :value="option.value" v-for="(option, index) in admins">
                            {{ option.label }}
                          </Option>
                        </template>
                      </Select>
                    </FormItem>
                  </Col>
                  <Col :sm="24" :md="12" :lg="12">
                    <FormItem :label="$t('create_before')" prop="create_before" :error="errors.form.create_before | a2s">
                      <InputNumber v-model="form.create_before" />
                    </FormItem>
                  </Col>
                </Row>
              </div>
            </transition>

            <FormItem>
              <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('expenses')">
                <span v-if="!saving">{{ $t('submit') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button
                ghost
                type="primary"
                :loading="saving"
                :disabled="saving"
                style="margin-left: 8px;"
                @click="handleSubmit('expenses', true)"
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
  data.recurring = data.recurring == 1 ? '1' : '0';
  vm.attachments = data.attachments && data.attachments.length ? [...data.attachments] : [];
  vm.form = { ...data, ...data.extra_attributes };
  return vm.form;
};
export default {
  mixins: [Attachment, Form('expense', 'app/expenses', true, formatRes)],
  data() {
    return {
      admins: [],
      accounts: [],
      categories: [],
      attributes: [],
      form: {
        id: '',
        title: '',
        details: '',
        amount: null,
        reference: '',
        account_id: '',
        category_id: '',
        approved_by_id: '',
        date: new Date(),
        recurring: false,
        repeat: 'monthly',
        start_date: new Date(),
        create_before: null,
      },
      rules: {
        title: [{ required: true, message: this.$t('field_is_required', { field: this.$t('title') }), trigger: 'blur' }],
        reference: [{ required: false, message: this.$t('field_is_required', { field: this.$t('reference') }), trigger: 'blur' }],
        account_id: [{ required: true, message: this.$t('field_is_required', { field: this.$tc('account') }), trigger: 'change' }],
        category_id: [{ required: true, message: this.$t('field_is_required', { field: this.$tc('category') }), trigger: 'change' }],
        date: [
          {
            type: 'date',
            required: true,
            trigger: 'change',
            message: this.$t('field_is_required', { field: this.$t('date') }),
          },
        ],
        approved_by_id: [
          {
            trigger: 'change',
            required: !this.$store.getters.superAdmin,
            message: this.$t('field_is_required', { field: this.$t('require_approval_by') }),
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
    this.$http.get('app/users/search?roles=admin').then(res => (this.admins = res.data));
    // this.$http.get('app/categories/search').then(res => (this.categories = res.data));
    this.$http.get('app/all_categories/search').then(res => (this.categories = this.flattenDeep(res.data)));
  },
  methods: {
    create() {
      this.$http
        .get('app/expenses/create')
        .then(res => (this.attributes = res.data))
        .finally(() => (this.loading = false));
    },
    fetch(id) {
      this.$http
        .get(`app/expenses/${id}`)
        .then(res => formatRes(res.data, this))
        .finally(() => (this.loading = false));
    },
  },
};
</script>
