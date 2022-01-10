<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">
        <span v-if="all">{{ $t('all') }} {{ $tc('user', 2) }}</span>
        <span v-else>{{ $tc('employee', 2) }}</span>
      </p>
      <span slot="extra">
        <router-link to="/users/add">
          <Icon type="ios-grid-outline" />
          {{ $t('add') }} {{ $tc('user') }}
        </router-link>
        <Button type="primary" size="small" v-if="all" @click="all = false">
          <Icon type="ios-people" />
          {{ $tc('employee', 2) }}
        </Button>
        <Button type="primary" size="small" v-else @click="all = true">
          <Icon type="ios-people" />
          {{ $t('all') }} {{ $tc('user', 2) }}
        </Button>
      </span>
      <div>
        <table-component
          :url="url"
          :columns="columns"
          :options="options"
          :refresh="refresh"
          :dblClickCB="showInfo"
          :bulkDelCB="deleteRecords"
        />
      </div>
    </Card>
    <Modal :width="282" v-model="view" :title="user ? user.name : ''" :footer-hide="true" :mask-closable="false" class="np-header-footer">
      <user-card-component :u="user" @close="closeUserCard" />
    </Modal>
  </div>
</template>

<script>
import Table from '@mpsjs/mixins/Table';
import UserCardComponent from '@mpscom/helpers/UserCardComponent';

export default {
  components: { UserCardComponent },
  mixins: [Table('user', 'app/users', 'username', 'name')],
  data() {
    return {
      user: '',
      all: false,
      view: false,
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('name'), sortable: true, key: 'name', sortType: 'asc', minWidth: 200 },
        { title: this.$t('username'), sortable: true, key: 'username', minWidth: 150 },
        { title: this.$t('email'), sortable: true, key: 'email', minWidth: 200 },
        { title: this.$t('active'), sortable: true, key: 'active', minWidth: 100, render: this.renderActive },
        { title: this.$tc('role', 2), sortable: false, key: 'roles', minWidth: 200, render: this.renderRoles },
        {
          minWidth: 150,
          key: 'location',
          sortable: false,
          render: this.renderLocation,
          title: this.$t('default_x', { x: this.$tc('location') }),
        },
        { title: this.$tc('location', 2), sortable: false, key: 'locations', minWidth: 200, render: this.renderLocations },
        { title: this.$t('birth_date'), sortable: false, key: 'birth_date', minWidth: 120, render: this.renderBirthDate },
        { title: this.$t('hire_date'), sortable: false, key: 'hire_date', minWidth: 110, render: this.renderHireDate },
        { title: this.$tc('salary'), sortable: false, key: 'salary', minWidth: 125, render: this.renderSalary },
        { title: this.$t('hourly_rate'), sortable: false, key: 'hourly_rate', minWidth: 125, render: this.renderHourRate },
        { title: this.$t('commission_rate'), sortable: false, key: 'commission_rate', minWidth: 150, render: this.renderCR },
        { title: this.$t('commission_method'), sortable: false, key: 'commission_method', minWidth: 160, render: this.renderCM },
        { title: this.$t('address'), sortable: false, key: 'address', minWidth: 200, render: this.renderAddress },
        { title: this.$t('clock_in'), sortable: false, key: 'clock_in', minWidth: 200, render: this.renderCI },
        { title: this.$tc('permission', 2), sortable: true, key: 'permission', minWidth: 275, render: this.renderPermissions },
        { title: this.$t('require_password'), sortable: false, key: 'require_password', minWidth: 150, render: this.renderRP },
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
                logsFn: this.viewTimeLogs,
                salaryFn: this.viewSalaries,
                deleteFn: this.deleteRecord,
                record: { model: 'user', name: 'username' },
                viewCardFn: this.$store.state.settings.impersonation == 1 ? this.viewCard : null,
              },
            });
          },
        },
      ],
      options: {
        orderBy: 'name',
        perPage: this.$store.state.settings.rows,
      },
    };
  },
  computed: {
    url() {
      return 'app/users' + (this.all ? '?all=yes' : '');
    },
  },
  watch: {
    all() {
      this.refresh++;
    },
  },
  beforeRouteEnter(to, from, next) {
    next(vm => {
      if (!vm.$store.getters.superAdmin) {
        vm.$Notice.error({ title: vm.$tc('access_denied'), desc: vm.$t('not_allowed_resource') });
        vm.$router.push(from.path);
      }
    });
  },
  methods: {
    viewCard(row) {
      this.user = row;
      this.view = true;
    },
    viewSalaries(row) {
      this.$router.push(`/users/salaries?user_id=${row.id}`);
    },
    viewTimeLogs(id) {
      this.$router.push(`/reports/time_clock?user_id=${id}`);
    },
    closeUserCard() {
      this.user = null;
      this.view = false;
    },
    renderIcon(h, params) {
      return this.renderBoolean(h, params, 'super');
    },
    renderRoles(h, params) {
      return <div>{params.row.roles.map(e => this.capitalize(e.name)).join(', ')}</div>;
    },
    renderLocations(h, params) {
      return <div>{params.row.locations ? params.row.locations.map(l => l.name).join(', ') : ''}</div>;
    },
    renderLocation(h, params) {
      return <div>{params.row.location ? params.row.location.name : ''}</div>;
    },
    renderBirthDate(h, params) {
      let setting = params.row.meta.find(s => s.meta_key == 'birth_date');
      return <div>{setting ? this.$options.filters.formatDate(setting.value, this.$store.state.settings.dateformat) : ''}</div>;
    },
    renderHireDate(h, params) {
      let setting = params.row.meta.find(s => s.meta_key == 'hire_date');
      return <div>{setting ? this.$options.filters.formatDate(setting.meta_value, this.$store.state.settings.dateformat) : ''}</div>;
    },
    renderSalary(h, params) {
      let setting = params.row.meta.find(s => s.meta_key == 'salary');
      return <div>{setting ? this.$options.filters.formatNumber(setting.meta_value, this.$store.getters.settings.decimals) : ''}</div>;
    },
    renderHourRate(h, params) {
      let setting = params.row.meta.find(s => s.meta_key == 'hourly_rate');
      return <div>{setting ? this.$options.filters.formatNumber(setting.meta_value, this.$store.getters.settings.decimals) : ''}</div>;
    },
    renderCR(h, params) {
      let setting = params.row.meta.find(s => s.meta_key == 'commission_rate');
      return (
        <div>{setting ? this.$options.filters.formatNumber(setting.meta_value, this.$store.getters.settings.decimals) + '%' : ''}</div>
      );
    },
    renderCM(h, params) {
      let setting = params.row.meta.find(s => s.meta_key == 'commission_method');
      return (
        <div style="text-align:center;">
          {setting ? (
            setting.meta_value == 'sale' ? (
              <div class="ivu-tag ivu-tag-primary ivu-tag-checked">
                <span class="ivu-tag-text ivu-tag-color-white">{setting.meta_value}</span>
              </div>
            ) : (
              <div class="ivu-tag ivu-tag-success ivu-tag-checked">
                <span class="ivu-tag-text ivu-tag-color-white">{setting.meta_value}</span>
              </div>
            )
          ) : (
            ''
          )}
        </div>
      );
    },
    renderAddress(h, params) {
      let setting = params.row.meta.find(s => s.meta_key == 'address');
      return <div>{setting ? setting.meta_value : ''}</div>;
    },
    renderActive(h, params) {
      return this.renderBoolean(h, params, 'active');
    },
    renderPermissions(h, params) {
      return (
        <div>
          <div style="display:flex;">
            {this.renderBoolean(h, params, 'view_all')} <span style="margin-left:8px;">{this.$t('view_all')}</span>
          </div>
          <div style="display:flex;">
            {this.renderBoolean(h, params, 'edit_all')} <span style="margin-left:8px;">{this.$t('edit_all')}</span>
          </div>
          <div style="display:flex;">
            {this.renderBoolean(h, params, 'bulk_actions')} <span style="margin-left:8px;">{this.$t('bulk_actions')}</span>
          </div>
        </div>
      );
    },
    renderCI(h, params) {
      let setting = params.row.meta.find(s => s.meta_key == 'clock_in');
      return (
        <div>
          {setting && setting.meta_value == 'login'
            ? this.$t('auto_clock_in_with_login')
            : setting && setting.meta_value == 'register'
            ? this.$t('auto_clock_in_with_register')
            : ''}
        </div>
      );
    },
    renderRP(h, params) {
      let setting = params.row.meta.find(s => s.meta_key == 'require_password');
      return (
        <div style="text-align:center;">
          {setting && setting.meta_value == 1 ? (
            <i class="ivu-icon ivu-icon-md-checkmark" style="font-size: 16px; color: #19be6b;" />
          ) : (
            <i class="ivu-icon ivu-icon-md-close" style="font-size: 16px; color: #ed4014;" />
          )}
        </div>
      );
    },
  },
};
</script>
