<template>
  <div class="serials form-responsive">
    <CellGroup>
      <Cell v-for="(serial, index) in serials" :key="'serial_' + index">
        <Row :gutter="16">
          <Col :sm="24" :md="9" :lg="9">
            <FormItem :label="$tc('serial_number')" prop="serial" class="mb0">
              <Input v-model="serial.number" @on-focus="addNew(index)" />
            </FormItem>
          </Col>
          <Col :sm="24" :md="9" :lg="9">
            <FormItem :label="$t('till')" prop="serial" class="mb0">
              <Input v-model="serial.till" />
            </FormItem>
          </Col>
          <Col :sm="24" :md="6" :lg="6">
            <span v-if="serial.number || (serial.till && serial.number < serial.till)">
              <p style="line-height: 34px;">
                <strong>{{ countSerials(serial.number, serial.till) }}</strong>
                {{ $tc('serial_number', countSerials(serial.number, serial.till)) }}
              </p>
            </span>
          </Col>
        </Row>
      </Cell>
      <Cell>
        <p>
          {{
            $t('total_serials', {
              n: provided,
              serials: $tc('serial_number', provided),
              total: this.hasVariants ? this.totalVariationsQty : this.totalStockQty,
            })
          }}
        </p>
      </Cell>
    </CellGroup>
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
    hasVariants: {
      required: true,
    },
    totalVariationsQty: {
      type: Number,
      required: true,
    },
    totalStockQty: {
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
      if (i == this.serials.length - 1) {
        this.serials.push({ number: '', till: '' });
      }
    },
  },
};
</script>

<style lang="scss">
.serials .ivu-cell-main {
  display: block !important;
  @media (max-width: 480px) {
    display: inline-block !important;
  }
}
</style>
