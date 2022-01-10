<template>
  <div>
    <Card dis-hover class="user-card">
      <Avatar v-if="user.avatar" :src="user.avatar" size="120" class="mb16 mt16" />
      <h3>{{ user.name }}</h3>
      <p class="mt16">
        {{ user.username }}<br />
        {{ user.phone }}
      </p>
      <div v-if="loading" style="height: 180px;">
        <Loading />
      </div>
      <div v-else class="qr-code" v-html="image"></div>
    </Card>
    <Button type="primary" @click="print()" icon="ios-print" long class="np">Print</Button>
  </div>
</template>

<script>
import qrcode from 'qrcode-generator';
export default {
  props: ['u'],
  data() {
    return {
      qr: '',
      image: null,
      loading: false,
      qrcode: qrcode,
    };
  },
  computed: {
    user() {
      return this.u ? this.u : this.$store.getters.user;
    },
  },
  watch: {
    u(u) {
      this.loadCard();
    },
  },
  mounted() {
    this.loadCard();
  },
  methods: {
    loadCard() {
      if (this.user.active && this.user.can_impersonate) {
        this.loading = true;
        this.$http
          .post(`/app/impersonate/${this.user.username}/url`)
          .then(res => {
            if (res.data.success) {
              this.qr = this.qrcode(0, 'L');
              this.qr.addData(res.data.url);
              this.qr.make();
              this.image = this.qr.createSvgTag({ scalable: true });
            }
          })
          .catch(() => this.$emit('close', true))
          .finally(() => (this.loading = false));
      }
    },
    print() {
      window.print();
    },
  },
};
</script>

<style>
.user-card {
  width: 250px;
  max-width: 250px;
  min-width: 250px;
  padding-top: 16px;
  text-align: center;
  margin: 0 auto 16px auto;
}
.user-card .qr-code {
  max-width: 180px;
  margin: 16px auto 0 auto;
}
</style>
