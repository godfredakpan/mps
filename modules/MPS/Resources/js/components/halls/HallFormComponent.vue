<template>
  <Card :dis-hover="true">
    <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('hall') }}</p>
    <router-link to="/settings/halls" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list') }} {{ $tc('hall', 2) }}
    </router-link>
    <div>
      <Form ref="hall" :model="form" :rules="rules" :label-width="150" class="form-responsive">
        <Row :gutter="16">
          <Col :sm="24" :md="24" :lg="24">
            <Loading v-if="loading" />
            <Alert type="error" show-icon class="mb26" v-if="errors.message">
              <div v-html="errors.message"></div>
            </Alert>
            <Row :gutter="16">
              <Col :sm="24" :md="12" :lg="12">
                <FormItem :label="$tc('code')" prop="code" :error="errors.form.code | a2s">
                  <Input v-model="form.code" />
                </FormItem>
                <FormItem :label="$t('title')" prop="title" :error="errors.form.title | a2s">
                  <Input v-model="form.title" />
                </FormItem>
                <FormItem prop="location_id" :error="errors.form.location_id | a2s" :label="$t('default_x', { x: $tc('location') })">
                  <Select v-model="form.location_id" placeholder>
                    <template v-if="locations.length > 0">
                      <Option :key="index" :value="option.value" v-for="(option, index) in locations">{{ option.label }}</Option>
                    </template>
                  </Select>
                </FormItem>
                <FormItem :label="$t('details')" prop="details" :error="errors.form.details | a2s">
                  <Input type="textarea" v-model="form.details" :autosize="{ minRows: 2, maxRows: 8 }" />
                </FormItem>
              </Col>
              <Col :sm="24" :md="12" :lg="12"> </Col>
            </Row>

            <form-custom-fields v-model="form" :attributes="attributes" @update="updateCF" />

            <FormItem>
              <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('halls')">
                <span v-if="!saving">{{ $t('submit') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button
                ghost
                type="primary"
                :loading="saving"
                :disabled="saving"
                style="margin-left: 8px;"
                @click="handleSubmit('halls', true)"
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
  if (data.attributes) {
    vm.attributes = data.attributes;
    delete data.attributes;
  }
  data.extra_attributes = vm.formatAttributes(vm.attributes, data.extra_attributes);
  if (data.extra_attributes) {
    vm.form = { ...data, ...data.extra_attributes };
  } else {
    vm.form = { ...data };
  }
  return vm.form;
};
export default {
  mixins: [Form('hall', 'app/halls', true, formatRes)],
  data() {
    return {
      locations: [],
      attributes: [],
      form: { id: '', code: '', title: '', details: '' },
      rules: {
        code: [{ required: true, message: this.$t('field_is_required', { field: this.$t('code') }), trigger: 'blur' }],
        title: [{ required: true, message: this.$t('field_is_required', { field: this.$t('title') }), trigger: 'blur' }],
        location_id: [
          {
            required: true,
            trigger: 'change',
            message: this.$t('field_is_required', { field: this.$t('default_x', { x: this.$tc('location') }) }),
          },
        ],
      },
    };
  },
  created() {
    this.$http.get('app/locations/search').then(res => (this.locations = res.data));
  },
  methods: {
    create() {
      this.$http
        .get('app/halls/create')
        .then(res => (this.attributes = res.data))
        .finally(() => (this.loading = false));
    },
    fetch(id) {
      this.$http
        .get(`app/halls/${id}`)
        .then(res => formatRes(res.data, this))
        .finally(() => (this.loading = false));
    },
  },
};
</script>
