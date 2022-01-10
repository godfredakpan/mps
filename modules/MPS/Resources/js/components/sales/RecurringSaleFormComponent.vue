<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('recurring_sale') }}</p>
      <router-link to="/sales/recurring" slot="extra">
        <Icon type="ios-grid-outline" />
        {{ $t('list') }} {{ $tc('recurring_sale', 2) }}
      </router-link>
      <div>
        <Form ref="recurring_sale" :model="form" :rules="rules" :label-width="150" class="form-responsive">
          <Row :gutter="16">
            <Col :sm="24" :md="24" :lg="24">
              <Loading v-if="loading" />
              <Alert type="error" show-icon class="mb26" v-if="errors.message">
                <div v-html="errors.message"></div>
              </Alert>
              <Row :gutter="16">
                <Col :sm="24" :md="12" :lg="12">
                  <FormItem :label="$t('start_date')" prop="start_date" :error="errors.form.start_date | a2s">
                    <DatePicker type="date" v-model="form.start_date" format="yyyy-MM-dd" style="width: 100%;" />
                  </FormItem>
                  <!-- </Col>
                            <Col :sm="24" :md="12" :lg="12"> -->
                  <FormItem :label="$t('repeat')" prop="repeat" :error="errors.form.repeat | a2s">
                    <Select v-model="form.repeat" placeholder="">
                      <Option value="daily">{{ $t('daily') }}</Option>
                      <Option value="weekly">{{ $t('weekly') }}</Option>
                      <Option value="monthly">{{ $t('monthly') }}</Option>
                      <Option value="quarterly">{{ $t('quarterly') }}</Option>
                      <Option value="semiannually">{{ $t('semiannually') }}</Option>
                      <Option value="annually">{{ $t('annually') }}</Option>
                      <Option value="biennially">{{ $t('biennially') }}</Option>
                      <Option value="triennially">{{ $t('triennially') }}</Option>
                    </Select>
                  </FormItem>
                  <!-- </Col>
                            <Col :sm="24" :md="12" :lg="12"> -->
                  <FormItem :label="$tc('customer')" prop="customer_id" :error="errors.form.customer_id | a2s">
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
                  <FormItem :label="$t('create_before')" prop="create_before" :error="errors.form.create_before | a2s">
                    <InputNumber v-model="form.create_before" />
                  </FormItem>
                  <FormItem :label="$t('reference')" prop="reference" :error="errors.form.reference | a2s">
                    <Input v-model="form.reference" />
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
                  <FormItem :label="$t('shipping_fee')" prop="shipping_fee" :error="errors.form.shipping_fee | a2s">
                    <InputNumber v-model="form.shipping_fee" />
                  </FormItem>
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
                    <FormItem class="mb0">
                      <RadioGroup v-model="form.discount_method" vertical>
                        <Radio label="order">{{ $t('apply_to_order_amount') }}</Radio>
                        <Radio label="items">{{ $t('apply_to_order_items') }}</Radio>
                      </RadioGroup>
                    </FormItem>
                  </span>
                </Col>
              </Row>
              <div class="order-contents">
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
                      <span class="price">{{ $t('price') }}</span>
                      <span class="discount">{{ $t('discount') }}</span>
                      <span class="quantity">{{ $t('quantity') }}</span>
                      <span class="taxes">{{ $t('taxes') }}</span>
                      <span class="subtotal">{{ $t('subtotal') }}</span>
                    </div>
                    <template v-if="form.items && form.items.length">
                      <template v-for="(row, index) in form.items">
                        <row-item-component
                          :row="row"
                          :index="index"
                          :remove="remove"
                          :customer="customer"
                          :editItem="editItem"
                          :calcItemTax="calcItemTax"
                          :calcRowTotal="calcRowTotal"
                          :key="'row_' + index + '_' + row.id"
                          :getPortionChoosable="getPortionChoosable"
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
              <!-- :class="{ mb0: form.draft != 1 }" -->
              <FormItem v-if="is_draftable()" prop="draft">
                <Checkbox v-model="form.draft" true-value="1" false-value="0">
                  <span>{{ $t('order_draft_text') }}</span>
                </Checkbox>
              </FormItem>
              <!-- <transition
              mode="out-in"
              name="slide-in"
              enter-active-class="animate__animated faster animate__fadeInDown"
              leave-active-class="animate__animated fastest animate__fadeOutDown"
            >
              <FormItem prop="payment" v-if="is_draftable() && form.draft != 1">
                <Checkbox v-model="form.payment" true-value="1" false-value="0">
                  <span>{{ $t('auto_payment_text') }}</span>
                </Checkbox>
              </FormItem>
            </transition> -->
              <FormItem>
                <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('recurring_sales')">
                  <span v-if="!saving">{{ $t('submit') }}</span>
                  <span v-else>{{ $t('saving') }}...</span>
                </Button>
                <Button
                  ghost
                  type="primary"
                  :loading="saving"
                  :disabled="saving"
                  style="margin-left: 8px;"
                  @click="handleSubmit('recurring_sales', true)"
                >
                  <span v-if="!saving">{{ $t('save_n_stay') }}</span>
                  <span v-else>{{ $t('saving') }}...</span>
                </Button>
                <Button type="warning" ghost @click="handleReset()" style="margin-left: 8px;">{{ $t('reset') }}</Button>
              </FormItem>
            </Col>
          </Row>
        </Form>
      </div>
    </Card>
    <Modal :width="600" :footer-hide="true" v-model="show_item_edit" :title="edit_item ? $t('edit') + ' - ' + edit_item.name : ''">
      <div v-if="edit_item">
        <Form ref="editItemForm" :model="edit_item" label-position="top">
          <edit-row-item-component
            :taxes="taxes"
            :edit_item="edit_item"
            :itemPrice="itemPrice"
            :deleteItem="deleteItem"
            :updateItem="updateItem"
            :itemDiscount="itemPrice"
            :modOptLabel="modOptLabel"
            :addPortionToEditItem="addPortionToEditItem"
            :changeSelectedPortion="changeSelectedPortion"
          />
        </Form>
      </div>
    </Modal>
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
  mixins: [Order('recurring_sale'), OrderHelpers('price', 'form', false)],
};
</script>
