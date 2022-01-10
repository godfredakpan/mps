<template>
  <div class="scroll--indicator-wrapper" :style="{ height: height, background: background }">
    <div class="scroll--indicator" :style="{ background: color, width: width }"></div>
  </div>
</template>

<script>
export default {
  props: {
    height: {
      type: String,
      default: '2px',
      required: false,
    },
    color: {
      type: String,
      required: false,
      default: 'linear-gradient(to right, #ec008c, #fc6767)',
    },
    background: {
      type: String,
      required: false,
      default: '#eaeaea',
    },
  },

  data() {
    return {
      width: '',
      scrolled: '',
      docHeight: '',
    };
  },

  created() {
    window.addEventListener('load', this.scrollHandler);
    window.addEventListener('scroll', this.scrollHandler);
  },

  destroyed() {
    window.removeEventListener('load', this.scrollHandler);
    window.removeEventListener('scroll', this.scrollHandler);
  },

  methods: {
    scrollHandler() {
      this.scrolled = document.body.scrollTop || document.documentElement.scrollTop;
      this.docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
      this.width = (this.scrolled / this.docHeight) * 100 + '%';
    },
  },
};
</script>

<style scoped>
.scroll--indicator-wrapper {
  width: 100%;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 9999;
}
.scroll--indicator {
  width: 0;
  height: 100%;
}
</style>
