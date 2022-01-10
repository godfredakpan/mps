<template>
  <div class="layout">
    <Layout>
      <Content class>
        <Header :style="{ position: 'absolute', width: '100%' }">
          <div class="logo">{{ $store.getters.settings.name }}</div>
        </Header>
        <Row type="flex" justify="center" class="content">
          <Col class="login">
            <!-- <div class="logo">{{ $store.getters.settings.name }}</div> -->
            <transition
              appear
              name="fade"
              mode="out-in"
              enter-active-class="animate__animated animate__bounceInDown"
              leave-active-class="animate__animated faster animate__fadeOutDownBig"
            >
              <div v-if="vf === 'login'">
                <Card shadow>
                  <p slot="title">{{ $t('login') }}</p>
                  <div>
                    <p>{{ $t('login_heading') }}</p>
                    <Alert type="error" show-icon style="margin-top: 16px;" v-if="errors.message">
                      <span v-html="errors.message"></span>
                    </Alert>
                    <Form ref="login" :model="login" :rules="ruleLogin" @keyup.enter.native="handleSubmit('login')">
                      <FormItem prop="username" :style="{ marginTop: '16px', marginBottom: '16px' }" :error="errors.form.username | a2s">
                        <Input type="text" v-model="login.username" :placeholder="$t('username_email')" />
                      </FormItem>
                      <FormItem prop="password" :style="{ marginBottom: '16px' }" :error="errors.form.password | a2s">
                        <Input type="password" password v-model="login.password" :placeholder="$t('password')" />
                      </FormItem>
                      <Checkbox v-model="login.remember">{{ $t('remember_me') }}</Checkbox>
                      <FormItem :style="{ paddingTop: '16px', marginBottom: '0' }">
                        <Button long type="primary" @click="handleSubmit('login')" :loading="loading" :disabled="loading">
                          <span v-if="!loading">
                            <Icon type="log-in"></Icon>
                            {{ $t('login') }}
                          </span>
                          <span v-else>{{ $t('loading') }}</span>
                        </Button>
                      </FormItem>
                      <FormItem :style="{ paddingTop: '16px', marginBottom: '0' }">
                        {{ $t('forgot_password') }}?
                        <a type="text" @click="handleReset('login')">{{ $t('reset_now') }}</a>
                      </FormItem>
                    </Form>
                  </div>
                </Card>
              </div>
            </transition>
            <transition
              appear
              name="fade"
              mode="out-in"
              enter-active-class="animate__animated animate__bounceInDown"
              leave-active-class="animate__animated faster animate__fadeOutDownBig"
            >
              <div v-if="vf === 'reset'">
                <Card shadow>
                  <p slot="title">{{ $t('reset_password') }}</p>
                  <div>
                    <p>{{ $t('reset_password_heading') }}</p>
                    <Form ref="resetForm" :model="resetForm" :rules="ruleReset" @keyup.enter.native="handleSubmit('resetForm')">
                      <FormItem prop="email" :style="{ marginTop: '16px', marginBottom: '16px' }">
                        <Input type="text" v-model="resetForm.email" :placeholder="$t('email_address')" />
                      </FormItem>
                      <FormItem prop="password" :style="{ marginBottom: '16px' }">
                        <Input type="password" password v-model="resetForm.password" :placeholder="$t('new_password')" />
                      </FormItem>
                      <FormItem prop="password_confirmation" :style="{ marginBottom: '0' }">
                        <Input type="password" password v-model="resetForm.password_confirmation" :placeholder="$t('confirm_password')" />
                      </FormItem>
                      <FormItem :style="{ paddingTop: '16px', marginBottom: '0' }">
                        <Button long type="primary" @click="handleSubmit('resetForm')" :loading="loading" :disabled="loading">
                          <span v-if="!loading">
                            <Icon type="shuffle" size="16"></Icon>
                            {{ $t('reset_password') }}
                          </span>
                          <span v-else>{{ $t('loading') }}</span>
                        </Button>
                      </FormItem>
                      <FormItem :style="{ paddingTop: '16px', marginBottom: '0' }">
                        {{ $t('remember_credentials') }}?
                        <a type="text" @click="handleReset('forget')">{{ $t('login_now') }}</a>
                      </FormItem>
                    </Form>
                  </div>
                </Card>
              </div>
            </transition>
            <transition
              name="fade"
              mode="out-in"
              enter-active-class="animate__animated animate__bounceInDown"
              leave-active-class="animate__animated faster animate__fadeOutDownBig"
            >
              <div v-if="vf === 'forget'">
                <Card shadow>
                  <p slot="title">{{ $t('reset_password') }}</p>
                  <div>
                    <p>{{ $t('forgot_password_heading') }}</p>
                    <Form ref="forget" :model="forget" :rules="ruleForget" @keyup.enter.native="handleSubmit('forget')">
                      <FormItem
                        label="Email Address"
                        prop="email"
                        :style="{ paddingTop: '8px', marginBottom: '0' }"
                        :error="errors.email | a2s"
                      >
                        <Input type="email" v-model="forget.email" :placeholder="$t('email_address')" />
                      </FormItem>
                      <FormItem :style="{ paddingTop: '16px', marginBottom: '0' }">
                        <Button long type="primary" @click="handleSubmit('forget')" :loading="loading" :disabled="loading">
                          <span v-if="!loading">
                            <Icon type="ios-email" size="16"></Icon>
                            {{ $t('send_email') }}
                          </span>
                          <span v-else>{{ $t('submitting') }}</span>
                        </Button>
                      </FormItem>
                      <FormItem :style="{ paddingTop: '16px', marginBottom: '0' }">
                        {{ $t('remember_credentials') }}?
                        <a type="text" @click="handleReset('forget')">{{ $t('login_now') }}</a>
                      </FormItem>
                    </Form>
                  </div>
                </Card>
              </div>
            </transition>
          </Col>
        </Row>
        <Footer class="layout-footer-center">&copy; {{ new Date().getFullYear() }} {{ $store.getters.settings.name }}</Footer>
      </Content>
    </Layout>
  </div>
</template>

<script>
export default {
  data() {
    const validatePassCheck = (rule, value, callback) => {
      if (value !== this.resetForm.password) {
        callback(new Error(this.$t('password_x_match')));
      } else {
        callback();
      }
    };

    return {
      vf: 'login',
      loading: false,
      forget: { email: '' },
      errors: { message: '', form: {} },
      resetForm: { email: '', password: '', token: '' },
      login: { username: '', password: '', remember: '' },
      ruleLogin: {
        username: [
          {
            required: true,
            message: this.$t('field_is_required', { field: this.$t('username_email') }),
            trigger: ['change', 'blur'],
          },
        ],
        password: [{ required: true, message: this.$t('field_is_required', { field: this.$t('password') }), trigger: ['change', 'blur'] }],
      },
      ruleForget: {
        email: [
          {
            required: true,
            message: this.$t('field_is_required', { field: this.$t('email_address') }),
            trigger: ['change', 'blur'],
          },
          { type: 'email', message: this.$t('email_invalid'), trigger: ['change', 'blur'] },
        ],
      },
      ruleReset: {
        email: [
          {
            required: true,
            trigger: ['change', 'blur'],
            message: this.$t('field_is_required', { field: this.$t('email_address') }),
          },
          { type: 'email', message: this.$t('email_invalid'), trigger: ['change', 'blur'] },
        ],
        password: [{ required: true, message: this.$t('field_is_required', { field: this.$t('password') }), trigger: ['change', 'blur'] }],
        password_confirmation: [
          {
            required: true,
            message: this.$t('field_is_required', { field: this.$t('confirm_password') }),
            trigger: ['change', 'blur'],
          },
          { validator: validatePassCheck, trigger: ['change', 'blur'] },
        ],
      },
    };
  },
  beforeRouteEnter(to, from, next) {
    if (to.meta.force) {
      this.$event.fire('logOut');
      next();
    } else {
      next(vm => {
        if (vm.$store.getters.user) {
          if (!vm.$store.getters.previous && !vm.$store.getters.previous.includes('login')) {
            vm.$router.push('/');
          } else {
            vm.$router.push(vm.$store.getters.previous('/'));
          }
        }
      });
    }
  },
  mounted() {
    if (this.$store.getters.demo) {
      this.login.username = 'super';
      this.login.password = '123456';
    }
    this.$Loading.finish();
    if (window) {
      document.title = this.$t('login') + ' - ' + this.$store.state.settings.name;
      this.refreshToken();
    }
    if (this.$route.query.reset) {
      this.vf = 'reset';
      if (window) {
        document.title = this.$t('reset_password') + ' - ' + this.$store.state.settings.name;
      }
      this.resetForm.token = this.$route.query.reset;
    }
    // this.http = axios.create({ baseURL: window.baseURL });
  },
  methods: {
    handleSubmit(name) {
      this.$refs[name].validate(valid => {
        this.loading = true;
        if (valid) {
          name == 'login' ? this.signIn() : name == 'resetForm' ? this.resetPassword() : this.forgotPassword();
        } else {
          this.$Notice.error({
            title: this.$t('invalid_form'),
            desc: this.$t('invalid_form_text'),
            duration: 30,
          });
          this.loading = false;
        }
      });
    },
    signIn() {
      this.$http.defaults.baseURL = window.baseURL;
      this.$http
        .post('login', this.login)
        .then(response => {
          if (response.data.success) {
            this.$http
              .get('app/me')
              .then(res => {
                let register = res.data.register;
                delete res.data.register;
                this.$store.commit('setRegister', register);
                this.$store.commit('setUser', res.data);
                // this.$store.commit('setLocation', res.data.location_id);
                this.$Notice.destroy();
                if (this.$store.getters.customer) {
                  window.location.href = '/';
                } else {
                  this.$router.push(this.$store.getters.previous('/'));
                  this.$Notice.success({
                    title: this.$root.$t('success'),
                    desc: this.$root.$t('you_r_logged_in'),
                    duration: 5,
                  });
                  this.$nextTick(() => {
                    this.refreshToken(res.data.location_id);
                  });
                }
              })
              .catch(err => this.$event.fire('appError', err.response));
          } else {
            this.$Notice.error({
              title: this.$t('failed'),
              desc: response.data.lang_key ? this.$t(response.data.lang_key) : this.$t('failed_error_text'),
            });
          }
        })
        .catch(error => (this.errors = error))
        .finally(() => {
          this.$http.defaults.baseURL = window.mpsURL;
          this.loading = false;
        });
      this.$http.defaults.baseURL = window.mpsURL;
    },
    forgotPassword() {
      this.$http.defaults.baseURL = window.baseURL;
      this.$http
        .post('password/email', this.forget)
        .then(response => {
          this.$Notice.success({
            title: this.$t('email_sent'),
            desc: this.$t('reset_email_text'),
            duration: 15,
          });
          this.$router.push('/');
        })
        .catch(error => (this.errors = error))
        .finally(() => {
          this.$http.defaults.baseURL = window.mpsURL;
          this.loading = false;
        });
      this.$http.defaults.baseURL = window.mpsURL;
    },
    resetPassword() {
      this.$http.defaults.baseURL = window.baseURL;
      this.$http
        .post('password/reset', this.resetForm)
        .then(response => {
          this.$store.commit('setUser', response.data.user);
          this.$Notice.success({
            title: this.$t('password_rest'),
            desc: this.$t('password_rest_text'),
            duration: 10,
          });
          this.$router.push('/');
        })
        .catch(error => (this.errors = error))
        .finally(() => {
          this.$http.defaults.baseURL = window.mpsURL;
          this.loading = false;
        });
      this.$http.defaults.baseURL = window.mpsURL;
    },
    handleReset(name) {
      if (name === 'login') {
        this.$refs[name].resetFields();
        this.vf = 'forget';
        if (window) {
          document.title = this.$t('forgot_password') + ' - ' + this.$store.state.settings.name;
        }
      } else {
        this.vf = 'login';
        if (window) {
          document.title = this.$t('login') + ' - ' + this.$store.state.settings.name;
        }
      }
    },
    refreshToken(location_id) {
      this.$http.defaults.baseURL = window.mpsURL;
      this.$http
        .get('app/token')
        .then(res => {
          document.head.querySelector('meta[name="csrf-token"]').setAttribute('content', res.data.token);
          window.axios.defaults.headers.common['X-CSRF-TOKEN'] = res.data.token;
          this.$store.commit('setToken', res.data.token);
        })
        .then(() => {
          if (location_id) {
            this.changeLocation(location_id, true);
            this.$store.commit('setLocation', location_id);
          }
          this.$nextTick(() => {
            this.$event.fire('location:select');
          });
        })
        .catch(err => this.$event.fire('appError', err.response));
    },
    changeLocation(name, noNotice) {
      this.loading = true;
      this.$http.defaults.baseURL = window.mpsURL;
      this.$http
        .post('app/location', { location_id: name })
        .then(res => {
          if (res.data.success) {
            this.$store.commit('setLocation', name);
            if (!noNotice) {
              this.$Notice.destroy();
              this.$Notice.success({
                title: this.$root.$t('success'),
                desc: this.$root.$t('location_changed_text'),
                duration: 5,
              });
            }
            this.$event.fire('location:changed', res.data.data.id);
          } else {
            this.$Notice.error({
              title: this.$t('failed'),
              desc: this.$t('failed_error_text'),
              duration: 30,
            });
          }
        })
        .finally(() => (this.loading = false));
    },
  },
};
</script>

<style lang="scss" scoped>
.content {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 84px 20px 20px 20px;
  min-height: calc(100vh - 69px);
}
.logo {
  color: #fff;
  font-size: 16px;
  font-weight: bold;
  text-align: center;
}
.login {
  width: 300px;
}
.ivu-form-item.ivu-form-item-error {
  padding-bottom: 16px;
}
.ivu-form-item-error-tip {
  padding-bottom: 6px;
}
.layout-footer-center {
  text-align: center;
}
@media (max-width: 320px) {
  .login {
    width: 90%;
  }
}
</style>
