<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('item') }}</p>
      <router-link to="/items" slot="extra">
        <Icon type="ios-grid-outline" />
        {{ $t('list') }} {{ $tc('item', 2) }}
      </router-link>
      <div>
        <Form ref="item" :model="form" :rules="rules" :label-width="150" class="form-responsive">
          <Row :gutter="16">
            <Col :sm="24" :md="24" :lg="24">
              <Loading v-if="loading" />
              <div class="hidden-sm" style="margin: -16px -16px 16px -16px; border-bottom: 1px solid #e8eaec; padding: 16px;">
                <Steps :current="step" status="wait">
                  <Step :title="$t('general_step')" :content="$t('general_step_text')"></Step>
                  <Step :title="$t('variants_step')" :content="$t('variants_step_text')"></Step>
                  <Step :title="$t('location_step')" :content="$t('location_step_text')"></Step>
                  <!-- <Step :title="$t('modifier_step')" :content="$t('modifier_step_text')"></Step> -->
                  <Step :title="$t('serials_step')" :content="$t('serials_step_text')"></Step>
                </Steps>
              </div>
              <Alert type="error" show-icon class="mb26" v-if="errors.message">
                <div v-html="errors.message"></div>
              </Alert>
              <div v-if="step == 0">
                <Row :gutter="16">
                  <Col :sm="24" :md="12" :lg="12">
                    <!-- <FormItem :label="$t('type')" prop="type" :error="errors.form.type | a2s">
                        <Select v-model="form.type">
                            <Option value="standard">{{ $t('standard') }}</Option>
                            <Option value="combo">{{ $t('combo') }}</Option>
                            <Option value="service">{{ $t('service') }}</Option>
                            <Option value="recipe">{{ $t('recipe') }}</Option>
                        </Select>
                    </FormItem> -->
                    <FormItem :label="$t('sku')" prop="sku" :error="errors.form.sku | a2s">
                      <Input v-model="form.sku" readonly />
                    </FormItem>
                    <FormItem :label="$t('name')" prop="name" :error="errors.form.name | a2s">
                      <Input v-model="form.name" />
                    </FormItem>
                    <FormItem :label="$t('alt_name')" prop="alt_name" :error="errors.form.alt_name | a2s">
                      <Input v-model="form.alt_name" />
                    </FormItem>
                    <FormItem :label="$t('code')" prop="code" :error="errors.form.code | a2s" class="input-tip">
                      <Input v-model="form.code" />
                      <small>{{ $t('item_code_tip') }}</small>
                    </FormItem>
                    <FormItem :label="$tc('symbology')" prop="symbology" :error="errors.form.symbology | a2s">
                      <Select v-model="form.symbology">
                        <Option value="code128">Code128</Option>
                        <Option value="code39">Code39</Option>
                        <Option value="ean5">EAN5</Option>
                        <Option value="ean8">EAN8</Option>
                        <Option value="ean13">EAN13</Option>
                        <Option value="upce">UPC (E)</Option>
                        <Option value="upca">UPC (A)</Option>
                      </Select>
                    </FormItem>
                    <FormItem :label="$tc('category')" prop="category_id" :error="errors.form.category_id | a2s">
                      <Select v-model="form.category_id">
                        <Option v-for="item in categories" :value="item.id" :key="item.value">
                          {{ item.name }}
                        </Option>
                      </Select>
                    </FormItem>
                    <FormItem :label="$t('cost')" prop="cost" :error="errors.form.cost | a2s">
                      <InputNumber v-model="form.cost" />
                    </FormItem>
                    <FormItem :label="$t('price')" prop="price" :error="errors.form.price | a2s">
                      <InputNumber v-model="form.price" />
                    </FormItem>
                    <FormItem :label="$tc('tax', 2)" prop="taxes" :error="errors.form.taxes | a2s">
                      <Select v-model="form.taxes" multiple>
                        <Option v-for="item in taxes" :value="item.id" :key="'tax_' + item.id">
                          {{ item.name }}
                        </Option>
                      </Select>
                    </FormItem>
                  </Col>
                  <Col :sm="24" :md="12" :lg="12">
                    <FormItem :label="$tc('brand')" prop="brand" :error="errors.form.brand | a2s">
                      <Select v-model="form.brand_id" clearable>
                        <Option v-for="item in brands" :value="item.id" :key="'brand_' + item.id">
                          {{ item.name }}
                        </Option>
                      </Select>
                    </FormItem>
                    <FormItem :label="$tc('modifier', 2)" prop="modifier" :error="errors.form.modifier | a2s">
                      <Select v-model="form.modifiers" clearable multiple>
                        <Option v-for="item in modifiers" :value="item.value" :key="'modifier_' + item.value">
                          {{ item.label }}
                        </Option>
                      </Select>
                    </FormItem>
                    <FormItem :label="$t('hsn_number')" prop="hsn_number" :error="errors.form.hsn_number | a2s">
                      <Input v-model="form.hsn_number" />
                    </FormItem>
                    <FormItem :label="$t('weight')" prop="weight" :error="errors.form.weight | a2s">
                      <InputNumber v-model="form.weight" />
                    </FormItem>
                    <FormItem :label="$t('dimensions')" prop="dimensions" :error="errors.form.dimensions | a2s">
                      <Input v-model="form.dimensions" :placeholder="$t('dimensions_tip')" />
                    </FormItem>
                    <FormItem :label="$t('rack_location')" prop="location" :error="errors.form.rack | a2s" class="input-tip">
                      <Input v-model="form.rack" />
                      <small>{{ $t('rack_location_tip') }}</small>
                    </FormItem>
                    <FormItem class="input-tip" prop="max_discount" :label="$t('max_discount')" :error="errors.form.max_discount | a2s">
                      <InputNumber
                        v-model="form.max_discount"
                        :formatter="value => `${value}%`"
                        :parser="value => value.replace('%', '')"
                      />
                      <small>{{ $t('max_discount_tip') }}</small>
                    </FormItem>
                    <FormItem :label="$tc('supplier')" prop="supplier_id" :error="errors.form.supplier_id | a2s">
                      <Select
                        remote
                        clearable
                        filterable
                        :loading="searching"
                        v-model="form.supplier_id"
                        :remote-method="searchSuppliers"
                        :placeholder="$t('type_to_search_x', { x: $tc('supplier') })"
                      >
                        <Option v-for="(option, index) in suppliers" :value="option.value" :key="index + option.value">{{
                          option.label
                        }}</Option>
                      </Select>
                    </FormItem>
                    <FormItem
                      prop="supplier_item_id"
                      v-if="form.supplier_id"
                      :label="$t('supplier_item_id')"
                      :error="errors.form.supplier_item_id | a2s"
                    >
                      <Input v-model="form.supplier_item_id" />
                    </FormItem>
                  </Col>
                </Row>
                <Row>
                  <Col :sm="24" :md="24" :lg="24">
                    <FormItem :label="$tc('unit')" prop="unit" :error="errors.form.unit | a2s">
                      <Select v-model="form.unit_id" clearable @on-change="unitChanged">
                        <Option v-for="item in units" :value="item.id" :key="'unit_' + item.id">
                          {{ item.name }}
                        </Option>
                      </Select>
                    </FormItem>
                    <transition name="slide-fade">
                      <div v-if="form.unit && form.unit.subunits.length">
                        <Row>
                          <Col :sm="24" :md="12" :lg="12">
                            <FormItem :label="$t('sale_unit')" prop="unit" :error="errors.form.unit | a2s">
                              <Select v-model="form.sale_unit_id" clearable>
                                <Option v-for="(item, index) in form.unit.subunits" :value="item.id" :key="'sunit_' + index">
                                  {{ item.name }}
                                </Option>
                              </Select>
                            </FormItem>
                          </Col>
                          <Col :sm="24" :md="12" :lg="12">
                            <FormItem :label="$t('purchase_unit')" prop="unit" :error="errors.form.unit | a2s">
                              <Select v-model="form.purchase_unit_id" clearable>
                                <Option v-for="(item, index) in form.unit.subunits" :value="item.id" :key="'punit_' + index">
                                  {{ item.name }}
                                </Option>
                              </Select>
                            </FormItem>
                          </Col>
                        </Row>
                        <FormItem label="">
                          <Card dis-hover v-for="(unit, ui) in form.unit.subunits" :key="'unit_' + unit.id">
                            <p slot="title">{{ unit.name }} ({{ unit.code }})</p>
                            <Row :gutter="16">
                              <Col :sm="24" :md="12" :lg="12">
                                <span>{{ $t('cost') }}</span>
                                <InputNumber v-model="form.unit.subunits[ui].cost" />
                              </Col>
                              <Col :sm="24" :md="12" :lg="12">
                                <span>{{ $t('price') }}</span>
                                <InputNumber v-model="form.unit.subunits[ui].price" />
                              </Col>
                            </Row>
                          </Card>
                        </FormItem>
                      </div>
                    </transition>
                  </Col>
                </Row>
                <FormItem class="mb0">
                  <Checkbox v-model="form.tax_included" label="tax_included" :true-value="1" :false-value="0">
                    {{ $t('tax_inclusive') }}
                  </Checkbox>
                </FormItem>
                <FormItem class="mb0">
                  <Checkbox v-model="form.changeable" label="price_changeable" :true-value="1" :false-value="0">
                    {{ $t('allow_price_change') }}
                  </Checkbox>
                </FormItem>
                <FormItem class="mb0">
                  <Checkbox v-model="form.expiry" :true-value="1" :false-value="0">
                    {{ $t('item_has_expiry_date') }}
                  </Checkbox>
                </FormItem>
                <transition name="slide-fade">
                  <div v-if="form.changeable" style="margin-top: 16px;">
                    <Row>
                      <Col :sm="24" :md="12" :lg="12">
                        <FormItem :label="$t('min_price')" prop="min_price" :error="errors.form.min_price | a2s">
                          <InputNumber v-model="form.min_price" />
                        </FormItem>
                      </Col>
                      <Col :sm="24" :md="12" :lg="12">
                        <FormItem :label="$t('max_price')" prop="max_price" :error="errors.form.max_price | a2s">
                          <InputNumber v-model="form.max_price" />
                        </FormItem>
                      </Col>
                    </Row>
                  </div>
                </transition>

                <form-custom-fields v-model="form" :attributes="attributes" @update="updateCF" />

                <FormItem :label="$tc('photo')" prop="photo" :error="errors.form.photo | a2s">
                  <Upload :before-upload="handleUpload" :max-size="1024" action accept=".png, .jpeg, .jpg, .gif">
                    <Button icon="ios-cloud-upload-outline">{{ $t('select_x', { x: $t('photo') }) }}</Button>
                  </Upload>
                  <div v-if="photo || new_photo" class="primary" style="margin-bottom: 16px;">
                    <span v-if="form.photo">
                      <span style="margin: 10px 10px 10px 0;">{{ $t('selected_file') }}: {{ form.photo.name }}</span>
                      <Button type="error" shape="circle" size="small" icon="ios-trash" @click="clearPhoto()"></Button>
                    </span>
                    <br />
                    <img
                      alt="photo"
                      v-if="photo || new_photo"
                      :src="new_photo ? new_photo : photo"
                      style="max-width: 160px; max-height: 110px; border-radius: 4px 4px 0 0;"
                    />
                    <div>
                      <small v-if="new_photo" class="warning">{{ $t('not_uploaded_yet') }}</small>
                    </div>
                  </div>
                </FormItem>
                <FormItem :label="$t('gallery_images')" prop="images" :error="errors.form.images | a2s">
                  <Upload multiple :before-upload="handleImages" :max-size="2048" action accept=".png, .jpeg, .jpg">
                    <Button icon="ios-cloud-upload-outline">
                      {{ $t('select_x', { x: $t('gallery_images') }) }}
                    </Button>
                  </Upload>
                  <template v-if="form.images.length">
                    <span style="margin: 10px 10px 10px 0;">{{ $t('selected_file') }}</span>
                    <span v-for="(image, gi) in form.images" :key="'gallery_' + gi" class="primary" style="margin: 0 16px 16px 0;">
                      {{ gi + 1 }}. {{ image.name }}
                      <Button type="error" shape="circle" size="small" icon="ios-trash" @click="deleteImage(gi)"></Button>
                    </span>
                  </template>
                </FormItem>
                <FormItem :label="$t('video_url')" prop="video" :error="errors.form.video | a2s" class="input-tip">
                  <Input v-model="form.video" />
                  <small>{{ $t('video_tip') }}</small>
                </FormItem>
                <FormItem :label="$t('summary')" prop="summary" :error="errors.form.summary | a2s">
                  <Input type="textarea" v-model="form.summary" :autosize="{ minRows: 2, maxRows: 5 }" />
                </FormItem>
                <FormItem :label="$t('details')" prop="details" style="margin-bottom: 16px;" :error="errors.form.details | a2s">
                  <Input type="textarea" v-model="form.details" :autosize="{ minRows: 4, maxRows: 12 }" />
                </FormItem>
                <FormItem :label="$t('hide_in_x', { x: $t('pos') })" class="mb0">
                  <i-switch v-model="form.hide_in_pos" :true-value="1" :false-value="0" size="large">
                    <span slot="open">{{ $t('yes') }}</span>
                    <span slot="close">{{ $t('no') }}</span>
                  </i-switch>
                </FormItem>
                <FormItem :label="$t('hide_in_x', { x: $t('shop') })">
                  <i-switch v-model="form.hide_in_shop" :true-value="1" :false-value="0" size="large">
                    <span slot="open">{{ $t('yes') }}</span>
                    <span slot="close">{{ $t('no') }}</span>
                  </i-switch>
                </FormItem>
                <FormItem>
                  <Button type="primary" :loading="saving" :disabled="saving" @click="submit('items')">
                    <span v-if="!saving">{{ $t('save') }}</span>
                    <span v-else>{{ $t('saving') }}...</span>
                  </Button>
                  <Button type="primary" :loading="sloading" @click="goToStep(1, true)">{{ $t('next') }}</Button>
                </FormItem>
              </div>
              <div v-else-if="step == 1">
                <item-variation-component
                  :ivariants="form.variants"
                  :ivariations="form.variations"
                  :hasVariants="form.has_variants"
                  @step-changed="goToStep"
                  @variants-changed="setVariants"
                  @variations-changed="setVariations"
                  @has-variants-changed="setHasVariants"
                ></item-variation-component>
              </div>
              <div v-else-if="step == 2">
                <item-location-data-component
                  :istock="form.stock"
                  :isStock="form.is_stock"
                  :variants="form.variants"
                  :ivariations="form.variations"
                  :hasVariants="form.has_variants"
                  @step-changed="goToStep"
                  @stock-changed="setStock"
                  @is-stock-changed="setIsStock"
                  @variations-changed="setVariations"
                ></item-location-data-component>
              </div>
              <!-- <div v-else-if="step == 3">
                  <item-modifier-component
                      :pmodifiers="modifiers"
                      @step-changed="goToStep"
                      :pvalues="form.modifiers"
                      @modifiers-changed="setModifiers"
                  ></item-modifier-component>
              </div> -->
              <div v-else-if="step == 3">
                <div class="mb16">
                  <Checkbox v-model="form.has_serials" label="has_serials" :true-value="1" :false-value="0">
                    {{ $t('item_has_serial') }}
                  </Checkbox>
                </div>
                <transition name="slide-fade">
                  <div class="mb16" v-if="form.has_serials == 1" style="margin-top: 16px; margin-bottom: 0;">
                    <item-serial-component
                      :serials="form.serials"
                      :totalStockQty="totalStockQty"
                      :hasVariants="form.has_variants"
                      :totalVariationsQty="totalVariationsQty"
                    ></item-serial-component>
                  </div>
                </transition>
                <div class="mb16">
                  <Button type="primary" :loading="saving" :disabled="saving" @click="submit('items')">
                    <span v-if="!saving">{{ $t('submit') }}</span>
                    <span v-else>{{ $t('saving') }}...</span>
                  </Button>
                  <Button
                    ghost
                    type="primary"
                    :loading="saving"
                    :disabled="saving"
                    style="margin-left: 8px;"
                    @click="submit('items', true)"
                  >
                    <span v-if="!saving">{{ $t('save_n_stay') }}</span>
                    <span v-else>{{ $t('saving') }}...</span>
                  </Button>
                  <Button type="warning" ghost @click="goToStep(2)" style="margin-left: 8px;">
                    {{ $t('back') }}
                  </Button>
                  <Button type="error" ghost @click="handleReset()" style="margin-left: 8px;">{{ $t('reset') }}</Button>
                </div>
              </div>
            </Col>
          </Row>
        </Form>
      </div>
    </Card>
  </div>
</template>

<script>
import _delay from 'lodash/delay';
import Form from '@mpsjs/mixins/Form';
import _debounce from 'lodash/debounce';
import ItemSerialComponent from './sub/ItemSerialComponent';
import ItemVariationComponent from './sub/ItemVariationComponent';
import ItemLocationDataComponent from './sub/ItemLocationDataComponent';
const formatRes = (data, vm) => {
  if (data.attributes) {
    vm.attributes = data.attributes;
    delete data.attributes;
  }
  data.extra_attributes = vm.formatAttributes(vm.attributes, data.extra_attributes);
  // this.photo = data.photo ? data.photo : null;
  // this.attributes = data.attributes ? data.attributes : [];
  data.images = [];
  delete data.photo;
  if (data.location_stock) {
    delete data.location_stock;
  }
  if (data.valid_promotions) {
    delete data.valid_promotions;
  }
  data.cost = parseFloat(data.cost);
  data.price = parseFloat(data.price);
  data.category_id = data.categories[0].id;
  data.sku = data.sku ? data.sku : vm.sku();
  data.hide_in_pos = data.hide_in_pos == 1 ? 1 : 0;
  data.hide_in_shop = data.hide_in_shop == 1 ? 1 : 0;
  data.max_price = data.max_price ? parseFloat(data.max_price) : null;
  data.min_price = data.min_price ? parseFloat(data.min_price) : null;
  data.max_discount = data.max_discount ? parseFloat(data.max_discount) : null;
  data.taxes = data.taxes.map(t => t.id);
  data.stock = data.stock.map(l => {
    return {
      rack: l.rack,
      location_id: l.location_id,
      cost: l.cost ? parseFloat(l.cost) : null,
      price: l.price ? parseFloat(l.price) : null,
      quantity: l.quantity ? parseFloat(l.quantity) : null,
    };
  });
  if (!data.serials) {
    data.serials = [{ number: '', till: '' }];
  }
  if (!data.modifiers) {
    data.modifiers = [];
  }
  if (!data.variants) {
    data.variants = [];
    // } else {
    //     let variants = [];
    //     for (let k in data.variants) {
    //         variants.push({ name: k, options: data.variants[k] });
    //     }
    //     // for (let [key, value] of Object.entries(data.variants)) {
    //     //     variants.push({ name: key, options: value });
    //     // }
    //     data.variants = variants;
  }
  if (!data.variations || data.variations.length < 1) {
    data.variations = [
      { sku: vm.sku(), code: '', cost: null, price: null, quantity: null, weight: null, dimensions: '', del: null, meta: [] },
    ];
  }
  data.modifiers = data.modifiers.map(m => m.id);
  data.variations = data.variations.map(v => {
    v.cost = v.cost ? parseFloat(v.cost) : null;
    v.price = v.price ? parseFloat(v.price) : null;
    v.weight = v.weight ? parseFloat(v.weight) : null;
    v.quantity = v.quantity ? parseFloat(v.quantity) : null;
    if (!v.stock) {
      v.stock = vm.$store.getters.locations.map(l => {
        return { ...l, location_id: l.value, price: null, cost: null, quantity: null, rack: '' };
      });
    } else {
      v.stock = v.stock.map(s => {
        s.cost = s.cost ? parseFloat(s.cost) : null;
        s.price = s.price ? parseFloat(s.price) : null;
        s.quantity = s.quantity ? parseFloat(s.quantity) : null;
        return s;
      });
    }
    return v;
  });
  data.stock = vm.$store.getters.locations.map(l => {
    if (data.unit && data.unit.subunits.length) {
      data.unit.subunits = data.unit.subunits.map(u => {
        if (!u.price) {
          u.price = null;
        }
        if (!u.cost) {
          u.cost = null;
        }
        return u;
      });
    }
    return {
      ...l,
      rack: '',
      cost: null,
      price: null,
      quantity: null,
      location_id: l.value,
      units: data.unit && data.unit.subunits.length ? data.unit.subunits : null,
    };
  });
  // if (data.stock.units) {
  //     data.stock.units = data.stock.units.map(u => {
  //         if (!u.price) {
  //             u.price = null;
  //         }
  //         if (!u.cost) {
  //             u.cost = null;
  //         }
  //         return u;
  //     });
  // }
  data.variations = data.variations.map(v => {
    v.stock = vm.$store.getters.locations.map(l => {
      return { ...l, location_id: l.value, price: null, cost: null, quantity: null, rack: '' };
    });
    return v;
  });
  vm.form = { ...data, ...data.extra_attributes };
  return vm.form;
};
export default {
  components: { ItemSerialComponent, ItemVariationComponent, ItemLocationDataComponent },
  mixins: [Form('item', 'app/items', true, formatRes)],
  data() {
    return {
      step: 0,
      taxes: [],
      photo: null,
      units: [],
      brands: [],
      modifiers: [],
      suppliers: [],
      categories: [],
      attributes: [],
      new_photo: null,
      sloading: false,
      searching: false,
      form: {
        id: '',
        code: '',
        name: '',
        stock: [],
        expiry: 0,
        taxes: [],
        cost: null,
        images: [],
        details: '',
        unit_id: '',
        brand_id: '',
        photo: null,
        video: null,
        summary: '',
        price: null,
        is_stock: 0,
        alt_name: '',
        weight: null,
        changeable: 0,
        dimensions: '',
        has_serials: 0,
        has_variants: 0,
        min_price: null,
        max_price: null,
        type: 'standard',
        tax_included: '',
        sale_unit_id: '',
        sale_unit_price: '',
        category_id: null,
        supplier_id: null,
        max_discount: null,
        purchase_unit_id: '',
        purchase_unit_price: '',
        supplier_item_id: '',
        sku: this.sku(),
        // sku: new Date().getTime(),
        variations: [
          {
            sku: this.sku(),
            code: '',
            cost: null,
            price: null,
            quantity: null,
            weight: null,
            dimensions: '',
            del: null,
            meta: [],
          },
        ],
        serials: [{ number: '', till: '' }],
        variants: [],
        modifiers: [],
      },

      rules: {
        type: [{ required: true, message: this.$t('field_is_required', { field: this.$t('type') }), trigger: 'change' }],
        name: [{ required: true, message: this.$t('field_is_required', { field: this.$t('name') }), trigger: 'blur' }],
        code: [
          { required: true, message: this.$t('field_is_required', { field: this.$t('code') }), trigger: 'blur' },
          {
            required: true,
            pattern: /^([a-zA-Z0-9-_]{2,25})$/,
            message: this.$t('alpha_dash_error'),
            trigger: ['blur', 'change'],
          },
        ],
        cost: [
          {
            required: true,
            type: 'number',
            trigger: ['change', 'blur'],
            message: this.$t('field_is_required', { field: this.$t('cost') }),
          },
        ],
        price: [
          {
            required: true,
            type: 'number',
            trigger: ['change', 'blur'],
            message: this.$t('field_is_required', { field: this.$t('price') }),
          },
        ],
        category_id: [{ required: true, message: this.$t('field_is_required', { field: this.$tc('category') }), trigger: 'change' }],
        symbology: [{ required: true, message: this.$t('field_is_required', { field: this.$t('symbology') }), trigger: 'change' }],
        taxes: [
          {
            type: 'array',
            trigger: 'change',
            message: this.$t('field_are_required', { field: this.$tc('tax', 2) }),
          },
        ],
      },
    };
  },
  computed: {
    totalVariationsQty() {
      return this.form.variations.reduce((a, v) => a + v.quantity, 0);
    },
    totalStockQty() {
      return this.form.stock.reduce((a, s) => a + s.quantity, 0);
    },
    isQtyOk() {
      return this.totalVariationsQty == this.totalStockQty;
    },
    // subunits() {
    //     if (this.units && this.units.length) {
    //         let selected = this.units.find(u => u.id == this.form.unit_id);
    //         if (selected) {
    //             let unit = {};
    //             let subunits = [];
    //             unit = { ...selected };
    //             delete unit.subunits;
    //             subunits = selected.subunits;
    //             subunits.unshift(unit);
    //             return subunits;
    //         }
    //     }
    //     return null;
    // },
  },
  created() {
    this.$http.get('app/taxes/search').then(res => (this.taxes = res.data));
    this.$http.get('app/units/search').then(res => (this.units = res.data));
    this.$http.get('app/brands/search').then(res => (this.brands = res.data));
    this.$http.get('app/modifiers/search').then(res => (this.modifiers = res.data));
    // this.$http.get('app/categories/search').then(res => (this.categories = res.data));
    this.$http.get('app/all_categories/search').then(res => (this.categories = this.flattenDeep(res.data)));
    this.form.stock = this.$store.getters.locations.map(l => {
      return {
        ...l,
        rack: '',
        cost: null,
        price: null,
        quantity: null,
        location_id: l.value,
        units: this.form.unit && this.form.unit.subunits.length ? this.form.unit.subunits : null,
      };
    });
    // this.form.variations = this.form.variations.map(v => {
    //     v.stock = this.$store.getters.locations.map(l => {
    //         return { ...l, location_id: l.value, price: null, cost: null, quantity: null, rack: '' };
    //     });
    //     return v;
    // });
  },
  methods: {
    create() {
      this.$http
        .get('app/items/create')
        .then(res => (this.attributes = res.data))
        .finally(() => (this.loading = false));
    },
    fetch(id) {
      this.$http
        .get(`app/items/${id}`)
        .then(res => (this.form = formatRes(res.data, this)))
        .finally(() => (this.loading = false));
    },
    setHasVariants(v) {
      this.form.has_variants = v;
    },
    setIsStock(v) {
      this.form.is_stock = v;
    },
    setStock(v) {
      this.form.stock = v;
    },
    setVariants(variants) {
      this.form.variants = variants;
    },
    // setModifiers(modifiers) {
    //     this.form.modifiers = modifiers;
    // },
    setVariations(variations) {
      this.form.variations = variations;
    },
    goToStep(n, check) {
      this.sloading = true;
      if (n == 1 && check) {
        this.$refs['item'].validate(valid => {
          if (valid) {
            this.step = parseInt(n);
            this.sloading = false;
            window.scrollTo(0, 0);
          } else {
            this.sloading = false;
            this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
          }
        });
      } else {
        window.scrollTo(0, 0);
        this.sloading = false;
        this.step = parseInt(n);
      }
    },
    unitChanged(uId) {
      if (uId) {
        this.loading = true;
        this.$http
          .get('app/units/' + uId)
          .then(res => {
            this.form.unit = res.data;
            this.form.unit.subunits = this.form.unit.subunits.map(u => {
              if (!u.price) {
                u.price = null;
              }
              if (!u.cost) {
                u.cost = null;
              }
              return u;
            });
            this.form.stock = this.form.stock.map(s => {
              s.units = this.form.unit.subunits.length ? this.form.unit.subunits.map(u => ({ id: u.id, cost: null, price: null })) : null;
              return s;
            });
          })
          .finally(() => (this.loading = false));
      } else {
        this.form.unit = null;
        this.form.stock = this.form.stock.map(s => {
          s.units = null;
          return s;
        });
      }
    },
    handleImages(file) {
      var reader = new FileReader();
      this.form.images.push(file);
      reader.readAsDataURL(file);
      return false;
    },
    deleteImage(i) {
      this.form.images.splice(i, 1);
    },
    handleUpload(file) {
      var reader = new FileReader();
      reader.addEventListener('load', () => (this.new_photo = reader.result), false);
      this.form['photo'] = file;
      reader.readAsDataURL(file);
      return false;
    },
    clearPhoto() {
      this.new_photo = null;
      this.form.photo = null;
    },
    searchSuppliers(search) {
      if (search !== '' && !this.suppliers.find(c => c.label == search)) {
        this.getSuppliers(search, this);
      }
    },
    getSuppliers: _debounce((search, vm) => {
      vm.searching = true;
      const search_delay = vm.$store.getters.search_delay;
      vm.$http
        .get('app/suppliers/search?q=' + search)
        .then(res => (vm.suppliers = res.data))
        .finally(() => (vm.searching = false));
    }, search_delay || 250),
    submit(page, stay) {
      let post = 'app/items';
      let msg = 'added';
      this.errors.message = '';
      let msg_text = 'record_added';
      if (this.form.id && this.form.id != '') {
        msg = 'updated';
        post = 'app/items/' + this.form.id;
        msg_text = 'record_updated';
        this.form['_method'] = 'PUT';
      }
      let data = { ...this.form };
      if (this.attributes.length > 0) {
        let extras = this.attributes.map(attr => {
          let extra = {};
          delete data[attr.slug];
          extra[attr.slug] = this.form[attr.slug];
          return extra;
        });
        data.extra_attributes = Object.assign(...extras);
      }

      data = this.$form(data);
      this.$http
        .post(post, data)
        .then(res => {
          if (res.data.success) {
            this.$Notice.destroy();
            this.$Notice.success({ title: this.$tc('item') + ' ' + this.$t(msg), desc: this.$t(msg_text) });
            if (stay) {
              if (msg == 'updated') {
                // if (formatRes) {
                //     this.form = formatRes(res.data.data, this);
                // }
              } else {
                this.handleReset();
              }
              this.step = 0;
            } else {
              this.$router.push({ name: page + '.list' });
            }
          } else {
            this.$Notice.error({ title: this.$t('failed'), desc: this.$t('failed_error_text') });
          }
        })
        .catch(error => (this.errors = error))
        .finally(() => (this.loading = false));
    },
  },
};
</script>
