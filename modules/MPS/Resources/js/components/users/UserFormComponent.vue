<template>
  <Card :dis-hover="true">
    <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('user') }}</p>
    <router-link to="/users" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list') }} {{ $tc('user', 2) }}
    </router-link>
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
                <FormItem :label="$t('name')" prop="name" :error="errors.form.name | a2s">
                  <Input v-model="form.name" />
                </FormItem>
                <FormItem :label="$t('phone')" prop="phone" :error="errors.form.phone | a2s">
                  <Input v-model="form.phone" />
                </FormItem>
                <FormItem :label="$t('email')" prop="email" :error="errors.form.email | a2s">
                  <Input v-model="form.email" />
                </FormItem>
                <FormItem :label="$t('username')" prop="username" :error="errors.form.username | a2s">
                  <Input v-model="form.username" />
                </FormItem>
                <template v-if="!form.customer_id">
                  <FormItem prop="location_id" :error="errors.form.location_id | a2s" :label="$t('default_x', { x: $tc('location') })">
                    <Select v-model="form.location_id" placeholder>
                      <template v-if="locations.length > 0">
                        <Option :key="index" :value="option.value" v-for="(option, index) in locations">
                          {{ option.label }}
                        </Option>
                      </template>
                    </Select>
                  </FormItem>
                  <FormItem :label="$tc('location', 2)" prop="locations" :error="errors.form.locations | a2s">
                    <Select v-model="form.locations" multiple :placeholder="$t('select_x', { x: $tc('location', 2) })">
                      <template v-if="locations.length > 0">
                        <Option :key="index" :value="option.value" v-for="(option, index) in locations">
                          {{ option.label }}
                        </Option>
                      </template>
                    </Select>
                  </FormItem>
                  <FormItem prop="clock_in" :error="errors.form.clock_in | a2s" :label="$t('auto_clock_in')">
                    <Select v-model="form.settings.clock_in" placeholder>
                      <Option key="clock_0" value="">{{ $t('disable') }}</Option>
                      <Option key="clock_1" value="login">{{ $t('auto_clock_in_with_login') }}</Option>
                      <Option key="clock_2" value="register">{{ $t('auto_clock_in_with_register') }}</Option>
                    </Select>
                  </FormItem>
                  <FormItem :label="$t('birth_date')" prop="birth_date" :error="errors.form.birth_date | a2s">
                    <DatePicker type="date" v-model="form.settings.birth_date" style="width: 100%;" />
                  </FormItem>
                </template>
              </Col>
              <Col :sm="24" :md="12" :lg="12">
                <template v-if="!form.customer_id">
                  <FormItem :label="$t('employee_number')" prop="number" :error="errors.form.number | a2s">
                    <Input v-model="form.settings.number" />
                  </FormItem>
                  <FormItem :label="$t('hire_date')" prop="hire_date" :error="errors.form.hire_date | a2s">
                    <DatePicker type="date" v-model="form.settings.hire_date" style="width: 100%;" />
                  </FormItem>
                  <FormItem :label="$tc('salary')" prop="salary" :error="errors.form.salary | a2s">
                    <InputNumber v-model="form.settings.salary" />
                    <!-- <small>{{ $t('salary_tip') }}</small> -->
                  </FormItem>
                  <FormItem :label="$t('hourly_rate')" prop="hourly_rate" :error="errors.form.hourly_rate | a2s" class="input-tip">
                    <InputNumber v-model="form.settings.hourly_rate" />
                    <small>{{ $t('hourly_rate_tip') }}</small>
                  </FormItem>
                  <FormItem prop="commission_rate" :label="$t('commission_rate')" :error="errors.form.commission_rate | a2s">
                    <InputNumber
                      :formatter="value => `${value}%`"
                      v-model="form.settings.commission_rate"
                      :parser="value => value.replace('%', '')"
                    />
                  </FormItem>
                  <!-- <FormItem
                  placeholder
                  prop="commission_method"
                  :label="$t('commission_method')"
                  :error="errors.form.commission_method | a2s"
                >
                  <Select v-model="form.settings.commission_method">
                    <Option value="sale">Sale</Option>
                    <Option value="profit">Profit</Option>
                  </Select>
                </FormItem> -->
                  <FormItem :label="$t('max_discount')" prop="max_discount" class="input-tip">
                    <InputNumber v-model="form.settings.max_discount" />
                    <small>{{ $t('max_discount_settings_tip') }}</small>
                  </FormItem>
                </template>
              </Col>
            </Row>
            <!-- <Row>
              <Col :sm="24" :md="12" :lg="12">
                <FormItem
                  prop="location_id"
                  :error="errors.form.location_id | a2s"
                  :label="$t('default_x', { x: $tc('location') })"
                >
                  <Select v-model="form.location_id" placeholder>
                    <Option
                      :key="index"
                      :value="option.value"
                      v-if="locations.length > 0"
                      v-for="(option, index) in locations"
                      >{{ option.label }}</Option
                    >
                  </Select>
                </FormItem>
              </Col>
              <Col :sm="24" :md="12" :lg="12">
                <FormItem :label="$tc('location', 2)" prop="locations" :error="errors.form.locations | a2s">
                  <Select v-model="form.locations" multiple :placeholder="$t('select_x', { x: $tc('location', 2) })">
                    <Option
                      :key="index"
                      :value="option.value"
                      v-if="locations.length > 0"
                      v-for="(option, index) in locations"
                      >{{ option.label }}</Option
                    >
                  </Select>
                </FormItem>
              </Col>
          </Row> -->
            <template v-if="!form.customer_id">
              <FormItem :label="$t('address')" prop="address" :error="errors.form.address | a2s">
                <Input type="textarea" v-model="form.settings.address" :autosize="{ minRows: 2, maxRows: 5 }" />
              </FormItem>
              <span v-if="form.media && form.media.length > 0">
                <FormItem>
                  <div v-if="form.media" style="padding: 10px; background: #f8f8f9; border-radius: 4px;">
                    <Card :title="$tc('file', 2)" icon="ios-options" :padding="0" shadow>
                      <CellGroup>
                        <Cell
                          :title="media.name"
                          :key="'media_' + index"
                          v-for="(media, index) of form.media"
                          :label="media.file_name + ', ' + media.type + ', ' + media.size"
                        >
                          <ButtonGroup size="small" slot="extra">
                            <Button size="small" type="primary" @click="downloadMedia(media)">
                              {{ $t('download') }}
                            </Button>
                            <Button size="small" type="error" @click="deleteMedia(media)">
                              {{ $t('delete') }}
                            </Button>
                          </ButtonGroup>
                        </Cell>
                      </CellGroup>
                    </Card>
                    <!-- <Row :gutter="16" v-for="(media, index) of form.media" :key="'media_' + index">
                    <Col :sm="24">
                      <h3>{{ $t('file_name') + ': ' + media.name }}</h3>
                      <Button type="error" size="small" icon="md-close"></Button>
                    </Col>
                  </Row> -->
                  </div>
                </FormItem>
              </span>
              <span v-if="form.files">
                <Row :gutter="16" v-for="(file, index) of form.files" :key="'file_' + index">
                  <Col :sm="24" :md="12" :lg="12">
                    <FormItem :label="$t('file_name')" prop="file_name" :error="errors.form.file | a2s">
                      <Input v-model="form.files[index].name" />
                    </FormItem>
                  </Col>
                  <Col :sm="24" :md="12" :lg="12">
                    <Upload
                      :before-upload="
                        file => {
                          return handleUpload(file, index);
                        }
                      "
                      action="#"
                    >
                      <Button icon="ios-cloud-upload-outline">{{ $t('select_x', { x: $tc('file') }) }}</Button>
                    </Upload>
                    <div v-if="form.files[index].file">{{ $tc('file') }}: {{ form.files[index].file.name }}</div>
                  </Col>
                </Row>
              </span>
              <FormItem>
                <ButtonGroup>
                  <Button @click="addFile">
                    <Icon type="md-add" />
                  </Button>
                  <Button @click="removeFile" :disabled="form.files && form.files.length < 2">
                    <Icon type="md-remove" />
                  </Button>
                </ButtonGroup>
              </FormItem>
            </template>
            <Divider dashed orientation="left" v-if="form.username != $store.getters.user.username && form.id">
              <small>
                <a @click="form.changePassword = !form.changePassword"> {{ $t('toggle_change_password') }}</a>
              </small>
            </Divider>
            <transition name="slide-fade">
              <div v-if="form.changePassword" style="margin-bottom: 16px;">
                <FormItem :label="$t('new_password')" prop="password" :error="errors.form.password | a2s">
                  <Input type="password" password v-model="form.password" />
                </FormItem>
                <FormItem prop="password_confirmation" :label="$t('confirm_password')" :error="errors.form.password_confirmation | a2s">
                  <Input type="password" password v-model="form.password_confirmation" />
                </FormItem>
              </div>
            </transition>
            <template v-if="!form.customer_id">
              <FormItem
                prop="roles"
                :label="$tc('role', 2)"
                :error="errors.form.roles | a2s"
                v-if="form.username != $store.getters.user.username"
              >
                <CheckboxGroup v-model="form.roles">
                  <Checkbox v-for="role in roles" :key="role.value" :label="role.label" :true-value="role.label">
                    {{ role.label | capitalize }}
                  </Checkbox>
                </CheckboxGroup>
              </FormItem>
            </template>
            <FormItem
              prop="active"
              :error="errors.form.active | a2s"
              :class="form.customer_id ? '' : 'mb0'"
              v-if="form.username != $store.getters.user.username"
            >
              <Checkbox v-model="form.active" :true-value="1" :false-value="0">
                <span>{{ $t('active') }}</span>
              </Checkbox>
            </FormItem>
            <template v-if="!form.customer_id">
              <!-- <FormItem v-if="form.username != $store.getters.user.username" prop="employee" class="mb0" :error="errors.form.employee | a2s">
              <Checkbox v-model="form.employee" :true-value="1" :false-value="0">
                <span>{{ $tc('employee') }}</span>
              </Checkbox>
            </FormItem>
            <span v-if="form.employee == 1"> -->
              <FormItem prop="view_all" class="mb0" :error="errors.form.view_all | a2s">
                <Checkbox v-model="form.view_all" :true-value="1" :false-value="0">
                  <span>{{ $t('view_all') }}</span>
                </Checkbox>
              </FormItem>
              <FormItem prop="edit_all" class="mb0" :error="errors.form.edit_all | a2s">
                <Checkbox v-model="form.edit_all" :true-value="1" :false-value="0">
                  <span>{{ $t('edit_all') }}</span>
                </Checkbox>
              </FormItem>
              <FormItem prop="bulk_actions" class="mb0" :error="errors.form.bulk_actions | a2s">
                <Checkbox v-model="form.bulk_actions" :true-value="1" :false-value="0">
                  <span>{{ $t('bulk_actions') }}</span>
                </Checkbox>
              </FormItem>
              <!-- </span> -->
              <FormItem prop="first_login" class="mb0">
                <Checkbox v-model="form.settings.first_login" :true-value="1" :false-value="0">
                  <span>{{ $t('first_login') }}</span>
                </Checkbox>
              </FormItem>
              <FormItem prop="can_impersonate" class="" :error="errors.form.can_impersonate | a2s">
                <Checkbox v-model="form.can_impersonate" :true-value="1" :false-value="0">
                  <span>{{ $t('can_impersonate_text') }}</span>
                </Checkbox>
              </FormItem>
              <!-- <FormItem prop="require_password">
              <Checkbox v-model="form.settings.require_password" :true-value="1" :false-value="0">
                <span>{{ $t('require_password_text') }}</span>
              </Checkbox>
            </FormItem> -->
            </template>
            <FormItem>
              <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('users')">
                <span v-if="!saving">{{ $t('submit') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button
                ghost
                type="primary"
                :loading="saving"
                :disabled="saving"
                style="margin-left: 8px;"
                @click="handleSubmit('users', true)"
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
import _debounce from 'lodash/debounce';
const formatRes = (data, vm) => {
  data.roles = data.roles.map(r => r.name);
  data['changePassword'] = false;
  data.settings = data.settings && data.settings.length != 0 ? data.settings : {};
  data.active = data.active ? 1 : 0;
  data.settings.clock_in = data.settings.clock_in;
  data.settings.first_login = data.settings.first_login == 1 ? 1 : 0;
  data.settings.require_password = data.settings.require_password == 1 ? 1 : 0;
  data.settings.salary = data.settings.salary ? parseFloat(data.settings.salary) : null;
  data.settings.hourly_rate = data.settings.hourly_rate ? parseFloat(data.settings.hourly_rate) : null;
  data.settings.max_discount = data.settings.max_discount ? parseFloat(data.settings.max_discount) : null;
  data.settings.commission_rate = data.settings.commission_rate ? parseFloat(data.settings.commission_rate) : null;
  data.files = [{ name: '', file: null }];
  vm.form = { ...data, ...data.extra_attributes };
  return vm.form;
};
export default {
  mixins: [Form('user', 'app/users', false, formatRes)],
  data() {
    const confirm = (rule, value, callback) => {
      if (value !== this.form.password) {
        callback(new Error(this.$t('confirm_password_not_match')));
      } else {
        callback();
      }
    };
    const emailOrPhone = (rule, value, callback) => {
      if (!this.form.email && !this.form.phone) {
        callback(new Error(this.$t('field_is_required', { field: this.$t('email_or_phone') })));
      } else {
        callback();
      }
    };
    return {
      roles: [],
      locations: [],
      searching: false,
      form: {
        id: '',
        name: '',
        active: 1,
        phone: '',
        email: '',
        roles: [],
        employee: 1,
        username: '',
        password: '',
        locations: [],
        location_id: '',
        can_impersonate: 0,
        changePassword: true,
        password_confirmation: '',
        files: [{ name: '', file: null }],
        settings: {
          number: '',
          address: '',
          salary: null,
          first_login: '',
          hire_date: null,
          birth_date: null,
          hourly_rate: null,
          max_discount: null,
          require_password: '',
          commission_rate: null,
        },
      },
      rules: {
        name: [{ required: true, message: this.$t('field_is_required', { field: this.$t('name') }), trigger: 'blur' }],
        username: [{ required: true, message: this.$t('field_is_required', { field: this.$t('username') }), trigger: 'blur' }],
        phone: [{ validator: emailOrPhone, required: false, trigger: 'change' }],
        email: [
          { validator: emailOrPhone, required: false, trigger: 'change' },
          { type: 'email', message: this.$t('email_invalid'), trigger: ['change', 'blur'] },
        ],
        location_id: [
          {
            required: true,
            trigger: 'change',
            message: this.$t('field_is_required', { field: this.$t('default_x', { x: this.$tc('location') }) }),
          },
        ],
        roles: [
          {
            min: 1,
            type: 'array',
            required: true,
            trigger: 'change',
            message: this.$t('field_is_required', { field: this.$tc('role') }),
          },
        ],
        password: [{ required: true, min: 6, message: this.$t('password_error'), trigger: 'blur' }],
        password_confirmation: [
          { required: true, message: this.$t('field_is_required', { field: this.$t('confirm_password') }), trigger: 'blur' },
          { validator: confirm, required: true, trigger: 'change' },
        ],
      },
    };
  },
  beforeRouteEnter(to, from, next) {
    next(vm => {
      if (!vm.$store.getters.superAdmin) {
        vm.$Notice.error({ title: vm.$tc('access_denied'), desc: vm.$t('not_allowed_resource') });
        vm.$router.push(from.path);
      }
    });
  },
  created() {
    this.$http.get('app/roles/search').then(res => (this.roles = res.data.filter(r => r.label != 'customer' && r.label != 'supplier')));
    this.$http.get('app/locations/search').then(res => (this.locations = res.data));
  },
  methods: {
    fetch(id) {
      this.$http
        .get(`app/users/${id}`)
        .then(res => (this.form = formatRes(res.data, this)))
        .finally(() => (this.loading = false));
    },
    handleUpload(file, index) {
      this.form.files[index].file = file;
      return false;
    },
    addFile() {
      this.form.files.push({ name: '', file: null });
    },
    removeFile() {
      if (this.form.files.length > 1) {
        this.form.files.pop();
      }
    },
    downloadMedia(media) {
      window.location.href = 'app/media/' + media.id;
    },
    deleteMedia(media) {
      this.$Modal.confirm({
        title: this.$t('delete_x', { x: media.name }),
        content: this.$t('delete_confirm') + '<br><br><strong>' + this.$t('r_u_sure') + '</strong>',
        okText: this.$t('yes'),
        cancelText: this.$t('cancel'),
        onOk: () => {
          this.$http.delete('app/media/' + media.id).then(res => {
            if (res.data.success) {
              this.form.media = this.form.media.filter(m => m.id != media.id);
            } else {
              this.$Notice.error({ title: this.$t('unknown_error'), desc: this.$t('unknown_error_text2'), duration: 10 });
            }
          });
        },
      });
    },
  },
};
</script>
