<template>
  <li v-if="showFullScreenBtn" class="ivu-menu-item">
    <!-- <Tooltip :content="value ? $t('exit_fullscreen') : $t('fullscreen')" placement="bottom-end"> -->
    <Icon @click.native="handleChange" :type="value ? 'ios-contract' : 'ios-expand'" :size="16"></Icon>
    <!-- </Tooltip> -->
  </li>
</template>

<script>
export default {
  name: 'Fullscreen',
  computed: {
    showFullScreenBtn() {
      return window.navigator.userAgent.indexOf('MSIE') < 0;
    },
  },
  props: {
    value: {
      type: Boolean,
      default: false,
    },
  },
  created() {
    this.$event.listen('toggleFullScreen', this.handleChange);
  },
  methods: {
    handleFullscreen() {
      // let main = document.body;
      let main = document.documentElement;
      if (this.value) {
        if (document.exitFullscreen) {
          document.exitFullscreen();
        } else if (document.mozCancelFullScreen) {
          document.mozCancelFullScreen();
        } else if (document.webkitCancelFullScreen) {
          document.webkitCancelFullScreen();
        } else if (document.msExitFullscreen) {
          document.msExitFullscreen();
        }
      } else {
        if (main.requestFullscreen) {
          main.requestFullscreen();
        } else if (main.mozRequestFullScreen) {
          main.mozRequestFullScreen();
        } else if (main.webkitRequestFullScreen) {
          main.webkitRequestFullScreen();
        } else if (main.msRequestFullscreen) {
          main.msRequestFullscreen();
        }
      }
    },
    handleChange() {
      this.handleFullscreen();
    },
  },
  mounted() {
    let isFullscreen =
      document.fullscreenElement ||
      document.mozFullScreenElement ||
      document.webkitFullscreenElement ||
      document.fullScreen ||
      document.mozFullScreen ||
      document.webkitIsFullScreen;
    isFullscreen = !!isFullscreen;
    document.addEventListener('fullscreenchange', () => {
      this.$emit('input', !this.value);
      this.$emit('on-change', !this.value);
    });
    document.addEventListener('mozfullscreenchange', () => {
      this.$emit('input', !this.value);
      this.$emit('on-change', !this.value);
    });
    document.addEventListener('webkitfullscreenchange', () => {
      this.$emit('input', !this.value);
      this.$emit('on-change', !this.value);
    });
    document.addEventListener('msfullscreenchange', () => {
      this.$emit('input', !this.value);
      this.$emit('on-change', !this.value);
    });
    this.$emit('input', isFullscreen);
  },
};
</script>
