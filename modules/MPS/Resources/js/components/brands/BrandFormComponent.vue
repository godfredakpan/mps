<template>
  <Card :dis-hover="true">
    <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('brand') }}</p>
    <router-link to="/settings/brands" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list') }} {{ $tc('brand', 2) }}
    </router-link>
    <div>
      <Form ref="brand" :model="form" :rules="rules" :label-width="150" class="form-responsive">
        <Row :gutter="16">
          <Col :sm="24" :md="24" :lg="24">
            <Loading v-if="loading" />
            <Alert type="error" show-icon class="mb26" v-if="errors.message">
              <div v-html="errors.message"></div>
            </Alert>
            <Row :gutter="16">
              <Col :sm="24" :md="18" :lg="12">
                <FormItem :label="$tc('name')" prop="name" :error="errors.form.name | a2s">
                  <Input v-model="form.name" />
                </FormItem>
                <FormItem :label="$t('code')" prop="code" :error="errors.form.code | a2s">
                  <Input v-model="form.code" />
                </FormItem>
                <FormItem :label="$tc('order')" prop="order" :error="errors.form.order | a2s">
                  <InputNumber v-model="form.order" />
                </FormItem>
                <FormItem :label="$t('details')" prop="details" :error="errors.form.details | a2s">
                  <Input type="textarea" v-model="form.details" :autosize="{ minRows: 2, maxRows: 8 }" />
                </FormItem>
                <FormItem :label="$tc('photo')" prop="photo" :error="errors.form.photo | a2s">
                  <Upload :before-upload="handleUpload" :max-size="1024" action accept=".png, .jpeg, .jpg, .gif">
                    <Button icon="ios-cloud-upload-outline">{{ $t('select_x', { x: $t('photo') }) }}</Button>
                  </Upload>
                  <div v-if="photo || form.photo" class="primary">
                    <div style="position: relative;max-width: 160px;max-height: 110px;">
                      <Button
                        v-if="photo"
                        type="error"
                        shape="circle"
                        size="small"
                        icon="ios-trash"
                        @click="clearPhoto()"
                        style="position:absolute;top:5px;right:5px"
                      ></Button>
                      <img
                        alt="photo"
                        v-if="photo || form.photo"
                        :src="photo ? photo : form.photo"
                        style="max-width: 160px; max-height: 110px; border-radius: 4px;"
                      />
                    </div>
                    <small v-if="photo" class="warning">{{ $t('not_uploaded_yet') }}</small>
                  </div>
                </FormItem>
              </Col>
              <Col :sm="24" :md="6" :lg="12"></Col>
            </Row>
            <FormItem>
              <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('brands')">
                <span v-if="!saving">{{ $t('submit') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button
                ghost
                type="primary"
                :loading="saving"
                :disabled="saving"
                style="margin-left: 8px;"
                @click="handleSubmit('brands', true)"
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
  mixins: [Form('brand', 'app/brands', false, formatRes)],
  data() {
    return {
      brands: [],
      photo: null,
      form: { id: '', code: '', name: '', order: null, details: '', photo: null },
      rules: {
        name: [{ required: true, message: this.$t('field_is_required', { field: this.$t('name') }), trigger: 'blur' }],
      },
    };
  },
  methods: {
    fetch(id) {
      this.$http
        .get(`app/brands/${id}`)
        .then(res => formatRes(res.data, this))
        .finally(() => (this.loading = false));
    },
    handleUpload(file) {
      var reader = new FileReader();
      reader.addEventListener('load', () => (this.photo = reader.result), false);
      this.form['photo'] = file;
      reader.readAsDataURL(file);
      return false;
    },
    clearPhoto() {
      this.photo = null;
      this.form.photo = null;
    },
  },
};
</script>
