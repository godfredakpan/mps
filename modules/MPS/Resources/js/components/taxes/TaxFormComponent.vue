<template>
  <Card :dis-hover="true">
    <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('tax') }}</p>
    <router-link to="/settings/taxes" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list') }} {{ $tc('tax', 2) }}
    </router-link>
    <div>
      <Form ref="tax" :model="form" :rules="rules" :label-width="150" class="form-responsive">
        <Row :gutter="16">
          <Col :sm="24" :md="24" :lg="24">
            <Loading v-if="loading" />
            <Alert type="error" show-icon class="mb26" v-if="errors.message">
              <div v-html="errors.message"></div>
            </Alert>
            <Row :gutter="16">
              <Col :sm="24" :md="12" :lg="12">
                <FormItem :label="$t('code')" prop="code" :error="errors.code | a2s">
                  <Input v-model="form.code" />
                </FormItem>
                <FormItem :label="$t('name')" prop="name" :error="errors.name | a2s">
                  <Input v-model="form.name" />
                </FormItem>
                <FormItem :label="$t('rate')" prop="rate" :error="errors.rate | a2s">
                  <InputNumber v-model="form.rate" :precision="2"></InputNumber>
                </FormItem>
                <FormItem :label="$t('tax_number')" prop="number" :error="errors.number | a2s">
                  <Input v-model="form.number" />
                </FormItem>
                <FormItem :label="$t('details')" prop="details" :error="errors.form.details | a2s">
                  <Input type="textarea" v-model="form.details" :autosize="{ minRows: 2, maxRows: 5 }" />
                </FormItem>
              </Col>
              <Col :sm="24" :md="12" :lg="12"></Col>
            </Row>
            <FormItem prop="compound" class="mb0" :error="errors.form.compound | a2s">
              <Checkbox v-model="form.compound" :true-value="1" :false-value="0">
                <span>{{ $t('compound_tax_text') }}</span>
              </Checkbox>
            </FormItem>
            <FormItem prop="recoverable" class="mb0" :error="errors.form.recoverable | a2s">
              <Checkbox v-model="form.recoverable" :true-value="1" :false-value="0">
                <span>{{ $t('recoverable_tax_text') }}</span>
              </Checkbox>
            </FormItem>
            <FormItem prop="state" :class="{ mb0: form.state == 1 }" :error="errors.form.state | a2s">
              <Checkbox v-model="form.state" :true-value="1" :false-value="0">
                <span>{{ $t('state_tax_text') }}</span>
              </Checkbox>
            </FormItem>
            <transition name="slide-fade">
              <FormItem v-if="form.state" prop="same" :error="errors.form.same | a2s">
                <RadioGroup v-model="form.same">
                  <Radio :label="1" :true-value="1" :false-vale="0">
                    <span>{{ $t('same_state_text') }}</span>
                  </Radio>
                  <Radio :label="0" :true-value="0" :false-vale="1">
                    <span>{{ $t('different_state_text') }}</span>
                  </Radio>
                </RadioGroup>
              </FormItem>
            </transition>
            <FormItem>
              <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('taxes')">
                <span v-if="!saving">{{ $t('submit') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button
                ghost
                type="primary"
                :loading="saving"
                :disabled="saving"
                style="margin-left: 8px;"
                @click="handleSubmit('taxes', true)"
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
  mixins: [Form('tax', 'app/taxes')],
  data() {
    return {
      form: { id: '', code: '', name: '', rate: null, number: '', description: '', compound: 0, recoverable: 0, state: 0, same: 0 },
      rules: {
        name: [{ required: true, message: this.$t('field_is_required', { field: this.$t('name') }), trigger: 'blur' }],
        code: [{ required: true, message: this.$t('field_is_required', { field: this.$t('code') }), trigger: 'blur' }],
        rate: [{ required: true, type: 'number', message: this.$t('field_is_required', { field: this.$t('rate') }), trigger: 'blur' }],
      },
    };
  },
  methods: {
    fetch(id) {
      this.$http
        .get(`app/taxes/${id}`)
        .then(res => {
          delete res.data.created_at;
          delete res.data.updated_at;
          res.data.same = res.data.same ? 1 : 0;
          res.data.state = res.data.state ? 1 : 0;
          res.data.rate = parseFloat(res.data.rate);
          res.data.compound = res.data.compound ? 1 : 0;
          res.data.recoverable = res.data.recoverable ? 1 : 0;
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
