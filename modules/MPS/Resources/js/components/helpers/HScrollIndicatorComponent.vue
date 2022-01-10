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
    selector: {
      type: String,
      required: true,
    },
  },

  data() {
    return {
      width: '',
      elWidth: '',
      scrolled: '',
    };
  },

  created() {
    this.$nextTick(() => {
      document.querySelector(this.selector).addEventListener('load', this.scrollHandler);
      document.querySelector(this.selector).addEventListener('scroll', this.scrollHandler);
    });
  },

  destroyed() {
    if (document.querySelector(this.selector)) {
      document.querySelector(this.selector).removeEventListener('load', this.scrollHandler);
      document.querySelector(this.selector).removeEventListener('scroll', this.scrollHandler);
    }
  },

  methods: {
    scrollHandler() {
      this.scrolled = document.querySelector(this.selector).scrollLeft;
      this.elWidth = document.querySelector(this.selector).scrollWidth - document.querySelector(this.selector).clientWidth;
      this.width = (this.scrolled / this.elWidth) * 100 + '%';
    },
  },
};
</script>

<style scoped>
.scroll--indicator-wrapper {
  width: 100%;
  /*position: absolute;
    top: 0;
    left: 0;
    right: 0;
    z-index: 9;*/
}
.scroll--indicator {
  width: 0;
  height: 100%;
}
</style>
