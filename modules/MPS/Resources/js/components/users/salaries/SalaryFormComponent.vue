<template>
  <Card :dis-hover="true">
    <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('salary') }}</p>
    <router-link to="/users/salaries" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list') }} {{ $tc('salary', 2) }}
    </router-link>
    <div>
      <Form ref="salary" :model="form" :rules="rules" :label-width="150" class="form-responsive">
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
                <FormItem :label="$t('reference')" prop="reference" :error="errors.form.reference | a2s">
                  <Input v-model="form.reference" />
                </FormItem>
                <FormItem :label="$tc('user')" prop="user_id">
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
                <FormItem :label="$tc('account')" prop="account_id">
                  <Select remote clearable filterable :loading="searching" v-model="form.account_id" placeholder>
                    <Option v-for="(option, index) in accounts" :value="option.value" :key="index + option.value">{{
                      option.label
                    }}</Option>
                  </Select>
                </FormItem>
              </Col>
              <Col :sm="24" :md="12" :lg="12">
                <FormItem :label="$tc('amount')" prop="amount" :error="errors.form.amount | a2s">
                  <InputNumber v-model="form.amount" />
                </FormItem>
                <FormItem :label="$t('type')" prop="type" :error="errors.form.type | a2s">
                  <Select v-model="form.type" placeholder="">
                    <Option value="salary">{{ $tc('salary') }}</Option>
                    <Option value="commission">{{ $tc('commission') }}</Option>
                  </Select>
                </FormItem>
                <FormItem v-if="form.type == 'commission'" :label="$t('points')" prop="points" :error="errors.form.points | a2s">
                  <InputNumber v-model="form.points" />
                </FormItem>
                <FormItem :label="$t('status')" prop="status" :error="errors.form.status | a2s">
                  <Select v-model="form.status" placeholder="">
                    <Option value="due">{{ $t('due') }}</Option>
                    <Option value="paid">{{ $t('paid') }}</Option>
                  </Select>
                </FormItem>
              </Col>
            </Row>

            <attachments-component :error="errors.form.attachments | a2s" @selected="handleUpload" @clear="clearAttachments">
              <list-attachments-component :attachments="attachments" @remove="deleteAttachment" />
            </attachments-component>
            <FormItem :label="$t('details')" prop="details">
              <Input type="textarea" v-model="form.details" :autosize="{ minRows: 3, maxRows: 8 }" />
            </FormItem>
            <FormItem prop="advance" :error="errors.form.advance | a2s">
              <Checkbox v-model="form.advance" :true-value="1" :false-value="0">
                <span>{{ $t('advance_salary_text') }}</span>
              </Checkbox>
            </FormItem>
            <FormItem>
              <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('salaries')">
                <span v-if="!saving">{{ $t('submit') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button
                ghost
                type="primary"
                :loading="saving"
                :disabled="saving"
                style="margin-left: 8px;"
                @click="handleSubmit('salaries', true)"
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
import Attachment from '@mpsjs/mixins/Attachment';
const formatRes = (data, vm) => {
  data.amount = parseFloat(data.amount);
  data.advance = data.advance == 1 ? 1 : 0;
  vm.attachments = data.attachments && data.attachments.length ? [...data.attachments] : [];
  vm.form = { ...data };
  return vm.form;
};
export default {
  mixins: [Attachment, Form('salary', 'app/salaries', true, formatRes)],
  data() {
    return {
      users: [],
      accounts: [],
      salaries: [],
      account_id: null,
      searching: false,
      form: { id: '', type: '', amount: null, advance: 0, user_id: '', account_id: '', status: '', points: null },
      rules: {
        type: [{ required: true, message: this.$t('field_is_required', { field: this.$t('type') }), trigger: 'change' }],
        status: [{ required: true, message: this.$t('field_is_required', { field: this.$t('status') }), trigger: 'change' }],
        user_id: [{ required: true, message: this.$t('field_is_required', { field: this.$tc('user') }), trigger: 'change' }],
        account_id: [{ required: true, message: this.$t('field_is_required', { field: this.$tc('account') }), trigger: 'change' }],
        points: [
          { type: 'number', trigger: 'change', required: true, message: this.$t('field_is_required', { field: this.$t('points') }) },
        ],
        amount: [
          { required: true, type: 'number', message: this.$t('field_is_required', { field: this.$t('amount') }), trigger: 'change' },
        ],
        date: [
          {
            type: 'date',
            required: true,
            trigger: 'change',
            message: this.$t('field_is_required', { field: this.$t('date') }),
          },
        ],
      },
    };
  },
  methods: {
    create() {
      this.$http
        .get('app/accounts/search')
        .then(res => (this.accounts = res.data))
        .finally(() => (this.loading = false));
    },
    fetch(id) {
      this.$http
        .get('app/accounts/search')
        .then(res => {
          this.accounts = res.data;
          this.$http.get(`app/salaries/${id}`).then(res => formatRes(res.data, this));
        })
        .finally(() => (this.loading = false));
    },
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
  },
};
</script>
