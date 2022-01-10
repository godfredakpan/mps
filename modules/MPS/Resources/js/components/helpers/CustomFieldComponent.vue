<template>
  <div>
    <FormItem :label="attr.name" v-if="attr.type == 'text'" :prop="prop" :rules="rules">
      <Input v-model="lv" />
    </FormItem>
    <FormItem :label="attr.name" v-else-if="attr.type == 'textarea'" :prop="prop" :rules="rules">
      <Input type="textarea" v-model="lv" />
    </FormItem>
    <FormItem :label="attr.name" v-else-if="attr.type == 'number'" :prop="prop" :rules="rules">
      <InputNumber v-model="lv"></InputNumber>
    </FormItem>
    <FormItem :label="attr.name" v-else-if="attr.type == 'select'" :prop="prop" :rules="rules">
      <Select v-model="lv" placeholder>
        <Option v-for="(item, index) in getOptions(attr.options)" :value="item" :key="index + item">{{ item }}</Option>
      </Select>
    </FormItem>
    <FormItem :label="attr.name" v-else-if="attr.type == 'checkbox'" :prop="prop" :rules="rules">
      <CheckboxGroup v-model="lv">
        <Checkbox v-for="(item, index) in getOptions(attr.options)" :key="index + item" :label="item"></Checkbox>
      </CheckboxGroup>
    </FormItem>
    <FormItem :label="attr.name" v-else-if="attr.type == 'radio'" :prop="prop" :rules="rules">
      <RadioGroup v-model="lv">
        <Radio v-for="(item, index) in getOptions(attr.options)" :key="index + item" :label="item"></Radio>
      </RadioGroup>
    </FormItem>
    <FormItem :label="attr.name" v-else-if="attr.type == 'date'" :prop="prop" :rules="rules">
      <DatePicker type="date" placeholder v-model="lv" style="width: 100%;"></DatePicker>
    </FormItem>
    <FormItem :label="attr.name" v-else-if="attr.type == 'datetime'" :prop="prop" :rules="rules">
      <DatePicker type="datetime" placeholder v-model="lv" style="width: 100%;"></DatePicker>
    </FormItem>
  </div>
</template>

<script>
export default {
  props: {
    prop: { required: false },
    rules: { required: false },
    value: { required: false },
    attr: { type: Object, required: true },
  },
  data() {
    return { lv: this.attr.type == 'number' ? null : this.value };
  },
  watch: {
    lv: function(v) {
      this.$emit('input', v);
      this.$emit('update', v);
    },
  },
  mounted() {
    // this.$nextTick(() => {
    setTimeout(() => {
      this.lv = this.attr.type == 'number' ? (this.value ? parseFloat(this.value) : null) : this.value;
    }, 500);
    // });
  },
  methods: {
    // update(str) {
    //   this.$emit('input', str);
    //   this.$emit('update', str);
    // },
    getOptions(str) {
      return str.split('|');
    },
  },
};
</script>
