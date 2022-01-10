<template>
  <div class="side-menu-wrapper">
    <slot></slot>
    <Menu
      ref="menu"
      width="auto"
      :theme="theme"
      v-show="!collapsed"
      :accordion="accordion"
      :open-names="openedNames"
      :active-name="activeName"
      @on-select="handleSelect"
      @on-open-change="handleOpenChange"
    >
      <template v-for="item in menuList">
        <template v-if="item.children && item.children.length === 1">
          <side-menu-item
            :parent-item="item"
            v-if="showChildren(item)"
            :key="`menu-${item.name ? item.name : item.meta.mName}`"
          ></side-menu-item>
          <menu-item
            v-else
            :name="getNameOrHref(item, true)"
            :key="`menu-${item.children[0].name ? item.children[0].name.name : item.children[0].name.meta.mName}`"
          >
            <Icon :type="item.children[0].icon || ''" />
            <span>{{ showTitle(item.children[0]) }}</span>
          </menu-item>
        </template>
        <template v-else>
          <side-menu-item
            :parent-item="item"
            v-if="showChildren(item)"
            :key="`menu-${item.name ? item.name : item.meta.mName}`"
          ></side-menu-item>
          <menu-item v-else :name="getNameOrHref(item)" :key="`menu-${item.name ? item.name : item.meta.mName}`">
            <Icon :type="item.icon || ''" />
            <span>{{ showTitle(item) }}</span>
          </menu-item>
        </template>
      </template>
    </Menu>
    <div class="menu-collapsed" v-show="collapsed" :list="menuList">
      <template v-for="item in menuList">
        <collapsed-menu
          hide-title
          :theme="theme"
          :parent-item="item"
          :icon-size="iconSize"
          @on-click="handleSelect"
          :root-icon-size="rootIconSize"
          v-if="item.children && item.children.length > 1"
          :key="`drop-menu-${item.name ? item.name : item.meta.mName}`"
        ></collapsed-menu>
        <Tooltip
          v-else
          transfer
          placement="right"
          :key="`drop-menu-${item.name}`"
          :content="showTitle(item.children && item.children[0] ? item.children[0] : item)"
        >
          <a @click="handleSelect(getNameOrHref(item, true))" class="drop-menu-a" :style="{ textAlign: 'center' }">
            <Icon :size="rootIconSize" :color="textColor" :type="item.icon || (item.children && item.children[0].icon)" />
          </a>
        </Tooltip>
      </template>
    </div>
  </div>
</template>
<script>
import mixin from './mixins/mixin';
import SideMenuItem from './MenuItem.vue';
import CollapsedMenu from './Collapsed.vue';
export default {
  name: 'SideMenu',
  mixins: [mixin],
  components: {
    SideMenuItem,
    CollapsedMenu,
  },
  props: {
    menuList: {
      type: Array,
      default() {
        return [];
      },
    },
    collapsed: {
      type: Boolean,
    },
    theme: {
      type: String,
      default: 'dark',
    },
    rootIconSize: {
      type: Number,
      default: 20,
    },
    iconSize: {
      type: Number,
      default: 16,
    },
    accordion: Boolean,
    activeName: {
      type: String,
      default: '',
    },
    openNames: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      openedNames: [],
    };
  },
  methods: {
    handleSelect(name) {
      this.$emit('on-select', name);
    },
    handleOpenChange(name) {
      this.$emit('on-open-change', name);
    },
    getOpenedNamesByActiveName(name) {
      // return this.$route.matched.map(item => item.name).filter(item => item !== name);
      return this.$route.matched
        .map(item => item && item.meta && !item.meta.hideInMenu && item.meta.mName)
        .filter(item => item && item !== name);
    },
    updateOpenName(name) {
      if (name === this.$config.homeName) this.openedNames = [];
      else this.openedNames = this.getOpenedNamesByActiveName(name);
    },
    getUnion(arr1, arr2) {
      return Array.from(new Set([...arr1, ...arr2]));
    },
  },
  computed: {
    textColor() {
      return this.theme === 'dark' ? '#fff' : '#495060';
    },
  },
  watch: {
    activeName(name) {
      if (this.accordion) this.openedNames = this.getOpenedNamesByActiveName(name);
      else this.openedNames = this.getUnion(this.openedNames, this.getOpenedNamesByActiveName(name));
    },
    openNames(newNames) {
      this.openedNames = newNames;
    },
  },
  mounted() {
    this.openedNames = this.getUnion(this.openedNames, this.getOpenedNamesByActiveName(name));
    this.$nextTick(() => {
      if (this.$refs.menu) {
        this.$refs.menu.updateOpened();
      }
    });
  },
  created() {
    this.$event.listen('menu:update', route => {
      if (!(route && route.meta.mName && this.openedNames.includes(route && route.meta.mName))) {
        this.$nextTick(() => {
          this.openedNames = route && route.meta.mName ? [route.meta.mName] : [];
          this.$nextTick(() => {
            if (this.$refs.menu) {
              this.$refs.menu.updateOpened();
            }
          });
        });
      }
    });
  },
};
</script>

<style lang="scss">
.side-menu-wrapper {
  user-select: none;
  .menu-collapsed {
    padding-top: 10px;
    .ivu-dropdown {
      width: 100%;
      .ivu-dropdown-rel a {
        width: 100%;
      }
    }
    .ivu-tooltip {
      width: 100%;
      .ivu-tooltip-rel {
        width: 100%;
      }
      .ivu-tooltip-popper .ivu-tooltip-content {
        .ivu-tooltip-arrow {
          border-right-color: #fff;
        }
        .ivu-tooltip-inner {
          color: #495060;
          background: #fff;
        }
      }
    }
  }
  a.drop-menu-a {
    width: 100%;
    padding: 6px;
    color: #495060;
    margin: 4px auto;
    text-align: center;
    display: inline-block;
    &:active,
    &:focus,
    &:hover {
      background: rgba(0, 0, 0, 0.1);
    }
  }
}
.menu-title {
  padding-left: 6px;
}
.ivu-select-dropdown.ivu-dropdown-transfer {
  max-height: 90%;
  min-width: 160px;
  // margin-left: 5px;
  // border-top-left-radius: 0;
  // border-bottom-left-radius: 0;
  box-shadow: 1px 1px 4px 1px rgba(0, 0, 0, 0.2);
}
</style>
