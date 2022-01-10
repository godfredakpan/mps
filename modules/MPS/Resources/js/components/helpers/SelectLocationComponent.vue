<template>
  <div>
    <Loading v-if="loading" />
    <span v-if="error && !loading">
      <h3 v-if="pos" slot="header" class="text-center bold" style="margin-bottom: 10px;">
        {{ $t('register_opened') }}
      </h3>
      <div style="padding: 16px;">
        <Alert type="warning" show-icon style="margin: 0;">
          <span v-html="$t('register_opened_at_x', { x: '<strong>' + register_location.label + '</strong>' })"></span>
          <template slot="desc">
            <span
              v-html="
                $t('register_opened_error', {
                  y: location ? '<strong>' + location.name + '</strong>' : '',
                  x: register_location ? '<strong>' + register_location.label + '</strong>' : '',
                })
              "
            ></span>
          </template>
        </Alert>
      </div>
    </span>
    <span v-else>
      <h3 v-if="pos" slot="header" class="text-center bold" style="margin-bottom: 10px;">
        {{ title }}
      </h3>
      <Row v-if="!location">
        <template v-if="$store.getters.locations && $store.getters.locations.length">
          <Col :sm="24" :md="12" :key="'store_' + index" v-for="(option, index) in $store.getters.locations">
            <div style="padding: 16px;">
              <a @click="selectLocation(option.value)">
                <div class="loc">
                  <div class="text-center" style="margin-bottom: 5px;">{{ option.label }}</div>
                  <Button long :style="{ color: option.color, background: option.color, borderColor: option.color }">
                    {{ option.label }}
                  </Button>
                </div>
              </a>
            </div>
          </Col>
        </template>
        <template>
          <Col :sm="24" :md="24">
            <h4 class="text-center">{{ $t('select_location_text') }}</h4>
          </Col>
        </template>
      </Row>
      <Row v-else-if="location && !register && pos" type="flex" justify="space-between">
        <template v-for="(option, index) in location.registers">
          <Col :sm="24" :md="12" :key="'store_' + index" v-if="option.opened != 1">
            <div style="padding: 16px;">
              <a @click="selectRegister(option.id)">
                <div class="loc">
                  <div class="text-center" style="margin-bottom: 5px;">{{ option.name }} ({{ option.code }})</div>
                </div>
              </a>
            </div>
          </Col>
        </template>
        <template v-if="allOpened">
          <Col :sm="24">
            <div style="padding: 16px;">
              <Alert show-icon style="margin: 0;">
                {{ $t('all_registers_are_open') }}
                <template slot="desc">
                  {{ $t('registers_open_text') }}
                </template>
              </Alert>
            </div>
          </Col>
        </template>
      </Row>
      <Row v-else-if="register && pos">
        <Col :sm="24">
          <div style="padding: 16px;">
            <Form ref="openRegister" :model="form" :rules="rules" @submit.native.prevent="">
              <FormItem class="apg" :label="$t('cash_in_hand')" prop="cash_in_hand">
                <Input
                  search
                  size="large"
                  ref="openRegisterInput"
                  v-model="form.cash_in_hand"
                  @on-search="openRegister()"
                  :placeholder="$t('cash_in_hand')"
                  :enter-button="$t('open_x', { x: $tc('register') })"
                ></Input>
              </FormItem>
            </Form>
          </div>
        </Col>
      </Row>
    </span>
    <div v-if="pos" class="text-center">
      <Button icon="md-home" @click="$router.replace('/')" style="margin: 10px auto 0 auto;">
        {{ $t('back_to_home') }}
      </Button>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    loc: { required: true },
    pos: { type: Boolean, required: false },
  },
  data() {
    return {
      error: false,
      loading: true,
      location: null,
      register: null,
      form: { cash_in_hand: null },
      // title: this.$t('select_x', { x: this.$tc('location') }),
      rules: {
        cash_in_hand: [
          {
            required: true,
            type: 'number',
            trigger: ['blur', 'change'],
            message: this.$t('field_is_required', { field: this.$t('cash_in_hand') }),
          },
        ],
      },
    };
  },
  computed: {
    allOpened() {
      return this.location && !this.location.registers.find(r => r.opened != 1);
    },
    register_location() {
      return this.$store.getters.locations.find(l => l.value == this.$store.getters.register.location_id);
    },
    title() {
      if (this.location && !this.register) {
        return this.$t('select_x', { x: this.$tc('register') });
      } else if (this.location && this.register) {
        return this.$t('opening_cash');
      }
      return this.$t('select_x', { x: this.$tc('location') });
    },
  },
  watch: {
    loc: function(loc) {
      this.location = loc;
      this.checkRegisterLocation();
    },
  },
  created() {
    this.$nextTick(() => {
      this.loading = false;
      this.register = this.$store.getters.register;
    });
    // if (this.location && !this.register) {
    //     this.title = this.$t('select_x', { x: this.$tc('register') });
    // }
  },
  methods: {
    checkRegisterLocation() {
      if (this.$store.getters.register && this.$store.getters.location.value != this.$store.getters.register.location_id) {
        this.error = true;
        // if (!this.location.registers.find(r => r.id == this.$store.getters.register.register_id)) {
        //     this.register = null;
        //     this.title = this.$t('select_x', { x: this.$tc('register') }) + ' (' + this.location.name + ')';
        // }
      }
    },
    selectLocation(name) {
      this.loading = true;
      this.$http
        .post('app/location', { location_id: name })
        .then(res => {
          // this.title = this.$t('select_x', { x: this.$tc('register') });
          this.$store.commit('setLocation', name);
          this.$nextTick(() => {
            this.$event.fire('location:select');
          });
          if (res.data.orders) {
            let orders = {};
            if (Object.keys(res.data.orders).length) {
              for (const oId in res.data.orders) {
                orders[oId] = { ...res.data.orders[oId], ...res.data.orders[oId].extra_attributes };
              }
            }
            this.$store.commit('setOrders', orders);
          }
          if (
            this.$store.getters.register &&
            this.$store.getters.location &&
            this.$store.getters.register.location_id == this.$store.getters.location.value
          ) {
            this.$event.fire('register:opened', true);
          } else if (this.$store.getters.register) {
            this.error = true;
            // this.loading = false;
          } else {
            if (this.pos) {
              this.location = res.data.data;
            } else {
              this.$Notice.destroy();
              if (res.data.success) {
                this.$Notice.success({
                  title: this.$t('success'),
                  desc: this.$t('location_changed_text'),
                  duration: 5,
                });
              } else {
                this.$Notice.error({
                  title: this.$t('failed'),
                  desc: this.$t('failed_error_text'),
                  duration: 30,
                });
              }
            }
          }
        })
        .finally(() => (this.loading = false));
    },
    selectRegister(id) {
      this.register = this.location.registers.find(r => r.id == id);
      // this.title = this.$t('cash_in_hand');
      this.$nextTick(() => {
        this.$refs.openRegisterInput.focus();
      });
    },
    openRegister() {
      this.loading = true;
      this.$refs.openRegister.validate(valid => {
        if (valid) {
          this.$http
            .post('app/register/' + this.register.id, { cash_in_hand: this.form.cash_in_hand })
            .then(res => {
              if (res.data.success) {
                this.$store.commit('setRegister', res.data.data);
                this.$event.fire('register:opened', true);
                this.$Notice.destroy();
                this.$Notice.success({
                  title: this.$t('success'),
                  desc: this.$t('register_opened_text'),
                  duration: 5,
                });
              } else {
                this.$Notice.error({
                  title: this.$t('failed'),
                  desc: this.$t('failed_error_text'),
                  duration: 30,
                });
              }
            })
            .finally(() => (this.loading = false));
        } else {
          this.loading = false;
        }
      });
    },
  },
};
</script>

<style lang="scss" scoped>
.loc {
  padding: 16px;
  border-radius: 4px;
  border: 1px solid #e8eaec;
  &:hover {
    color: #ffffff;
    background: #495060;
    border-color: #495060;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }
}
</style>
