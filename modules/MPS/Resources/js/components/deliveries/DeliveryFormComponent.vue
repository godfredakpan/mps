<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('delivery') }}</p>
      <router-link to="/deliveries" slot="extra">
        <Icon type="ios-grid-outline" />
        {{ $t('list') }} {{ $tc('delivery', 2) }}
      </router-link>
      <div>
        <Form ref="delivery" :model="form" :rules="rules" :label-width="150" class="form-responsive">
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
                  <FormItem :label="$t('status')" prop="status" :error="errors.form.status | a2s">
                    <Select v-model="form.status" placeholder>
                      <Option value="preparing">{{ $t('preparing') }}</Option>
                      <Option value="delivering">{{ $t('delivering') }}</Option>
                      <Option value="delivered">{{ $t('delivered') }}</Option>
                    </Select>
                  </FormItem>
                  <FormItem
                    prop="delivered_by"
                    :label="$tc('delivered_by')"
                    :error="errors.form.delivered_by | a2s"
                    :rules="{
                      trigger: 'change',
                      required: this.form.status == 'delivered',
                      message: this.$t('field_is_required', { field: this.$t('delivered_at') }),
                    }"
                  >
                    <Input v-model="form.delivered_by" />
                  </FormItem>
                  <FormItem
                    prop="delivered_at"
                    :label="$tc('delivered_at')"
                    :error="errors.form.delivered_at | a2s"
                    :rules="{
                      trigger: 'change',
                      required: this.form.status == 'delivered',
                      message: this.$t('field_is_required', { field: this.$t('delivered_at') }),
                    }"
                  >
                    <Input v-model="form.delivered_at" />
                  </FormItem>
                </Col>
                <Col :sm="24" :md="12" :lg="12">
                  <FormItem :label="$tc('reference')" prop="reference" :error="errors.form.reference | a2s">
                    <Input v-model="form.reference" />
                  </FormItem>
                  <FormItem :label="$tc('driver')" prop="driver" :error="errors.form.driver | a2s">
                    <Input v-model="form.driver" />
                  </FormItem>
                  <FormItem
                    prop="received_by"
                    :label="$tc('received_by')"
                    :error="errors.form.received_by | a2s"
                    :rules="{
                      trigger: 'change',
                      required: this.form.status == 'delivered',
                      message: this.$t('field_is_required', { field: this.$t('received_by') }),
                    }"
                  >
                    <Input v-model="form.received_by" />
                  </FormItem>
                </Col>
              </Row>
              <transition
                mode="out-in"
                name="slide-in"
                enter-active-class="animate__animated faster animate__fadeInDown"
                leave-active-class="animate__animated fastest animate__fadeOutDown"
              >
                <div class="order-contents">
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
                        <span class="details">{{ $t('description') }}</span>
                        <span class="quantity">{{ $t('quantity') }}</span>
                      </div>
                      <div class="order-item" :key="index + '_' + row.id" v-for="(row, index) in form.items">
                        <span class="item">
                          <span class="index">
                            <span class="number-rmdash">{{ index + 1 }}</span>
                            <!-- <span class="delete_link">
                              <Icon size="18" type="md-close" class="" color="#ed4014" @click="deleteItem(row)" />
                            </span> -->
                          </span>
                          <span class="details">
                            <strong>{{ row.name }}</strong>
                          </span>
                          <span class="quantity">
                            <!-- :readonly="!!row.selected.variations.length || !!row.selected.portions.length" -->
                            <InputNumber
                              :readonly="true"
                              v-model="row.quantity"
                              :size="!!row.selected.variations.length || !!row.selected.portions.length ? 'small' : 'default'"
                            ></InputNumber>
                          </span>
                        </span>
                        <template v-if="row.selected.variations && row.selected.variations.length">
                          <div class="combo-item variation" :key="'svi_' + svi" v-for="(sv, svi) in row.selected.variations">
                            <span class="index">
                              <span class="number-rmdash"></span>
                              <!-- <span class="delete_link">
                                <Icon size="18" class="pointer" color="#ed4014" type="md-close" @click="remove(row, 'Variation', sv)" />
                              </span> -->
                            </span>
                            <span class="details leading-medium">
                              {{ svi + 1 }}.
                              <span v-html="metaString(sv.meta)"></span>
                            </span>
                            <span class="quantity">
                              <InputNumber
                                :readonly="true"
                                v-model="sv.quantity"
                                @on-change="itemVariationQuantityChanged(form.items[index])"
                              ></InputNumber>
                            </span>
                          </div>
                        </template>
                        <template v-if="row.selected.portions.length">
                          <div class="combo" v-for="(sp, spi) in row.selected.portions" :key="'spi_' + spi">
                            <div class="combo" v-for="p in row.portions.filter(ip => ip.id == sp.id)" :key="'pi_' + p.id">
                              <div class="combo-item variation">
                                <span class="index">
                                  <span class="number-rmdash"></span>
                                  <!-- <span class="delete_link">
                                    <Icon
                                      size="18"
                                      class="pointer"
                                      color="#ed4014"
                                      type="md-close"
                                      @click="remove(row, 'Portion', sp, spi)"
                                    />
                                  </span> -->
                                </span>
                                <span class="details leading-medium">
                                  <p>
                                    {{ spi + 1 }}. {{ $tc('portion') }}:
                                    <strong>{{ p.name == 'regular' ? $t('regular') : p.name }}</strong>
                                  </p>
                                </span>
                                <span class="quantity">
                                  <InputNumber
                                    :readonly="true"
                                    v-model="sp.quantity"
                                    @on-change="itemPortionQuantityChanged(form.items[index])"
                                  ></InputNumber>
                                </span>
                              </div>
                              <div :key="'ei_' + ei" class="combo-item bt0" style="padding-top: 1px;" v-for="(e, ei) in p.essentials">
                                <span class="remove"></span>
                                <span class="details"> #{{ ei + 1 }} {{ e.item.name }} </span>
                                <span class="quantity input">
                                  {{ parseFloat(e.quantity * sp.quantity) }}
                                </span>
                              </div>
                              <template v-for="(g, gi) in p.choosables">
                                <div
                                  class="combo-item bt0"
                                  style="padding-top: 1px;"
                                  :key="'gii_' + gii + '_' + gi"
                                  v-for="(gitem, gii) in g.items"
                                  v-if="getPortionChoosable(sp, g.id, gitem.item_id)"
                                >
                                  <span class="remove"></span>
                                  <span class="details">
                                    #{{ gi + 1 + p.essentials.length }}
                                    {{ gitem.item.name }}
                                  </span>
                                  <span class="quantity input">
                                    {{ parseFloat(gitem.quantity * sp.quantity) }}
                                  </span>
                                </div>
                              </template>
                            </div>
                          </div>
                        </template>
                        <template v-if="row.selected.modifiers.length">
                          <div class="combo-item bg">
                            <span class="index"> </span>
                            <span class="details">
                              <p style="font-weight: bold;">{{ $tc('modifier', 2) }}</p>
                            </span>
                            <span class="quantity">&nbsp;</span>
                          </div>
                          <div :key="'mi_' + mi" v-for="(m, mi) in row.selected.modifiers">
                            <div class="combo-item pt pb">
                              <span class="index">
                                <span class="number-rmdash"></span>
                                <!-- <span class="delete_link">
                                  <Icon size="18" class="pointer" color="#ed4014" type="md-close" @click="remove(row, 'Modifier', m, mi)" />
                                </span> -->
                              </span>
                              <span class="details leading-medium">
                                {{ mi + 1 }}. <strong>{{ m.option }}</strong> ({{ m.title }})
                              </span>
                              <span class="quantity">
                                <InputNumber :readonly="true" v-model="m.quantity"></InputNumber>
                              </span>
                            </div>
                          </div>
                        </template>
                      </div>
                    </div>
                  </div>
                </div>
              </transition>

              <form-custom-fields v-model="form" :attributes="attributes" @update="updateCF" />
              <attachments-component :error="errors.form.attachments | a2s" @selected="handleUpload" @clear="clearAttachments" />
              <FormItem :label="$t('details')" prop="details" :error="errors.form.details | a2s">
                <Input type="textarea" v-model="form.details" :autosize="{ minRows: 4, maxRows: 8 }" />
              </FormItem>

              <FormItem class="mb0">
                <Button type="primary" :loading="saving" :disabled="saving" @click="addDelivery('deliveries')">
                  <span v-if="!saving">{{ $t('submit') }}</span>
                  <span v-else>{{ $t('saving') }}...</span>
                </Button>
                <Button
                  ghost
                  type="primary"
                  :loading="saving"
                  :disabled="saving"
                  style="margin-left: 8px;"
                  @click="addDelivery('deliveries', true)"
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
  </div>
</template>

<script>
import Form from '@mpsjs/mixins/Form';
import AttachmentsComponent from '@mpscom/helpers/AttachmentsComponent';
const formatRes = (data, vm) => {
  data.items = data.items.map(i => {
    i.item.id = i.id;
    i.item.guid = vm.guid();
    i.item.item_id = i.item_id;
    i.item.unit_id = i.unit_id;
    i.item.batch_no = i.batch_no;
    i.item.delivery_item_id = i.id;
    i.item.sale_item_id = i.sale_item_id;
    i.item.item_unit_id = i.item.unit_id;
    i.item.quantity = parseFloat(i.quantity);
    i.item.expiry_date = new Date(i.expiry_date);
    i.item.selected = { modifiers: [], serials: [], variations: [], portions: [] };
    if (i.modifier_options && i.modifier_options.length) {
      i.item.selected.modifiers = i.modifier_options.map(o => {
        let m = i.item.modifiers.find(m => m.id == o.modifier_id);
        let option = m.options.find(mo => mo.id == o.id);
        let modifier = {
          id: o.id,
          meta: o.meta,
          title: m.title,
          mId: o.modifier_id,
          option: option.name,
          quantity: parseFloat(o.pivot.quantity),
        };
        return modifier;
      });
    }
    if (i.variations && i.variations.length) {
      i.item.selected.variations = i.variations.map(v => {
        let variation = {
          id: v.id,
          meta: v.meta,
          quantity: parseFloat(v.pivot.quantity),
        };
        return variation;
      });
    }
    if (i.portions && i.portions.length) {
      i.item.selected.portions = i.portions.map(p => {
        let portion = {
          id: p.id,
          name: p.name,
          quantity: parseFloat(p.pivot.quantity),
          choosables: p.pivot && p.pivot.choosables ? p.pivot.choosables.map(c => ({ id: c.id, selected: c.item_id })) : [],
        };
        return portion;
      });
    }
    return i.item;
  });
  if (data.date) {
    data.date = new Date(data.date);
  }
  if (data.delivered_at) {
    data.delivered_at = new Date(data.delivered_at);
  }
  vm.form = { ...data };
  return vm.form;
};
export default {
  components: { AttachmentsComponent },
  mixins: [Form('delivery', 'app/deliveries', false, formatRes)],
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
      saving: false,
      loading: false,
      searching: false,
      variantModal: false,
      form: {
        id: '',
        items: [],
        driver: '',
        sale_id: '',
        details: '',
        reference: '',
        attachments: [],
        customer_id: '',
        location_id: '',
        date: new Date(),
        delivered_at: '',
        delivered_by: '',
        status: 'preparing',
        new_attachments: [],
      },
      rules: {
        date: [{ required: true, type: 'date', message: this.$t('field_is_required', { field: this.$t('date') }), trigger: 'change' }],
        status: [{ required: true, message: this.$t('field_is_required', { field: this.$t('status') }), trigger: 'change' }],
      },
    };
  },
  created() {
    this.loading = true;
    if (this.$route.query.sale_id) {
      this.form.sale_id = this.$route.query.sale_id;
      this.$http
        .get('app/sales/' + this.form.sale_id)
        .then(res => {
          if (res.data.draft) {
            this.$Notice.warning({ title: this.$tc('draft'), desc: this.$t('order_draft_text') });
            this.$router.back();
          }
          this.form.customer_id = res.data.customer_id;
          this.form.location_id = res.data.location_id;
          this.form.items = res.data.items.map(i => {
            i.item.id = i.id;
            i.item.item_id = i.item_id;
            i.item.sale_item_id = i.id;
            i.item.unit_id = i.unit_id;
            i.item.batch_no = i.batch_no;
            i.item.item_unit_id = i.item.unit_id;
            i.item.quantity = parseFloat(i.quantity);
            i.item.expiry_date = new Date(i.expiry_date);
            i.item.selected = { modifiers: [], serials: [], variations: [], portions: [] };
            if (i.modifier_options && i.modifier_options.length) {
              i.item.selected.modifiers = i.modifier_options.map(o => {
                let m = i.item.modifiers.find(m => m.id == o.modifier_id);
                let option = m.options.find(mo => mo.id == o.id);
                let modifier = {
                  id: o.id,
                  meta: o.meta,
                  title: m.title,
                  mId: o.modifier_id,
                  option: option.name,
                  quantity: parseFloat(o.pivot.quantity),
                };
                return modifier;
              });
            }
            if (i.variations && i.variations.length) {
              i.item.selected.variations = i.variations.map(v => {
                let variation = {
                  id: v.id,
                  meta: v.meta,
                  quantity: parseFloat(v.pivot.quantity),
                };
                return variation;
              });
            }
            if (i.portions && i.portions.length) {
              i.item.selected.portions = i.portions.map(p => {
                let portion = {
                  id: p.id,
                  name: p.name,
                  quantity: parseFloat(p.pivot.quantity),
                  choosables: p.pivot && p.pivot.choosables ? p.pivot.choosables.map(c => ({ id: c.id, selected: c.item_id })) : [],
                };
                return portion;
              });
            }
            return i.item;
          });
        })
        .finally(() => (this.loading = false));
    } else if (!this.$route.params.id) {
      this.$router.push(this.$store.getters.previous('/'));
    }
  },
  methods: {
    fetch(id) {
      this.$http
        .get(`app/deliveries/${id}`)
        .then(res => formatRes(res.data, this))
        .catch(err => console.log(err))
        .finally(() => (this.loading = false));
    },
    renderFileList() {
      fileList.forEach((file, index) => {
        console.log(index + 1 + ': ' + file.name);
      });
    },
    addToOrder(item, qty, c, set) {
      this.qty = 0;
      if (!item.selected) {
        item.selected = { variations: [], serials: [], portions: [], modifiers: [] };
      }
      if (!item.meta) {
        item.meta = {};
      }

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
          selected: { ...item.selected },
          quantity: qty ? parseFloat(qty) : 1,
        };
        this.form.items.push(nItem);
        this.alert(this.$root.$t('added_to_cart', { item: nItem.name }));
        // this.playSound(true);
      }
    },

    deleteItem(item) {
      this.form.items = this.form.items.filter(i => i.guid !== item.guid);
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
    getPortionChoosable(p, gId, iId) {
      let group = p.choosables.find(g => g.id == gId);
      return group.selected == iId;
    },
    handleUpload(attachments) {
      this.form.attachments.push(attachments);
    },
    clearAttachments() {
      this.form.attachments = [];
      this.form.new_attachments = [];
    },
    addDelivery(page, stay) {
      // this.handleSubmit(page, stay);
      this.$refs.delivery.validate(valid => {
        if (valid) {
          this.saving = true;
          let post = `app/sales/${this.form.sale_id}/deliveries`;
          let msg = 'added';
          this.errors.message = '';
          let msg_text = 'record_added';
          if (this.form.id && this.form.id != '') {
            msg = 'updated';
            post = 'app/deliveries/' + this.form.id;
            msg_text = 'record_updated';
            this.form['_method'] = 'PUT';
          }
          post = post + (stay ? '?stay=1' : '');
          let data = { ...this.form };
          // if (data.location) {
          //   delete data.location;
          // }
          // if (data.user) {
          //   delete data.user;
          // }
          data.items = data.items.map(i => {
            // console.log(i.selected.portions);
            let item = {
              code: i.code,
              name: i.name,
              item_id: i.item_id,
              quantity: i.quantity,
              sale_item_id: i.sale_item_id,
              id: this.form.id ? i.id : '',
              selected: { ...i.selected },
              serials: i.selected.serials,
            };
            item.selected = { modifiers: [], variations: [], portions: [] };
            if (i.selected.modifiers.length) {
              item.selected.modifiers = i.selected.modifiers.map(m => ({
                id: m.mId,
                selected: m.id,
                quantity: m.quantity,
              }));
            }
            if (i.selected.portions.length) {
              item.selected.portions = i.selected.portions.map(p => ({
                id: p.id,
                quantity: p.quantity,
                choosables: p.choosables,
              }));
            }
            if (i.selected.variations.length) {
              item.selected.variations = [...i.selected.variations];
            }
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
          if (data.date) {
            data.date = this.$moment(data.date).format(this.$moment.HTML5_FMT.DATE);
          }
          if (data.delivered_at) {
            data.delivered_at = this.$moment(data.delivered_at).format(this.$moment.HTML5_FMT.DATE);
          }
          data = this.$form(data);
          this.$http
            .post(post, data)
            .then(res => {
              if (res.data.success) {
                this.$Notice.destroy();
                this.$Notice.success({ title: this.$tc('delivery') + ' ' + this.$t(msg), desc: this.$t(msg_text) });
                if (stay) {
                  if (msg == 'updated') {
                    this.form = formatRes(res.data.data, this);
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
            .finally(() => (this.saving = false));
        } else {
          this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
        }
      });
    },
  },
};
</script>
