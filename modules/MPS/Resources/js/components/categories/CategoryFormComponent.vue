<template>
  <Card :dis-hover="true">
    <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('category') }}</p>
    <router-link to="/settings/categories" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list') }} {{ $tc('category', 2) }}
    </router-link>
    <div>
      <Form ref="category" :model="form" :rules="rules" :label-width="150" class="form-responsive">
        <Row :gutter="16">
          <Col :sm="24" :md="24" :lg="12">
            <Loading v-if="loading" />
            <Alert type="error" show-icon class="mb26" v-if="errors.message">
              <div v-html="errors.message"></div>
            </Alert>
            <FormItem :label="$t('code')" prop="code" :error="errors.form.code | a2s">
              <Input v-model="form.code" />
            </FormItem>
            <FormItem :label="$t('name')" prop="name" :error="errors.form.name | a2s">
              <Input v-model="form.name" />
            </FormItem>
            <FormItem prop="parent_id" v-if="categories.length > 0" :label="$tc('parent_category')" :error="errors.form.parent_id | a2s">
              <Select v-model="form.parent_id" placeholder>
                <Option :key="index" :value="option.id" v-for="(option, index) in categories">
                  {{ option.name }}
                </Option>
              </Select>
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
            <FormItem>
              <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('categories')">
                <span v-if="!saving">{{ $t('submit') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button
                ghost
                type="primary"
                :loading="saving"
                :disabled="saving"
                style="margin-left: 8px;"
                @click="handleSubmit('categories', true)"
              >
                <span v-if="!saving">{{ $t('save_n_stay') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button type="warning" ghost @click="handleReset()" style="margin-left: 8px;">{{ $t('reset') }}</Button>
            </FormItem>
          </Col>
          <Col :md="24" :lg="12"></Col>
        </Row>
      </Form>
    </div>
  </Card>
</template>

<script>
import Form from '@mpsjs/mixins/Form';
export default {
  mixins: [Form('category', 'app/categories')],
  data() {
    return {
      cdata: [],
      photo: null,
      categories: [],
      form: { id: '', name: '', code: '', photo: null },
      rules: {
        name: [{ required: true, message: this.$t('field_is_required', { field: this.$t('name') }), trigger: 'blur' }],
        code: [
          { required: true, message: this.$t('field_is_required', { field: this.$t('code') }), trigger: 'blur' },
          {
            required: true,
            pattern: /^([a-zA-Z0-9-_]{2,25})$/,
            message: this.$t('alpha_dash_error'),
            trigger: ['blur', 'change'],
          },
        ],
      },
    };
  },
  created() {
    // this.$http.get('app/all_categories/search').then(res => (this.categories = res.data));
    this.$http.get('app/all_categories/search').then(res => (this.categories = this.flattenDeep(res.data)));
  },
  methods: {
    fetch(id) {
      this.$http
        .get(`app/categories/${id}`)
        .then(res => {
          delete res.data.created_at;
          delete res.data.updated_at;
          this.form = res.data;
        })
        .catch(err => {
          this.$event.fire('appError', err.response);
        })
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
