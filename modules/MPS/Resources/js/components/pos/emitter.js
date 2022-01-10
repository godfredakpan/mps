function broadcast(componentName, eventName, params) {
  this.$children.forEach(child => {
    const name = child.$options.name;

    if (name === componentName) {
      child.$emit.apply(child, [eventName].concat(params));
    } else {
      // todo 如果 params 是空数组，接收到的会是 undefined
      broadcast.apply(child, [componentName, eventName].concat([params]));
    }
  });
}

export default {
  methods: {
    dispatchEvents(elem, value) {
      const events = ['input', 'on-change'];
      // 'onsearch', 'on-search', 'on-blur'
      // 'oninput', 'change', 'onchange', 'on-input-change',
      // 'keyup', 'onkeyup', 'on-keyup', 'keypress', 'onkeypress', 'on-keypress',
      events.map(e => {
        // const event = new CustomEvent(e, {
        //   bubbles: true,
        //   detail: { input: value, value: value },
        // });
        const event = document.createEvent('HTMLEvents');
        event.initEvent(e, true, true);
        elem.dispatchEvent(event);
      });
      this.broadcast('FormItem', 'on-form-blur', value);
    },
    dispatch(componentName, eventName, params) {
      let parent = this.$parent || this.$root;
      let name = parent.$options.name;
      while (parent && (!name || name !== componentName)) {
        parent = parent.$parent;
        if (parent) {
          name = parent.$options.name;
        }
      }
      if (parent) {
        parent.$emit.apply(parent, [eventName].concat(params));
      }
    },
    broadcast(componentName, eventName, params) {
      broadcast.call(this, componentName, eventName, params);
    },
  },
};
