<template>
  <div>
    <Alert type="error" v-if="errors">
      <p v-html="errors.replace('.,', '.<br />')"></p>
    </Alert>
    <div v-if="$store.state.settings.default_logo" class="text-center mb16">
      <img class="image" :src="$store.state.settings.default_logo" :alt="$t('default_logo')" />
    </div>
    <Upload
      action
      type="drag"
      ref="upload"
      :max-size="300"
      :show-upload-list="false"
      :before-upload="handleUpload"
      :on-exceeded-size="handleMaxSize"
      :on-format-error="handleFormatError"
      :format="['jpg', 'jpeg', 'png', 'svg']"
    >
      <!-- :on-success="handleSuccess" -->
      <!-- :action="$store.getters.payment.moduleURL + '/app/settings/logo'" -->
      <div style="width:100%;height:58px;line-height:58px;">
        <div style="display:flex;align-items:center;justify-content:center;">
          <Icon type="ios-cloud-upload" size="32" style="color:#3399ff; margin-right:8px;"></Icon>
          <p>
            {{ $t('click_or_drag') }}
          </p>
        </div>
      </div>
    </Upload>
  </div>
</template>

<script>
export default {
  data() {
    return {
      errors: null,
      form: { logo: '' },
    };
  },
  methods: {
    handleUpload(logo) {
      this.form.logo = logo;
      const data = this.$form(this.form);
      this.$http
        .post('/app/settings/logo', data)
        .then(res => {
          if (res.data.success) {
            this.$store.commit('changeDefaultLogo', res.data.logo);
            this.$Notice.success({
              title: this.$t('success'),
              desc: this.$t('x_updated', { x: this.$t('logo') }),
            });
            this.errors = null;
            this.$emit('close');
          }
        })
        .catch(err => (this.errors = err.message));
      return false;
    },
    handleFormatError(file) {
      this.$Notice.warning({
        title: 'The file format is incorrect',
        desc: 'File format of ' + file.name + ' is incorrect, please select jpg or png.',
      });
    },
    handleMaxSize(file) {
      this.$Notice.warning({
        title: 'Exceeding file size limit',
        desc: 'File  ' + file.name + ' is too large, no more than 300KB.',
      });
    },
  },
};
</script>
