<template>
  <Dropdown
    ref="dropdown"
    :transfer="hideTitle"
    :placement="placement"
    @on-click="handleClick"
    :class="hideTitle ? '' : 'collased-menu-dropdown'"
  >
    <a class="drop-menu-a" type="text" @mouseover="handleMousemove($event, children)" :style="{ textAlign: !hideTitle ? 'left' : '' }">
      <Icon :size="rootIconSize" :color="textColor" :type="parentItem.icon" />
      <span class="menu-title" v-if="!hideTitle">
        {{ showTitle(parentItem) }}
      </span>
      <Icon style="float: right;" v-if="!hideTitle" type="ios-arrow-forward" :size="16" />
    </a>
    <DropdownMenu ref="dropdown" slot="list">
      <template v-for="child in children">
        <collapsed-menu :parent-item="child" :icon-size="iconSize" v-if="showChildren(child)" :key="`drop-${child.name}`"></collapsed-menu>
        <DropdownItem v-else :key="`drop-${child.name}`" :name="child.name" :divided="child.meta.divided">
          <Icon :size="iconSize" :type="child.icon" />
          <span class="menu-title">{{ showTitle(child) }}</span>
        </DropdownItem>
      </template>
    </DropdownMenu>
  </Dropdown>
</template>
<script>
import mixin from './mixins/mixin';
import itemMixin from './mixins/item-mixin';
export default {
  name: 'CollapsedMenu',
  mixins: [mixin, itemMixin],
  props: {
    hideTitle: {
      type: Boolean,
      default: false,
    },
    rootIconSize: {
      type: Number,
      default: 16,
    },
  },
  data() {
    return {
      placement: 'right-end',
    };
  },
  methods: {
    handleClick(name) {
      this.$emit('on-click', name);
    },
    handleMousemove(event, children) {
      const { pageY } = event;
      const height = children.length * 38;
      const isOverflow = pageY + height < window.innerHeight;
      this.placement = isOverflow ? 'right-start' : 'right-end';
    },
    findNodeUpperByClasses(ele, classes) {
      let parentNode = ele.parentNode;
      if (parentNode) {
        let classList = parentNode.classList;
        if (classList && classes.every(className => classList.contains(className))) {
          return parentNode;
        } else {
          return this.findNodeUpperByClasses(parentNode, classes);
        }
      }
    },
  },
  mounted() {
    let dropdown = this.findNodeUpperByClasses(this.$refs.dropdown.$el, ['ivu-select-dropdown', 'ivu-dropdown-transfer']);
    if (dropdown) dropdown.style.overflow = 'visible';
  },
};
</script>
