<template>
  <div class="layout pos" @click="hideKeyboard">
    <template v-if="$store.getters.staff">
      <Layout :style="{ height: '100vh' }">
        <Loading v-if="loading" />
        <Header
          :style="{ padding: 0 }"
          :class="{ 'header-light bg-light': theme == 'light', 'header-primary bg-primary': theme == 'primary' }"
        >
          <Row type="flex" justify="space-between">
            <Col>
              <div class="layout-logo">
                <i @click="collapsedSider" :class="rotateIcon" class="close pointer"></i>
                <div style="display: flex;">
                  <router-link to="/" class="logo">
                    <span class="hidden-md">{{ $store.state.settings.name }}</span>
                    <span class="visible-md">{{ $store.state.settings.short_name }}</span>
                  </router-link>
                  <span class="hidden-xs">
                    <Tooltip placement="right" content="Online" v-if="isOnline">
                      <Icon type="ios-planet" class="success" size="20" style="margin: -3px 10px 0 10px;" />
                    </Tooltip>
                    <Tooltip placement="right" content="Offline" v-else>
                      <Icon type="ios-planet-outline" class="error" size="18" style="margin: -3px 10px 0 10px;" />
                    </Tooltip>
                  </span>
                </div>
              </div>
              <div v-if="!isSmallScreen" class="scan hidden-sm">
                <AutoComplete
                  ref="scanCode"
                  v-model="query"
                  icon="ios-search"
                  @on-change="searchItems"
                  element-id="scan_barcode"
                  @on-select="selectSaleItem"
                  :placeholder="$t('search_scan_barcode')"
                  @on-focus="e => showKeyboard(e, searchItems, true)"
                >
                  <Option v-for="r in result" :value="r.id" :key="r.id"> {{ r.name }} ({{ r.code }}) </Option>
                </AutoComplete>
              </div>
              <Menu
                :theme="theme"
                mode="horizontal"
                @on-select="onOrder"
                :active-name="$store.getters.current"
                style="float: left; margin-left: 10px;"
              >
                <div class="layout-nav">
                  <Submenu name="4">
                    <template slot="title">
                      <Icon type="ios-desktop" size="18" />
                    </template>
                    <MenuItem v-for="(no, ni) in order_nos" :key="'order_' + ni" :name="no">
                      <Icon type="ios-desktop" />
                      {{ $tc('ref_tab') }}: {{ no }}
                    </MenuItem>
                    <MenuItem name="">
                      <Icon type="md-clipboard" />
                      {{ $t('open_order') }}
                    </MenuItem>
                  </Submenu>
                  <MenuItem name="" class="hidden-md">
                    <Tooltip :content="$t('open_order')">
                      <Icon type="ios-add-circle" size="18" />
                    </Tooltip>
                  </MenuItem>
                  <li class="ivu-menu-item" @click="getRegisterDetails()">
                    <i class="ivu-icon ivu-icon-ios-cash-outline" style="font-size: 18px;"></i>
                    <span class="hidden-lg">{{ $tc('register') }}</span>
                  </li>
                  <a v-if="$store.getters.demo" href="/" target="_blank" class="blink hidden-lg ivu-btn ivu-btn-primary">Shop</a>
                </div>
              </Menu>
            </Col>
            <Col>
              <Menu mode="horizontal" i-menu :theme="theme" active-name="pos" class="top-menu" @on-select="onSelect">
                <div class="layout-nav">
                  <template v-if="$store.state.settings.impersonation == 1">
                    <MenuItem class="hidden-sm" name="stop" v-if="$store.getters.user && $store.getters.user.acting_user">
                      <Tooltip :content="$t('x_impersonating', { x: $t('stop') })">
                        <Icon type="md-log-out" size="18" color="red" />
                      </Tooltip>
                    </MenuItem>
                    <MenuItem name="impersonate">
                      <Tooltip :content="$t('x_impersonating', { x: $t('start') })">
                        <Icon type="md-log-in" size="18" />
                      </Tooltip>
                    </MenuItem>
                  </template>
                  <MenuItem name="dashboard" class="hidden-md">
                    <Tooltip :content="$t('dashboard')">
                      <Icon type="md-home" size="18" />
                    </Tooltip>
                  </MenuItem>
                  <MenuItem name="customer_view" class="hidden-md">
                    <Tooltip :content="$t('customer_view')">
                      <Icon type="ios-desktop" size="18" />
                    </Tooltip>
                  </MenuItem>
                  <MenuItem name="settings" class="hidden-md">
                    <Tooltip :content="$t('settings')">
                      <Icon type="ios-cog" size="18" />
                    </Tooltip>
                  </MenuItem>
                  <MenuItem name="" class="hidden-lg" @click.native="keys = true">
                    <Tooltip :content="$t('shortcuts')">
                      <Icon type="md-key" size="18" />
                    </Tooltip>
                  </MenuItem>
                  <MenuItem name="" class="hidden-sm" @click.native="calculator = true">
                    <Tooltip :content="$t('calculator')">
                      <Icon type="ios-calculator" size="18" />
                    </Tooltip>
                  </MenuItem>
                  <li class="ivu-menu-item hidden-sm" @click="showAlerts()">
                    <Tooltip :content="$tc('alert', 2)">
                      <span class="topbar-badge ivu-badge">
                        <i class="ivu-icon ivu-icon-md-notifications" style="font-size: 18px;"></i>
                        <sup class="ivu-badge-dot"></sup>
                      </span>
                    </Tooltip>
                  </li>
                  <Submenu name="4">
                    <template slot="title" v-if="$store.state.user && $store.state.user.acting_user">
                      <Avatar
                        size="small"
                        :src="$store.state.user.acting_user.avatar"
                        v-if="$store.state.user.acting_user && $store.state.user.acting_user.avatar"
                      />
                      <Avatar v-else size="small" icon="ios-person" />
                      <span class="hidden-md">
                        {{ $t('hi') }}
                        <span class="capitalize">{{ $store.state.user.acting_user.username }}</span>
                      </span>
                    </template>
                    <template v-else slot="title">
                      <Avatar size="small" :src="$store.state.user.avatar" v-if="$store.state.user && $store.state.user.avatar" />
                      <Avatar v-else size="small" icon="ios-person" />
                      <span class="hidden-md">
                        {{ $t('hi') }}
                        <span class="capitalize">{{ $store.state.user.username }}</span>
                      </span>
                    </template>
                    <MenuItem name="" class="cursor-default no-hover" v-if="$store.state.user && $store.state.user.acting_user">
                      <div class="text-center bold" style="line-height:1.6;">
                        <div>{{ $t('impersonating_as') }}</div>
                        <Tag type="border" color="primary">{{ $store.state.user.acting_user.name }}</Tag>
                        <div class="mt16">{{ $t('logged_in_as') }}</div>
                      </div>
                    </MenuItem>
                    <MenuItem name="" class="cursor-default no-hover" v-if="$store.state.user">
                      <div class="text-center" style="cursor:default;">
                        <div v-if="$store.state.user.avatar" class="mb16">
                          <Avatar size="100" :src="$store.state.user.avatar" />
                        </div>
                        <Tag type="border" color="success">{{ $store.state.user.name }}</Tag>
                      </div>
                    </MenuItem>
                    <MenuItem name="profile">
                      <Icon type="md-person" size="16" />
                      {{ $t('profile') }}
                    </MenuItem>
                    <MenuItem name="change_password">
                      <Icon type="md-key" size="16" />
                      {{ $t('change_password') }}
                    </MenuItem>
                    <MenuGroup title="Logout">
                      <MenuItem name @click.native="logout()">
                        <Icon type="md-log-out" size="16" />
                        {{ $t('logout') }}
                      </MenuItem>
                    </MenuGroup>
                  </Submenu>
                  <fullscreen-component v-model="isFullscreen" class="hidden-sm" />
                </div>
              </Menu>
              <i @click="collapsedOrder" :class="rotateOrderIcon" class="close order-icon visible-sm"></i>
            </Col>
          </Row>
        </Header>
        <Layout class="pos-contents">
          <Content>
            <Layout class="layout-pos">
              <Sider
                ref="side1"
                collapsible
                :width="200"
                hide-trigger
                breakpoint="lg"
                :collapsed-width="0"
                v-model="isCollapsed"
                class="categories categories-scroll scroll-x shadow"
                :class="{ 'sidebar-light bg-light': theme != 'dark' }"
              >
                <Menu
                  accordion
                  width="auto"
                  :theme="theme"
                  v-if="categories"
                  :open-names="category"
                  @on-select="categoryChanged"
                  :active-name="active_category"
                  @on-open-change="categoryOpenChanged"
                >
                  <template v-for="c in categories">
                    <menu-component :category="c" :key="c.id"></menu-component>
                  </template>
                </Menu>
              </Sider>
              <Content class="content">
                <Loading v-if="cloading" />
                <div class="above-grid">
                  <Cascader
                    transfer
                    trigger="hover"
                    v-model="category"
                    :data="categories"
                    class="category-cascader"
                    @on-change="categoryChanged"
                  >
                    <a href="javascript:void(0)">{{ selected_category && selected_category.name }}</a>
                  </Cascader>
                  <span v-if="register_location" style="float: right;">
                    <Icon size="18" type="ios-navigate" style="margin-top: -4px;" :color="$store.getters.location.color" />
                    <span class="name">{{ register_location.label }}</span>
                  </span>
                </div>
                <div class="pos-grid scroll-x" v-if="items.length > 0">
                  <div class="pos-grid-scroll">
                    <a v-for="gitem in items" @click="addToOrder(gitem)" :key="gitem.id">
                      <pos-grid-item-component :item="gitem"></pos-grid-item-component>
                    </a>
                    <div style="clear: both; height: 16px;"></div>
                  </div>
                </div>
                <div v-else class="contents-middle" style="flex-direction:column;height:100%;">
                  <svg
                    viewBox="0 0 330.418 330.418"
                    xmlns="http://www.w3.org/2000/svg"
                    style="width:80px;height:80px;opacity:0.25;margin-bottom:2rem;"
                  >
                    <path d="M90.791,161c2.762,0,5-2.239,5-5v-7c0-2.761-2.238-5-5-5s-5,2.239-5,5v7C85.791,158.761,88.029,161,90.791,161z" />
                    <path
                      d="M239.627,82h-17.918V38c0-2.761-2.238-5-5-5s-5,2.239-5,5v44h-93V10h93v12c0,2.761,2.238,5,5,5s5-2.239,5-5V5 c0-2.761-2.238-5-5-5h-103c-2.762,0-5,2.239-5,5v77H90.791c-2.762,0-5,2.239-5,5v43c0,2.761,2.238,5,5,5s5-2.239,5-5V92h138.836 v159c0,38.277-31.141,69.418-69.418,69.418S95.791,289.277,95.791,251v-79c0-2.761-2.238-5-5-5s-5,2.239-5,5v79 c0,43.791,35.627,79.418,79.418,79.418s79.418-35.627,79.418-79.418V87C244.627,84.239,242.389,82,239.627,82z"
                    />
                    <path d="M132.709,48c-2.762,0-5,2.239-5,5s2.238,5,5,5h17.809c2.762,0,5-2.239,5-5s-2.238-5-5-5H132.709z" />
                    <path d="M179.9,48c-2.762,0-5,2.239-5,5s2.238,5,5,5h17.809c2.762,0,5-2.239,5-5s-2.238-5-5-5H179.9z" />
                    <path
                      d="M190.709,241c2.762,0,5-2.239,5-5v-42.5c0-16.818-13.683-30.5-30.5-30.5s-30.5,13.682-30.5,30.5v52 c0,16.818,13.683,30.5,30.5,30.5c8.147,0,15.807-3.172,21.567-8.934c1.952-1.953,1.952-5.118-0.001-7.071 c-1.953-1.953-5.118-1.953-7.071,0c-3.871,3.872-9.019,6.004-14.495,6.004c-11.304,0-20.5-9.196-20.5-20.5v-52 c0-11.304,9.196-20.5,20.5-20.5s20.5,9.196,20.5,20.5V236C185.709,238.761,187.947,241,190.709,241z"
                    />
                  </svg>
                  <h1 class="text-fade text-center more">{{ $t('no_item_in_category') }}</h1>
                </div>
                <div class="below-grid">
                  <div class="grid-search transparent">
                    <Input
                      search
                      v-model="search"
                      element-id="grid-search"
                      @on-search="searchGridItems"
                      :placeholder="$t('search_item_ph')"
                      @on-focus="e => showKeyboard(e, searchGridItems)"
                    />
                  </div>
                </div>
              </Content>
              <Sider
                ref="order"
                collapsible
                :width="401"
                hide-trigger
                breakpoint="md"
                class="order-items"
                :collapsed-width="0"
                v-model="isOrderCollapsed"
                @on-collapse="onOrderCollapse"
              >
                <pos-order-items-component :width="400" :order="order" :attributes="attributes"></pos-order-items-component>
              </Sider>
            </Layout>
          </Content>
        </Layout>
      </Layout>
      <Modal v-model="calculator" title="Calculator" width="350" class="calculator-modal vertical-center-modal no-footer">
        <div class="calculator">
          <calculator-component></calculator-component>
        </div>
        <template slot="footer"><span class="calculator-footer"></span></template>
      </Modal>
      <Modal
        v-model="openOrder"
        :footer-hide="true"
        :title="$t('open_order')"
        :closable="openOrderClose"
        :mask-closable="openOrderClose"
        @on-visible-change="openOrderChange"
        :width="$store.getters.restaurant ? 600 : 400"
      >
        <div v-if="$store.getters.restaurant">
          <div v-if="halls && halls.length">
            <div style="max-width: 400px; margin: 0 auto;">
              <Form :model="selected" @submit.native.prevent="" v-if="halls.length > 1">
                <FormItem label="">
                  <Select v-model="selected.hall" placeholder="" size="large">
                    <Option v-for="hall in halls" :key="hall.id" :value="hall.id">{{ hall.title }} ({{ hall.code }})</Option>
                  </Select>
                </FormItem>
              </Form>
            </div>
            <div v-for="hall in halls" :key="'hall_' + hall.id">
              <div v-if="hall.id == selected.hall">
                <Row v-if="hall.tables && hall.tables.length">
                  <span :key="'table_' + index" v-for="(table, index) in hall.tables">
                    <Col :xs="24" :sm="12" :md="8">
                      <div style="padding: 16px;">
                        <a @click="openNewOrder(hall.code, table.code)">
                          <div class="table" :class="{ active: order_nos.includes(hall.code + '-' + table.code) }">
                            <div class="text-center">{{ table.title }} ({{ table.code }})</div>
                          </div>
                        </a>
                      </div>
                    </Col>
                  </span>
                </Row>
                <div v-else>
                  <h2 class="text-center text-fade">{{ $t('no_table_to_display') }}</h2>
                </div>
              </div>
              <div v-else-if="!selected.hall">
                <h2 class="text-center text-fade">{{ $t('select_hall_to_load_tables') }}</h2>
              </div>
            </div>
            <Divider>
              <small>{{ $t('open_with_ref') }}</small>
            </Divider>
          </div>
        </div>
        <div style="max-width: 580px; margin: 0 auto;">
          <Form ref="openOrder" :model="open" :rules="openRules" @submit.native.prevent="openNewOrder">
            <FormItem class="apg" :label="$t('reference_table')" prop="reference">
              <Input v-model="open.reference" ref="openOrderInput" :placeholder="$t('reference_table')" size="large">
                <Button slot="append" @click="openNewOrder">{{ $t('open') }}</Button>
              </Input>
            </FormItem>
          </Form>
        </div>
        <div class="other-buttons">
          <div>
            <Button type="primary" icon="ios-cash-outline" @click="getRegisterDetails()" v-if="$store.getters.register">
              {{ $t('x_details', { x: $tc('register') }) }}
            </Button>
            <Button type="primary" icon="ios-print" @click="printLastSale" v-if="$store.getters.last_pos_sale">
              {{ $t('print_x', { x: $tc('last_pos_sale') }) }}
            </Button>
          </div>
          <Button icon="md-home" @click="$router.replace('/')" v-if="!openOrderClose">
            {{ $t('back_to_home') }}
          </Button>
        </div>
      </Modal>
      <Modal v-model="register" :title="$t('register_details')" :mask-closable="false" :footer-hide="true" class="np-header-footer">
        <div v-if="rloading">
          <Loading />
          <h1>{{ $t('register_details') }}</h1>
        </div>
        <close-pos-register-component v-else :record="registerDetails" :close="closeRegister" />
      </Modal>
      <Modal v-model="alerts" :title="$t('alerts_title')" :mask-closable="false" :footer-hide="true">
        <alerts-component :alerts="alerts_data" :close="() => (alerts = false)" />
      </Modal>
      <Modal v-model="keys" :width="380" :title="$t('shortcuts')" :footer-hide="true">
        <pos-shortcut-keys-component />
      </Modal>
      <Modal scrollable footer-hide :closable="false" :mask-closable="false" v-model="isStoreOpen">
        <select-location-component :pos="true" :loc="location" />
      </Modal>
      <Modal
        :width="400"
        footer-hide
        v-model="impersonate"
        :mask-closable="false"
        :title="$t('impersonate')"
        @on-visible-change="impersonateModal"
      >
        <user-impersonate-component :u="impersonateCount" @close="closeImpersonateModal" />
      </Modal>
      <Modal
        :footer-hide="true"
        :mask-closable="false"
        v-model="variantModal"
        :title="$t('select_x', { x: $tc('portion') + ', ' + $tc('modifier', 2) + ' & ' + $tc('variant', 2) })"
      >
        <select-variation-component :item="item" ref="selectVarMod" @on-submit="variationSubmitted"></select-variation-component>
      </Modal>
      <template v-if="$store.state.settings.onscreen_keyboard == 1">
        <simple-keyboard
          :input="input"
          v-show="visible"
          @onClose="hideKeyboard"
          :element="inputElement"
          @onAccept="onKeyboardAccept"
          @onChange="onKeyboardChange"
        />
      </template>
    </template>
  </div>
</template>

<script>
import emitter from './emitter';
import _debounce from 'lodash/debounce';
import MenuComponent from './sub/MenuComponent';
import SimpleKeyboard from './SimpleKeyboard';
import AlertsComponent from '../helpers/AlertsComponent';
import PosGridItemComponent from './PosGridItemComponent';
import PosOrderItemsComponent from './PosOrderItemsComponent';
import CalculatorComponent from '../helpers/CalculatorComponent';
import OrderCommonMethods from '@mpsjs/mixins/OrderCommonMethods';
import ClosePosRegisterComponent from './ClosePosRegisterComponent';
import FullscreenComponent from '@mpscom/helpers/FullscreenComponent';
import PosShortcutKeysComponent from '@mpscom/helpers/PosShortcutKeysComponent';
import UserImpersonateComponent from '@mpscom/helpers/UserImpersonateComponent';

export default {
  mixins: [OrderCommonMethods('price', 'order', true), emitter],
  components: {
    MenuComponent,
    SimpleKeyboard,
    AlertsComponent,
    CalculatorComponent,
    FullscreenComponent,
    PosGridItemComponent,
    PosOrderItemsComponent,
    PosShortcutKeysComponent,
    UserImpersonateComponent,
    ClosePosRegisterComponent,
  },
  data() {
    const checkOpenOrderRef = (rule, value, callback) => {
      if (!value || value == '') {
        callback(new Error(this.$t('field_is_required', { field: this.$t('reference_table') })));
      } else if (!new RegExp(/^([a-zA-Z0-9]{1,25})$/).test(value)) {
        callback(new Error(this.$t('alpha_num_error')));
      } else if (this.order_nos && this.order_nos.find(o => o === value)) {
        callback(new Error(this.$t('invalid_ref_error')));
      } else {
        callback();
      }
    };
    return {
      query: '',
      halls: [],
      items: [],
      result: [],
      keys: false,
      order: null,
      category: [],
      search: null,
      alerts: false,
      loading: true,
      saving: false,
      location: null,
      attributes: [],
      categories: [],
      register: false,
      cloading: false,
      rloading: false,
      openOrder: false,
      alerts_data: null,
      calculator: false,
      category_id: null,
      impersonate: false,
      isCollapsed: false,
      impersonateCount: 1,
      isFullscreen: false,
      openOrderClose: true,
      registerDetails: null,
      active_category: null,
      selected_category: null,
      isOrderCollapsed: false,
      selected: { hall: null },
      open: { reference: null },
      openRules: {
        reference: [{ required: true, validator: checkOpenOrderRef, trigger: ['blur', 'change'] }],
      },
      photos: [],
      isStoreOpen: false,
      input: null,
      visible: false,
      inputAction: null,
      inputRefocus: null,
      inputElement: null,
    };
  },
  computed: {
    isSmallScreen() {
      return window && document.documentElement.clientWidth <= 768;
    },
    orders() {
      return this.$store.getters.orders;
    },
    order_nos() {
      return Object.keys(this.$store.getters.orders).sort();
    },
    register_location() {
      return this.$store.getters.location && this.$store.getters.register
        ? this.$store.getters.locations.find(l => l.value == this.$store.getters.register.location_id)
        : null;
    },
    theme() {
      return this.$store.state.settings.theme;
    },
    rotateIcon() {
      return ['ivu-icon', this.isCollapsed ? 'ivu-icon-ios-folder-outline' : 'ivu-icon-ios-folder'];
    },
    rotateOrderIcon() {
      return ['ivu-icon', this.isOrderCollapsed ? 'ivu-icon-ios-cart' : 'ivu-icon-md-close pull-right'];
    },
    store_location() {
      return (
        this.$store.getters.user &&
        (!this.$store.getters.location ||
          !this.$store.getters.register ||
          this.$store.getters.location.value != this.$store.getters.register.location_id)
      );
    },
  },
  watch: {
    openOrder: function(v) {
      if (v) {
        this.$nextTick(() => {
          if (this.$refs.openOrderInput) {
            this.$refs.openOrderInput.focus();
          }
        });
      }
    },
    isStoreOpen: function(v) {
      if (!v) {
        this.$nextTick(() => {
          if (this.$refs.openOrderInput) {
            this.$refs.openOrderInput.focus();
          }
        });
      }
    },
  },
  created() {
    if (!this.$store.getters.staff) {
      window.location.href = '/';
    } else {
      // Set Title
      if (window && this.$route.meta.title) {
        document.title = this.$route.meta.title + ' - ' + this.$store.getters.settings.name;
      }
      this.category_id = this.$store.state.settings.default_category;
      this.$http
        .get('app/pos?category=' + this.$store.state.settings.default_category)
        .then(({ data }) => {
          this.$store.commit('setCustomers', data.customers);
          this.$store.commit('setTaxes', data.taxes);
          let orders = {};
          if (Object.keys(data.orders).length) {
            for (const oId in data.orders) {
              orders[oId] = { ...data.orders[oId], ...data.orders[oId].extra_attributes };
            }
          }
          this.$store.commit('setOrders', orders);
          data.items = data.items.map((i, p) => {
            i.item_id = i.id;
            if (this.$store.getters.demo) {
              i.photo =
                i.photo && !i.photo.includes('dummy.jpg')
                  ? i.photo
                  : this.photos[p] && !i.photo.includes('dummy.jpg')
                  ? this.photos[p]
                  : this.photos[Math.floor(Math.random() * this.photos.length)];
            }
            return i;
          });
          this.halls = data.halls;
          this.items = data.items;
          this.location = data.location;
          this.attributes = data.attributes;
          if (data.register && data.location.id == this.$store.getters.location.value) {
            this.$store.commit('setRegister', data.register);
          }
          const psd = c => ({ value: c.id, label: c.name, ...c });
          this.categories = this.mapDeep(data.categories, psd, 'children', true);
          this.selected.hall = data.halls && data.halls.length ? data.halls[0].id : null;
          this.selected_category = this.categories.filter(c => c.value == this.$store.state.settings.default_category)[0];
          this.$nextTick(() => {
            this.active_category = this.$store.state.settings.default_category;
          });
        })
        .then(() => {
          if (this.order_nos.length < 1) {
            if (this.$store.state.settings.auto_open_order == 1) {
              this.onOrder('Order 1');
            } else {
              this.openOrder = true;
              this.openOrderClose = false;
            }
          } else {
            this.$store.commit('setCurrent', this.order_nos[this.order_nos.length - 1]);
          }
          this.updateOrder();
        })
        .then(
          () =>
            (this.isStoreOpen =
              !this.$store.getters.location ||
              !this.$store.getters.register ||
              this.$store.getters.location.value != this.$store.getters.register.location_id)
        )
        .finally(() => (this.loading = false));

      this.$event.listen('register:opened', opened => {
        if (opened) {
          this.isStoreOpen = false;
          if (this.openOrder) {
            this.openOrderChange(true);
          }
          this.$nextTick(() => {
            if (this.order_nos.length) {
              this.openOrder = false;
              this.openOrderClose = true;
              this.$store.commit('setCurrent', this.order_nos[this.order_nos.length - 1]);
              this.updateOrder();
            }
          });
        }
      });

      // Delete order event
      this.$event.listen('order:delete', noAlert => {
        if (!noAlert && this.orders[this.$store.getters.current] && this.orders[this.$store.getters.current].orderId) {
          this.deleteOpenOrder(this.orders[this.$store.getters.current].orderId);
        }
        this.$store.commit('delOrder', this.$store.getters.current);
        this.$nextTick(() => {
          if (this.order_nos.length > 0) {
            this.onOrder(this.order_nos[0]);
            this.$storage.write('order', this.order_nos[0]);
          } else {
            this.$store.commit('setCurrent', this.order_nos[this.order_nos.length - 1]);
            if (this.$store.state.settings.auto_open_order == 1) {
              this.onOrder('Order 1');
            } else {
              this.openOrder = true;
              this.openOrderClose = false;
              this.$storage.write('order', null);
            }
          }
        });
        if (!noAlert) {
          this.$Notice.success({ title: this.$root.$tc('order') + ' ' + this.$t('deleted'), desc: this.$root.$t('record_deleted') });
        }
      });

      // Change order event
      this.$event.listen('order:updated', () => {
        this.orderDidChange(this.$store.getters.current);
      });
      this.$event.listen('pos:order:update', data => {
        this.addToOrder(data.item, data.qty, data.set, data.force, data.vCheck);
        this.orderDidChange(this.$store.getters.current);
      });

      // POS Shorcuts
      this.$event.listen('openAlerts', () => (this.alerts = true));
      this.$event.listen('pos:openOrder', () => {
        if (this.$store.state.settings.auto_open_order == 1) {
          this.onOrder('Order 1');
        } else {
          this.openOrder = true;
        }
      });
      this.$event.listen('showShortcutKeys', () => (this.keys = !this.keys));
      this.$event.listen('toggleCalculator', () => (this.calculator = !this.calculator));
      this.$event.listen('toggleCategories', () => (this.isCollapsed = !this.isCollapsed));

      // Subscribe to store events
      this.unsub = this.$store.subscribe(({ type }) => {
        if (type === 'addOrderRow' || type === 'delOrderRow') {
          this.updateOrderNow();
        }
        if (type === 'addOrderRow' || type === 'delOrderRow' || type === 'setOrderRow') {
          this.orderDidChange(this.$store.getters.current);
        }
        if (type === 'delPromotionItem') {
          this.order = this.orders[this.$store.getters.current];
          this.$storage.write('order', this.order);
        }
      });

      // Load pos data
      this.photos = [
        '/demo/pasta.jpg',
        '/demo/burger.jpg',
        '/demo/meatball.jpg',
        '/demo/pancake.jpg',
        '/demo/mee.jpg',
        '/demo/sushi.jpg',
        '/demo/cookie.jpg',
      ];

      this.category.push(this.$store.state.settings.default_category);
      this.isCollapsed = this.$store.getters.settings.categories_sidebar == 1 ? true : false;

      this.configureDebouncer();
      this.updateOrderDebouncer();

      this.$event.listen('addOrderRowByItemID', params => {
        this.addOrderRowByItemID(params.id, params.item_id);
      });
      this.$event.fire('pos:order:update', ({ item, qty, set, force, vCheck }) => {
        console.log('pos:order:update');
        this.addToOrder(item, qty, set, force, vCheck);
      });
      this.$event.listen('pos:print', data => {
        console.log('pos:print');
        this.print(data);
      });
      this.unsub = this.$store.subscribe(({ type }) => {
        if (type === 'addOrderRow' || type === 'setOrderRow') {
          this.updateOrder();
        }
      });
    }
  },
  mounted() {
    if (this.$store.state.settings.pos_server == 1) {
      this.$connect();
      this.$socket.addEventListener('error', event => {
        console.error('WebSocket error: ', event);
        this.$Notice.error({ title: this.$t('error'), desc: this.$t('pos_server_error'), duration: 10 });
      });
      this.$socket.addEventListener('message', event => {
        this.$Notice.info({ title: event.data, duration: 5 });
      });
    }
    this.$nextTick(() => {
      if (!this.openOrder) {
        if (!this.isMobile()) {
          document.querySelector('#scan_barcode').focus();
        }
      } else {
        this.$refs.openOrderInput.focus();
      }
    });
    if (this.$store.state.settings.onscreen_keyboard == 1) {
      // var keyboardContainer = document.getElementById('touch-keyboard');
      // keyboardContainer.addEventListener('click', e => {
      //   // e.preventDefault();
      //   setTimeout(() => {
      //     // this.inputElement.focus();
      //     console.log(this.inputElement);
      //     if (e.target !== keyboardContainer && !keyboardContainer.contains(e.target)) {
      //       console.log('Clicked inside ', keyboardContainer);
      //     }
      //   }, 0);
      // });
      // keyboardContainer.addEventListener('focusout', e => {});
      setTimeout(() => {
        const PosOrder = document.getElementById('pos-order');
        document.addEventListener('focusin', e => {
          if (
            window &&
            document.documentElement.clientWidth >= 998 &&
            e.target.id != 'grid-search' &&
            e.target.id != 'scan_barcode' &&
            // ((e.target.tagName.toUpperCase() == 'INPUT' && !e.target.classList.contains('ivu-input-number-input')) ||
            (e.target.tagName.toUpperCase() == 'INPUT' || e.target.tagName.toUpperCase() == 'TEXTAREA')
          ) {
            this.showKeyboard(e);
          }
        });
      }, 1500);
    }
  },
  beforeDestory() {
    this.unsub();
    this.$storage.write('order', null);
    if (this.$store.state.settings.pos_server == 1) {
      this.$socket.removeEventListener('error', () => {
        console.log('Socket error listener removed.');
      });
      this.$socket.removeEventListener('message', () => {
        console.log('Socket message listener removed.');
      });
      this.$disconnect();
    }
  },
  beforeRouteLeave(to, from, next) {
    this.orderDidChange(this.$store.getters.current);
    next();
  },
  methods: {
    onKeyboardAccept() {
      this.inputElement.value = this.input;
      if (this.inputAction) {
        this.inputAction(this.input);
      } else {
        let el;
        if (this.inputElement.id) {
          el = document.getElementById(this.inputElement.id.split('__')[0]);
        }
        if (el && el.dataset.details) {
          const data = JSON.parse(el.dataset.details);
          this.$event.fire(data.event, { data, qty: this.input });
        } else {
          this.dispatchEvents(this.inputElement, this.input);
        }
      }

      this.$nextTick(() => {
        const inputElement = this.inputElement;
        this.hideKeyboard();

        if (this.inputRefocus) {
          setTimeout(() => {
            inputElement.focus();
            inputElement.click();
            // setTimeout(() => (this.inputElement.value = this.inputElement.value), 0);
          }, 500);
        }
      });
    },
    onKeyboardChange(input) {
      this.input = input;
      this.inputElement.value = input;
    },
    // onKeyPress(button) {
    //   console.log('button', button);
    // },
    showKeyboard(e, action = null, refocus = false) {
      if (this.$store.state.settings.onscreen_keyboard == 1) {
        if (window && document.documentElement.clientWidth >= 998) {
          this.inputAction = action;
          this.input = e.target.value;
          this.inputRefocus = refocus;
          this.inputElement = e.target;
          this.visible = true;
        }
      }
    },
    hideKeyboard(e) {
      if (this.$store.state.settings.onscreen_keyboard == 1) {
        const vk = document.getElementById('touch-keyboard');
        setTimeout(() => {
          if (!e || (e && e.target != this.inputElement && vk && !vk.contains(e.target))) {
            this.input = null;
            this.inputAction = null;
            this.inputElement = null;
            this.inputRefocus = false;
            setTimeout(() => (this.visible = false), 50);
          }
        }, 50);
      }
    },
    print(data, n = 0) {
      if (this.$socket.readyState != 1) {
        this.$connect();
        this.$socket.addEventListener('error', event => {
          console.error('WebSocket error: ', event);
          this.$Notice.error({ title: this.$t('error'), desc: this.$t('pos_server_error'), duration: 10 });
        });
        this.$socket.addEventListener('message', event => {
          this.$Notice.info({ title: event.data, duration: 5 });
        });
        if (n == 0) {
          this.$Notice.info({ desc: this.$t('pos_server_reconnect_text'), duration: 5 });
          setTimeout(() => this.print(data, 1), 1000);
        }
        return false;
      }
      this.$nextTick(() => {
        if (this.$socket.readyState == 1) {
          this.$socket.send(JSON.stringify(data));
        } else {
          this.$Notice.error({ title: this.$t('error'), desc: this.$t('pos_server_connection_error'), duration: 10 });
        }
      });
    },
    printLastSale() {
      this.$event.fire('pos:printReceipt');
    },
    searchGridItems(q) {
      this.cloading = true;
      this.$http
        .get(`app/items/search?pos=1&category=${this.category_id}&q=${q}`)
        .then(res => {
          this.items = res.data.map((i, p) => {
            i.item_id = i.id;
            if (this.$store.getters.demo) {
              i.photo =
                i.photo && !i.photo.includes('dummy.jpg')
                  ? i.photo
                  : this.photos[p] && !i.photo.includes('dummy.jpg')
                  ? this.photos[p]
                  : this.photos[Math.floor(Math.random() * this.photos.length)];
            }
            return i;
          });
        })
        .finally(() => (this.cloading = false));
    },
    impersonateModal(v) {
      if (v) {
        this.impersonateCount++;
      }
    },
    closeImpersonateModal() {
      this.impersonate = false;
    },
    showAlerts() {
      this.$http
        .get('app/alerts')
        .then(res => (this.alerts_data = res.data))
        .then(() => (this.alerts = true))
        .finally(() => (this.loading = false));
    },
    closeRegister() {
      this.register = false;
      this.$store.commit('setRegister', null);
      this.$router.push('/');
    },
    getRegisterDetails() {
      if (this.$store.getters.register && this.$store.getters.register.id) {
        this.rloading = true;
        this.register = true;
        this.$http
          .get('app/pos/register/' + this.$store.getters.register.id)
          .then(res => (this.registerDetails = res.data))
          .catch(err => console.log(err))
          .finally(() => (this.rloading = false));
      } else {
        this.$Notice.error({ title: this.$t('failed'), desc: this.$t('failed_error_text'), duration: 10 });
      }
    },
    locationModealChanged(open) {
      if (!open) {
        if (this.openOrder && this.$store.getters.location) {
          this.$refs.openOrder.resetFields();
          if (!this.$store.getters.restaurant) {
            this.$nextTick(() => {
              this.$refs.openOrderInput.focus();
            });
          }
        } else if (!this.$store.getters.location) {
          this.$router.push('/');
        }
      }
    },
    updateOrderNow() {
      if (this.$store.getters.current && this.orders[this.$store.getters.current]) {
        if (!this.orders[this.$store.getters.current].date) {
          this.orders[this.$store.getters.current].date = new Date();
        }
        if (!this.orders[this.$store.getters.current].type) {
          this.orders[this.$store.getters.current].type = 'nett';
        }
        if (!this.orders[this.$store.getters.current].discount) {
          this.orders[this.$store.getters.current].discount = null;
        }
        if (!this.orders[this.$store.getters.current].promotions) {
          this.orders[this.$store.getters.current].promotions = [];
        }
        if (!this.orders[this.$store.getters.current].taxes) {
          this.orders[this.$store.getters.current].taxes = [];
        }
        if (!this.orders[this.$store.getters.current].total_items) {
          this.orders[this.$store.getters.current].total_items = 0;
        }
        if (!this.orders[this.$store.getters.current].grand_total) {
          this.orders[this.$store.getters.current].grand_total = 0;
        }
        if (!this.orders[this.$store.getters.current].total_quantity) {
          this.orders[this.$store.getters.current].total_quantity = 0;
        }
        if (!this.orders[this.$store.getters.current].customer_id) {
          this.orders[this.$store.getters.current].customer_id = this.$store.state.settings.default_customer;
        }
        this.order = { ...this.orders[this.$store.getters.current] };
        this.order.total_items = this.orderTotalItems;
        this.order.grand_total = this.orderPayableAmount;
        this.order.total_quantity = this.orderTotalQuantity;
        if (document && document.querySelector('#scan_barcode')) {
          if (!this.isMobile()) {
            document.querySelector('#scan_barcode').focus();
          }
        }
        this.$storage.write('order', this.order);
      }
    },
    updateOrderDebouncer() {
      this.updateOrder = _debounce(() => {
        this.updateOrderNow();
      }, 500);
    },
    configureDebouncer() {
      this.orderDidChange = _debounce(oId => {
        if (this.$store.getters.location && oId && this.orders[oId]) {
          this.saving = true;
          let order = { ...this.orders[oId], oId: oId };
          if (!order.items || order.items.length < 1) {
            this.saving = false;
            return;
          }
          order.total_items = order.items.length;
          order.total_quantity = order.items.map(item => item.quantity).reduce((total, curr) => total + curr, 0);
          order.grand_total = order.items.reduce(
            (total, item) =>
              total +
              this.formatDecimal(
                parseFloat(item.price) * parseFloat(item.quantity) + (item.tax_included == 1 ? 0 : parseFloat(item.total_tax_amount))
              ),
            0
          );
          if (this.attributes.length > 0) {
            let extras = this.attributes.map(attr => {
              let extra = {};
              extra[attr.slug] = order[attr.slug];
              delete order[attr.slug];
              return extra;
            });
            order.extra_attributes = Object.assign(...extras);
          }
          order.date = this.$moment(this.order && this.order.date ? this.order.date : new Date()).format(this.$moment.HTML5_FMT.DATE);
          this.$http
            .post('app/orders', order)
            .then(res => {
              if (res.data.success) {
                if (!order.created_at || !order.oId || !order.user || !order.customer) {
                  this.$store.commit('setOrder', oId, res.data.order);
                }
                this.$store.commit('updateOrder', { key: 'orderId', value: res.data.success, oId: this.$store.getters.current });
              } else {
                this.$Notice.error({ title: this.$t('failed'), desc: this.$t('failed_error_text'), duration: 120 });
              }
            })
            .finally(() => (this.saving = false));
          this.$storage.write('order', order);
        }
      }, 500);
    },
    deleteOpenOrder(orderId) {
      if (orderId) {
        this.$http
          .delete('app/orders/' + orderId)
          .then(res => {
            if (!res.data.success) {
              this.$Notice.error({ title: this.$t('failed'), desc: this.$t('failed_error_text'), duration: 120 });
            }
          })
          .catch(err => console.error(err));
      }
    },
    onOrder(name) {
      if (name) {
        if (!this.order_nos.find(n => n == name)) {
          this.$store.commit('addOrder', name);
          this.order_nos.push(name);
          this.orderDidChange(name);
        } else {
          this.$store.commit('setCurrent', name);
          this.$Notice.success({ title: this.$t('order_changed'), desc: this.$t('order_changed_text'), duration: 1 });
        }
        this.$nextTick(() => {
          this.updateOrder();
        });
      } else {
        if (this.$store.state.settings.auto_open_order == 1 && this.order_nos.length < 1) {
          this.onOrder('Order 1');
        } else {
          this.openOrder = true;
        }
      }
    },
    // selectItem(id) {
    //   this.query = '';
    //   this.addToOrder(JSON.parse(id));
    // },
    // selectSaleItem(id) {
    //   this.cloading = true;
    //   this.$http
    //     .get('app/sale_order_items/' + id)
    //     .then(res => this.addToOrder(res.data, 1, false, false, true))
    //     .catch(err => console.log(err))
    //     .finally(() => {
    //       this.query = '';
    //       this.cloading = false;
    //     });
    // },
    searchItems(search) {
      if (!search.includes('(')) {
        this.getItems(search, this);
      }
    },
    getItems: _debounce((search, vm) => {
      if (search.length > 35) {
        return;
      }
      vm.searching = true;
      let parsedBarcode = null;
      let iUrl = 'app/items/search?q=' + search;
      const search_delay = vm.$store.getters.search_delay;
      const scale_barcode = vm.$store.getters.scale_barcode;
      if (scale_barcode && scale_barcode.total_digits && scale_barcode.total_digits == search.length) {
        parsedBarcode = vm.parseScaleBarcode(search, scale_barcode);
        iUrl = 'app/items/search?scale=1&q=' + parsedBarcode.item_code + '&barcode=' + search;
      }
      vm.$http
        .get(iUrl)
        .then(res => {
          if (parsedBarcode && res.data && res.data.id) {
            let price =
              res.data.location_stock.length > 0 && res.data.location_stock[0].price
                ? parseFloat(res.data.location_stock[0].price)
                : parseFloat(res.data.price);
            res.data.barcode_price = vm.formatDecimal(parsedBarcode.price);
            res.data.barcode_qty = vm.formatQtyDecimal(parsedBarcode.weight);
            if (!res.data.barcode_price) {
              res.data.barcode_price = vm.formatDecimal(price / res.data.barcode_qty);
            }
            if (!res.data.barcode_qty) {
              res.data.barcode_qty = vm.formatQtyDecimal(parsedBarcode.price / price);
            }
            console.log('getItems barcode');
            vm.addToOrder(res.data);
            vm.query = '';
            vm.result = [];
          } else {
            if (res.data && res.data.length == 1) {
              vm.query = '';
              vm.result = [];
              console.log('getItems barcode else');
              vm.addToOrder(res.data[0]);
            } else {
              vm.result = res.data || [];
            }
          }
        })
        .finally(() => {
          vm.cloading = false;
          vm.searching = false;
        });
    }, search_delay || 250),
    onLocationChange(name) {
      this.$store.commit('setLocation', name);
    },
    collapsedSider() {
      this.$refs.side1.toggleCollapse();
    },
    collapsedOrder() {
      this.isCollapsed = true;
      this.$refs.order.toggleCollapse();
    },
    onOrderCollapse(collapse) {
      if (!collapse) {
        this.$event.fire('order:show');
      }
    },
    onSelect(name) {
      if (name == 'impersonate') {
        this.impersonate = !this.impersonate;
      } else if (name == 'stop') {
        this.$http.post('app/impersonate/stop').then(res => {
          if (res.data.success) {
            this.$store.commit('setActingUser', null);
            this.$Notice.success({ title: this.$t('success'), desc: this.$t('stopped_impersonating_text') });
          }
        });
      } else if (name == 'customer_view') {
        let routeData = this.$router.resolve({ name });
        window.open(routeData.href, '_blank');
      } else if (name) {
        this.activeMenu = name;
        this.$router.push({ name });
      }
    },
    openOrderChange(open) {
      if (open) {
        if (this.$refs.openOrder) {
          this.$refs.openOrder.resetFields();
        }
        this.$nextTick(() => {
          if (this.$refs.openOrderInput) {
            this.$refs.openOrderInput.focus();
          }
        });
      }
    },
    openNewOrder(hall, table) {
      if (hall && table) {
        this.open.reference = hall + '-' + table;
      }
      this.$refs.openOrder.validate(valid => {
        if (valid) {
          if (this.order_nos && this.order_nos.find(o => o == this.open.reference)) {
            this.onOrder(this.open.reference);
            this.openOrder = false;
            this.openOrderClose = true;
            this.$Notice.warning({ title: this.$t('order_exists'), desc: this.$t('order_exists_text'), duration: 3 });
          } else {
            this.onOrder(this.open.reference);
            this.openOrder = false;
            this.openOrderClose = true;
            this.$Notice.success({ title: this.$t('success'), desc: this.$t('new_order_opened'), duration: 3 });
          }
        } else {
          this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
        }
      });
    },
    categoryChanged(cId, cdata) {
      this.cloading = true;
      if (Array.isArray(cId)) {
        cId = cId[cId.length - 1];
      }
      let categories = [...this.categories];
      let flatCategories = this.flattenDeep(categories, 'children', false);
      this.selected_category = flatCategories.filter(c => c.value == cId)[0];
      this.active_category = this.selected_category.id;
      if (!this.selected_category.parent_id) {
        this.category = [];
      }
      if (cdata && cdata.length > 0) {
        this.category = cdata.map(c => c.id);
      }
      this.category_id = cId;
      this.$http
        .get('app/items?category=' + cId)
        .then(res => {
          res.data = res.data.map(i => {
            i.item_id = i.id;
            return i;
          });
          res.data = res.data.map((i, p) => {
            i.item_id = i.id;
            if (this.$store.getters.demo) {
              i.photo =
                i.photo && !i.photo.includes('dummy.jpg')
                  ? i.photo
                  : this.photos[p] && !i.photo.includes('dummy.jpg')
                  ? this.photos[p]
                  : this.photos[Math.floor(Math.random() * this.photos.length)];
            }
            return i;
          });
          this.items = res.data;
        })
        .finally(() => (this.cloading = false));
      setTimeout(() => {
        if (window && document.documentElement.clientWidth <= 768) {
          this.isCollapsed = true;
        }
      }, 100);
    },
    logout() {
      this.$event.fire('logOut');
    },
    toggleFullScreen() {
      this.$event.fire('expand:view');
    },
    closeOpened() {
      this.activeParnetMenu = [];
      this.$nextTick(() => {
        if (this.$refs.sideMenu) {
          this.$refs.sideMenu.updateOpened();
        }
      });
    },
    categoryOpenChanged(v) {
      this.category = v;
    },
  },
};
</script>

<style lang="scss" scoped>
@import 'scss/pos.scss';
</style>
