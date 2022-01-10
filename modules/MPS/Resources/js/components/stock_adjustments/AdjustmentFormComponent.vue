<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">
        {{
          form.id
            ? $t('edit_x', { x: $tc('stock_adjustment') })
            : $t('add_x', { x: $tc('stock_adjustment') + ' (' + $store.getters.location.label + ')' })
        }}
      </p>
      <router-link to="/adjustments/stock" slot="extra">
        <Icon type="ios-grid-outline" />
        {{ $t('list') }} {{ $tc('stock_adjustment', 2) }}
      </router-link>
      <div>
        <Form ref="adjustment" :model="form" :rules="rules" :label-width="150" class="form-responsive">
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
                  <FormItem :label="$t('type')" prop="type" :error="errors.form.type | a2s">
                    <Select v-model="form.type" placeholder="">
                      <Option value="addition">{{ $t('addition') }}</Option>
                      <Option value="damage">{{ $t('damage') }}</Option>
                      <Option value="subtraction">{{ $t('subtraction') }}</Option>
                    </Select>
                  </FormItem>
                </Col>
                <Col :sm="24" :md="12" :lg="12">
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
                <div class="order-contents">
                  <div class="affix-content">
                    <AutoComplete
                      ref="scanCode"
                      v-model="query"
                      icon="ios-search"
                      @on-change="searchItems"
                      element-id="scan_barcode"
                      @on-select="selectPurchaseItem"
                      :placeholder="$t('search_scan_barcode')"
                    >
                      <Option v-for="r in result" :value="r.id" :key="r.id"> {{ r.name }} ({{ r.code }}) </Option>
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
                        <span style="width: 150px; text-align:center; padding: 0 10px;">{{ $t('cost') }}</span>
                        <span style="width: 100px; text-align:center; padding: 0 10px;">{{ $t('quantity') }}</span>
                        <span style="width: 100px; text-align:center; padding: 0 10px;">{{ $t('taxes') }}</span>
                        <span style="width: 200px; text-align:center; padding: 0 10px;">{{ $t('subtotal') }}</span>
                      </div>
                      <template v-if="form.items && form.items.length">
                        <template v-for="(row, index) in form.items">
                          <row-item-component
                            :row="row"
                            :index="index"
                            :editItem="editItem"
                            :deleteItem="deleteItem"
                            :calcRowTotal="calcRowTotal"
                            :key="'row_' + index + '_' + row.id"
                            :deleteItemVariation="deleteItemVariation"
                            :itemVariationQuantityChanged="itemVariationQuantityChanged"
                          />
                        </template>
                      </template>
                    </div>
                  </div>

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
              <FormItem v-if="is_draftable()" prop="draft">
                <Checkbox v-model="form.draft" true-value="1" false-value="0">
                  <span>{{ $t('order_draft_text') }}</span>
                </Checkbox>
              </FormItem>

              <FormItem>
                <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('stock_adjustments')">
                  <span v-if="!saving">{{ $t('submit') }}</span>
                  <span v-else>{{ $t('saving') }}...</span>
                </Button>
                <Button
                  ghost
                  type="primary"
                  :loading="saving"
                  :disabled="saving"
                  style="margin-left: 8px;"
                  @click="handleSubmit('stock_adjustments', true)"
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
      <span v-if="edit_item">
        <Form ref="editItemForm" :model="edit_item" :rules="itemRules">
          <edit-row-item-component
            :taxes="taxes"
            :edit_item="edit_item"
            :itemPrice="itemPrice"
            :itemDiscount="itemPrice"
            :deleteItem="deleteItem"
            :updateItem="updateItem"
            :itemUnitChanged="itemUnitChanged"
            :expiryDateOptions="expiry_date_options"
            :addPortionToEditItem="addPortionToEditItem"
            :changeSelectedPortion="changeSelectedPortion"
          />
        </Form>
      </span>
    </Modal>
    <Modal :footer-hide="true" :mask-closable="false" v-model="variantModal" :title="$t('select_x', { x: $tc('variant', 2) })">
      <select-variation-component :item="item" ref="selectVarMod" @on-submit="variationSubmitted" purchase></select-variation-component>
    </Modal>
  </div>
</template>

<script>
import Order from '@mpsjs/mixins/Order';
import OrderHelpers from '@mpsjs/mixins/OrderHelpers';
import RowItemComponent from './sub/RowItemComponent';
import EditRowItemComponent from './sub/EditRowItemComponent';

export default {
  mixins: [Order('adjustment', 'app/adjustments', 'cost', null, true), OrderHelpers('cost', 'form', false, true)],
  components: { EditRowItemComponent, RowItemComponent },
  data() {
    return {
      form: { type: 'addition' },
    };
  },
};
</script>
