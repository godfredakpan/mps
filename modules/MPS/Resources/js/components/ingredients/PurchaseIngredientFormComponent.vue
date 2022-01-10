<template>
  <Card :dis-hover="true">
    <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('purchase') }}</p>
    <router-link to="/purchases" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list') }} {{ $tc('purchase', 2) }}
    </router-link>
    <div>
      <Form ref="purchase" :model="form" :rules="rules" :label-width="150" class="form-responsive">
        <Row :gutter="16">
          <Col :sm="24" :md="24" :lg="24">
            <Loading v-if="loading" />
            <Alert type="error" show-icon class="mb26" v-if="errors.message">
              <div v-html="errors.message"></div>
            </Alert>
            <Row :gutter="16">
              <Col :sm="24" :md="12" :lg="12">
                <!-- <FormItem :label="$t('type')" prop="type" :error="errors.form.type | a2s">
                                    <Select v-model="form.type" placeholder="">
                                        <Option value="nett">{{ $t('nett') }}</Option>
                                        <Option value="layaway">{{ $t('layaway') }}</Option>
                                        <Option value="on_account">{{ $t('on_account') }}</Option>
                                    </Select>
                                </FormItem> -->
                <FormItem :label="$t('date')" prop="date" :error="errors.form.date | a2s">
                  <DatePicker type="date" v-model="form.date" format="yyyy-MM-dd" style="width: 100%;" />
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
                <FormItem :label="$t('reference')" prop="reference" :error="errors.form.reference | a2s">
                  <Input v-model="form.reference" />
                </FormItem>
                <FormItem :label="$t('order_taxes')" prop="order_taxes">
                  <Select v-model="form.taxes" multiple style="width: 100%;">
                    <Option v-for="option in taxes" :value="option.id" :key="'tax_' + option.id">
                      {{ option.name }}
                    </Option>
                  </Select>
                </FormItem>
                <!-- <FormItem :label="$t('account')" prop="account">
                                    <Input v-model="form.account" />
                                </FormItem> -->
              </Col>
              <Col :sm="24" :md="12" :lg="12">
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
                  @on-select="selectItem"
                  @on-change="searchItems"
                  element-id="scan_barcode"
                  :placeholder="$t('search_scan_barcode')"
                >
                  <Option v-for="r in result" :value="JSON.stringify(r)" :key="r.id"> {{ r.name }} ({{ r.code }}) </Option>
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
                <Table border :columns="columns" :data="form.items" class="table" style="margin-bottom: 16px;">
                  <template slot-scope="{ row, index }" slot="description">
                    <div class="pointer" @click="editItem(index)">
                      <p style="line-height: 1.4em;">
                        {{ row.name }} <span style="display: inline-block;">[ {{ row.code }} ]</span><br />
                        <small>
                          {{ row.discount ? $t('discount') + ': ' + row.discount + '% ' : '' }}
                          <span style="display: inline-block;">
                            @{{ row.cost | formatNumber($store.state.settings.decimals) }} x
                            {{ row.quantity | formatNumber($store.state.settings.decimals) }}
                          </span>
                        </small>
                      </p>
                    </div>
                  </template>

                  <template slot-scope="{ row, index }" slot="price">
                    <div class="text-right">{{ row.cost | formatNumber($store.state.settings.decimals) }}</div>
                    <!-- <InputNumber v-model="row.cost" :readonly="row.changeable == 1 ? false : true" /> -->
                  </template>

                  <template slot-scope="{ row, index }" slot="quantity">
                    <InputNumber v-model="form.items[index].quantity" />
                  </template>

                  <template slot-scope="{ row, index }" slot="taxes">
                    <div class="pointer" @click="editItem(index)">
                      <div v-for="tax in row.taxes" :key="tax.id + '_' + tax.id">
                        {{ tax.code }}
                        <span class="float-right">
                          {{ (tax.amount * row.quantity) | formatNumber($store.state.settings.decimals) }}
                        </span>
                      </div>
                    </div>
                    <!-- <div class="text-center">{{ row.taxes.map(t => t.name).join(', ') }}</div> -->
                  </template>

                  <template slot-scope="{ row, index }" slot="total">
                    <div class="text-right pointer" @click="editItem(index)">
                      {{ calcRowTotal(row) | formatNumber($store.state.settings.decimals) }}
                    </div>
                  </template>
                </Table>
              </div>

              <!-- <Affix :offset-bottom="0" @on-change="change"> -->
              <div class="affix-content">
                <Alert banner type="warning" style="margin: 16px 0 0; padding: 8px 16px;">
                  <Row :gutter="16" style="margin-bottom: 0;">
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
                      <strong class="float-right">{{
                        (itemsDiscountValue + orderDiscount) | formatNumber($store.state.settings.decimals)
                      }}</strong>
                    </Col>
                    <Col :sm="12" :md="6">
                      <Divider type="vertical" class="hidden-sm" />
                      {{ $tc('tax') }}:
                      <strong class="float-right">{{ totalItemTax | formatNumber($store.state.settings.decimals) }}</strong>
                    </Col>
                    <Col :sm="12" :md="6">
                      <Divider type="vertical" class="hidden-sm" />
                      {{ $t('payable_amount') }}:
                      <span class="float-right" style="font-weight: bold;">
                        <span v-if="itemsDiscountValue">
                          <del>{{ (orderTotalAmount + itemsDiscountValue) | formatNumber($store.state.settings.decimals) }}</del>
                        </span>
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
                    </Col>
                  </Row>
                </Alert>
              </div>
              <!-- </Affix> -->
            </div>

            <form-custom-fields v-model="form" :attributes="attributes" @update="updateCF" />

            <FormItem :label="$t('details')" prop="details">
              <Input type="textarea" v-model="form.details" :autosize="{ minRows: 2, maxRows: 5 }" />
            </FormItem>
            <FormItem v-if="is_draftable()" prop="draft" :class="{ mb0: form.draft != 1 }">
              <Checkbox v-model="form.draft" true-value="1" false-value="0">
                <span>{{ $t('order_draft_text') }}</span>
              </Checkbox>
            </FormItem>
            <transition
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
            </transition>
            <FormItem>
              <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('purchases')">
                <span v-if="!saving">{{ $t('submit') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button
                ghost
                type="primary"
                :loading="saving"
                :disabled="saving"
                style="margin-left: 8px;"
                @click="handleSubmit('purchases', true)"
              >
                <span v-if="!saving">{{ $t('save_n_stay') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button type="warning" ghost @click="handleReset()" style="margin-left: 8px;">{{ $t('reset') }}</Button>
            </FormItem>
          </Col>
        </Row>
      </Form>
      <Modal
        :width="300"
        :footer-hide="true"
        v-model="show_item_edit"
        @on-visible-change="editClose"
        :title="edit_item ? $t('edit') + ' - ' + edit_item.name : ''"
      >
        <span v-if="edit_item">
          <Form ref="editItem" :model="edit_item" :rules="itemRules">
            <FormItem :label="$t('cost')" prop="cost">
              <InputNumber v-model="edit_item.cost"></InputNumber>
            </FormItem>
            <FormItem :label="$t('quantity')" prop="quantity">
              <InputNumber v-model="edit_item.quantity"></InputNumber>
            </FormItem>
            <FormItem :label="$tc('tax', 2)" prop="taxes">
              <Select v-model="edit_item.allTaxes" multiple style="width: 100%;">
                <Option v-for="option in this.taxes" :value="option.id" :key="'tax_' + option.id">
                  {{ option.name }}
                </Option>
              </Select>
            </FormItem>
            <FormItem :label="$t('discount_')" prop="cdiscount">
              <InputNumber v-model="edit_item.discount"></InputNumber>
            </FormItem>
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
          <Button long type="error" @click="deleteItem(edit_item.guid)">{{ $t('remove_from_order') }}</Button>
        </span>
      </Modal>
    </div>
  </Card>
</template>

<script>
import Order from '@mpsjs/mixins/Order';
import OrderHelpers from '@mpsjs/mixins/OrderHelpers';
export default {
  mixins: [Order('purchase'), OrderHelpers('cost', 'form', false)],
  methods: {},
};
</script>
