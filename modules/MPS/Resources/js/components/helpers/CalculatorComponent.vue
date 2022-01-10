<template>
  <div class="calculator" @click="setFocus" @keyup="setFocus">
    <div class="calculator-display">
      <div class="calculator-formula" v-cloak>{{ formula }}</div>
      <input class="calculator-formula-input" size="small" ref="input" :autofocus="true" @keyup="keymonitor" />
      <div class="calculator-result">{{ result }}</div>
    </div>

    <div class="calculator-items">
      <div class="calculator-row">
        <Button type="warning" class="calculator-btn calculator-btn-big" @click="drop()">←</Button>
        <!-- <Button type="warning" class="calculator-btn" @click="cleanResult()">CE</Button> -->
        <Button type="error" class="calculator-btn" @click="cleanAll()">C</Button>
        <Button class="calculator-btn" @click="toggle()">±</Button>
        <Button class="calculator-btn" @click="square()">√</Button>
      </div>

      <div class="calculator-row">
        <Button class="calculator-btn" @click="operate(7)">7</Button>
        <Button class="calculator-btn" @click="operate(8)">8</Button>
        <Button class="calculator-btn" @click="operate(9)">9</Button>
        <Button class="calculator-btn" @click="operate('/')">/</Button>
        <Button class="calculator-btn" @click="percent()">%</Button>
      </div>

      <div class="calculator-row">
        <Button class="calculator-btn" @click="operate(4)">4</Button>
        <Button class="calculator-btn" @click="operate(5)">5</Button>
        <Button class="calculator-btn" @click="operate(6)">6</Button>
        <Button class="calculator-btn" @click="operate('*')">*</Button>
        <Button class="calculator-btn" @click="devided()">1/x</Button>
      </div>

      <div class="calculator-row">
        <Button class="calculator-btn" @click="operate(1)">1</Button>
        <Button class="calculator-btn" @click="operate(2)">2</Button>
        <Button class="calculator-btn" @click="operate(3)">3</Button>
        <Button class="calculator-btn" @click="operate('-')">-</Button>
        <Button class="calculator-btn" @click="operate('+')">+</Button>
      </div>

      <div class="calculator-row">
        <Button class="calculator-btn calculator-btn-big" @click="operate(0)">0</Button>
        <Button class="calculator-btn" @click="operate('.')">.</Button>
        <Button type="success" class="calculator-btn calculator-btn-big" @click="equal()">=</Button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      result: 0,
      formula: '',
    };
  },
  mounted() {
    this.setFocus();
  },
  methods: {
    keymonitor(event) {
      let key = this.getKey(event);
      if (!isNaN(key) || key == '*' || key == '+' || key == '-' || key == '=' || key == '/' || key == 'Shift') {
        this.formula = this.$refs.input.value;
      } else if (key == 'Backspace') {
        this.drop();
      } else {
        this.$refs.input.value = this.formula;
        // this.formula.slice(0, -1)
      }
      if (key == 'Enter') {
        this.equal();
      }
    },

    getKey(e) {
      if (e.key) return e.key;
      let keyFromCode = String.fromCharCode(e.keyCode);
      if (keyFromCode) return keyFromCode;
      if (e.keyCode === 13) return 'Enter';
      if (e.keyCode === 16) return 'Shift';
    },

    setFocus() {
      this.$refs.input.value = this.formula;
      this.$refs.input.focus();
    },

    operate(element) {
      this.formula += element;
      this.setFocus();
    },

    equal() {
      this.result = eval(this.formula);
      this.setFocus();
    },

    cleanResult() {
      this.result = 0;
      this.setFocus();
    },

    cleanAll() {
      this.formula = '';
      this.result = 0;
      this.$refs.input.value = '';
      this.setFocus();
    },

    drop() {
      this.formula = this.formula.slice(0, -1);
      this.setFocus();
    },

    percent() {
      this.result = Math.round(eval(this.formula) / 100);
      this.setFocus();
    },

    square() {
      eval(this.formula) < 0 ? (this.formula = 'Can not square the negative value') : (this.result = Math.sqrt(eval(this.formula)));
      this.setFocus();
    },

    devided() {
      this.formula === '' || this.formula.endsWith('+' || '-' || '*' || '/' || '%') ? {} : (this.formula = '1/(' + this.formula + ')');
      this.equal();
    },

    toggle() {
      this.formula === '' || this.formula.endsWith('+' || '-' || '*' || '/' || '%')
        ? {}
        : this.formula.startsWith('-')
        ? (this.formula = Math.abs(eval(this.formula)).toString())
        : (this.formula = '-(' + this.formula + ')');
      this.equal();
    },
  },
};
</script>

<style lang="scss" scoped>
@import 'resources/sass/variables.sass';

.calculator .calculator-display {
  border: 1px solid $border;
  background-color: $background;
  margin: 10px;
  height: 70px;
  border-radius: $radius;
}
.calculator .calculator-formula {
  width: 100%;
  height: 40%;
  text-align: right;
  padding: 5px 10px;
  color: $sub-color;
  text-overflow: ellipsis;
  white-space: nowrap;
  overflow: hidden;
  font-size: 12px;
  font-family: 'Lucida Console', Monaco, monospace;
}
.calculator-formula-input {
  color: transparent;
  border: 0;
  background: transparent;
  position: absolute;
  height: 0;
  &:focus {
    box-shadow: none;
    outline: none;
  }
}
.calculator .calculator-result {
  width: 100%;
  height: 60%;
  text-align: right;
  box-sizing: border-box;
  padding: 5px 10px;
  color: $content;
  text-overflow: ellipsis;
  white-space: nowrap;
  overflow: hidden;
  font-size: 1.5rem;
}
.calculator .calculator-items {
  margin: 0 -10px 0 10px;
  text-align: left;
  height: auto;
  font-size: 16px;
}
.calculator .calculator-items .calculator-row {
  width: 100%;
}
.calculator-btn {
  margin: 0;
  padding: 0;
  width: calc(20% - 8px);
  height: 3rem;
  display: inline-block;
  text-align: center;
  line-height: 3rem;
  margin-bottom: 8px;
  font-size: 14px;
}
.calculator-btn-big {
  width: calc(40% - 12px);
}
</style>
