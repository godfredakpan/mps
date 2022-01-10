export default {
  methods: {
    showTitle(item, vm) {
      let { title } = item.meta;
      return title ? title : item.name;
    },
    showChildren(item) {
      return item.children && (item.children.length > 1 || (item.meta && item.meta.showAlways));
    },
    getNameOrHref(item, children0) {
      return item.href
        ? `isTurnByHref_${item.href}`
        : children0 && item.children && item.children.length
        ? item.children[0].name
        : item.name;
    },
  },
};
