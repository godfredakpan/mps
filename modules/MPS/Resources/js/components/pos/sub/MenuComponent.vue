<template>
  <div>
    <Submenu :key="category.id" :name="category.id" v-if="category.children && category.children.length > 0">
      <template slot="title">
        {{ category.name }}
      </template>
      <!-- <MenuGroup :title="category.name"> -->
      <!-- <MenuItem class="all-border" :name="category.id">{{ $t('list_all_items') }}</MenuItem> -->
      <!-- </MenuGroup> -->
      <menu-component v-for="child in category.children" :key="child.id" :category="child"></menu-component>
    </Submenu>
    <MenuItem :key="category.id" :name="category.id" v-else>
      {{ category.label }}
    </MenuItem>
  </div>
</template>

<script>
export default {
  name: 'menu-component',
  props: {
    category: { type: Object, required: true },
  },
  methods: {},
};
</script>
<style lang="scss">
.ivu-menu-vertical {
  .ivu-menu-submenu-title,
  .ivu-menu-item-group-title {
    padding-left: 16px !important;
  }
  .ivu-menu {
    margin-left: -8px;
    // padding-left: 8px;
    // border-top: 1px solid rgba(0, 0, 0, 0.05);
    // border-left: 1px solid rgba(0, 0, 0, 0.05);
    background: rgba(0, 0, 0, 0.05) !important;
    // .ivu-menu {
    //     margin-left: -16px;
    //     // padding-left: 16px;
    //     .ivu-menu {
    //         margin-left: -24px;
    //         // padding-left: 24px;
    //         .ivu-menu {
    //             margin-left: -32px;
    //             // padding-left: 32px;
    //             .ivu-menu {
    //                 margin-left: -40px;
    //                 // padding-left: 40px;
    //                 .ivu-menu {
    //                     margin-left: -48px;
    //                     // padding-left: 48px;
    //                 }
    //             }
    //         }
    //     }
    // }
  }
  .ivu-menu-submenu {
    .ivu-menu-item {
      padding-left: 24px !important;
    }
    .ivu-menu-submenu {
      padding-left: 8px !important;
    }
  }
}
/*.child-menu {
    > .child-menu {
        padding-left: 16px !important;
        > .child-menu {
            padding-left: 32px !important;
        }
    }
}*/
.ivu-menu-vertical .ivu-menu-item-group-title {
  height: 30px;
  line-height: 30px;
}
.ps__scrollbar-y-rail {
  z-index: 999;
}
.all-border {
  /*background: #515a6e;*/
  /*padding: 0 !important;
    height: 30px !important;
    line-height: 30px !important;*/
  /*border-top: 1px solid #515a6e;*/
  /*border-bottom: 1px solid #515a6e;*/
}
</style>
