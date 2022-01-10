<template>
  <div class="layout">
    <Layout :class="{ 'layout-fixed': $store.getters.fixed_layout == 1 }" :style="{ minHeight: '100vh' }">
      <Header
        :style="{ padding: 0 }"
        :class="{ 'header-light bg-light': theme == 'light', 'header-primary bg-primary': theme == 'primary' }"
      >
        <Row>
          <Col :xs="24" :sm="12" :md="12" style="z-index: 90;">
            <div class="layout-logo">
              <i @click="collapsedSider" :class="rotateIcon" class="close pointer"></i>
              <div style="display: flex;">
                <router-link to="/" class="logo">
                  <!-- <span>{{ $store.state.settings.name }}</span> -->
                  <span class="hidden-md">{{ $store.state.settings.name }}</span>
                  <span class="visible-md">{{ $store.state.settings.short_name }}</span>
                </router-link>
                <Tooltip content="Online" v-if="isOnline">
                  <Icon type="ios-planet" class="success" size="20" style="margin: -3px 10px 0 10px;" />
                </Tooltip>
                <Tooltip content="Offline" v-else>
                  <Icon type="ios-planet-outline" class="error" size="18" style="margin: -3px 10px 0 10px;" />
                </Tooltip>
                <template v-if="$store.getters.staff">
                  <Menu
                    :theme="theme"
                    mode="horizontal"
                    class="location loc-xs-only"
                    @on-select="onLocationChange"
                    :active-name="$store.getters.location ? $store.getters.location.value : null"
                  >
                    <div class="">
                      <Submenu name="languages" class="no-arrow">
                        <template slot="title">
                          <span v-html="localeToFlag($store.state.user_language)" style="margin-right:8px;"></span>
                        </template>
                        <template v-for="lang in $store.state.languages">
                          <li :key="lang" class="ivu-menu-item" @click="changeLocale(lang)">
                            <span v-html="localeToFlag(lang)" style="margin-right:8px;"></span>
                            {{ lang | regionName }}
                          </li>
                        </template>
                      </Submenu>

                      <li
                        class="ivu-menu-item"
                        style="cursor: default;"
                        v-if="$store.state.user && $store.state.user.locations.length <= 1 && $store.getters.locations.length < 2"
                      >
                        <span v-if="$store.getters.location">
                          <i class="ivu-icon ivu-icon-ios-navigate" :style="{ 'font-size': '18px', color: $store.getters.location.color }">
                          </i>
                          {{ $store.getters.location.label }}
                        </span>
                      </li>

                      <Submenu v-else name="locations">
                        <template slot="title">
                          <span v-if="$store.getters.location">
                            <!-- <span :style="{ color: $store.getters.location.color }"> -->
                            <Icon type="ios-navigate" :color="$store.getters.location.color" size="18" />
                            <span class="name">{{ $store.getters.location.label }}</span>
                            <!-- <span class="name">{{ $store.getters.location.label }}</span> -->
                            <!-- </span> -->
                          </span>
                          <span v-else>
                            <span class="name">{{ $t('select_x', { x: $tc('location') }) }}</span>
                          </span>
                        </template>
                        <template v-for="loc in $store.getters.locations">
                          <template
                            v-if="
                              $store.getters.location &&
                                $store.getters.location.value != loc.value &&
                                ($store.getters.superAdmin || $store.getters.isUserLocation(loc.id))
                            "
                          >
                            <MenuItem :key="loc.value" :name="loc.value">
                              <Icon type="ios-navigate" :color="loc.color" />
                              {{ loc.label }}
                            </MenuItem>
                          </template>
                        </template>
                      </Submenu>
                      <a v-if="$store.getters.demo" href="/" target="_blank" class="blink hidden-lg ivu-btn ivu-btn-primary">Shop Demo</a>
                    </div>
                  </Menu>
                </template>
              </div>
            </div>
          </Col>
          <Col :xs="24" :sm="12" :md="12" style="z-index: 80;">
            <template v-if="$store.getters.staff">
              <Menu :theme="theme" class="top-menu" mode="horizontal" @on-select="onSelect" :active-name="activeMenu" v-if="user">
                <div class="layout-nav">
                  <MenuItem name="email" class="hidden-md" v-if="$store.getters.demo">
                    <Tooltip :content="$t('email_x', { x: $t('view') })">
                      <Icon type="md-mail" size="16" color="#ffd3a5" />
                    </Tooltip>
                  </MenuItem>
                  <template v-if="$store.state.settings.impersonation == 1">
                    <MenuItem name="stop" v-if="$store.getters.user && $store.getters.user.acting_user">
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
                  <MenuItem name="dashboard" class="hidden-sm">
                    <Tooltip :content="$t('dashboard')">
                      <Icon type="md-home" size="18" />
                    </Tooltip>
                  </MenuItem>
                  <li class="ivu-menu-item" v-if="$store.state.modules.Shop">
                    <a href="/">
                      <Tooltip :content="$t('shop')">
                        <bag-icon :size="16" mode="dark" style="margin-bottom:-2px;padding:1px" />
                      </Tooltip>
                    </a>
                  </li>
                  <MenuItem v-if="$store.getters.superAdmin" name="settings" class="hidden-sm">
                    <Tooltip :content="$t('settings')">
                      <Icon type="ios-cog" size="18" />
                    </Tooltip>
                  </MenuItem>
                  <!-- <Submenu name="languages" style="color: rgba(255, 255, 255, 1);" class="no-arrow">
                  <template slot="title">
                    <span v-html="localeToFlag($store.state.user_language)" style="margin-right:8px;"></span>
                  </template>
                  <template v-for="lang in $store.state.languages">
                    <li :key="lang" class="ivu-menu-item" @click="changeLocale(lang)">
                      <span v-html="localeToFlag(lang)" style="margin-right:8px;"></span>
                      {{ lang | regionName }}
                    </li>
                  </template>
                </Submenu> -->
                  <MenuItem name="calendar">
                    <Tooltip :content="$t('calendar')">
                      <Icon type="md-calendar" size="18" />
                    </Tooltip>
                  </MenuItem>
                  <MenuItem name="" class="hidden-sm" @click.native="keys = true">
                    <Tooltip :content="$t('shortcuts')">
                      <Icon type="md-key" size="18" />
                    </Tooltip>
                  </MenuItem>
                  <MenuItem name="pos">
                    <Tooltip :content="$t('pos')">
                      <Icon type="ios-apps" size="18" />
                    </Tooltip>
                  </MenuItem>
                  <!-- <MenuItem name="4">
                  <Badge dot class="topbar-badge">
                    <Icon type="md-notifications" size="18" />
                  </Badge>
                </MenuItem>-->
                  <li class="ivu-menu-item" @click="showAlerts()" style="margin-right:0.5rem">
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
            </template>
            <template v-else>
              <Menu :theme="theme" class="top-menu" mode="horizontal" @on-select="onSelect" :active-name="activeMenu" v-if="user">
                <div class="layout-nav">
                  <li class="ivu-menu-item" v-if="$store.state.modules.Shop">
                    <a href="/">
                      <Tooltip :content="$t('shop')">
                        <bag-icon :size="16" mode="dark" style="margin-bottom:-2px;padding:1px" /> Go to Shop
                      </Tooltip>
                    </a>
                  </li>
                </div>
              </Menu>
            </template>
          </Col>
        </Row>
      </Header>
      <Layout>
        <div>
          <Sider
            ref="side1"
            collapsible
            :width="220"
            hide-trigger
            breakpoint="md"
            v-model="collapsed"
            :collapsed-width="50"
            :style="{ overflow: 'hidden' }"
            :class="{ 'sidebar-light bg-light': theme != 'dark', 'left-sider': true }"
          >
            <side-menu
              accordion
              ref="sideMenu"
              :theme="theme"
              @on-select="onSelect"
              :menu-list="menuList"
              :collapsed="collapsed"
              :active-name="$route.name"
              class="menu-scroller scroll-light"
            ></side-menu>
          </Sider>
        </div>
        <div
          scroll-region
          class="main-scrollbar content-scroller"
          :class="{ 'contents-wider': collapsed, 'contents-wide': true, 'layout-collapsed': collapsed }"
        >
          <Layout ref="contents" :style="{ padding: 0 }" :class="{ fullwidth: isCollapsed, 'layout-content': true }">
            <Layout :class="{ 'mhvh-50': $store.getters.fixed_layout == 1 }">
              <Content class="content">
                <Alert
                  banner
                  closable
                  show-icon
                  type="warning"
                  v-if="$store.getters.demo"
                  style="border-radius: 0; border-top: 0; border-left: 0; border-right: 0; margin: 0;"
                >
                  Modern Point of Sale Solution <small>(Demo v{{ version }})</small>
                  <template slot="desc">
                    You can
                    <a target="_blank" href="https://github.com/Tecdiary/MPS/discussions/new?category=q-a"> ask question</a>,
                    <a target="_blank" href="https://github.com/Tecdiary/MPS/issues/new?labels=bug">
                      report bug/error
                    </a>
                    and
                    <a target="_blank" href="https://github.com/Tecdiary/MPS/discussions/new?category=ideas"> request feature/report</a>.
                  </template>
                </Alert>
                <transition
                  appear
                  name="fade"
                  mode="out-in"
                  enter-active-class="animate__animated fast animate__fadeInDown"
                  leave-active-class="animate__animated faster animate__fadeOutRight"
                >
                  <div style="background: #f5f7f9;" :class="isSmallScreen ? 'small-screen-contents' : 'medium-screen-contents'">
                    <router-view></router-view>
                  </div>
                </transition>
              </Content>
              <Footer class="layout-footer-center" style="padding: 16px 20px;">
                &copy; {{ new Date().getFullYear() }} {{ $store.state.settings.name }} - All rights reserved.
                <div class="version float-right">
                  Version {{ version }}
                  <small>
                    <a href="https://tecdiary.github.io/MPS/" target="_blank">
                      <Icon type="md-help-circle" size="18" style="margin-top: -5px;" />
                    </a>
                  </small>
                </div>
              </Footer>
            </Layout>
          </Layout>
        </div>
      </Layout>
    </Layout>
    <Modal v-model="alerts" :title="$t('alerts_title')" :mask-closable="false" :footer-hide="true">
      <alerts-component :alerts="alerts_data" :close="() => (alerts = false)" />
    </Modal>
    <Modal v-model="keys" :width="980" :title="$t('shortcuts')" :footer-hide="true">
      <shortcut-keys-component />
    </Modal>
    <Modal
      scrollable
      footer-hide
      :mask-closable="false"
      v-model="store_location"
      :closable="!!$store.getters.superAdmin"
      :title="$t('select_x', { x: $tc('location') })"
    >
      <select-location-component :loc="null" />
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
    <Modal :width="400" footer-hide v-model="email" :title="$t('view_x', { x: $t('email') })">
      <List border item-layout="vertical">
        <ListItem>
          <h3 class="text-center">{{ $t('for_demo_purpose') }}</h3>
        </ListItem>
        <ListItem>
          <a class="ivu-btn ivu-btn-primary ivu-btn-long" href="/admin/notifications/sale" target="_blank">{{ $tc('sale') }}</a>
        </ListItem>
        <ListItem>
          <a class="ivu-btn ivu-btn-primary ivu-btn-long" href="/admin/notifications/purchase" target="_blank">{{ $tc('purchase') }}</a>
        </ListItem>
        <ListItem>
          <div class="text-center">{{ $tc('payment') }}</div>
          <template slot="action">
            <ButtonGroup vertical style="width:100%;">
              <a class="ivu-btn ivu-btn-primary ivu-btn-long" href="/admin/notifications/payment/created" target="_blank">{{
                $t('created')
              }}</a>
              <a class="ivu-btn ivu-btn-success ivu-btn-long" href="/admin/notifications/payment/received" target="_blank">{{
                $t('received')
              }}</a>
              <a class="ivu-btn ivu-btn-warning ivu-btn-long" href="/admin/notifications/payment/reminder" target="_blank">{{
                $t('reminder')
              }}</a>
            </ButtonGroup>
          </template>
        </ListItem>
      </List>
    </Modal>
  </div>
</template>
<script>
import BagIcon from '@mpscom/icons/Bag';
import SideMenu from '@mpscom/sidebar/Menu';
import { routes } from '@mpsjs/routes/index';
import packageData from '@app/composer.json';
import ShopOpenIcon from '@mpscom/icons/ShopOpen';
import AlertsComponent from '@mpscom/helpers/AlertsComponent';
import FullscreenComponent from '@mpscom/helpers/FullscreenComponent';
import ShortcutKeysComponent from '@mpscom/helpers/ShortcutKeysComponent';
import UserImpersonateComponent from '@mpscom/helpers/UserImpersonateComponent';
export default {
  components: { ShopOpenIcon, BagIcon, AlertsComponent, FullscreenComponent, SideMenu, ShortcutKeysComponent, UserImpersonateComponent },
  data() {
    return {
      routes: {},
      keys: false,
      email: false,
      alerts: false,
      loading: false,
      collapsed: false,
      alerts_data: null,
      impersonate: false,
      isCollapsed: false,
      impersonateCount: 1,
      isFullscreen: false,
      store_location: false,
      version: packageData.version,
    };
  },
  watch: {
    collapsed: function(newVal, oldVal) {
      if (window) {
        document.body.className = this.$store.getters.theme == 'dark' ? 'bg-dark' : 'bg-light';
      }
    },
  },
  created() {
    if (this.$store.getters.customer) {
      window.location.href = '/';
    } else {
      if (!this.user) {
        this.$router.push({ path: '/login' });
      }
      this.$event.listen('location:select', () => {
        this.store_location = this.user && !this.$store.getters.location;
      });
      this.$event.listen('openAlerts', () => (this.alerts = true));
      this.$event.listen('showShortcutKeys', () => (this.keys = !this.keys));
      this.routes = routes(this.$store.state.user_language)[0].children;
      this.collapsed = this.user && this.user.settings && this.user.settings.collapsed == 1 ? true : false;
      this.store_location = this.user && !this.user.location_id && !this.$store.getters.location;
    }
  },
  mounted() {
    if (window && this.$store.getters.fixed_layout == 1) {
      document.body.classList.add('fixed');
    }
  },
  beforeMount() {
    if (
      this.$route.meta.access &&
      !this.$store.getters.superAdmin &&
      this.$route.meta.access == 'super' &&
      !this.can(this.$route.meta.permission)
    ) {
      this.$Loading.error();
      this.$Notice.error({
        title: this.$t('access_denied'),
        desc: this.$t('not_allowed_resource'),
        duration: 30,
      });
      this.$router.push('/');
    }
    if (window && this.$route.meta.title) {
      document.title = this.$route.meta.title + ' - ' + this.$store.getters.settings.name;
    }
    // this.store_location = this.user && !this.$store.getters.location;
  },
  computed: {
    // store_location() {
    //     return this.user && !this.$store.getters.location;
    // },
    activeMenu() {
      return this.$route.name;
    },
    user() {
      return this.$store.getters.user;
    },
    theme() {
      return this.$store.getters.theme;
    },
    rotateIcon() {
      return ['ivu-icon', this.collapsed ? 'ivu-icon-md-menu' : this.isCollapsed ? 'ivu-icon-ios-menu' : 'ivu-icon-md-more'];
    },
    menuList() {
      return this.getMenuByRoutes(this.routes, true);
    },
  },
  methods: {
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
    getMenuByRoutes(list, access) {
      let res = [];
      this.forEach(list, item => {
        if (!item.meta || (item.meta && !item.meta.hideInMenu)) {
          if (this.$store.getters.superAdmin) {
            this.addMenu(res, item, access);
          } else if (item.meta && item.meta.permission) {
            if (this.can(item.meta.permission)) {
              this.addMenu(res, item, access);
            }
          } else if (item.meta && !item.meta.access) {
            this.addMenu(res, item, access);
          }
        }
      });
      return res;
    },
    addMenu(res, item, access) {
      if (this.$store.getters.superAdmin || !item.meta.permissions || (item.meta.permissions && this.canAny(item.meta.permissions))) {
        let obj = {
          meta: item.meta,
          name: item.name,
          icon: (item.meta && item.meta.icon) || '',
        };
        if (((item.children && item.children.length !== 0) || (item.meta && item.meta.showAlways)) && this.showThisMenuEle(item, access)) {
          obj.children = this.getMenuByRoutes(item.children, access);
        }
        if (item.meta && item.meta.href) {
          obj.href = item.meta.href;
        }
        if (this.showThisMenuEle(item, access)) {
          res.push(obj);
        }
      }
    },
    showThisMenuEle(item, access) {
      return !item.meta.restaurant || (item.meta.restaurant && item.meta.restaurant == this.$store.getters.restaurant);
    },
    forEach(arr, fn) {
      if ((arr && !arr.length) || !fn) return;
      let i = -1;
      let len = arr.length;
      while (++i < len) {
        let item = arr[i];
        fn(item, i, arr);
      }
    },
    onLocationChange(name) {
      this.loading = true;
      this.$http
        .post('app/location', { location_id: name })
        .then(res => {
          this.$Notice.destroy();
          if (res.data.success) {
            this.$store.commit('setLocation', name);
            this.$Notice.success({
              title: this.$t('success'),
              desc: this.$t('location_changed_text'),
              duration: 5,
            });
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
    collapsedSider() {
      this.$refs.side1.toggleCollapse();
      if (window) {
        let event;
        if (typeof Event === 'function') {
          event = new Event('resize');
        } else {
          event = document.createEvent('Event');
          event.initEvent('resize', true, true);
        }
        window.dispatchEvent(event);
      }
    },
    onOpenChange(v) {
      this.$nextTick(() => {
        this.$refs.menuScroller.update();
        setTimeout(() => this.$refs.menuScroller.update(), 500);
      });
    },
    onSelect(route) {
      if (route == 'languages') {
        console.log(route);
      } else if (route == 'email') {
        this.email = !this.email;
      } else if (route == 'impersonate') {
        this.impersonate = !this.impersonate;
      } else if (route == 'stop') {
        this.$http.post('app/impersonate/stop').then(res => {
          if (res.data.success) {
            this.$store.commit('setActingUser', null);
            this.$Notice.success({ title: this.$t('success'), desc: this.$t('stopped_impersonating_text') });
          }
        });
      } else if (typeof route === 'string') {
        route = { name: route };
      }
      let { name, params, query } = route;
      this.$router.replace({ query: {} }).catch(err => {}); // TODO
      if (name && name != this.$route.name) {
        if (name.indexOf('isTurnByHref_') > -1) {
          window.open(name.split('_')[1]);
          return;
        }
        this.$router.push({ name, params, query });
        this.$nextTick(() => {
          if (window && document.documentElement.clientWidth <= 768) {
            this.collapsed = true;
            this.isCollapsed = true;
          }
          if (this.$refs.contents) {
            this.$refs.contents.$el.scrollTo(0, 0);
          }
          if (this.$refs.menuScroller) {
            this.$nextTick(() => {
              if (this.$refs.menuScroller) {
                let sH = this.$refs.menuScroller.$el.scrollHeight;
                this.$refs.menuScroller.update();
                this.$nextTick(() => {
                  if (sH > this.$refs.menuScroller.$el.scrollHeight) {
                    this.$refs.menuScroller.$el.scrollTop = 0;
                  }
                });
              }
            });
          }
        });
      }
    },
    logout() {
      this.$event.fire('logOut');
    },
    toggleFullScreen() {
      this.$event.fire('expand:view');
    },
    localeToFlag(lang) {
      if (lang == 'en') {
        return 'US'.toUpperCase().replace(/./g, char => String.fromCodePoint(char.charCodeAt(0) + 127397));
      }
      return lang.toUpperCase().replace(/./g, char => String.fromCodePoint(char.charCodeAt(0) + 127397));
    },
    changeLocale(lang) {
      this.$event.fire('locale:change', lang);
    },
  },
};
</script>

<style lang="scss" scoped>
.top-menu {
  float: right;
}
.bc-info {
  float: right;
}
.close {
  // display: none;
  float: left;
  width: 50px;
  height: 50px;
  line-height: 50px;
  margin-left: -20px;
  text-align: center;
  display: inline-block;
}
.layout-logo {
  width: 100%;
  // min-width: 360px;
}

@media (max-width: 992px) {
  .layout-logo {
    margin: 0;
  }
  .close {
    margin-left: 0;
  }
}

@media (max-width: 767px) {
  .layout-logo {
    margin: 0;
    width: 100%;
    height: 50px;
    display: block;
    line-height: 50px;
    text-align: center;
    .logo {
      padding-left: 0;
    }
  }
  .top-menu {
    float: none;
    display: block;
    border-bottom: 1px solid rgba(51, 56, 67, 0.3);
  }
  .layout-nav {
    float: right;
    display: flex;
    margin: 0 auto;
    justify-content: center;
    @media (max-width: 539px) {
      top: 48px;
      width: 100%;
      float: none;
      height: 42px;
      line-height: 42px;
      position: absolute;
      background: #515a6e;
    }
    .ivu-select-dropdown {
      will-change: top, right;
      left: auto !important;
      right: 0 !important;
    }
  }
  .ivu-layout-sider.ivu-layout-sider-zero-width.ivu-layout-sider-collapsed {
    height: 0;
  }
  .layout-footer-center {
    text-align: center;
  }
}

.version {
  float: right;
  width: 125px;
  text-align: right;
  margin-right: -20px;
  transition: all 0.25s ease-in;
  a {
    opacity: 0;
    transition: all 0.2s ease-in;
  }
  &:hover {
    margin-right: 0;
    a {
      opacity: 1;
    }
  }
  @media (max-width: 767px) {
    float: none;
    width: 100%;
    text-align: center;
  }
}
</style>
