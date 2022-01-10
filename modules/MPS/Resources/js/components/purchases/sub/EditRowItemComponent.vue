<template>
  <div>
    <FormItem :label="$t('quantity')" prop="quantity">
      <InputNumber v-model="edit_item.quantity" size="large"></InputNumber>
    </FormItem>
    <div class="mt16 mb16" v-if="edit_item.has_serials == 1">
      <item-serial-component :serials="edit_item.selected.serials" :quantity="edit_item.quantity"></item-serial-component>
    </div>
    <Row :gutter="16">
      <Col :sm="24" :md="12" :lg="12">
        <FormItem :label="$tc('unit')" v-if="edit_item.allUnits && edit_item.allUnits.length > 0">
          <Select v-model="edit_item.unit_id" style="width: 100%;" @on-change="itemUnitChanged">
            <Option :value="option.id" :key="'unit_' + index + '_' + option.id" v-for="(option, index) in edit_item.allUnits">
              {{ option.name }}
            </Option>
          </Select>
        </FormItem>
      </Col>
      <Col :sm="24" :md="12" :lg="12">
        <FormItem :label="$t('cost')" prop="cost">
          <InputNumber v-model="edit_item.cost"></InputNumber>
        </FormItem>
      </Col>
      <Col :sm="24" :md="12" :lg="12">
        <FormItem :label="$t('discount_')" prop="cdiscount">
          <InputNumber v-model="edit_item.discount"></InputNumber>
        </FormItem>
      </Col>
      <Col :sm="24" :md="12" :lg="12">
        <FormItem :label="$t('batch_no')" prop="batch_no">
          <Input v-model="edit_item.batch_no" />
        </FormItem>
      </Col>
      <Col :sm="24" :md="12" :lg="12">
        <FormItem v-if="edit_item.expiry" :label="$t('expiry_date')" prop="expiry_date">
          <DatePicker type="date" format="yyyy-MM-dd" style="width: 100%" :options="expiryDateOptions" v-model="edit_item.expiry_date" />
        </FormItem>
      </Col>
    </Row>
    <FormItem :label="$tc('tax', 2)" prop="taxes">
      <Select v-model="edit_item.allTaxes" multiple style="width: 100%;">
        <Option v-for="option in this.taxes" :value="option.id" :key="'tax_' + option.id">
          {{ option.name }}
        </Option>
      </Select>
    </FormItem>
    <FormItem :label="$t('comment')">
      <Input type="textarea" v-model="edit_item.comment" :placeholder="$t('item_comment')" :autosize="{ minRows: 2, maxRows: 5 }"></Input>
    </FormItem>
    <FormItem>
      <Button long type="primary" @click="updateItem()">{{ $t('update') }}</Button>
    </FormItem>
    <Button long type="error" @click="deleteItem(edit_item)">{{ $t('remove_from_order') }}</Button>
  </div>
</template>

<script>
import ItemSerialComponent from './ItemSerialComponent';
export default {
  props: {
    taxes: { type: Array },
    // itemPrice: { type: Function },
    deleteItem: { type: Function },
    updateItem: { type: Function },
    itemUnitChanged: { type: Function },
    // itemDiscount: { type: Function },
    // addPortionToEditItem: { type: Function },
    // changeSelectedPortion: { type: Function },
    edit_item: { type: Object, required: true },
    expiryDateOptions: { type: Object, required: true },
  },
  components: { ItemSerialComponent },
};
</script>
