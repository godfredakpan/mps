<template>
  <div>
    <Row :gutter="16">
      <Col :sm="24" :md="24" :lg="24" v-if="!edit_item.portions.length && !edit_item.has_variants">
        <FormItem
          prop="quantity"
          class="text-center"
          :label="$t('quantity')"
          :rules="{
            type: 'number',
            required: true,
            trigger: 'change',
            message: $t('field_is_required', { field: $t('quantity') }),
          }"
        >
          <InputNumber v-model="edit_item.quantity" size="large"></InputNumber>
          <!-- @on-change="editItemQuantityChanged" -->
        </FormItem>
      </Col>
      <template v-if="edit_item.portions && edit_item.portions.length">
        <Col :sm="24" :md="24" :lg="24">
          <span v-for="(eisp, eispi) in edit_item.selected.portions" :key="'eispi_' + eispi">
            <span v-for="portion in edit_item.portions.filter(ip => ip.id == eisp.id)" :key="'pi_' + portion.id">
              <Card dis-hover class="cardp">
                <p slot="title">
                  <Icon type="ios-option"></Icon>
                  {{ eispi + 1 }}. {{ $tc('portion') }}: ({{ portion.name == 'regular' ? $t('regular') : portion.name }})
                </p>
                <Button
                  type="primary"
                  size="small"
                  slot="extra"
                  @click="addPortionToEditItem()"
                  v-if="!eispi && edit_item.portions.length > 1"
                >
                  {{ $t('add_x', { x: $tc('portion') }) }}
                </Button>
                <Row :gutter="16">
                  <Col :sm="24" :md="12" :lg="12" class="mb16">
                    <FormItem :label="$tc('portion')">
                      <Select :value="eisp.id" @on-change="v => changeSelectedPortion(v, eispi)">
                        <Option :value="p.id" :key="'pi_' + pi" v-for="(p, pi) in edit_item.portions">
                          {{ p.name == 'regular' ? $t('regular') : p.name }}
                        </Option>
                      </Select>
                    </FormItem>
                  </Col>
                  <Col :sm="24" :md="12" :lg="12" class="mb16">
                    <FormItem :label="$tc('quantity')">
                      <InputNumber v-model="eisp.quantity"></InputNumber>
                    </FormItem>
                  </Col>
                  <Col :sm="24" :md="24" :lg="24">
                    <template v-if="portion.portion_items && portion.portion_items.length">
                      <Divider dashed orientation="left" style="margin-top: 0;">
                        <small style="color: #aaa;">
                          {{ $tc('item', portion.portion_items.length) }}
                        </small>
                      </Divider>
                      <p style="margin-bottom: 8px; font-weight: bold;">
                        {{ portion.portion_items.map(e => e.item.name).join(', ') }}
                      </p>
                    </template>
                    <template v-if="portion.essentials && portion.essentials.length">
                      <Divider dashed orientation="left" style="margin-top: 0;">
                        <small style="color: #aaa;">
                          {{ $t('essential_items') }}
                        </small>
                      </Divider>
                      <p style="margin-bottom: 8px; font-weight: bold;">
                        {{ portion.essentials.map(e => e.item.name).join(', ') }}
                      </p>
                    </template>
                    <template v-if="portion.choosables && portion.choosables.length">
                      <Divider dashed orientation="left">
                        <small style="color: #aaa;">
                          {{ $t('choosable_items') }}
                        </small>
                      </Divider>
                      <Row :gutter="16">
                        <Col :sm="24" :md="12" :lg="12" :key="'gi_' + gi" v-for="(g, gi) in portion.choosables">
                          <!-- :prop="'selected.portions.' + eispi + '.choosables.' + gi + '.selected'" -->
                          <FormItem
                            :label="g.name"
                            :prop="'selected.portions.' + eispi + '.choosables.' + gi + '.selected'"
                            :rules="{
                              required: true,
                              trigger: 'change',
                              message: $t('select_x', { x: $tc('item') }),
                            }"
                          >
                            <Select v-model="eisp.choosables[gi].selected">
                              <Option :key="'gii_' + gii" :value="option.item_id" v-for="(option, gii) in g.items">
                                {{ option.item.name }}
                              </Option>
                            </Select>
                          </FormItem>
                        </Col>
                      </Row>
                    </template>

                    <template v-if="portion.portion_items && portion.portion_items.length">
                      <div :key="'eiv_' + ei" v-for="(e, ei) in portion.portion_items">
                        <template v-if="e.item.variants && e.item.variants.length">
                          <div class="mb16" :key="'t_' + ei">
                            <span class="bold">{{ e.item.name }} ({{ $tc('variation') }})</span>
                          </div>
                          <Row :gutter="16" :key="'r_' + ei">
                            <Col :xs="24" :sm="12" v-for="(v, i) in e.item.variants" :key="'pcsv_' + i">
                              <FormItem :label="v.name" :key="'pcsvf_' + v.id">
                                <Select v-model="eisp.portion_items[ei].meta[v.name]" @on-change="selectPortionItemVariant(eispi, ei)">
                                  <Option :key="opt" :value="opt" v-for="opt in v.options">{{ opt }}</Option>
                                </Select>
                              </FormItem>
                            </Col>
                          </Row>
                        </template>
                      </div>
                    </template>
                    <template v-if="portion.essentials && portion.essentials.length">
                      <div :key="'eiv_' + ei" v-for="(e, ei) in portion.essentials">
                        <template v-if="e.item.variants && e.item.variants.length">
                          <div class="mb16" :key="'t_' + ei">
                            <span class="bold">{{ e.item.name }} ({{ $tc('variation') }})</span>
                          </div>
                          <Row :gutter="16" :key="'r_' + ei">
                            <Col :xs="24" :sm="12" v-for="(v, i) in e.item.variants" :key="'pcsv_' + i">
                              <FormItem :label="v.name" :key="'pcsvf_' + v.id">
                                <Select v-model="eisp.essentials[ei].meta[v.name]" @on-change="selectEssentialVariant(eispi, ei)">
                                  <Option :key="opt" :value="opt" v-for="opt in v.options">{{ opt }}</Option>
                                </Select>
                              </FormItem>
                            </Col>
                          </Row>
                        </template>
                      </div>
                    </template>
                    <template v-if="portion.choosables && portion.choosables.length">
                      <div :key="'giv_' + gi" v-for="(g, gi) in portion.choosables">
                        <template v-for="option in g.items">
                          <template
                            v-if="
                              eisp.choosables[gi].selected &&
                                eisp.choosables[gi].selected == option.item.id &&
                                option.item.variants &&
                                option.item.variants.length
                            "
                          >
                            <Row :gutter="16" :key="'r_' + gi">
                              <Col :lg="24" class="mb16" :key="'t_' + gi">
                                <span class="bold">{{ g.name }}</span
                                >: {{ option.item.name }} ({{ $tc('variation') }})
                              </Col>
                              <Col :xs="24" :sm="12" v-for="(v, i) in option.item.variants" :key="'pcsv_' + i">
                                <FormItem :label="v.name" :key="'pcsvf_' + v.id">
                                  <Select v-model="eisp.choosables[gi].meta[v.name]" @on-change="selectChoosableVariant(eispi, gi)">
                                    <Option :key="opt" :value="opt" v-for="opt in v.options">{{ opt }}</Option>
                                  </Select>
                                </FormItem>
                              </Col>
                            </Row>
                          </template>
                        </template>
                      </div>
                    </template>
                  </Col>
                </Row>
              </Card>
            </span>
          </span>
        </Col>
        <Col :sm="24" :md="24" :lg="24"></Col>
      </template>
      <template v-if="edit_item.variants && edit_item.variants.length">
        <span v-for="(eisv, eisvi) in edit_item.selected.variations" :key="'sv_meta_' + eisvi">
          <Divider dashed orientation="left">
            <span style="color: #515a6e;" v-html="metaString(eisv.meta)"></span>
          </Divider>
          <Col :sm="24" :md="12" :lg="12">
            <FormItem
              :label="$tc('quantity')"
              :prop="'selected.variations.' + eisvi + '.quantity'"
              :rules="{
                required: true,
                type: 'number',
                trigger: 'change',
                max: parseFloat(eisv.available),
                message: $t('only_x_quantity_available', { x: parseFloat(eisv.available) }),
              }"
            >
              <InputNumber v-model="eisv.quantity"></InputNumber>
            </FormItem>
          </Col>
          <Col :sm="24" :md="12" :lg="12" style="padding-top: 2.5em;" class="text-primary">
            {{ $t('available_x', { x: $t('quantity') }) }}:
            <strong>{{ eisv.available }}</strong>
          </Col>
        </span>
        <!-- <Col :sm="24" :md="12" :lg="12" v-if="edit_item.variants.length % 2 == 1"></Col> -->
        <Col :sm="24" :md="24" :lg="24"></Col>
      </template>
      <template v-if="edit_item.modifiers && edit_item.modifiers.length">
        <Col :sm="24" :md="24" :lg="24">
          <Divider dashed orientation="left">
            <small style="color: #aaa;">
              {{ $tc('modifier', 2) }}
            </small>
          </Divider>
        </Col>
        <Col :sm="24" :md="12" :lg="12" v-for="(m, mi) in edit_item.modifiers" :key="'mi_' + mi">
          <FormItem :label="m.title" prop="modifiers">
            <span v-if="m.show_as == 'radio'">
              <RadioGroup vertical v-model="m.selected">
                <Radio :key="opt.id" :label="opt.id" v-for="opt in m.options">{{ modOptLabel(opt) }}</Radio>
              </RadioGroup>
            </span>
            <span v-else-if="m.show_as == 'checkbox'">
              <CheckboxGroup vertical v-model="m.selected">
                <Checkbox :key="opt.id" :label="opt.id" v-for="opt in m.options">{{ modOptLabel(opt) }}</Checkbox>
              </CheckboxGroup>
            </span>
            <span v-else-if="m.show_as == 'select'">
              <Select v-model="m.selected">
                <Option :key="opt.id" :value="opt.id" v-for="opt in m.options">{{ modOptLabel(opt) }}</Option>
              </Select>
            </span>
            <span v-else-if="m.show_as == 'select_multiple'">
              <Select v-model="m.selected" multiple>
                <Option :key="opt.id" :value="opt.id" v-for="opt in m.options">{{ modOptLabel(opt) }}</Option>
              </Select>
            </span>
          </FormItem>
        </Col>
        <Col :sm="24" :md="24" :lg="24"></Col>
        <!-- <Col :sm="24" :md="12" :lg="12" v-if="edit_item.modifiers.length % 2 == 1"></Col> -->
      </template>
      <Divider dashed orientation="left" style="margin-top: 0;">
        <small style="color: #aaa;">
          {{ $t('general') }}
        </small>
      </Divider>
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
        <FormItem
          prop="price"
          :label="$t('price')"
          :rules="[
            {
              required: true,
              type: 'number',
              message: $t('field_is_required', { field: $t('price') }),
              trigger: ['change', 'blur'],
            },
            { validator: itemPrice, trigger: ['change', 'blur'] },
          ]"
          v-if="edit_item.changeable"
        >
          <InputNumber v-model="edit_item.price"></InputNumber>
        </FormItem>
      </Col>
      <Col :sm="24" :md="12" :lg="12">
        <FormItem prop="discount" :label="$t('discount_')" :rules="{ validator: itemDiscount, trigger: ['change', 'blur'] }">
          <InputNumber v-model="edit_item.discount"></InputNumber>
        </FormItem>
      </Col>
      <Col :sm="24" :md="12" :lg="12">
        <FormItem :label="$tc('promo', 2)" v-if="edit_item.allPromotions && edit_item.allPromotions.length > 0">
          <Select v-model="edit_item.promotions" multiple style="width: 100%;">
            <Option :value="option.id" :key="'tax_' + index + '_' + option.id" v-for="(option, index) in edit_item.allPromotions">
              {{ option.name }}
            </Option>
          </Select>
        </FormItem>
      </Col>
    </Row>
    <FormItem :label="$tc('tax', 2)" prop="taxes" v-if="edit_item.changeable">
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
import _isEqual from 'lodash/isEqual';
export default {
  props: {
    taxes: { type: Array },
    itemPrice: { type: Function },
    deleteItem: { type: Function },
    updateItem: { type: Function },
    modOptLabel: { type: Function },
    itemDiscount: { type: Function },
    itemUnitChanged: { type: Function },
    addPortionToEditItem: { type: Function },
    changeSelectedPortion: { type: Function },
    edit_item: { type: Object, required: true },
  },
  methods: {
    selectChoosableVariant(pi, gi) {
      let meta = this.edit_item.selected.portions[pi].choosables[gi].meta;
      let variation = this.edit_item.selected.portions[pi].choosables[gi].variations.find(v => _isEqual(v.meta, meta));
      this.edit_item.selected.portions[pi].choosables[gi].variation_id = variation ? variation.id : '';
    },
    selectPortionItemVariant(pi, ei) {
      let meta = this.edit_item.selected.portions[pi].portion_items[ei].meta;
      let variation = this.edit_item.selected.portions[pi].portion_items[ei].variations.find(v => _isEqual(v.meta, meta));
      this.edit_item.selected.portions[pi].portion_items[ei].variation_id = variation ? variation.id : '';
    },
    selectEssentialVariant(pi, ei) {
      let meta = this.edit_item.selected.portions[pi].essentials[ei].meta;
      let variation = this.edit_item.selected.portions[pi].essentials[ei].variations.find(v => _isEqual(v.meta, meta));
      this.edit_item.selected.portions[pi].essentials[ei].variation_id = variation ? variation.id : '';
    },
  },
};
</script>
