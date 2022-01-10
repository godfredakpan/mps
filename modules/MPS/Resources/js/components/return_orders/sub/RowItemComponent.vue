<template>
  <div class="order-item">
    <span class="item">
      <span class="index">{{ index + 1 }}</span>
      <span class="remove">
        <!-- TODO make optional -->
        <span v-if="row.selected.variations && row.selected.portions && !row.selected.portions.length && !row.selected.variations.length">
          <Icon size="18" type="md-close" class="pointer" color="#ed4014" @click="deleteItem(row.guid)" />
        </span>
      </span>
      <span class="details pointer" @click="editItem(index)">
        <strong>{{ row.name }}</strong>
        <br />
        <span>
          <span
            v-if="customer && customer.customer_group"
            v-html="
              customer.customer_group.name + ' (<small>' + $t('discount') + ': ' + customer.customer_group.discount + '%</small>)<br />'
            "
          ></span>
          <span v-if="row.comment">{{ row.comment }} </span>
        </span>
      </span>
      <template v-if="row.selected.variations && row.selected.portions && !row.selected.portions.length && !row.selected.variations.length">
        <span class="price">
          {{ formatNumber(row[field] - row.discount_amount + calcItemTax(row)) }}
        </span>
        <span class="discount"></span>
        <span class="quantity">
          <InputNumber
            v-model="row.quantity"
            :readonly="!!row.selected.variations.length || !!row.selected.portions.length"
            :size="!!row.selected.variations.length || !!row.selected.portions.length ? 'small' : 'default'"
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
        <span class="discount"></span>
        <span class="quantity">
          <InputNumber
            v-model="row.quantity"
            :readonly="!!row.selected.variations.length || !!row.selected.portions.length"
            :size="!!row.selected.variations.length || !!row.selected.portions.length ? 'small' : 'default'"
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
          {{ formatNumber(sv[field]) }}
        </span>
        <span class="discount">
          {{ formatNumber(sv.discount_amount) }}
        </span>
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
          {{ formatNumber((sv[field] - sv.discount_amount + sv.total_tax_amount) * sv.quantity) }}
        </span>
      </div>
    </template>
    <template v-if="row.selected.portions.length">
      <div class="combo" v-for="(sp, spi) in row.selected.portions" :key="'spi_' + spi">
        <div class="combo" v-for="p in row.portions.filter(ip => ip.id == sp.id)" :key="'pi_' + p.id">
          <div class="combo-item variation">
            <span class="index"></span>
            <span class="remove">
              <Icon size="18" class="pointer" color="#ed4014" type="md-close" @click="deleteItemPortion(row, sp, spi)" />
            </span>
            <span class="details leading-medium">
              <p>
                {{ spi + 1 }}. {{ $tc('portion') }}:
                <strong>{{ p.name == 'regular' ? $t('regular') : p.name }}</strong>
              </p>
            </span>
            <span class="price">{{ formatNumber(sp[field]) }}</span>
            <span class="discount">
              {{ formatNumber(sp.discount_amount) }}
            </span>
            <span class="quantity">
              <InputNumber v-model="sp.quantity" @on-change="itemPortionQuantityChanged(form.items[index])"></InputNumber>
            </span>
            <span class="taxes">
              <div v-for="tax in sp.taxes" :key="'sp_' + tax.id">
                {{ tax.code }}
                <span class="float-right">
                  {{ formatNumber(tax.amount * sp.quantity) }}
                </span>
              </div>
            </span>
            <span class="subtotal">
              {{ formatNumber((sp[field] - sp.discount_amount + sp.total_tax_amount) * sp.quantity) }}
            </span>
          </div>
          <div :key="'ei_' + ei" class="combo-item bt0" style="padding-top: 1px;" v-for="(e, ei) in p.essentials">
            <span class="index"></span>
            <span class="remove"></span>
            <span class="details"> #{{ ei + 1 }} {{ e.item.name }} </span>
            <span class="price"></span>
            <span class="discount"></span>
            <span class="quantity input">
              {{ parseFloat(e.quantity * sp.quantity) }}
            </span>
            <span class="taxes"></span>
            <span class="subtotal"></span>
          </div>
          <template v-for="(g, gi) in p.choosables">
            <div
              class="combo-item bt0"
              style="padding-top: 1px;"
              :key="'gii_' + gii + '_' + gi"
              v-for="(gitem, gii) in g.items"
              v-if="getPortionChoosable(sp, g.id, gitem.item_id)"
            >
              <span class="index"></span>
              <span class="remove"></span>
              <span class="details">
                #{{ gi + 1 + p.essentials.length }}
                {{ gitem.item.name }}
              </span>
              <span class="price"></span>
              <span class="discount"></span>
              <span class="quantity input">
                {{ parseFloat(gitem.quantity * sp.quantity) }}
              </span>
              <span class="taxes"></span>
              <span class="subtotal"></span>
            </div>
          </template>
        </div>
      </div>
    </template>
    <template v-if="row.selected.modifiers.length">
      <div class="combo-item bg">
        <span class="index"></span>
        <span class="remove">&nbsp;</span>
        <span class="details">
          <p style="font-weight: bold;">{{ $tc('modifier', 2) }}</p>
        </span>
        <span class="price">&nbsp;</span>
        <span class="discount">&nbsp;</span>
        <span class="quantity">&nbsp;</span>
        <span class="taxes">&nbsp;</span>
        <span class="subtotal">&nbsp;</span>
      </div>
      <div :key="'mi_' + mi" v-for="(m, mi) in row.selected.modifiers">
        <div class="combo-item pt pb">
          <span class="index"></span>
          <span class="remove">
            <Icon size="18" class="pointer" color="#ed4014" type="md-close" @click="deleteItemModifier(row, m, mi)" />
          </span>
          <span class="details leading-medium">
            {{ mi + 1 }}. <strong>{{ m.option }}</strong> ({{ m.title }})
          </span>
          <span class="price">
            {{ formatNumber(m[field]) }}
          </span>
          <span class="discount">
            {{ formatNumber(m.discount_amount * m.quantity) }}
          </span>
          <span class="quantity">
            <InputNumber v-model="m.quantity"></InputNumber>
          </span>
          <span class="taxes">
            <div v-for="tax in m.taxes" :key="'m_' + tax.id">
              {{ tax.code }}
              <span class="float-right">
                {{ formatNumber(tax.amount * m.quantity) }}
              </span>
            </div>
          </span>
          <span class="subtotal">
            <!-- {{
              formatNumber(
                (m[field] - m.discount_amount + m.total_tax_amount) * m.quantity
              )
            }} -->
          </span>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
export default {
  props: {
    field: { type: String },
    editItem: { type: Function },
    deleteItem: { type: Function },
    calcItemTax: { type: Function },
    calcRowTotal: { type: Function },
    customer: { type: [Boolean, Object] },
    row: { type: Object, required: true },
    deleteItemVariation: { type: Function },
    index: { type: Number, required: true },
    itemVariationQuantityChanged: { type: Function },
  },
};
</script>
