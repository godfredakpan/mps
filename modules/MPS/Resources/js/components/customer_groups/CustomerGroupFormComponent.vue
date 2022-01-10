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
                <FormItem :label="$tc('code')" prop="code" :error="errors.form.code | a2s">
                  <Input v-model="form.code" />
                </FormItem>
                <FormItem :label="$t('discount')" prop="discount" :error="errors.form.discount | a2s">
                  <InputNumber v-model="form.discount" :formatter="value => `${value}%`" :parser="value => value.replace('%', '')" />
                </FormItem>
              </Col>
              <Col :sm="24" :md="12" :lg="12"></Col>
            </Row>

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
const formatRes = (data, vm) => {
  data.discount = parseFloat(data.discount);
  return data;
};
export default {
  mixins: [Form('customer_group', 'app/customer_groups', false, formatRes)],
  data() {
    return {
      form: {
        id: '',
        name: '',
        code: '',
        discount: null,
      },
      rules: {
        name: [
          {
            required: true,
            trigger: 'blur',
            message: this.$t('field_is_required', { field: this.$t('name') }),
          },
        ],
        code: [
          {
            required: true,
            trigger: 'blur',
            message: this.$t('field_is_required', { field: this.$t('code') }),
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
      },
    };
  },
  methods: {
    fetch(id) {
      this.$http
        .get(`app/customer_groups/${id}`)
        .then(res => (this.form = formatRes(res.data)))
        .finally(() => (this.loading = false));
    },
  },
};
</script>
