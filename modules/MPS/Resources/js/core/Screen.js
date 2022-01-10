const ScreenSize = {
  install(Vue, options = {}) {
    const vm = new Vue({
      data: {
        windowWidth: window.innerWidth,
        windowHeight: window.innerHeight,
      },
    });

    window.addEventListener('resize', function reportWindowSize() {
      vm.$data.windowWidth = window.innerWidth;
      vm.$data.windowHeight = window.innerHeight;
    });

    Vue.mixin({
      computed: {
        isSmallScreen() {
          return vm.$data.windowWidth < 768;
        },
        isMediumScreen() {
          return vm.$data.windowWidth > 768;
        },
        isLargeScreen() {
          return vm.$data.windowWidth > 1024;
        },
        getWindowWidth() {
          return vm.$data.windowWidth;
        },
        getWindowHeight() {
          return vm.$data.windowHeight;
        },
      },
    });
  },
};

if (typeof window !== 'undefined' && window.Vue) {
  window.Vue.use(ScreenSize);
}

export default ScreenSize;
