export default {
  props: {
    parentItem: {
      type: Object,
      default: () => {},
    },
    theme: String,
    iconSize: Number,
  },
  computed: {
    parentName() {
      return this.parentItem.name ? this.parentItem.name : this.parentItem.meta.mName;
    },
    children() {
      return this.parentItem.children;
    },
    textColor() {
      return this.theme === 'dark' ? '#fff' : '#495060';
    },
  },
};
