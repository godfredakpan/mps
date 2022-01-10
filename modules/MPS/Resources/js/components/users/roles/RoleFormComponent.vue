<template>
  <Card :dis-hover="true">
    <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('role') }}</p>
    <router-link to="/users/roles" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list') }} {{ $tc('role', 2) }}
    </router-link>
    <div>
      <Form ref="role" :model="form" :rules="rules" :label-width="150" class="form-responsive">
        <Row :gutter="16">
          <Col :sm="24" :md="24" :lg="24">
            <Loading v-if="loading" />
            <Alert type="error" show-icon class="mb26" v-if="errors.message">
              <div v-html="errors.message"></div>
            </Alert>
            <Row :gutter="16">
              <Col :sm="24" :md="18" :lg="12">
                <FormItem :label="$tc('name')" prop="name" :error="errors.form.name | a2s">
                  <Input v-model="form.name" element-id="role-name" />
                </FormItem>
              </Col>
              <Col :sm="24" :md="6" :lg="12"></Col>
            </Row>
            <FormItem>
              <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('roles')">
                <span v-if="!saving">{{ $t('submit') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button
                ghost
                type="primary"
                :loading="saving"
                :disabled="saving"
                style="margin-left: 8px;"
                @click="handleSubmit('roles', true)"
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
  data.order = data.order ? parseFloat(data.order) : null;
  vm.form = { ...data };
  return vm.form;
};
export default {
  mixins: [Form('role', 'app/roles', false, formatRes)],
  data() {
    return {
      roles: [],
      form: { id: '', name: '' },
      rules: {
        name: [{ required: true, message: this.$t('field_is_required', { field: this.$t('name') }), trigger: 'blur' }],
      },
    };
  },
  methods: {
    fetch(id) {
      this.$http
        .get(`app/roles/${id}`)
        .then(res => formatRes(res.data, this))
        .finally(() => (this.loading = false));
    },
  },
};
</script>
