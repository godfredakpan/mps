<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('stock_transfer') }}</p>
      <router-link to="/transfers/stock" slot="extra">
        <Icon type="ios-grid-outline" />
        {{ $t('list') }} {{ $tc('stock_transfer', 2) }}
      </router-link>
      <div>
        <Form ref="transfer" :model="form" :rules="rules" :label-width="150" class="form-responsive">
          <Row :gutter="16">
            <Col :sm="24" :md="24" :lg="24">
              <Loading v-if="loading" />
              <Alert type="error" show-icon class="mb26" v-if="errors.message">
                <div v-html="errors.message"></div>
              </Alert>
              <Row :gutter="16">
                <Col :sm="24" :md="12" :lg="12">
                  <FormItem :label="$t('date')" prop="date" :error="errors.form.date | a2s">
                    <DatePicker type="date" v-model="form.date" format="yyyy-MM-dd" style="width: 100%;" />
                  </FormItem>
                  <FormItem :label="$t('from_location')" prop="from" :error="errors.form.from | a2s">
                    <Select v-model="form.from" placeholder>
                      <template v-if="locations.length > 0">
                        <Option :key="index" :value="option.value" v-for="(option, index) in locations">
                          {{ option.label }}
                        </Option>
                      </template>
                    </Select>
                  </FormItem>
                  <FormItem :label="$t('to_location')" prop="to" :error="errors.form.to | a2s">
                    <Select v-model="form.to" placeholder>
                      <template v-if="locations.length > 0">
                        <Option :key="index" :value="option.value" v-for="(option, index) in locations">
                          {{ option.label }}
                        </Option>
                      </template>
                    </Select>
                  </FormItem>
                </Col>
                <Col :sm="24" :md="12" :lg="12">
                  <FormItem :label="$t('status')" prop="status" :error="errors.form.status | a2s">
                    <Select v-model="form.status" placeholder>
                      <Option value="pending">{{ $t('pending') }}</Option>
                      <Option value="transferring">{{ $t('transferring') }}</Option>
                      <Option value="transferred">{{ $t('transferred') }}</Option>
                    </Select>
                  </FormItem>
                  <FormItem :label="$tc('reference')" prop="reference" :error="errors.form.reference | a2s">
                    <Input v-model="form.reference" />
                  </FormItem>
                </Col>
              </Row>
              <transition
                mode="out-in"
                name="slide-in"
                enter-active-class="animate__animated faster animate__fadeInDown"
                leave-active-class="animate__animated fastest animate__fadeOutDown"
              >
                <div class="order-contents" v-if="form.from">
                  <div class="affix-content">
                    <AutoComplete
                      size="large"
                      ref="scanCode"
                      v-model="query"
                      icon="ios-search"
                      @on-select="selectItem"
                      @on-change="searchItems"
                      element-id="scan_barcode"
                      :placeholder="$t('search_scan_barcode')"
                    >
                      <Option v-for="r in result" :value="JSON.stringify(r)" :key="r.id"> {{ r.name }} ({{ r.code }}) </Option>
                    </AutoComplete>
                  </div>

                  <div v-if="!form.items || form.items.length < 1">
                    <Alert show-icon>
                      {{ $t('empty_order') }}
                      <template slot="desc">
                        {{ $t('empty_order_text') }}
                      </template>
                    </Alert>
                  </div>

                  <div v-else>
                    <div class="order-items">
                      <div class="header">
                        <span class="index">#</span>
                        <span class="remove">
                          <Icon type="ios-trash" size="16" />
                        </span>
                        <span class="details">{{ $t('description') }}</span>
                        <span style="width: 200px;">{{ $t('quantity') }}</span>
                      </div>
                      <div class="order-item" :key="index + '_' + row.id" v-for="(row, index) in form.items">
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
                          </span>
                          <span style="width: 200px;">
                            <InputNumber
                              v-model="row.quantity"
                              :readonly="!!row.selected.variations.length"
                              :size="!!row.selected.variations.length ? 'small' : 'default'"
                            ></InputNumber>
                          </span>
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
                            <span style="width: 200px;">
                              <InputNumber v-model="sv.quantity" @on-change="itemVariationQuantityChanged(form.items[index])"></InputNumber>
                            </span>
                          </div>
                        </template>
                      </div>
                    </div>
                  </div>
                </div>
              </transition>

              <form-custom-fields v-model="form" :attributes="attributes" @update="updateCF" />
              <attachments-component :error="errors.form.attachments | a2s" @selected="handleUpload" @clear="clearAttachments">
                <list-attachments-component :attachments="attachments" @remove="deleteAttachment" />
              </attachments-component>
              <FormItem :label="$t('details')" prop="details" :error="errors.form.details | a2s">
                <Input type="textarea" v-model="form.details" :autosize="{ minRows: 4, maxRows: 8 }" />
              </FormItem>

              <FormItem class="mb0">
                <Button type="primary" :loading="saving" :disabled="saving" @click="addTransfer('transfers.stock')">
                  <span v-if="!saving">{{ $t('submit') }}</span>
                  <span v-else>{{ $t('saving') }}...</span>
                </Button>
                <Button
                  ghost
                  type="primary"
                  :loading="saving"
                  :disabled="saving"
                  style="margin-left: 8px;"
                  @click="addTransfer('transfers.stock', true)"
                >
                  <span v-if="!saving">{{ $t('save_n_stay') }}</span>
                  <span v-else>{{ $t('saving') }}...</span>
                </Button>
                <Button v-if="!form.id" @click="handleReset()" style="margin-left: 8px;">{{ $t('reset') }}</Button>
              </FormItem>
            </Col>
          </Row>
        </Form>
      </div>
    </Card>
    <Modal :footer-hide="true" :mask-closable="false" v-model="variantModal" :title="$t('select_x', { x: $tc('variant', 2) })">
      <!-- <select-variation-component :item="item" ref="selectVarMod" @on-submit="variationSubmitted"></select-variation-component> -->
      <div v-if="item">
        <Form ref="vform" :model="item" label-position="top">
          <Row :gutter="16">
            <Col :sm="24" :md="24" :lg="24">
              <FormItem :label="$t('quantity')" class="text-center">
                <InputNumber size="large" v-model="item.quantity" />
              </FormItem>
            </Col>
            <template v-if="item.serials && item.serials.length">
              <Col :sm="24" :md="24" :lg="24">
                <FormItem
                  :label="$tc('serial_number', 2)"
                  prop="serials"
                  :rules="[
                    {
                      required: true,
                      type: 'array',
                      size: item.quantity,
                      message: $t('field_are_required', { field: $tc('serial_number', 2) }),
                      trigger: 'change',
                    },
                  ]"
                  v-if="item.serials.length"
                >
                  <Select v-model="item.selected.serials" multiple style="width: 100%;">
                    <Option :value="serial.id" :key="'serial_' + si" v-for="(serial, si) in item.serials">
                      {{ serial.number }}
                    </Option>
                  </Select>
                </FormItem>
              </Col>
            </template>
            <template v-if="item.variants && item.variants.length">
              <Col :sm="24" :md="24" :lg="24">
                <Divider dashed orientation="left">
                  <small style="color: #aaa;">
                    {{ $tc('variant', 2) }}
                  </small>
                </Divider>
              </Col>
              <Col :sm="24" :md="12" :lg="12" v-for="(v, i) in item.variants" :key="'sv_' + i">
                <FormItem
                  :label="v.name"
                  :prop="'meta.' + v.name"
                  :rules="{
                    required: true,
                    trigger: 'change',
                    message: $t('select_x', { x: $tc('variant') }),
                  }"
                >
                  <Select v-model="item.meta[v.name]" @on-change="selectVariant">
                    <Option :key="opt" :value="opt" v-for="opt in v.options">{{ opt }}</Option>
                  </Select>
                </FormItem>
              </Col>
              <Col :sm="24" :md="12" :lg="12" v-if="item.variants.length % 2 == 1"></Col>
              <Col :sm="24" :md="24" :lg="24">
                <Alert type="info" show-icon class="mb16" v-if="show">
                  <strong>{{ this.$t('available_x', { x: this.$t('quantity') }) }}: {{ this.qty }}</strong>
                </Alert>
              </Col>
            </template>
          </Row>
        </Form>
        <div class="text-right">
          <Button long type="primary" :disabled="item.quantity < 1" @click="variantSeleted">
            {{ this.$t('add') }}
          </Button>
        </div>
      </div>
    </Modal>
    <Modal :width="600" :footer-hide="true" v-model="show_item_edit" :title="edit_item ? $t('edit') + ' - ' + edit_item.name : ''">
      <div v-if="edit_item">
        <Form ref="editItemForm" :model="edit_item" label-position="top">
          <Row :gutter="16">
            <Col :sm="24" :md="24" :lg="24" v-if="!edit_item.has_variants">
              <FormItem :label="$t('quantity')" class="text-center">
                <InputNumber size="large" v-model="edit_item.quantity" />
              </FormItem>
            </Col>

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
              <Col :sm="24" :md="24" :lg="24"></Col>
            </template>

            <Divider dashed orientation="left" style="margin-top: 0;">
              <small style="color: #aaa;">
                {{ $t('general') }}
              </small>
            </Divider>
          </Row>
          <FormItem :label="$t('comment')">
            <Input
              type="textarea"
              v-model="edit_item.comment"
              :placeholder="$t('item_comment')"
              :autosize="{ minRows: 2, maxRows: 5 }"
            ></Input>
          </FormItem>
          <FormItem>
            <Button long type="primary" @click="updateItem()">{{ $t('update') }}</Button>
          </FormItem>
        </Form>
        <Button long type="error" @click="deleteItem(edit_item)">{{ $t('remove_from_order') }}</Button>
      </div>
    </Modal>
  </div>
</template>

<script>
import Form from '@mpsjs/mixins/Form';
import _isEqual from 'lodash/isEqual';
import _debounce from 'lodash/debounce';
import Attachment from '@mpsjs/mixins/Attachment';
import SelectVariationComponent from '@mpscom/helpers/SelectVariationComponent';

const formatRes = (data, vm) => {
  data.items = data.items.map(i => {
    i.item.id = i.id;
    i.item.item_id = i.item_id;
    i.item.stock_transfer_item_id = i.id;
    i.item.quantity = parseFloat(i.quantity);
    i.item.stock_transfer_id = i.stock_transfer_id;
    i.item.selected = { variations: [], serials: [] };
    if (i.variations.length) {
      i.item.selected.variations = i.variations.map(v => {
        let o = i.item.variations.find(o => v.id == o.id);
        let variation = {
          id: v.id,
          meta: v.meta,
          quantity: parseFloat(v.pivot.quantity),
          available: parseFloat(v.pivot.quantity + o.stock[0].quantity),
        };
        return variation;
      });
    }
    return i.item;
  });
  vm.attachments = data.attachments && data.attachments.length ? [...data.attachments] : [];
  vm.form = { ...data };
  return vm.form;
};
export default {
  components: { SelectVariationComponent },
  mixins: [Attachment, Form('transfer', 'app/transfers/stock', false, formatRes)],
  data() {
    const toLocation = (rule, value, callback) => {
      if (!value || value == '') {
        callback(new Error(this.$t('field_is_required', { field: this.$t('to_location') })));
      } else if (value == this.form.from) {
        callback(new Error(this.$t('same_location_error')));
      } else {
        callback();
      }
    };
    const fromLocation = (rule, value, callback) => {
      if (!value || value == '') {
        callback(new Error(this.$t('field_is_required', { field: this.$t('from_location') })));
      } else if (value == this.form.to) {
        callback(new Error(this.$t('same_location_error')));
      } else {
        callback();
      }
    };
    return {
      query: '',
      item: null,
      result: [],
      qty: false,
      show: false,
      locations: [],
      loading: false,
      cloading: false,
      edit_item: null,
      searching: false,
      attachments: null,
      variantModal: false,
      show_item_edit: false,
      form: {
        id: '',
        to: '',
        from: '',
        items: [],
        details: '',
        reference: '',
        attachments: [],
        status: 'pending',
      },
      rules: {
        to: [{ required: true, validator: toLocation, trigger: 'change' }],
        from: [{ required: true, validator: fromLocation, trigger: 'change' }],
        status: [{ required: true, message: this.$t('field_is_required', { field: this.$t('status') }), trigger: 'change' }],
        date: [
          {
            type: 'date',
            required: true,
            trigger: 'change',
            message: this.$t('field_is_required', { field: this.$t('date') }),
          },
        ],
      },
    };
  },
  created() {
    this.$http
      .get('app/locations/search')
      .then(res => (this.locations = res.data))
      .finally(() => (this.loading = false));
  },
  methods: {
    fetch(id) {
      this.$http
        .get(`app/transfers/stock/${id}`)
        .then(res => formatRes(res.data, this))
        .finally(() => (this.loading = false));
    },
    selectItem(item) {
      this.query = '';
      this.addToOrder(JSON.parse(item));
    },
    searchItems(search) {
      if (!search.includes('(')) {
        this.getItems(search, this);
      }
    },
    getItems: _debounce((search, vm) => {
      vm.searching = true;
      const search_delay = vm.$store.getters.search_delay;
      vm.$http
        .get('app/items_wt/search?q=' + search + '&location=' + vm.form.from)
        .then(res => {
          if (res.data.length == 1) {
            vm.query = '';
            // vm.result = [];
            vm.addToOrder(res.data[0]);
          } else {
            vm.result = res.data;
          }
        })
        .finally(() => (vm.searching = false));
    }, search_delay || 250),
    // variationSubmitted(v) {
    //   let qty = v.quantity ? v.quantity : 1;
    //   this.addToOrder(v, qty);
    //   this.variantModal = false;
    // },
    addToOrder(item, qty, c, set) {
      this.qty = 0;
      if (!item.selected) {
        item.selected = { variations: [], serials: [] };
      }
      if (!item.meta) {
        item.meta = {};
      }

      this.cloading = true;
      if (
        !c &&
        ((item.serials && item.serials.length) ||
          (item.variants && item.variants.length > 0 && item.variations && item.variations.length > 0))
      ) {
        this.item = JSON.parse(JSON.stringify(item));
        this.variantModal = true;
        return false;
      }
      let nItem = {};
      let exists =
        this.form.items.length > 0
          ? this.form.items.find(
              i => i.id == item.id && (!i.variation || (i.variation && item.variation && i.variation.id == item.variation.id))
            )
          : false;
      if (exists) {
        exists.quantity = set ? parseFloat(qty) : parseFloat(exists.quantity) + (qty ? parseFloat(qty) : 1);
        if (item.selected_variation) {
          exists.selected.variations.push(item.selected_variation);
        }
        this.alert(this.$root.$t('cart_item_updated', { item: item.name }));
        // this.playSound(true);
      } else {
        if (item.unit) {
          item.allUnits = [{ ...item.unit, subunits: null }, ...item.unit.subunits];
        }

        if (item.selected.variations.length) {
          item.selected.variations.push(item.selected_variation);
        }
        nItem = {
          ...item,
          id: '',
          comment: '',
          item_id: item.id,
          guid: this.guid(),
          selected: { ...item.selected, variations: item.selected_variation ? [{ ...item.selected_variation }] : [] },
          quantity: qty ? parseFloat(qty) : 1,
        };
        this.form.items.push(nItem);
        this.alert(this.$root.$t('added_to_cart', { item: nItem.name }));
        // this.playSound(true);
      }
      this.cloading = false;

      this.$nextTick(() => {
        this.query = '';
        document.querySelector('#scan_barcode').focus();
      });
    },
    selectVariant() {
      let variation = this.item.variations.find(v => _isEqual(v.meta, this.item.meta));
      if (variation) {
        this.qty = this.formatDecimal(variation.stock[0].quantity);
        this.show = true;
        this.item.variation_id = variation.id;
      }
    },
    variantSeleted() {
      this.$refs.vform.validate(valid => {
        if (valid) {
          let qty = this.item.quantity ? this.item.quantity : 1;
          if (this.item.serials.length && this.item.selected.serials.length != this.item.quantity) {
            this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
            return false;
          }
          if (this.item.variation_id) {
            let selectedVariation = this.item.variations ? this.item.variations.find(v => v.id == this.item.variation_id) : null;
            let variation = selectedVariation
              ? {
                  quantity: qty,
                  id: selectedVariation.id,
                  meta: selectedVariation.meta,
                  available: this.formatDecimal(selectedVariation.stock[0].quantity),
                }
              : null;
            this.item.selected_variation = selectedVariation ? variation : null;
          } else {
            this.item.selected_variation = null;
          }

          // this.variationSubmitted(this.item);
          this.addToOrder(this.item, qty, true);
          this.variantModal = false;
          this.$nextTick(() => {
            this.$refs.vform.resetFields();
          });
        } else {
          this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
        }
      });
    },
    editItem(index) {
      if (!this.form.items[index].guid) {
        this.form.items[index].guid = this.guid();
      }

      this.edit_item = JSON.parse(JSON.stringify(this.form.items[index]));
      this.show_item_edit = true;
    },
    updateItem() {
      // TODO Manually check the edit_item
      this.$refs.editItemForm.validate(valid => {
        if (valid) {
          if (this.edit_item.serials.length && this.edit_item.selected.serials.length != this.edit_item.quantity) {
            this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
            return false;
          }
          if (this.edit_item.selected.variations.length) {
            this.edit_item.selected_variation = null;
            this.edit_item.quantity = this.edit_item.selected.variations.reduce((a, p) => a + parseFloat(p.quantity), 0);
          }
          this.$nextTick(() => {
            let item = JSON.parse(JSON.stringify(this.edit_item));
            this.form.items = this.form.items.map(i => (i.guid == item.guid ? item : i));
            this.addToOrder(item, item.quantity, true, true);
            this.edit_item = null;
            this.show_item_edit = false;
          });
        } else {
          this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
        }
      });
    },
    deleteItem(item) {
      this.form.items = this.form.items.filter(i => i.guid !== item.guid);
      this.edit_item = null;
      this.show_item_edit = false;
    },
    deleteItemVariation(row, variation) {
      let item = this.form.items.find(i => i.guid == row.guid);
      item.selected_variation = null;
      item.selected.variations = item.selected.variations.filter(v => v.id !== variation.id);
      item.quantity = item.selected.variations.reduce((a, v) => a + parseFloat(v.quantity), 0);
      if (item.selected.variations.length <= 0) {
        this.deleteItem(item);
      }
    },
    itemVariationQuantityChanged(item) {
      item.selected_variation = null;
      item.quantity = item.selected.variations.reduce((a, v) => a + parseFloat(v.quantity), 0);
      this.addToOrder(item, item.quantity, true, true);
    },
    addTransfer(page, stay) {
      // this.handleSubmit(page, stay);
      this.$refs.transfer.validate(valid => {
        if (valid) {
          this.loading = true;
          // this.submit(page, stay);
          let post = 'app/transfers/stock';
          let msg = 'added';
          this.errors.message = '';
          let msg_text = 'record_added';
          if (this.form.id && this.form.id != '') {
            msg = 'updated';
            post = 'app/transfers/stock/' + this.form.id;
            msg_text = 'record_updated';
            this.form['_method'] = 'PUT';
          }
          let data = { ...this.form };
          if (data.date) {
            data.date = this.$moment(data.date).format(this.$moment.HTML5_FMT.DATE);
          }
          // if (data.from_location) {
          //   data.from = data.from_location.id;
          //   delete data.from_location;
          // }
          // if (data.to_location) {
          //   data.to = data.to_location.id;
          //   delete data.to_location;
          // }
          // if (data.user) {
          //   delete data.user;
          // }
          data.items = data.items.map(i => {
            let item = {
              id: i.id,
              code: i.code,
              name: i.name,
              item_id: i.item_id,
              quantity: i.quantity,
              variations: i.selected.variations.map(v => {
                let iv = {};
                iv[v.id] = { quantity: v.quantity };
                return iv;
              }),
              serials: i.selected.serials,
            };
            return item;
          });

          if (this.attributes && this.attributes.length > 0) {
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
                this.$Notice.success({ title: this.$tc('transfer') + ' ' + this.$t(msg), desc: this.$t(msg_text) });
                if (stay) {
                  if (msg == 'updated') {
                    if (fnFormat) {
                      this.form = fnFormat(res.data.data, this);
                    }
                  } else {
                    this.handleReset();
                  }
                } else {
                  this.$router.push({ name: page + '.list' });
                }
              } else {
                this.$Notice.error({ title: this.$t('failed'), desc: this.$t('failed_error_text') });
              }
            })
            .catch(error => (this.errors = error))
            .finally(() => (this.loading = false));
        } else {
          this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
        }
      });
    },
  },
};
</script>
