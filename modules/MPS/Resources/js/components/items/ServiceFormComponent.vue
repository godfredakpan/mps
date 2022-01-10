<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('service') }}</p>
      <router-link to="/items/services" slot="extra">
        <Icon type="ios-grid-outline" />
        {{ $t('list') }} {{ $tc('service', 2) }}
      </router-link>
      <div>
        <Form ref="item" :model="form" :rules="rules" :label-width="150" class="form-responsive">
          <Row :gutter="16">
            <Col :sm="24" :md="24" :lg="24">
              <Loading v-if="loading" />
              <Alert type="error" show-icon class="mb26" v-if="errors.message">
                <div v-html="errors.message"></div>
              </Alert>
              <Row :gutter="16">
                <Col :sm="24" :md="12" :lg="12">
                  <FormItem :label="$t('name')" prop="name" :error="errors.form.name | a2s">
                    <Input v-model="form.name" />
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
                  <FormItem :label="$tc('taxes')" prop="taxes" :error="errors.form.taxes | a2s">
                    <Select v-model="form.taxes" filterable multiple>
                      <Option v-for="item in taxes" :value="item.id" :key="'tax_' + item.id">
                        {{ item.name }}
                      </Option>
                    </Select>
                  </FormItem>
                </Col>
                <Col :sm="24" :md="12" :lg="12">
                  <FormItem :label="$t('sku')" prop="sku" :error="errors.form.sku | a2s">
                    <Input v-model="form.sku" readonly />
                  </FormItem>
                  <FormItem :label="$t('alt_name')" prop="alt_name" :error="errors.form.alt_name | a2s">
                    <Input v-model="form.alt_name" />
                  </FormItem>
                  <FormItem :label="$t('sac_number')" prop="hsn_number" :error="errors.form.hsn_number | a2s">
                    <Input v-model="form.hsn_number" />
                  </FormItem>
                  <FormItem class="input-tip" prop="max_discount" :label="$t('max_discount')" :error="errors.form.max_discount | a2s">
                    <InputNumber v-model="form.max_discount" :formatter="value => `${value}%`" :parser="value => value.replace('%', '')" />
                    <small>{{ $t('max_discount_tip') }}</small>
                  </FormItem>
                  <FormItem :label="$tc('modifier', 2)" prop="modifier" :error="errors.form.modifier | a2s">
                    <Select v-model="form.modifiers" clearable multiple>
                      <Option v-for="item in modifiers" :value="item.value" :key="'modifier_' + item.value">
                        {{ item.label }}
                      </Option>
                    </Select>
                  </FormItem>
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
                  <!-- <small v-if="new_photo" class="warning"><br />{{ $t('not_uploaded_yet') }}</small> -->
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
              <FormItem prop="details" :label="$t('details')" style="margin-bottom: 16px;" :error="errors.form.details | a2s">
                <Input type="textarea" v-model="form.details" :autosize="{ minRows: 4, maxRows: 12 }" />
              </FormItem>
              <FormItem>
                <Button type="primary" :loading="saving" :disabled="saving" @click="submit('items')">
                  <span v-if="!saving">{{ $t('submit') }}</span>
                  <span v-else>{{ $t('saving') }}...</span>
                </Button>
                <Button ghost type="primary" :loading="saving" :disabled="saving" style="margin-left: 8px;" @click="submit('items', true)">
                  <span v-if="!saving">{{ $t('save_n_stay') }}</span>
                  <span v-else>{{ $t('saving') }}...</span>
                </Button>
                <Button type="error" ghost @click="handleReset()" style="margin-left: 8px;">{{ $t('reset') }}</Button>
              </FormItem>
            </Col>
          </Row>
        </Form>
      </div>
    </Card>
  </div>
</template>

<script>
import Form from '@mpsjs/mixins/Form';
import _debounce from 'lodash/debounce';
const formatRes = (data, vm) => {
  if (data.attributes) {
    vm.attributes = data.attributes;
    delete data.attributes;
  }
  data.extra_attributes = vm.formatAttributes(vm.attributes, data.extra_attributes);
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
  data.max_price = data.max_price ? parseFloat(data.max_price) : null;
  data.min_price = data.min_price ? parseFloat(data.min_price) : null;
  data.max_discount = data.max_discount ? parseFloat(data.max_discount) : null;
  data.taxes = data.taxes.map(t => t.id);
  // data.stock = data.stock.map(l => {
  //     return {
  //         rack: l.rack,
  //         location_id: l.location_id,
  //         cost: l.cost ? parseFloat(l.cost) : null,
  //         price: l.price ? parseFloat(l.price) : null,
  //         quantity: l.quantity ? parseFloat(l.quantity) : null,
  //     };
  // });
  if (!data.modifiers) {
    data.modifiers = [];
  }
  data.modifiers = data.modifiers.map(m => m.id);
  let numberAttrs = vm.attributes.filter(a => a.type == 'number');
  numberAttrs.map(a => {
    data.extra_attributes[a.slug] = data.extra_attributes[a.slug] ? parseFloat(data.extra_attributes[a.slug]) : null;
  });
  vm.form = { ...data, ...data.extra_attributes };
  return vm.form;
};
export default {
  mixins: [Form('item', 'app/items', true, formatRes, 'services')],
  data() {
    const costV = (rule, value, callback) => {
      if (value && parseFloat(value) < parseFloat(this.minCost)) {
        callback(new Error(this.$t('min_cost_error', { min: parseFloat(this.minCost) })));
      } else {
        callback();
      }
    };
    const priceV = (rule, value, callback) => {
      if (value && parseFloat(value) < parseFloat(this.minCost)) {
        callback(new Error(this.$t('min_price_error', { min: parseFloat(this.minCost) })));
      } else {
        callback();
      }
    };
    return {
      step: 0,
      taxes: [],
      photo: null,
      costV: costV,
      option: false,
      modifiers: [],
      priceV: priceV,
      categories: [],
      attributes: [],
      new_photo: null,
      sloading: false,
      searching: false,
      minCost: null,
      form: {
        id: '',
        code: '',
        name: '',
        stock: {},
        taxes: [],
        cost: null,
        details: '',
        photo: null,
        summary: '',
        images: [],
        video: null,
        price: null,
        is_stock: 0,
        alt_name: '',
        weight: null,
        changeable: 0,
        modifiers: [],
        dimensions: '',
        type: 'service',
        has_serials: 0,
        has_variants: 0,
        min_price: null,
        max_price: null,
        sku: this.sku(),
        tax_included: '',
        category_id: null,
        supplier_id: null,
        max_discount: null,
        supplier_item_id: '',
      },
      current: { name: '' },

      rules: {
        type: [{ required: true, message: this.$t('field_is_required', { field: this.$t('type') }), trigger: 'change' }],
        name: [{ required: true, message: this.$t('field_is_required', { field: this.$t('name') }), trigger: 'blur' }],
        code: [
          { required: true, message: this.$t('field_is_required', { field: this.$t('code') }), trigger: 'blur' },
          {
            required: true,
            trigger: ['blur', 'change'],
            pattern: /^([a-zA-Z0-9-_]{2,25})$/,
            message: this.$t('alpha_dash_error'),
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
        cost: [
          {
            required: true,
            type: 'number',
            trigger: ['change', 'blur'],
            message: this.$t('field_is_required', { field: this.$t('cost') }),
          },
          { validator: costV, type: 'number', trigger: ['change', 'blur'] },
        ],
        price: [
          {
            required: true,
            type: 'number',
            trigger: ['change', 'blur'],
            message: this.$t('field_is_required', { field: this.$t('price') }),
          },
          { validator: priceV, type: 'number', trigger: ['change', 'blur'] },
        ],
      },
    };
  },
  created() {
    this.$http.get('app/taxes/search').then(res => (this.taxes = res.data));
    this.$http.get('app/modifiers/search').then(res => (this.modifiers = res.data));
    this.$http.get('app/all_categories/search').then(res => (this.categories = this.flattenDeep(res.data)));
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
    submit(page, stay) {
      this.$refs['item'].validate(valid => {
        if (valid) {
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
                } else {
                  this.$router.push({ name: page + '.services.list' });
                }
              } else {
                this.$Notice.error({ title: this.$t('failed'), desc: this.$t('failed_error_text') });
              }
            })
            .catch(error => (this.errors = error))
            .finally(() => (this.loading = false));
        } else {
          this.sloading = false;
          this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
        }
      });
    },
  },
};
</script>
