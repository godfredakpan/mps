<template>
  <div id="touch-keyboard" class="touch-keyboard">
    <div class="touch-keyboard-inner">
      <Input :value="input_value" readonly size="large" placeholder="" />
      <div :class="keyboardClass"></div>
    </div>
  </div>
</template>

<script>
import Keyboard from 'simple-keyboard';
import 'simple-keyboard/build/css/index.css';

export default {
  name: 'SimpleKeyboard',
  props: {
    keyboardClass: {
      default: 'simple-keyboard',
      type: String,
    },
    element: {
      type: HTMLElement,
    },
    input: {
      type: String,
    },
  },
  data: () => ({
    keyboard: null,
    input_value: null,
  }),
  mounted() {
    this.keyboard = new Keyboard(this.keyboardClass, {
      onChange: this.onChange,
      onKeyPress: this.onKeyPress,
      // autoUseTouchEvents: true,
      // syncInstanceInputs: true,
      // stopMouseUpPropagation: true,
      // stopMouseDownPropagation: true,
      theme: 'hg-theme-default hg-layout-default',
      layout: {
        default: [
          '` 1 2 3 4 5 6 7 8 9 0 - = {bksp}',
          '{tab} q w e r t y u i o p [ ] \\',
          "{lock} a s d f g h j k l ; ' {enter}",
          '{shift} z x c v b n m , . / {shift}',
          '@ % & {space} {accept} {close}',
        ],
        shift: [
          '~ ! @ # $ % ^ & * ( ) _ + {bksp}',
          '{tab} Q W E R T Y U I O P { } |',
          '{lock} A S D F G H J K L : " {enter}',
          '{shift} Z X C V B N M < > ? {shift}',
          '@ % & {space} {accept} {close}',
        ],
      },
      mergeDisplay: true,
      display: {
        '{enter}': '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;↵&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
        '{shift}': '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;⇧&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
        '{tab}': '&nbsp;&nbsp;&nbsp;⇥&nbsp;&nbsp;&nbsp;',
        '{lock}': '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;⇪&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
        '{space}': '└──────┘',
        // '{space}': '⎵',
        '{close}': '✕',
        '{accept}': this.$t('accept_x', { x: '' }),
        // '{tab}': 'tab ⇥',
        '{bksp}': '&nbsp;&nbsp;&nbsp;&nbsp;⌫&nbsp;&nbsp;&nbsp;&nbsp;',
        '{backspace}': '&nbsp;&nbsp;&nbsp;&nbsp;⌫&nbsp;&nbsp;&nbsp;&nbsp;',
        // '{backspace}': 'backspace ⌫',
        // '{enter}': 'enter ↵',
        // '{escape}': 'esc ⎋',
        // '{capslock}': 'caps lock ⇪',
        // '{shiftleft}': 'shift ⇧',
        // '{shiftright}': 'shift ⇧',
        // '{controlleft}': 'ctrl ⌃',
        // '{controlright}': 'ctrl ⌃',
        // '{altleft}': 'alt ⌥',
        // '{altright}': 'alt ⌥',
        // '{metaleft}': 'cmd ⌘',
        // '{metaright}': 'cmd ⌘',
      },
      buttonTheme: [
        {
          class: 'hg-extra',
          buttons: '@ % &',
        },
        {
          class: 'hg-highlight',
          buttons: '{bksp} {backspace} {tab} {shift}',
        },
        {
          class: 'hg-highlight2',
          buttons: '{lock} {enter}',
        },
      ],
    });
    this.input_value = this.input;
  },
  methods: {
    onChange(input) {
      this.input_value = input;
      this.$emit('onChange', input);
    },
    onKeyPress(button) {
      this.$emit('onKeyPress', button);
      if (button === '{close}') this.handleClose();
      if (button === '{accept}') this.handleAccept();
      if (button === '{shift}' || button === '{lock}') this.handleShift();
    },
    handleAccept() {
      this.$emit('onAccept');
    },
    handleClose() {
      this.$emit('onClose');
    },
    handleShift() {
      let currentLayout = this.keyboard.options.layoutName;
      let shiftToggle = currentLayout === 'default' ? 'shift' : 'default';

      this.keyboard.setOptions({
        layoutName: shiftToggle,
      });
    },
    // handleEnter(event) {
    //   this.$emit('on-enter', event);
    //   if (this.search) this.$emit('on-search', this.currentValue);
    // },
    // handleInput(event) {
    //   if (this.isOnComposition) return;
    //   let value = event.target.value;
    //   if (this.number && value !== '') value = Number.isNaN(Number(value)) ? value : Number(value);
    //   this.$emit('input', value);
    //   this.setCurrentValue(value);
    //   this.$emit('on-change', event);
    // },
    // handleChange(event) {
    //   this.$emit('on-input-change', event);
    // },
    // handleSearch() {
    //   if (this.itemDisabled) return false;
    //   this.$refs.input.focus();
    //   this.$emit('on-search', this.currentValue);
    // },
  },
  watch: {
    input(input) {
      this.input_value = input;
      this.keyboard.setInput(input);
    },
  },
};
</script>

<style>
.touch-keyboard {
  bottom: 0;
  width: 100%;
  z-index: 9999;
  position: fixed;
  font-weight: bold;
  max-width: 998px;
  /* background-color: rgba(0, 0, 0, 0.4); */
}
@media only screen and (min-width: 999px) {
  .touch-keyboard {
    left: 50%;
    margin-left: -499px;
  }
}
@media only screen and (max-width: 998px) {
  /* 768 for tablets */
  .touch-keyboard {
    display: none;
  }
}
.touch-keyboard-inner {
  padding: 6px;
  margin: 0 auto;
  max-width: 998px;
  border-radius: 5px 5px 0 0;
  background-color: #cdcdcd;
  box-shadow: 0 -1px 4px rgba(0, 0, 0, 0.1);
}
.simple-keyboard {
  margin: 0;
  border-radius: 0;
  padding: 6px 0 0 0;
  background-color: #cdcdcd;
}
.hg-theme-default {
  font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans',
    sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji' !important;
}
.hg-theme-default .hg-button {
  border-radius: 4px !important;
}
.simple-keyboard .hg-highlight {
  color: #fff !important;
  background: #515a6e !important;
}
.simple-keyboard .hg-highlight2 {
  color: #fff !important;
  background: #363e4f !important;
}
.simple-keyboard .hg-button-accept {
  max-width: 25%;
  color: #fff !important;
  background: #19be6b !important;
}
.simple-keyboard .hg-button-close {
  max-width: 50px;
  color: #fff !important;
  background: #ff9900 !important;
}
.simple-keyboard .hg-extra {
  width: 7% !important;
  max-width: 75px !important;
  min-width: 20px !important;
}
</style>
