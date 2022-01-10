<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ $t('profile') }}</p>
      <span slot="extra">
        <router-link to="/">
          <Icon type="ios-grid-outline" />
          {{ $t('dashboard') }}
        </router-link>
        <Button v-if="!$store.getters.superAdmin" type="text" size="small" @click="view = !view" style="margin-left: 8px;">
          <Icon type="ios-contact" size="16" />
          {{ $t('view_x', { x: $t('card') }) }}
        </Button>
      </span>
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
                  <FormItem :label="$t('username')" prop="username" :error="errors.form.username | a2s">
                    <Input readonly v-model="form.username" />
                  </FormItem>
                </Col>
                <Col :sm="24" :md="12" :lg="12">
                  <FormItem :label="$t('email')" prop="email" :error="errors.form.email | a2s">
                    <Input readonly v-model="form.email" />
                  </FormItem>
                </Col>
                <Col :sm="24" :md="12" :lg="12">
                  <FormItem :label="$t('name')" prop="name" :error="errors.form.name | a2s">
                    <Input v-model="form.name" />
                  </FormItem>
                  <FormItem :label="$t('phone')" prop="phone" :error="errors.form.phone | a2s">
                    <Input v-model="form.phone" />
                  </FormItem>
                </Col>
                <Col :sm="24" :md="12" :lg="12">
                  <FormItem :label="$t('birth_date')" prop="birth_date" :error="errors.form.birth_date | a2s">
                    <DatePicker type="date" format="yyyy-MM-dd" style="width: 100%;" v-model="form.settings.birth_date" />
                  </FormItem>
                  <FormItem :label="$t('address')" prop="address" :error="errors.form.address | a2s">
                    <Input type="textarea" v-model="form.settings.address" />
                  </FormItem>
                </Col>
              </Row>

              <image-component
                :label="$t('avatar')"
                @clear="clearSelectedImage"
                @upload="handleImageUpload"
                :error="errors.form.name | a2s"
              />
              <FormItem :label="$t('initial_sidebar')" prop="sidebar" class="mb0">
                <RadioGroup v-model="form.settings.collapsed">
                  <Radio label="0">{{ $t('normal') }}</Radio>
                  <Radio label="1">{{ $t('collapsed') }}</Radio>
                </RadioGroup>
              </FormItem>
              <FormItem :label="$t('theme')" prop="theme" class="mb0">
                <RadioGroup v-model="form.settings.theme">
                  <Radio label="dark">{{ $t('default') }}</Radio>
                  <Radio label="light">{{ $t('light') }}</Radio>
                  <Radio label="primary">{{ $t('primary') }}</Radio>
                  <Checkbox v-model="form.settings.fixed_layout" true-value="1" false-value="0">
                    <span>{{ $t('fixed_layout') }}</span>
                  </Checkbox>
                </RadioGroup>
              </FormItem>
              <FormItem prop="play_sound">
                <Checkbox v-model="form.settings.play_sound" true-value="1" false-value="0">
                  <span>{{ $t('play_sound_on_pos') }}</span>
                </Checkbox>
              </FormItem>
              <FormItem>
                <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('primary', true)">
                  <span v-if="!saving">{{ $t('submit') }}</span>
                  <span v-else>{{ $t('saving') }}...</span>
                </Button>
                <Button v-if="!form.id" @click="handleReset()" style="margin-left: 8px;">{{ $t('reset') }}</Button>
                <Button v-if="!$store.getters.superAdmin" type="info" @click="view = !view" style="margin-left: 8px;">{{
                  $t('view_x', { x: $t('card') })
                }}</Button>
              </FormItem>
            </Col>
          </Row>
        </Form>
      </div>
    </Card>
    <Modal
      :width="282"
      v-model="view"
      :footer-hide="true"
      :mask-closable="false"
      class="np-header-footer"
      :title="$store.getters.user.name"
      v-if="!$store.getters.superAdmin"
    >
      <user-card-component @close="closeUserCard" />
    </Modal>
  </div>
</template>

<script>
import Form from '@mpsjs/mixins/Form';
import Image from '@mpsjs/mixins/Image';
import UserCardComponent from '@mpscom/helpers/UserCardComponent';

const formatRes = (data, vm) => {
  data.settings = data.settings && data.settings.length != 0 ? data.settings : {};
  data.settings.collapsed = data.settings && data.settings.collapsed ? data.settings.collapsed : '0';
  data.settings.theme = data.settings && data.settings.theme ? data.settings.theme : vm.$store.getters.settings.theme;
  data.settings.fixed_layout =
    data.settings && (data.settings.fixed_layout || data.settings.fixed_layout == 0)
      ? data.settings.fixed_layout
      : vm.$store.getters.settings.fixed;
  data.settings.play_sound =
    data.settings && (data.settings.play_sound || data.settings.play_sound == 0)
      ? data.settings.play_sound
      : vm.$store.getters.settings.play_sound;
  vm.form = { ...data, ...data.extra_attributes };
  return vm.form;
};
export default {
  components: { UserCardComponent },
  mixins: [Form('user', 'app/profile', true), Image],
  data() {
    return {
      view: false,
      form: {
        name: '',
        phone: '',
        email: '',
        avatar: '',
        username: '',
        settings: { collapsed: '0' },
      },
      rules: {
        name: [{ required: true, message: this.$t('field_is_required', { field: this.$t('name') }), trigger: 'blur' }],
        phone: [{ required: true, message: this.$t('field_is_required', { field: this.$t('phone') }), trigger: 'blur' }],
        // address: [{ required: true, message: this.$t('field_is_required', { field: this.$t('address') }), trigger: 'blur' }],
        // birth_date: [
        //     {
        //         type: 'date',
        //         required: true,
        //         trigger: 'change',
        //         message: this.$t('field_is_required', { field: this.$t('birth_date') }),
        //     },
        // ],
      },
    };
  },
  methods: {
    closeUserCard() {
      this.view = false;
    },
    create() {
      this.$http
        .get('app/profile')
        .then(res => (this.form = formatRes(res.data, this)))
        .finally(() => (this.loading = false));
    },
    submit(page, stay) {
      this.errors.message = '';
      let data = { ...this.form };
      if (this.attributes.length > 0) {
        let extras = this.attributes.map(attr => {
          let extra = {};
          delete data[attr.slug];
          extra[attr.slug] = this.form[attr.slug];
          return extra;
        });
        data.extra_attributes = Object.assign(...extras);
      }

      if (data.settings && data.settings.birth_date) {
        data.settings.birth_date = this.$moment(data.settings.birth_date).format(this.$moment.HTML5_FMT.DATE);
      }
      if (data.settings && data.settings.hire_date) {
        data.settings.hire_date = this.$moment(data.settings.hire_date).format(this.$moment.HTML5_FMT.DATE);
      }
      data.settings.collapsed = data.settings.collapsed;
      data.avatar = data.image;
      delete data.image;
      data = this.$form(data);
      this.$http
        .post('app/profile', data)
        .then(res => {
          if (res.data.success) {
            this.$Notice.destroy();
            this.form = formatRes(res.data, this);
            this.$store.commit('setUserProfile', { ...res.data });
            this.$store.commit('setUserSettings', { ...res.data.settings });
            this.$Notice.success({ title: this.$t('profile') + ' ' + this.$t('updated'), desc: this.$t('record_updated') });
          } else {
            this.$Notice.error({ title: this.$t('failed'), desc: this.$t('failed_error_text') });
          }
        })
        .catch(error => (this.errors = error))
        .finally(() => (this.saving = false));
    },
  },
};
</script>
