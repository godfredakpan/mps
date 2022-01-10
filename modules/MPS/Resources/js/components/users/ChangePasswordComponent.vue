<template>
  <Card :dis-hover="true">
    <p slot="title">{{ $t('change_password') }}</p>
    <router-link to="/profile" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('profile') }}
    </router-link>
    <div>
      <Form ref="user" :model="form" :rules="rules" :label-width="150" class="form-responsive">
        <Row :gutter="16">
          <Col :sm="24" :md="24" :lg="24">
            <Loading v-if="loading" />
            <Alert type="error" show-icon class="mb26" v-if="errors.message">
              <div v-html="errors.message"></div>
            </Alert>
            <Row :gutter="16">
              <Col :sm="24" :md="12" :lg="12">
                <FormItem :label="$t('current_password')" prop="current" :error="errors.form.current | a2s">
                  <Input type="password" password v-model="form.current" />
                </FormItem>
                <FormItem :label="$t('new_password')" prop="password" :error="errors.form.password | a2s">
                  <Input type="password" password v-model="form.password" />
                </FormItem>
                <FormItem :label="$t('confirm_password')" prop="password_confirmation" :error="errors.form.password_confirmation | a2s">
                  <Input type="password" password v-model="form.password_confirmation" />
                </FormItem>
              </Col>
              <Col :sm="24" :md="12" :lg="12"></Col>
            </Row>
            <FormItem>
              <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('users')">
                <span v-if="!saving">{{ $t('submit') }}</span>
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
export default {
  mixins: [Form('user', 'app/profile/change_password', false)],
  data() {
    const confirm = (rule, value, callback) => {
      if (value !== this.form.password) {
        callback(new Error(this.$t('confirm_password_not_match')));
      } else {
        callback();
      }
    };
    return {
      form: {
        current: '',
        password: '',
        password_confirmation: '',
      },
      rules: {
        current: [{ required: true, message: this.$t('field_is_required', { field: this.$t('current_password') }), trigger: 'blur' }],
        password: [{ required: true, min: 6, message: this.$t('password_error'), trigger: 'blur' }],
        password_confirmation: [
          { required: true, message: this.$t('field_is_required', { field: this.$t('confirm_password') }), trigger: 'blur' },
          { validator: confirm, required: true, trigger: 'change' },
        ],
      },
    };
  },
};
</script>
