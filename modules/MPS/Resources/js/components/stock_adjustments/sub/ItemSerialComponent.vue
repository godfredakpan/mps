<template>
  <div>
    <div v-for="(serial, index) in serials" :key="'serial_' + index" class="mb16">
      <Row :gutter="16">
        <Col :sm="24" :md="12" :lg="12">
          <FormItem :label="$tc('serial_number')" prop="serial" class="mb0">
            <Input v-model="serial.number" @on-focus="addNew(index)" />
          </FormItem>
        </Col>
        <Col :sm="24" :md="12" :lg="12">
          <FormItem :label="$t('till')" prop="serial" class="mb0">
            <Input v-model="serial.till" />
          </FormItem>
        </Col>
      </Row>
    </div>
    <div class="sr-info" :class="provided > quantity ? 'sr-error' : ''">
      {{
        $t('total_serials', {
          n: provided,
          serials: $tc('serial_number', provided),
          total: quantity,
        })
      }}
    </div>
  </div>
</template>

<script>
export default {
  props: {
    serials: {
      type: Array,
      twoWay: true,
      required: true,
    },
    quantity: {
      type: Number,
      required: true,
    },
  },
  computed: {
    provided() {
      return this.serials.reduce((a, s) => a + this.countSerials(s.number, s.till), 0);
    },
  },
  methods: {
    countSerials(n1, n2) {
      if (!n1 && !n2) {
        return 0;
      } else if (n1 && !n2) {
        return 1;
      }
      let n3 = n2 - n1;
      return n3 > 0 ? n3 + 1 : n2 == n1 ? 1 : '?';
    },
    addNew(i) {
      if (this.provided < this.quantity && this.quantity > i + 1 && i == this.serials.length - 1) {
        this.serials.push({ number: '', till: '' });
      }
    },
  },
};
</script>

<style>
.sr-info {
  padding: 8px 16px;
  background: #eee;
  border-radius: 5px;
}
.sr-error {
  color: rgb(175, 0, 0);
  background: rgb(255, 175, 175);
}
</style>
