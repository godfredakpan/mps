<template>
  <Card :dis-hover="true">
    <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('uom') }}</p>
    <router-link to="/settings/units" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list') }} {{ $tc('unit', 2) }}
    </router-link>
    <div>
      <Form ref="unit" :model="form" :rules="rules" :label-width="150" class="form-responsive">
        <Row :gutter="16">
          <Col :sm="24" :md="24" :lg="24">
            <Loading v-if="loading" />
            <Alert type="error" show-icon class="mb26" v-if="errors.message">
              <div v-html="errors.message"></div>
            </Alert>
            <Row :gutter="16">
              <Col :sm="24" :md="18" :lg="12">
                <FormItem :label="$t('code')" prop="code" :error="errors.form.code | a2s">
                  <Input v-model="form.code" />
                </FormItem>
                <FormItem :label="$tc('name')" prop="name" :error="errors.form.name | a2s">
                  <Input v-model="form.name" />
                </FormItem>
                <span v-if="units && units.length">
                  <FormItem :label="$t('base_unit')" prop="base_id" :error="errors.form.base_id | a2s">
                    <Select v-model="form.base_id">
                      <template v-for="unit in units">
                        <template v-if="unit.id != form.id">
                          <Option :key="unit.id" :value="unit.id">{{ unit.name }}</Option>
                        </template>
                      </template>
                    </Select>
                  </FormItem>
                </span>
                <span v-if="form.base_id">
                  <FormItem :label="$tc('operator')" prop="operator" :error="errors.form.operator | a2s">
                    <Select v-model="form.operator">
                      <Option value="+">{{ $t('+') }}</Option>
                      <Option value="-">{{ $t('-') }}</Option>
                      <Option value="*">{{ $t('*') }}</Option>
                      <Option value="/">{{ $t('/') }}</Option>
                    </Select>
                  </FormItem>
                  <FormItem :label="$t('operation_value')" prop="operation_value" :error="errors.form.operation_value | a2s">
                    <InputNumber v-model="form.operation_value"></InputNumber>
                  </FormItem>
                  <FormItem label="">
                    <Alert show-icon><Icon type="md-git-compare" slot="icon"></Icon> {{ formula }}</Alert>
                  </FormItem>
                </span>
              </Col>
              <Col :sm="24" :md="6" :lg="12"></Col>
            </Row>
            <FormItem>
              <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('units')">
                <span v-if="!saving">{{ $t('submit') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button
                ghost
                type="primary"
                :loading="saving"
                :disabled="saving"
                style="margin-left: 8px;"
                @click="handleSubmit('units', true)"
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
  data.operation_value = data.operation_value ? parseFloat(data.operation_value) : null;
  vm.form = { ...data };
  return vm.form;
};
export default {
  mixins: [Form('unit', 'app/units', true, formatRes)],
  data() {
    return {
      units: [],
      form: { id: '', code: '', name: '', operation_value: null, details: '', base_id: '', operator: '' },
      rules: {
        code: [{ required: true, message: this.$t('field_is_required', { field: this.$t('code') }), trigger: 'blur' }],
        name: [{ required: true, message: this.$t('field_is_required', { field: this.$t('name') }), trigger: 'blur' }],
        operator: [
          {
            required: false,
            trigger: 'change',
            message: this.$t('field_is_required', { field: this.$t('operator') }),
          },
        ],
        base_id: [{ required: false, message: this.$t('field_is_required', { field: this.$t('base_unit') }), trigger: 'change' }],
        operation_value: [
          {
            required: false,
            type: 'number',
            trigger: 'blur',
            message: this.$t('field_is_required', { field: this.$t('operation_value') }),
          },
        ],
      },
    };
  },
  computed: {
    formula() {
      let base_unit = this.units.find(u => u.id == this.form.base_id);
      if (base_unit) {
        return (
          base_unit.name +
          ' (' +
          base_unit.code +
          ') ' +
          (this.form.operator || '') +
          ' ' +
          (this.form.operation_value || '') +
          ' = ' +
          this.form.name +
          ' (' +
          this.form.code +
          ')'
        );
      }
      return null;
    },
  },
  // created() {
  //     this.create();
  // },
  methods: {
    fetch(id) {
      this.$http
        .get('app/units/search')
        .then(res => (this.units = res.data))
        .then(() => {
          this.$http.get(`app/units/${id}`).then(res => formatRes(res.data, this));
        })
        .finally(() => (this.loading = false));
    },
    create() {
      this.$http
        .get('app/units/search')
        .then(res => (this.units = res.data))
        .finally(() => (this.loading = false));
    },
  },
};
</script>
