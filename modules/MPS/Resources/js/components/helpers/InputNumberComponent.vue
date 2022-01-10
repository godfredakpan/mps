<template>
  <div class="input-number text-center">
    <div class="input-button first" @click="minus()">
      <Icon type="md-remove" size="16" />
    </div>
    <div class="field-container">
      <input :step="step" type="number" v-model="newValue" style="width: 100%;" @input="$emit('input', newValue)" />
      <!-- @change="$emit('on-change', newValue)" -->
    </div>
    <div class="input-button last" @click="plus()">
      <Icon type="md-add" size="16" />
    </div>
  </div>
</template>

<script>
export default {
  props: {
    value: {
      type: [Number, String],
      default: 0,
    },
    step: {
      default: 0.1,
    },
    min: {
      default: 0,
      type: Number,
    },
    max: {
      type: Number,
    },
  },
  data() {
    return {
      newValue: 0,
    };
  },
  methods: {
    plus() {
      if (this.max === undefined || this.newValue < this.max) {
        this.newValue = parseFloat(parseFloat(parseFloat(this.newValue) + parseFloat(this.step)).toFixed(2));
        this.$emit('input', this.newValue);
        // this.$emit('on-change', this.newValue);
      }
    },
    minus() {
      if (this.newValue > this.min) {
        this.newValue = parseFloat(parseFloat(parseFloat(this.newValue) - parseFloat(this.step)).toFixed(2));
        this.$emit('input', this.newValue);
        // this.$emit('on-change', this.newValue);
      }
    },
  },
  watch: {
    value: {
      handler: function (newVal, oldVal) {
        this.newValue = newVal;
      },
    },
  },
  created: function () {
    this.newValue = parseFloat(this.value);
  },
};
</script>
<style lang="scss" scoped>
.input-number {
  width: 50%;
  display: flex;
  max-width: 300px;
  margin: 0 auto;
  user-select: none;
  border-radius: 4px;
  align-items: center;
  flex-direction: row;
  background-color: #fff;
  border: 1px solid #dcdee2;
  justify-content: space-between;

  .field-container {
    flex: 1;
    input {
      width: 100px;
      border: none;
      padding: 3px;
      font-size: 15px;
      text-align: center;
      &:focus {
        outline: none;
      }
    }
  }
  .input-button {
    width: 50px;
    cursor: pointer;
    padding: 4px 10px;
    background-color: #f5f5f5;
    &.first {
      border-radius: 3px 0 0 3px;
      border-right: 1px solid #dcdee2;
    }
    &.last {
      border-radius: 0 3px 3px 0;
      border-left: 1px solid #dcdee2;
    }
    &:hover,
    &:active {
      background-color: #ddd;
    }
  }
}
</style>
