<template>
  <div class="order-item">
    <span class="item">
      <span class="index">{{ index + 1 }}</span>
      <span class="remove">
        <!-- TODO make optional -->
        <span v-if="row.selected.variations && !row.selected.variations.length">
          <Icon size="18" type="md-close" class="pointer" color="#ed4014" @click="deleteItem(row)" />
        </span>
      </span>
      <span class="details pointer" @click="editItem(index)">
        <strong>{{ row.name }}</strong>
        <br />
        <span v-if="row.comment">{{ row.comment }} </span>
      </span>
      <template v-if="row.selected.variations && !row.selected.variations.length">
        <span class="price">
          {{ formatNumber(row.cost) }}
          <!-- {{ formatNumber(row.cost - (row.discount_amount || 0) + calcItemTax(row)) }} -->
        </span>
        <!-- <span class="discount"></span> -->
        <span class="quantity">
          <InputNumber
            v-model="row.quantity"
            :readonly="!!row.selected.variations.length"
            :size="!!row.selected.variations.length ? 'small' : 'default'"
          ></InputNumber>
        </span>
        <span class="taxes">
          <div v-for="tax in row.taxes" :key="'tax_' + tax.id">
            {{ tax.code }}
            <span class="float-right">
              {{ formatNumber(tax.amount * row.quantity) }}
            </span>
          </div>
        </span>
        <span class="subtotal">
          {{ formatNumber(calcRowTotal(row)) }}
        </span>
      </template>
      <template v-else>
        <span class="price"></span>
        <!-- <span class="discount"></span> -->
        <span class="quantity">
          <InputNumber
            v-model="row.quantity"
            :readonly="!!row.selected.variations.length"
            :size="!!row.selected.variations.length ? 'small' : 'default'"
          ></InputNumber>
        </span>
        <span class="taxes"></span>
        <span class="subtotal"></span>
      </template>
    </span>
    <template v-if="row.selected.variations && row.selected.variations.length">
      <div class="combo-item variation" :key="'svi_' + svi" v-for="(sv, svi) in row.selected.variations">
        <span class="index"></span>
        <span class="remove">
          <Icon size="18" class="pointer" color="#ed4014" type="md-close" @click="deleteItemVariation(row, sv)" />
        </span>
        <span class="details leading-medium">
          {{ svi + 1 }}.
          <span v-html="metaString(sv.meta)"></span>
        </span>
        <span class="price">
          {{ formatNumber(sv.cost) }}
        </span>
        <!-- <span class="discount">
          {{ formatNumber(sv.discount_amount) }}
        </span> -->
        <span class="quantity">
          <InputNumber v-model="sv.quantity" @on-change="itemVariationQuantityChanged(form.items[index])"></InputNumber>
        </span>
        <span class="taxes">
          <div v-for="tax in sv.taxes" :key="'sv_' + tax.id">
            {{ tax.code }}
            <span class="float-right">
              {{ formatNumber(tax.amount * row.quantity) }}
            </span>
          </div>
        </span>
        <span class="subtotal">
          {{ formatNumber((sv.cost - (sv.discount_amount || 0) + sv.total_tax_amount) * sv.quantity) }}
        </span>
      </div>
    </template>
  </div>
</template>

<script>
export default {
  props: {
    editItem: { type: Function },
    deleteItem: { type: Function },
    calcRowTotal: { type: Function },
    customer: { type: [Boolean, Object] },
    row: { type: Object, required: true },
    deleteItemVariation: { type: Function },
    index: { type: Number, required: true },
    itemVariationQuantityChanged: { type: Function },
  },
};
</script>
