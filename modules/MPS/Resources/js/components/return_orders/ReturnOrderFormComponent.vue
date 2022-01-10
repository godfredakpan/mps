<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('return_order') }}</p>
      <router-link to="/return_orders" slot="extra">
        <Icon type="ios-grid-outline" />
        {{ $t('list') }} {{ $tc('return_order', 2) }}
      </router-link>
      <div>
        <Form ref="return_order" :model="form" :rules="rules" :label-width="150" class="form-responsive">
          <Row :gutter="16">
            <Col :sm="24" :md="24" :lg="24">
              <Loading v-if="loading" />
              <Alert type="error" show-icon class="mb26" v-if="errors.message">
                <div v-html="errors.message"></div>
              </Alert>
              <Row :gutter="16">
                <Col :sm="24" :md="12" :lg="12">
                  <FormItem :label="$t('type')" prop="type" :error="errors.form.type | a2s">
                    <Select v-model="form.type" placeholder="">
                      <Option value="sale">{{ $tc('sale') }}</Option>
                      <Option value="purchase">{{ $tc('purchase') }}</Option>
                    </Select>
                  </FormItem>
                  <FormItem :label="$t('date')" prop="date" :error="errors.form.date | a2s">
                    <DatePicker type="date" v-model="form.date" format="yyyy-MM-dd" style="width: 100%;" />
                  </FormItem>
                  <FormItem :label="$t('reference')" prop="reference" :error="errors.form.reference | a2s">
                    <Input v-model="form.reference" />
                  </FormItem>
                  <FormItem v-if="form.type == 'sale'" :label="$tc('customer')" prop="customer_id" :error="errors.form.customer_id | a2s">
                    <Select
                      remote
                      clearable
                      filterable
                      :loading="searching"
                      v-model="form.customer_id"
                      :remote-method="searchCustomers"
                      :placeholder="$t('type_to_search', { x: $tc('customer') })"
                    >
                      <Option v-for="(option, index) in customers" :value="option.value" :key="index + option.value">{{
                        option.label
                      }}</Option>
                    </Select>
                  </FormItem>
                  <FormItem
                    prop="supplier_id"
                    :label="$tc('supplier')"
                    v-if="form.type == 'purchase'"
                    :error="errors.form.supplier_id | a2s"
                  >
                    <Select
                      remote
                      clearable
                      filterable
                      :loading="searching"
                      v-model="form.supplier_id"
                      :remote-method="searchSuppliers"
                      :placeholder="$t('type_to_search', { x: $tc('supplier') })"
                    >
                      <Option v-for="(option, index) in suppliers" :value="option.value" :key="index + option.value">{{
                        option.label
                      }}</Option>
                    </Select>
                  </FormItem>
                  <!-- <FormItem :label="$t('account')" prop="account">
                    <Input v-model="form.account" />
                  </FormItem> -->
                </Col>
                <Col :sm="24" :md="12" :lg="12">
                  <FormItem :label="$t('order_taxes')" prop="order_taxes">
                    <Select v-model="form.taxes" multiple style="width: 100%;">
                      <Option v-for="option in taxes" :value="option.id" :key="'tax_' + option.id">
                        {{ option.name }}
                      </Option>
                    </Select>
                  </FormItem>
                  <!-- <FormItem :label="$t('shipping_fee')" prop="shipping_fee" :error="errors.form.shipping_fee | a2s">
                    <InputNumber v-model="form.shipping_fee" />
                  </FormItem> -->
                  <span v-if="$store.state.settings.max_discount > 0">
                    <FormItem :label="$t('discount')" prop="discount" :error="errors.form.discount | a2s">
                      <InputNumber
                        :min="0"
                        v-model="form.discount"
                        :formatter="value => `${value}%`"
                        :parser="value => value.replace('%', '')"
                        :max="parseFloat($store.state.settings.max_discount)"
                      />
                    </FormItem>
                    <FormItem class="mb16">
                      <RadioGroup v-model="form.discount_method" vertical>
                        <Radio label="order">{{ $t('apply_to_order_amount') }}</Radio>
                        <Radio label="items">{{ $t('apply_to_order_items') }}</Radio>
                      </RadioGroup>
                    </FormItem>
                  </span>
                </Col>
              </Row>

              <div v-if="form.type" class="order-contents">
                <!-- <Affix :offset-top="50" @on-change="change"> -->
                <div class="affix-content">
                  <AutoComplete
                    ref="scanCode"
                    v-model="query"
                    icon="ios-search"
                    @on-change="searchItems"
                    element-id="scan_barcode"
                    @on-select="selectSaleItem"
                    :placeholder="$t('search_scan_barcode')"
                  >
                    <Option v-for="r in result" :value="r.id" :key="r.id"> {{ r.name }} ({{ r.code }}) </Option>
                  </AutoComplete>
                </div>
                <!-- </Affix> -->

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
                      <span class="price">{{ $t(form.type == 'sale' ? 'price' : 'cost') }}</span>
                      <span class="discount">{{ $t('discount') }}</span>
                      <span class="quantity">{{ $t('quantity') }}</span>
                      <span class="taxes">{{ $t('taxes') }}</span>
                      <span class="subtotal">{{ $t('subtotal') }}</span>
                    </div>
                    <template v-if="form.items && form.items.length">
                      <template v-for="(row, index) in form.items">
                        <row-item-component
                          :row="row"
                          :field="field"
                          :index="index"
                          :customer="customer"
                          :editItem="editItem"
                          :deleteItem="deleteItem"
                          :calcItemTax="calcItemTax"
                          :calcRowTotal="calcRowTotal"
                          :key="'row_' + index + '_' + row.id"
                          :deleteItemVariation="deleteItemVariation"
                          :itemVariationQuantityChanged="itemVariationQuantityChanged"
                        />
                      </template>
                    </template>
                  </div>
                </div>

                <!-- <Affix :offset-bottom="0" @on-change="change"> -->
                <div class="affix-content">
                  <Alert banner type="warning" style="margin: 16px 0 0; padding: 8px 16px;">
                    <Row :gutter="16">
                      <Col :sm="12" :md="6">
                        {{ $tc('item', 2) }}:
                        <span class="float-right">
                          <strong>{{ orderTotalItems }}</strong>
                          {{ $tc('item', orderTotalItems) }} <strong>{{ orderTotalQuantity }}</strong>
                          {{ $t('quantity') }}
                        </span>
                      </Col>
                      <Col :sm="12" :md="6">
                        <Divider type="vertical" class="hidden-sm" />
                        {{ $t('discount') }}:
                        <strong class="float-right">
                          {{ (calculateItemDiscountAmount + orderDiscount) | formatNumber($store.state.settings.decimals) }}
                        </strong>
                      </Col>
                      <Col :sm="12" :md="6">
                        <Divider type="vertical" class="hidden-sm" />
                        {{ $tc('tax') }}:
                        <strong class="float-right">
                          {{ totalItemTax | formatNumber($store.state.settings.decimals) }}
                        </strong>
                      </Col>
                      <Col :sm="12" :md="6">
                        <Divider type="vertical" class="hidden-sm" />
                        {{ $t('payable_amount') }}:
                        <strong class="float-right">
                          <span v-if="orderOriginalTotal != orderPayableAmount">
                            <del>
                              {{ orderOriginalTotal | formatNumber($store.state.settings.decimals) }}
                            </del>
                          </span>
                          <span>
                            <span v-if="orderDiscount">
                              <span style="font-weight: normal;">
                                ({{ orderTotalAmount | formatNumber($store.state.settings.decimals) }} -
                                {{ orderDiscount | formatNumber($store.state.settings.decimals) }})
                              </span>
                              {{ orderPayableAmount | formatNumber($store.state.settings.decimals) }}
                            </span>
                            <span v-else>
                              {{ orderPayableAmount | formatNumber($store.state.settings.decimals) }}
                            </span>
                          </span>
                        </strong>
                      </Col>
                    </Row>
                  </Alert>
                  <!-- </Affix> -->
                </div>
              </div>

              <form-custom-fields v-model="form" :attributes="attributes" @update="updateCF" />
              <attachments-component :error="errors.form.attachments | a2s" @selected="handleUpload" @clear="clearAttachments">
                <list-attachments-component :attachments="attachments" @remove="deleteAttachment" />
              </attachments-component>
              <FormItem :label="$t('details')" prop="details">
                <Input type="textarea" v-model="form.details" :autosize="{ minRows: 2, maxRows: 5 }" />
              </FormItem>
              <span v-if="!form.id">
                <FormItem prop="payment" class="mb0">
                  <Checkbox v-model="form.payment" true-value="1" false-value="0">
                    <span>{{ $t('auto_payment_text') }}</span>
                  </Checkbox>
                </FormItem>
                <FormItem prop="deduct_from_register">
                  <Checkbox v-model="form.deduct_from_register" true-value="1" false-value="0">
                    <span>{{ $t('deduct_from_register') }}</span>
                  </Checkbox>
                </FormItem>
              </span>
              <FormItem>
                <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('return_orders')">
                  <span v-if="!saving">{{ $t('submit') }}</span>
                  <span v-else>{{ $t('saving') }}...</span>
                </Button>
                <Button
                  ghost
                  type="primary"
                  :loading="saving"
                  :disabled="saving"
                  style="margin-left: 8px;"
                  @click="handleSubmit('return_orders', true)"
                >
                  <span v-if="!saving">{{ $t('save_n_stay') }}</span>
                  <span v-else>{{ $t('saving') }}...</span>
                </Button>
                <Button type="warning" ghost @click="handleReset()" style="margin-left: 8px;">{{ $t('reset') }}</Button>
              </FormItem>
            </Col>
          </Row>
        </Form>
        <Modal :width="600" :footer-hide="true" v-model="show_item_edit" :title="edit_item ? $t('edit') + ' - ' + edit_item.name : ''">
          <div v-if="edit_item">
            <Form ref="editItemForm" :model="edit_item" label-position="top">
              <edit-row-item-component
                :taxes="taxes"
                :field="field"
                :edit_item="edit_item"
                :itemPrice="itemPrice"
                :deleteItem="deleteItem"
                :updateItem="updateItem"
                :itemDiscount="itemPrice"
                :modOptLabel="modOptLabel"
                :itemUnitChanged="itemUnitChanged"
                :addPortionToEditItem="addPortionToEditItem"
                :changeSelectedPortion="changeSelectedPortion"
              />
            </Form>
          </div>
        </Modal>
      </div>
    </Card>
    <Modal
      :footer-hide="true"
      :mask-closable="false"
      v-model="variantModal"
      :title="$t('select_x', { x: $tc('portion') + ', ' + $tc('modifier', 2) + ' & ' + $tc('variant', 2) })"
    >
      <select-variation-component :item="item" ref="selectVarMod" @on-submit="variationSubmitted"></select-variation-component>
    </Modal>
  </div>
</template>

<script>
import Order from '@mpsjs/mixins/Order';
import OrderHelpers from '@mpsjs/mixins/OrderHelpers';
import RowItemComponent from './sub/RowItemComponent';
import EditRowItemComponent from './sub/EditRowItemComponent';

export default {
  components: { EditRowItemComponent, RowItemComponent },
  mixins: [Order('return_order'), OrderHelpers('price', 'form', false)],
  watch: {
    'form.type': function(type) {
      this.field = type == 'sale' ? 'price' : 'cost';
    },
  },
  created() {
    this.form.type = 'sale';
  },
};
</script>
