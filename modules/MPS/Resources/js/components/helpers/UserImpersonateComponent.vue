<template>
  <div>
    <Loading v-if="loading" />
    <Input v-model="iurl" ref="impersonate" size="large" :placeholder="$t('scan_qrcode')" @on-change="impersonate" />
    <Button v-if="$store.getters.user && $store.getters.user.acting_user" type="warning" class="mt16" @click="stopImpersonating" long>
      {{ $t('x_impersonating', { x: $t('stop') }) }}
      ({{ $store.getters.user.acting_user.name }})
    </Button>
  </div>
</template>

<script>
export default {
  props: ['u'],
  data() {
    return {
      iurl: null,
      loading: false,
    };
  },
  watch: {
    u(v) {
      if (v) {
        this.$refs.impersonate.focus();
      } else {
        this.iurl = '';
      }
    },
  },
  methods: {
    impersonate() {
      if (this.iurl && this.iurl.length > 40 && this.iurl.includes(window.location.host)) {
        this.loading = true;
        this.$http
          .post(this.iurl)
          .then(res => {
            if (res.data.success) {
              this.iurl = '';
              this.$emit('close', true);
              this.$store.commit('setActingUser', res.data.user);
              this.$Notice.success({ title: this.$t('success'), desc: this.$t('impersonating_as_x', { x: res.data.user.name }) });
            }
          })
          .finally(() => (this.loading = false));
      }
    },
    stopImpersonating() {
      this.$http.post('app/impersonate/stop').then(res => {
        if (res.data.success) {
          this.$store.commit('setActingUser', null);
          this.$Notice.success({ title: this.$t('success'), desc: this.$t('stopped_impersonating_text') });
        }
      });
    },
  },
};
</script>
