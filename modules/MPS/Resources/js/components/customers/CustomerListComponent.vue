<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ $tc('customer', 2) }}</p>
      <router-link to="/customers/add" slot="extra">
        <Icon type="ios-grid-outline" />
        {{ $t('add') }} {{ $tc('customer') }}
      </router-link>
      <div>
        <table-component
          :url="url"
          :columns="columns"
          :options="options"
          :refresh="refresh"
          :dblClickCB="showInfo"
          :bulkDelCB="deleteRecords"
        ></table-component>
      </div>
    </Card>
    <pre style="font-size:10px">{{ $t('customer_balance_tip') }}</pre>
    <Modal footer-hide scrollable v-model="view" :width="750">
      <p slot="header">
        <span>
          {{ row.name }}
          <span v-if="row.company">({{ row.company }})</span>
        </span>
      </p>
      <customer-view-component
        :customer="row"
        :listUsersFn="listUsers"
        :addUserFn="showUserForm"
        :addAddressFn="showAddressForm"
        :listAddressesFn="listAddresses"
      />
    </Modal>
    <Modal
      width="500"
      footer-hide
      v-model="add_user"
      :mask-closable="false"
      class="np-header-footer"
      @on-visible-change="resetUserForm"
      :title="user_form.id ? $t('edit_x', { x: $tc('user') }) : $t('add_x', { x: $tc('user') })"
    >
      <Form ref="user_form" :model="user_form" :rules="user_rules" :label-width="150" class="form-responsive">
        <FormItem :label="$t('name')" prop="name" :error="errors.form.name | a2s">
          <Input v-model="user_form.name" />
        </FormItem>
        <FormItem :label="$t('phone')" prop="phone" :error="errors.form.phone | a2s">
          <Input v-model="user_form.phone" />
        </FormItem>
        <FormItem :label="$t('email')" prop="email" :error="errors.form.email | a2s">
          <Input v-model="user_form.email" />
        </FormItem>
        <FormItem :label="$t('username')" prop="username" :error="errors.form.username | a2s">
          <Input v-model="user_form.username" />
        </FormItem>
        <template v-if="!user_form.id">
          <FormItem :label="$t('new_password')" prop="password" :error="errors.form.password | a2s">
            <Input type="password" password v-model="user_form.password" />
          </FormItem>
          <FormItem prop="password_confirmation" :label="$t('confirm_password')" :error="errors.form.password_confirmation | a2s">
            <Input type="password" password v-model="user_form.password_confirmation" />
          </FormItem>
        </template>
        <FormItem prop="active" :error="errors.form.active | a2s">
          <Checkbox v-model="user_form.active" :true-value="1" :false-value="0">
            <span>{{ $t('active') }}</span>
          </Checkbox>
        </FormItem>
        <!-- <FormItem prop="email_verified_at" :error="errors.form.email_verified_at | a2s">
          <Checkbox v-model="user_form.email_verified_at" :true-value="1" :false-value="0">
            <span>{{ $t('no_email_verification_required') }}</span>
          </Checkbox>
        </FormItem> -->
        <FormItem class="mb0">
          <Button type="primary" :loading="saving" :disabled="saving" @click="addUser()">
            <span v-if="!saving">{{ $t('submit') }}</span>
            <span v-else>{{ $t('saving') }}...</span>
          </Button>
        </FormItem>
      </Form>
    </Modal>
    <Modal
      width="550"
      footer-hide
      v-model="add_address"
      :mask-closable="false"
      class="np-header-footer"
      :title="current ? $t('edit_x', { x: $t('address') }) : $t('add_x', { x: $t('address') })"
    >
      <address-form-component :current="current" :customer_id="row.id" @added="addressAdded" />
    </Modal>
    <Modal :title="$tc('user', 2)" :width="users && users.length ? 550 : 350" v-model="view_users" footer-hide class="np-header-footer">
      <div v-if="loading" class="py16">
        <Loading />
      </div>
      <div v-else>
        <div v-if="users && users.length">
          <div style="margin-top:-16px"></div>
          <template v-for="(user, ui) in users">
            <Button
              long
              class="mt16"
              :key="'u_' + ui"
              @click="showUserForm(row, user)"
              style="height:auto;white-space:normal;padding:8px;text-align:left"
            >
              {{ user.name }} ( <strong>{{ user.username }}</strong> )
              <div v-if="user.phone">{{ $t('phone') }}: {{ user.phone }}</div>
              <div v-if="user.email">{{ $t('email') }}: {{ user.email }}</div>
              <div>{{ $t('active') }}: {{ user.active == 1 ? $t('yes') : $t('no') }}</div>
            </Button>
          </template>
        </div>
        <h4 v-else>{{ $t('no_data') }}</h4>
      </div>
    </Modal>
    <Modal
      footer-hide
      :title="$t('addresses')"
      v-model="view_addresses"
      class="np-header-footer"
      :width="addresses && addresses.length ? 550 : 350"
    >
      <div v-if="loading" class="py16">
        <Loading />
      </div>
      <div v-else>
        <div v-if="addresses && addresses.length">
          <div style="margin-top:-16px"></div>
          <template v-for="(address, ai) in addresses">
            <Button
              long
              class="mt16"
              :key="'a_' + ai"
              @click="showAddressForm(row, address)"
              style="height:auto;white-space:normal;padding:8px;text-align:left"
            >
              {{ address.first_name }} {{ address.last_name }} <span v-if="address.company"> ({{ address.company }})</span><br />
              {{ address.house_no }} {{ address.street_no }} {{ address.address }} {{ address.city }} {{ address.postal_code }}
              {{ address.state_name_name }} {{ address.country_name_name }} <br />{{ address.phone }} {{ address.email }}
            </Button>
          </template>
        </div>
        <h4 v-else>{{ $t('no_data') }}</h4>
      </div>
    </Modal>
  </div>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
import AddressFormComponent from '@mpscom/customers/AddressFormComponent';
import CustomerViewComponent from '@mpscom/customers/CustomerViewComponent';
export default {
  components: { AddressFormComponent, CustomerViewComponent },
  mixins: [Table('customer', 'app/customers', 'name', 'company')],
  data() {
    const confirm = (rule, value, callback) => {
      if (value !== this.user_form.password) {
        callback(new Error(this.$t('confirm_password_not_match')));
      } else {
        callback();
      }
    };
    const emailOrPhone = (rule, value, callback) => {
      if (!this.user_form.email && !this.user_form.phone) {
        callback(new Error(this.$t('field_is_required', { field: this.$t('email_or_phone') })));
      } else {
        callback();
      }
    };

    return {
      row: {},
      users: [],
      view: false,
      current: null,
      addresses: [],
      saving: false,
      loading: false,
      add_user: false,
      view_users: false,
      add_address: false,
      selected_user: null,
      view_addresses: false,
      errors: { message: '', form: {} },
      user_form: {
        id: '',
        name: '',
        active: 1,
        phone: '',
        email: '',
        employee: 0,
        username: '',
        password: '',
        email_verified_at: 1,
        password_confirmation: '',
      },
      address_form: {},
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('name'), sortable: true, key: 'name', sortType: 'asc', minWidth: 200 },
        { title: this.$tc('company'), sortable: true, key: 'company', width: 175 },
        { title: this.$t('phone'), sortable: true, key: 'phone', width: 150 },
        { title: this.$t('email'), sortable: true, key: 'email', minWidth: 200 },
        { title: this.$t('points'), sortable: false, key: 'points', width: 125, render: this.renderPoints },
        { title: this.$t('balance'), sortable: false, key: 'journal', width: 125, render: this.renderBalance },
        {
          minWidth: 150,
          key: 'customer_group_id',
          title: this.$tc('customer_group'),
          render: this.renderCustomerGroup,
        },
        { title: this.$t('address'), sortable: true, key: 'address', minWidth: 200, render: this.renderAddress },
        { title: this.$t('state'), sortable: true, key: 'state_name', maxWidth: 150, minWidth: 150 },
        { title: this.$t('country'), sortable: true, key: 'country_name', maxWidth: 150, minWidth: 150 },
        { title: this.$tc('field', 2), sortable: false, key: 'extra_attributes', minWidth: 200, render: this.renderExtras },
        // { title: this.$t('actions'), key: 'actions', align: 'center', fixed: 'right', width: 100, render: this.renderActions },
        {
          width: 100,
          fixed: 'right',
          key: 'actions',
          align: 'center',
          title: this.$t('actions'),
          render: (h, params) => {
            return h('actions-dropdown-component', {
              props: {
                params,
                editFn: this.editRecord,
                listFn: this.listRecord,
                payFn: this.paymentRecord,
                listUsersFn: this.listUsers,
                deleteFn: this.deleteRecord,
                addUserFn: this.showUserForm,
                addAddressFn: this.showAddressForm,
                listAddressesFn: this.listAddresses,
                record: { model: 'item', name: 'name', title: this.$tc('customer') },
              },
            });
          },
        },
      ],
      options: {
        orderBy: 'name',
        perPage: this.$store.state.settings.rows,
      },
      user_rules: {
        name: [{ required: true, message: this.$t('field_is_required', { field: this.$t('name') }), trigger: 'blur' }],
        username: [{ required: true, message: this.$t('field_is_required', { field: this.$t('username') }), trigger: 'blur' }],
        phone: [{ validator: emailOrPhone, required: false, trigger: 'change' }],
        email: [
          { validator: emailOrPhone, required: false, trigger: 'change' },
          { type: 'email', message: this.$t('email_invalid'), trigger: ['change', 'blur'] },
        ],
        password: [{ required: this.selected_user ? false : true, min: 6, message: this.$t('password_error'), trigger: 'blur' }],
        password_confirmation: [
          {
            trigger: 'blur',
            required: this.selected_user ? false : true,
            message: this.$t('field_is_required', { field: this.$t('confirm_password') }),
          },
          { validator: confirm, required: this.selected_user ? false : true, trigger: 'change' },
        ],
      },
    };
  },
  computed: {
    url() {
      return 'app/customers' + (this.$route.query.due_limit ? '?due_limit=' + parseInt(this.$route.query.due_limit) : '');
    },
  },
  watch: {
    '$route.query.due_limit': function() {
      this.refresh++;
    },
  },
  methods: {
    showInfo(row) {
      this.row = row;
      this.view = true;
    },
    paymentRecord(row) {
      this.$router.push(
        `/payments/add?customer_id=${row.id}${row.journal.balance.amount < 0 ? '&amount=' + (0 - row.journal.balance.amount / 100) : ''}`
      );
    },
    listRecord(row) {
      this.$router.push('/customers/transactions/' + row.id);
    },
    showUserForm(row, selected_user) {
      this.row = row;
      if (selected_user) {
        this.user_form = { ...selected_user };
      }
      this.add_user = true;
    },
    showAddressForm(row, address) {
      // console.log(row, address);
      this.row = row;
      this.current = address;
      this.add_address = true;
    },
    resetUserForm(v) {
      if (!v) {
        this.selected_user = null;
        this.$refs.user_form.resetFields();
        this.saving = false;
        setTimeout(() => {
          this.user_form.id = null;
        }, 250);
      }
    },
    addUser() {
      this.$refs.user_form.validate(valid => {
        if (valid) {
          this.saving = true;
          this.user_form.customer_id = this.row.id;

          if (this.user_form.id) {
            this.$http
              .put('app/customers/update_user/' + this.user_form.id, this.user_form)
              .then(res => {
                if (res.data && res.data.success) {
                  this.add_user = false;
                  let usr = res.data.user;
                  this.selected_user = null;
                  this.users = this.users.map(u => (u.id == usr.id ? usr : u));
                  this.$Notice.success({ title: this.$t('success'), desc: this.$t('record_updated') });
                } else {
                  this.$Notice.error({ title: this.$t('failed'), desc: this.$t('failed_error_text'), duration: 10 });
                }
              })
              .catch()
              .finally(() => (this.saving = false));
          } else {
            this.$http
              .post('app/customers/add_user', this.user_form)
              .then(res => {
                if (res.data && res.data.success) {
                  this.add_user = false;
                  this.users.push(res.data.user);
                  this.$Notice.success({ title: this.$t('success'), desc: this.$t('record_added') });
                } else {
                  this.$Notice.error({ title: this.$t('failed'), desc: this.$t('failed_error_text'), duration: 10 });
                }
              })
              .catch()
              .finally(() => (this.saving = false));
          }
        } else {
          this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
        }
      });
    },
    addressAdded(address) {
      if (this.current) {
        this.current = null;
        this.addresses = this.addresses.map(a => (a.id == address.id ? address : a));
      } else {
        this.addresses.push(address);
      }
      this.add_address = false;
    },
    listUsers(row) {
      this.view_users = true;
      if (!this.users.length) {
        this.loading = true;
        this.$http
          .get('app/customers/users/' + row.id)
          .then(res => (this.users = res.data))
          .catch()
          .finally(() => (this.loading = false));
      }
    },
    listAddresses(row) {
      this.view_addresses = true;
      if (!this.addresses.length) {
        this.loading = true;
        this.$http
          .get('app/customers/addresses/' + row.id)
          .then(res => (this.addresses = res.data))
          .catch()
          .finally(() => (this.loading = false));
      }
    },
    renderCustomerGroup(h, params) {
      return <div>{params.row.customer_group ? params.row.customer_group.name : ''}</div>;
    },
    renderTransactions(h, params) {
      return h('transactions-icon-component', { props: { id: params.row.id, url: '/customers/transactions/' } });
    },
    renderPoints(h, params) {
      return <div class="text-right">{params.row.points ? this.formatNumber(params.row.points) : ''}</div>;
    },
    renderBalance(h, params) {
      return (
        <div class="text-right">
          {params.row.journal ? this.formatJournalBalance(params.row.journal.balance.amount, this.$store.getters.settings.decimals) : ''}
        </div>
      );
    },
  },
};
</script>
