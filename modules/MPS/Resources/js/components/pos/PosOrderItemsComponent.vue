<template>
  <div class="pos-order" id="pos-order" v-if="$store.getters.current && order">
    <div>
      <div class="above-order-items">
        <div class="order-info">
          <!-- <span class="details" style="margin-right:20px;">
            <a @click="extra = true" class="content bold">
              <span v-if="customer">
                {{ customer.label }}
              </span>
              <span v-else>
                {{ $t('select_x', { x: $tc('customer') }) }}
              </span>
            </a>
          </span> -->

          <Button @click="addCustomer()" type="text" size="small" style="margin:4px 0;padding:4px;height:auto">
            <Icon type="ios-add-circle" size="18" style="margin-top:2px" color="#19be6b" />
          </Button>
          <span class="details customer_selection" style="margin-right:20px;margin-left:8px;">
            <Select
              remote
              filterable
              :clearable="true"
              :loading="searching"
              v-model="order.customer_id"
              @on-change="customerChanged"
              :remote-method="searchCustomers"
              :placeholder="$t('type_to_search_x', { x: $tc('customer') })"
            >
              <Option v-for="option in result" :value="option.value" :key="option.value">{{ option.label }}</Option>
            </Select>
          </span>
          <span class="other">
            {{ $t('ref_tab') }}: <strong>{{ this.$store.getters.current }}</strong>
          </span>
        </div>
        <div class="order-sub-details">
          <span v-if="order.user" class="details">
            {{ $t('served_by') }}: <strong>{{ order.user.name }}</strong>
          </span>
          <span v-else class="details">
            {{ $t('served_by') }}:
            <strong>{{
              $store.getters.user && $store.getters.user.acting_user ? $store.getters.user.acting_user.name : $store.getters.user.name
            }}</strong>
          </span>
          <span class="other">
            <strong>{{ orderTotalItems }}</strong> {{ $tc('item', orderTotalItems) }} <strong>{{ orderTotalQuantity }}</strong>
            {{ $t('quantity') }}
          </span>
        </div>
      </div>
      <template v-if="order && order.items.length > 0">
        <div class="pos-order-items">
          <div class="order-items">
            <div class="header">
              <span class="index">#</span>
              <span class="details">{{ $t('decs') }}</span>
              <span class="quantity">{{ $t('qty') }}</span>
              <span class="subtotal">{{ $t('subtotal') }}</span>
            </div>
            <div class="pos-order-scroll scroll-x" ref="orderItems">
              <div class="order-item" :key="index + '_' + row.id" v-for="(row, index) in order.items">
                <span class="item">
                  <span class="index">
                    #{{ index + 1 }}
                    <!-- TODO make optional -->
                    <span
                      v-if="
                        row.selected.variations && row.selected.portions && !row.selected.portions.length && !row.selected.variations.length
                      "
                    >
                      <br />
                      <Icon type="md-close" size="16" class="pointer" color="#ed4014" @click="remove(row)" />
                    </span>
                  </span>
                  <span class="details pointer" @click="editItem(index)">
                    <strong>{{ row.name }}</strong>
                    <br />
                    <span>
                      <span
                        v-if="customer && customer.customer_group"
                        v-html="
                          customer.customer_group.name +
                            ' (<small>' +
                            $t('discount') +
                            ': ' +
                            customer.customer_group.discount +
                            '%</small>)<br />'
                        "
                      ></span>
                      <span
                        v-if="
                          row.selected.variations &&
                            row.selected.portions &&
                            !row.selected.portions.length &&
                            !row.selected.variations.length
                        "
                      >
                        <span v-if="row.discount_amount == 0">
                          @{{ row.price | formatNumber($store.state.settings.decimals) }}
                          <template v-if="calcItemTax(row) > 0">
                            <template v-if="row.tax_included == 1">
                              ({{ calcItemTax(row) | formatNumber($store.state.settings.decimals) }})
                            </template>
                            <template v-else> + {{ calcItemTax(row) | formatNumber($store.state.settings.decimals) }} </template>
                          </template>
                        </span>
                        <span v-else>
                          @<del>{{ row.price | formatNumber($store.state.settings.decimals) }}</del>
                          {{ (row.price - row.discount_amount) | formatNumber($store.state.settings.decimals) }}
                          <template v-if="calcItemTax(row) > 0">
                            <template v-if="row.tax_included == 1">
                              ({{ calcItemTax(row) | formatNumber($store.state.settings.decimals) }})
                            </template>
                            <template v-else> + {{ calcItemTax(row) | formatNumber($store.state.settings.decimals) }} </template>
                          </template>
                        </span>
                      </span>
                    </span>
                  </span>
                  <span class="quantity">
                    <InputNumber
                      v-model="row.quantity"
                      @on-change="itemQuantityChanged(order.items[index])"
                      :readonly="!!row.selected.variations.length || !!row.selected.portions.length"
                      :size="!!row.selected.variations.length || !!row.selected.portions.length ? 'small' : 'default'"
                    ></InputNumber>
                  </span>
                  <span class="subtotal">
                    <span v-if="row.selected.portions && !row.selected.portions.length">
                      {{ calcRowTotal(row) | formatNumber($store.state.settings.decimals) }}
                    </span>
                  </span>
                </span>
                <template v-if="row.selected.variations && row.selected.variations.length">
                  <div class="combo-item variation" :key="'svi_' + svi" v-for="(sv, svi) in row.selected.variations">
                    <span class="index">
                      <Icon size="16" class="pointer" color="#ed4014" type="md-close" @click="remove(row, 'Variation', sv)" />
                    </span>
                    <span class="details leading-medium">
                      <small>#{{ svi + 1 }}</small>
                      <span v-html="metaString(sv.meta)"></span>
                      <br />
                      <span v-if="sv.discount_amount == 0">
                        @{{ sv.price | formatNumber($store.state.settings.decimals) }}
                        <template v-if="calcItemTax(sv) > 0">
                          <template v-if="row.tax_included == 1">
                            ({{ calcItemTax(sv) | formatNumber($store.state.settings.decimals) }})
                          </template>
                          <template v-else> + {{ calcItemTax(sv) | formatNumber($store.state.settings.decimals) }} </template>
                        </template>
                      </span>
                      <span v-else>
                        @<del>{{ sv.price | formatNumber($store.state.settings.decimals) }}</del>
                        {{ (sv.price - sv.discount_amount) | formatNumber($store.state.settings.decimals) }}
                        <template v-if="calcItemTax(sv) > 0">
                          <template v-if="row.tax_included == 1">
                            ({{ calcItemTax(sv) | formatNumber($store.state.settings.decimals) }})
                          </template>
                          <template v-else> + {{ calcItemTax(sv) | formatNumber($store.state.settings.decimals) }} </template>
                        </template>
                      </span>
                    </span>
                    <span class="quantity">
                      <InputNumber v-model="sv.quantity" @on-change="itemVariationQuantityChanged(order.items[index])"></InputNumber>
                    </span>
                    <span class="subtotal"> </span>
                  </div>
                </template>
                <template v-if="row.selected.portions.length">
                  <div class="combo" v-for="(sp, spi) in row.selected.portions" :key="'spi_' + spi">
                    <div class="combo" v-for="p in row.portions.filter(ip => ip.id == sp.id)" :key="'pi_' + p.id">
                      <div class="combo-item variation">
                        <span class="index">
                          <Icon size="16" class="pointer" color="#ed4014" type="md-close" @click="remove(row, 'Portion', sp, spi)" />
                        </span>
                        <span class="details leading-medium">
                          <p>
                            {{ spi + 1 }}. {{ $tc('portion') }}:
                            <strong>{{ p.name == 'regular' ? $t('regular') : p.name }}</strong>
                            <br />
                            <template v-if="sp.discount_amount == 0">
                              @{{ sp.price | formatNumber($store.state.settings.decimals) }}
                              <template v-if="calcItemTax(sp) > 0">
                                <template v-if="row.tax_included == 1">
                                  ({{ calcItemTax(sp) | formatNumber($store.state.settings.decimals) }})
                                </template>
                                <template v-else> + {{ calcItemTax(sp) | formatNumber($store.state.settings.decimals) }} </template>
                              </template>
                            </template>
                            <template v-else>
                              @<del>{{ sp.price | formatNumber($store.state.settings.decimals) }}</del>
                              {{ (sp.price - sp.discount_amount) | formatNumber($store.state.settings.decimals) }}
                              <template v-if="calcItemTax(sp) > 0">
                                <template v-if="row.tax_included == 1">
                                  ({{ calcItemTax(sp) | formatNumber($store.state.settings.decimals) }})
                                </template>
                                <template v-else> + {{ calcItemTax(sp) | formatNumber($store.state.settings.decimals) }} </template>
                              </template>
                            </template>
                          </p>
                        </span>
                        <span class="quantity">
                          <InputNumber v-model="sp.quantity" @on-change="itemPortionQuantityChanged(order.items[index])"></InputNumber>
                        </span>
                        <span class="subtotal">
                          {{ formatNumber((sp.price - sp.discount_amount + sp.total_tax_amount) * sp.quantity) }}
                        </span>
                      </div>
                      <div :key="'ei_' + ei" class="combo-item bt0" style="padding-top: 1px;" v-for="(e, ei) in p.essentials">
                        <span class="index"></span>
                        <span class="details">
                          #{{ ei + 1 }} {{ e.item.name }}
                          <div style="line-height:1;margin-bottom:5px;" v-if="e.variation_id">
                            (<span v-html="metaString(e.item.variations.find(v => v.id == e.variation_id).meta)"></span>)
                          </div>
                        </span>
                        <span class="quantity input">
                          {{ parseFloat(e.quantity * sp.quantity) }}
                        </span>
                        <span class="subtotal"></span>
                      </div>
                      <template v-for="(g, gi) in p.choosables">
                        <div class="combo-item bt0" :key="'gii_' + gii + '_' + gi" v-for="(gitem, gii) in g.items">
                          <template v-if="getPortionChoosable(sp, g.id, gitem.item_id)">
                            <span class="index"></span>
                            <span class="details">
                              #{{ gi + 1 + p.essentials.length }}
                              {{ gitem.item.name }}
                              <div style="line-height:1;margin-bottom:5px;" v-if="gitem.variation_id">
                                (<span v-html="metaString(gitem.item.variations.find(v => v.id == gitem.variation_id).meta)"></span>)
                              </div>
                            </span>
                            <span class="quantity input">
                              {{ parseFloat(gitem.quantity * sp.quantity) }}
                            </span>
                            <span class="subtotal"></span>
                          </template>
                        </div>
                      </template>
                    </div>
                  </div>
                </template>
                <template v-if="row.selected.modifiers.length">
                  <div class="combo-item bg">
                    <span class="index"></span>
                    <span class="details">
                      <p style="font-weight: bold;">{{ $tc('modifier', 2) }}</p>
                    </span>
                    <span class="quantity">&nbsp;</span>
                    <span class="subtotal">&nbsp;</span>
                  </div>
                  <div :sm="24" :md="12" :lg="12" :key="'mi_' + mi" v-for="(m, mi) in row.selected.modifiers">
                    <div class="combo-item" style="padding-top: 5px;">
                      <span class="index">
                        <Icon size="16" class="pointer" color="#ed4014" type="md-close" @click="remove(row, 'Modifier', m, mi)" />
                      </span>
                      <span class="details leading-medium">
                        {{ mi + 1 }}. <strong>{{ m.option }}</strong>
                        <br />
                        {{ m.title }}
                        @{{ formatNumber(m.price - m.discount_amount + calcItemTax(m)) }}
                      </span>
                      <span class="quantity">
                        <InputNumber v-model="m.quantity" @on-change="itemModifierQuantityChanged(order.items[index])"></InputNumber>
                      </span>
                      <span class="subtotal"> </span>
                    </div>
                  </div>
                </template>
              </div>
            </div>
          </div>
        </div>
        <div class="below-order-items">
          <div class="order-total">
            <h3 class="total" v-show="orderTotalItems != 0">
              {{ $t('payable_amount') }}
              <span class="amount">
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
              </span>
            </h3>
          </div>
        </div>
        <div class="pos-order-actions">
          <ButtonGroup>
            <Tooltip v-if="$store.getters.last_pos_sale" class="btn" :content="$tc('last_pos_sale')" placement="top">
              <Button size="large" type="text" @click="print('receipt')">
                <Icon type="ios-print" size="18" />
              </Button>
            </Tooltip>
            <Tooltip class="btn btn-left" :content="$tc('order')" placement="top">
              <Button size="large" type="info" @click="extra = true">
                <Icon type="ios-information-circle-outline" size="18" />
              </Button>
            </Tooltip>
            <Tooltip class="btn" :content="$tc('delete')" placement="top">
              <Button size="large" type="error" @click="cancelOrder()">
                <Icon type="ios-trash" size="18" />
              </Button>
            </Tooltip>
            <Tooltip class="btn" :content="$tc('order')" placement="top">
              <Button size="large" type="warning" @click="print('order')">
                <Icon type="ios-print" size="18" />
              </Button>
            </Tooltip>
            <Tooltip class="btn btn-print" :content="$tc('bill')" placement="top">
              <Button size="large" type="primary" @click="print('bill')">
                <Icon type="ios-print" size="18" />
              </Button>
            </Tooltip>
            <Button size="large" class="btn btn-payment" type="success" @click="finalize()">{{ $tc('payment') }}</Button>
          </ButtonGroup>
        </div>
      </template>
      <template v-else>
        <Card dis-hover :bordered="false" style="margin:40px auto;">
          <div style="text-align:center">
            <Icon size="36" type="ios-cube-outline" />
            <h3 style="color:#808695;margin:10px 0 20px 0;">Please add items to order</h3>
            <Button v-if="$store.getters.last_pos_sale" class="mb16" type="primary" @click="print('receipt')">
              <Icon type="ios-print" size="18" /> {{ $t('print_x', { x: $tc('last_pos_sale') }) }}
            </Button>
            <br /><Button type="error" @click="cancelOrder()">
              <Icon type="ios-trash" size="18" /> {{ $t('delete_x', { x: $tc('order') }) }}
            </Button>
          </div>
        </Card>
      </template>
    </div>
    <!-- <Drawer :title="$t('order_form') + ' (' + $tc('ref_tab') + ': ' + $store.getters.current + ')'" v-model="extra" width="400" class="drawer-fixed"> -->
    <Modal
      scrollable
      fullscreen
      v-model="extra"
      :mask-closable="false"
      :title="$t('order_form') + ' (' + $tc('ref_tab') + ': ' + $store.getters.current + ')'"
    >
      <Form ref="orderForm" :model="order" :rules="rules" :label-width="140">
        <div class="drawer-content" style="height: calc(100vh - 112px) !important; margin: -16px; padding: 16px;">
          <div style="width: 90%; max-width: 1200px; margin: 0 auto;">
            <Row :gutter="16">
              <Col :sm="24" :md="12" :lg="12">
                <FormItem :label="$t('type')" prop="type" :error="errors.form.type | a2s">
                  <Select v-model="order.type" placeholder="">
                    <Option value="nett">{{ $t('nett') }}</Option>
                    <Option value="layaway">{{ $t('layaway') }}</Option>
                    <Option value="on_account">{{ $t('on_account') }}</Option>
                  </Select>
                </FormItem>
                <FormItem :label="$t('date')" prop="date" :error="errors.form.date | a2s">
                  <DatePicker type="date" v-model="order.date" format="yyyy-MM-dd" style="width: 100%;" />
                </FormItem>
                <FormItem :label="$tc('customer')" prop="customer_id" :error="errors.form.customer_id | a2s">
                  <Select
                    remote
                    filterable
                    :clearable="true"
                    :loading="searching"
                    v-model="order.customer_id"
                    @on-change="customerChanged"
                    :remote-method="searchCustomers"
                    :placeholder="$t('type_to_search_x', { x: $tc('customer') })"
                  >
                    <Option v-for="(option, index) in result" :value="option.value" :key="index + option.value">{{ option.label }}</Option>
                  </Select>
                </FormItem>
                <FormItem :label="$t('reference')" prop="reference" :error="errors.form.reference | a2s">
                  <Input v-model="order.reference" />
                </FormItem>
                <!-- <FormItem :label="$t('account')" prop="account">
                  <Input v-model="order.account" />
                </FormItem> -->
              </Col>
              <Col :sm="24" :md="12" :lg="12">
                <FormItem :label="$t('order_taxes')" prop="order_taxes">
                  <Select v-model="order.taxes" multiple style="width: 100%;">
                    <Option v-for="option in $store.getters.taxes" :value="option.id" :key="'tax_' + option.id">
                      {{ option.name }}
                    </Option>
                  </Select>
                </FormItem>
                <span v-if="$store.state.settings.max_discount > 0">
                  <FormItem :label="$t('discount')" prop="discount" :error="errors.form.discount | a2s">
                    <InputNumber v-model="order.discount" :formatter="value => `${value}%`" :parser="value => value.replace('%', '')" />
                  </FormItem>
                  <FormItem>
                    <RadioGroup v-model="order.discount_method" vertical>
                      <Radio label="order">{{ $t('apply_to_order_amount') }}</Radio>
                      <Radio label="items">{{ $t('apply_to_order_items') }}</Radio>
                    </RadioGroup>
                  </FormItem>
                </span>
              </Col>
              <Col :sm="24" :md="24" :lg="24">
                <form-custom-fields v-model="order" :attributes="attributes" @update="updateCF" />
                <FormItem :label="$t('details')" prop="details">
                  <Input type="textarea" v-model="order.details" :autosize="{ minRows: 2, maxRows: 5 }" />
                </FormItem>
              </Col>
            </Row>
          </div>
        </div>
      </Form>
      <div slot="footer">
        <Button size="large" type="info" @click="addCustomer()" style="float:left;">{{ $t('add_x', { x: $tc('customer') }) }}</Button>
        <Button size="large" type="text" @click="extra = false">{{ $t('close') }}</Button>
        <Button size="large" class="total" type="success" @click="finalize()">{{ $tc('payment') }}</Button>
      </div>
    </Modal>
    <!-- </Drawer> -->
    <Drawer :title="$tc('payment')" v-model="payment" width="400" :closable="!loading" :mask-closable="false" class="drawer-fixed">
      <Form ref="paymentForm" :model="paymentForm" :rules="paymentRules">
        <div class="drawer-content">
          <div class="fixed-top">
            <FormItem>
              <div class="table-wrapper payment-table">
                <div class="table">
                  <table cellspacing="0" cellpadding="0" border="0" style="width: 100%;">
                    <tbody class="ivu-table-tbody">
                      <tr class="ivu-table-row bg-info white">
                        <td class="">
                          <div class="ivu-table-cell">{{ $t('payable_amount') }}:</div>
                        </td>
                        <td>
                          <div class="ivu-table-cell text-right">
                            {{ orderPayableAmount | formatNumber($store.state.settings.decimals) }}
                          </div>
                        </td>
                      </tr>
                      <tr class="ivu-table-row bg-success white">
                        <td class="">
                          <div class="ivu-table-cell">{{ $t('paid') }}:</div>
                        </td>
                        <td>
                          <div class="ivu-table-cell text-right">
                            {{ paymentForm.amount | formatNumber($store.state.settings.decimals) }}
                          </div>
                        </td>
                      </tr>
                      <tr class="ivu-table-row bg-warning white">
                        <td class="">
                          <div class="ivu-table-cell">{{ $t('change') }}:</div>
                        </td>
                        <td>
                          <div class="ivu-table-cell text-right">
                            {{ paymentChange | formatNumber($store.state.settings.decimals) }}
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </FormItem>
          </div>
          <transition name="slide-fade">
            <span v-if="paymentForm.gateway == 'cash'">
              <FormItem style="margin-bottom: 8px;">
                <ButtonGroup class="quick-cash">
                  <Button
                    :key="q + '_' + i"
                    @click="cash(parseFloat(q))"
                    v-for="(q, i) in $store.state.settings.quick_cash.split('|').sort((a, b) => a - b)"
                  >
                    {{ q }}
                  </Button>
                </ButtonGroup>
              </FormItem>
              <FormItem>
                <ButtonGroup class="quick-cash">
                  <Button class="total" type="info" @click="cash(orderPayableAmount, true)">
                    {{ orderPayableAmount | formatNumber($store.state.settings.decimals) }}
                  </Button>
                  <Button type="error" @click="clearCash()">{{ $t('clear') }}</Button>
                </ButtonGroup>
              </FormItem>
            </span>
          </transition>
          <Row :gutter="16">
            <!-- <Col :sm="8">
              <FormItem :label="$t('type')" prop="type">
                <Select v-model="paymentForm.type" placeholder="" :error="errors.payment.type">
                  <Option value="nett">{{ $t('nett') }}</Option>
                  <Option value="layaway">{{ $t('layaway') }}</Option>
                  <Option value="on_account">{{ $t('on_account') }}</Option>
                </Select>
              </FormItem>
            </Col> -->
            <Col :sm="12">
              <FormItem :label="$t('paying_amount')" prop="amount" :error="errors.payment.amount">
                <InputNumber element-id="paymentFormAmount" v-model="paymentForm.amount"></InputNumber>
              </FormItem>
            </Col>
            <Col :sm="12">
              <FormItem :label="$t('gateway')" prop="gateway" :error="errors.payment.gateway">
                <Select v-model="paymentForm.gateway" placeholder="" @on-change="updatePaymentFormFocus()">
                  <Option value="cash">{{ $t('cash') }}</Option>
                  <Option value="cheque">{{ $t('cheque') }}</Option>
                  <Option value="gift_card">{{ $tc('gift_card') }}</Option>
                  <Option value="credit_card">{{ $t('credit_card') }}</Option>
                  <Option value="other">{{ $t('other') }}</Option>
                </Select>
              </FormItem>
            </Col>
          </Row>
          <span v-if="paymentForm.gateway == 'credit_card'">
            <FormItem :error="errors.payment.swipe" id="swipe_holder">
              <Input v-model="paymentForm.swipe" :placeholder="$t('swipe_card_text')" element-id="credit_card" />
            </FormItem>
            <FormItem :label="$t('card_holder')" prop="card_holder" :error="errors.payment.card_holder">
              <Input v-model="paymentForm.card_holder" />
            </FormItem>
            <Row>
              <Col span="12">
                <FormItem :label="$t('card_number')" prop="card_number" :error="errors.payment.card_number">
                  <Input v-model="paymentForm.card_number" />
                </FormItem>
              </Col>
              <Col span="1">&nbsp;</Col>
              <Col span="6">
                <FormItem :label="$t('expiry_date')" prop="expiry_date" :error="errors.payment.expiry_date">
                  <DatePicker type="month" format="MM/yy" style="width: 100%;" v-model="paymentForm.expiry_date"></DatePicker>
                </FormItem>
              </Col>
              <Col span="1">&nbsp;</Col>
              <Col span="4">
                <FormItem :label="$t('cvv')" prop="cvv" :error="errors.payment.cvv">
                  <Input v-model="paymentForm.cvv" element-id="cvv" />
                </FormItem>
              </Col>
            </Row>
          </span>
          <span v-if="paymentForm.gateway == 'gift_card'">
            <FormItem :label="$t('gift_card_number')" prop="gift_card_number" :error="errors.payment.gift_card_number">
              <AutoComplete
                icon="ios-search"
                style="width:100%"
                element-id="gift_card"
                @on-search="searchGiftCard"
                @on-select="giftCardSelected"
                v-model="paymentForm.gift_card_number"
              >
                <Option v-for="(card, ci) in gift_cards" :value="card.number" :key="'gift_card_' + ci">{{ card.number }}</Option>
              </AutoComplete>
              <span v-if="gift_card && gift_card.number">
                {{ $t('balance') }}: {{ gift_card.balance | formatNumber($store.state.settings.decimals) }}
              </span>
            </FormItem>
          </span>
          <span v-else-if="paymentForm.gateway == 'cheque'">
            <FormItem :label="$t('cheque_number')" prop="cheque_number" :error="errors.payment.cheque_number">
              <Input v-model="paymentForm.cheque_number" element-id="cheque" />
            </FormItem>
          </span>
          <FormItem :label="$t('payment_details')" prop="payment_details" :error="errors.payment.payment_details">
            <Input v-model="paymentForm.payment_details" type="textarea" :autosize="{ minRows: 2, maxRows: 4 }" />
          </FormItem>
        </div>
        <div class="drawer-footer" style="padding: 6px;">
          <ButtonGroup class="quick-cash">
            <Button size="large" :disabled="loading" @click="payment = false">{{ $t('cancel') }}</Button>
            <Button size="large" class="total" :loading="loading" type="primary" @click="handleSubmit()">
              {{ $t('submit') }}
            </Button>
          </ButtonGroup>
        </div>
      </Form>
    </Drawer>
    <Modal :width="600" :footer-hide="true" v-model="show_item_edit" :title="edit_item ? $t('edit') + ' - ' + edit_item.name : ''">
      <div v-if="edit_item">
        <Form ref="editItemForm" :model="edit_item" label-position="top">
          <Row :gutter="16">
            <Col :sm="24" :md="24" :lg="24" v-if="!edit_item.portions.length && !edit_item.has_variants">
              <FormItem
                prop="quantity"
                class="text-center"
                :label="$t('quantity')"
                :rules="{
                  min: 0.01,
                  type: 'number',
                  required: true,
                  trigger: 'change',
                  message: $t('field_is_required', { field: $t('quantity') }),
                }"
              >
                <InputNumber v-model="edit_item.quantity" :step="0.1" :min="0.01" size="large"></InputNumber>
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
                                      <Select
                                        v-model="eisp.portion_items[ei].meta[v.name]"
                                        @on-change="selectPortionItemVariant(eispi, ei)"
                                      >
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
          <FormItem>
            <Button long type="primary" @click="updateItem()">{{ $t('update') }}</Button>
          </FormItem>
        </Form>
        <Button long type="error" @click="remove(edit_item)">{{ $t('remove_from_order') }}</Button>
      </div>
    </Modal>
    <Modal
      width="550"
      footer-hide
      v-model="add_customer"
      :mask-closable="false"
      class="np-header-footer"
      :title="$t('add_x', { x: $tc('customer') })"
    >
      <customer-form-component @added="customerAdded" />
    </Modal>
  </div>
</template>

<script>
import _debounce from 'lodash/debounce';
import OrderHelpers from '@mpsjs/mixins/OrderHelpers';
import CustomerFormComponent from '@mpscom/pos/CustomerFormComponent';
export default {
  components: { CustomerFormComponent },
  mixins: [OrderHelpers('price', 'order', true)],
  props: {
    attributes: { required: true },
    order: { required: true, twoWay: true },
  },
  data() {
    const discountV = (rule, value, callback) => {
      if (value && parseFloat(value) > parseFloat(this.$store.state.settings.max_discount)) {
        callback(new Error(this.$t('max_discount_error', { percent: parseFloat(this.$store.state.settings.max_discount) })));
      } else {
        callback();
      }
    };
    const checlGiftCardBalance = (rule, value, callback) => {
      if (
        this.paymentForm.amount &&
        this.paymentForm.gateway == 'gift_card' &&
        parseFloat(this.gift_card.balance) < this.paymentForm.amount
      ) {
        callback(new Error(this.$t('gift_card_payment_error')));
      } else {
        callback();
      }
    };
    return {
      result: [],
      unsub: null,
      extra: false,
      printData: {},
      updated: false,
      loading: false,
      payment: false,
      printing: false,
      edit_item: null,
      searching: false,
      customer_id: null,
      printModal: false,
      add_customer: false,
      show_item_edit: false,
      paymentFormFocus: false,
      errors: { message: '', form: {}, payment: {} },
      paymentForm: { amount: null, gateway: 'cash' },
      paymentRules: {
        amount: [
          {
            type: 'number',
            trigger: 'blur',
            message: this.$t('field_is_required', { field: this.$t('amount') }),
          },
        ],
        gateway: [{ message: this.$t('field_is_required', { field: this.$t('gateway') }), trigger: 'change' }],
        cheque_number: [{ required: true, message: this.$t('field_is_required', { field: this.$t('cheque_number') }), trigger: 'blur' }],
        gift_card_number: [
          { required: true, message: this.$t('field_is_required', { field: this.$t('gift_card_number') }), trigger: 'blur' },
          { validator: checlGiftCardBalance, trigger: ['change', 'blur'] },
        ],
        card_holder: [{ required: true, message: this.$t('field_is_required', { field: this.$t('card_holder') }), trigger: 'blur' }],
        card_number: [{ required: true, message: this.$t('field_is_required', { field: this.$t('card_number') }), trigger: 'blur' }],
        expiry_date: [
          { required: true, type: 'date', message: this.$t('field_is_required', { field: this.$t('expiry_date') }), trigger: 'blur' },
        ],
        cvv: [{ required: true, message: this.$t('field_is_required', { field: this.$t('cvv') }), trigger: 'blur' }],
      },
      rules: {
        discount: [{ validator: discountV, type: 'number', trigger: ['change', 'blur'] }],
        details: [{ message: this.$t('field_is_required', { field: this.$t('details') }), trigger: 'blur' }],
        customer_id: [{ required: true, message: this.$t('field_is_required', { field: this.$tc('customer') }), trigger: 'change' }],
        date: [{ required: true, type: 'date', message: this.$t('field_is_required', { field: this.$t('date') }), trigger: 'change' }],
        type: [{ required: true, message: this.$t('field_is_required', { field: this.$t('type') }), trigger: 'change' }],
      },
    };
  },
  computed: {
    customers() {
      return this.$store.state.customers ? this.$store.state.customers : [];
    },
    customer() {
      return this.$store.state.customers ? this.$store.state.customers.find(c => c.value == this.order.customer_id) : null;
    },
    taxes() {
      return this.$store.state.taxes;
    },
  },
  watch: {
    extra: function(val) {
      if (!val && this.updated) {
        this.updated = false;
        this.$event.fire('order:updated');
      }
    },
    'paymentForm.gateway': function(newVal, oldVal) {
      this.$refs.paymentForm.fields.map(field => {
        if (field.prop != 'type' && field.prop != 'amount' && field.prop != 'gateway' && field.prop != 'payment_details') {
          field.resetField() || true;
        }
      });
    },
    'paymentForm.swipe': function(newVal, oldVal) {
      if (newVal && newVal.charAt(0) == '%') {
        setTimeout(() => {
          var card = newVal.split('^');
          let name = card && card[1] ? card[1].split('/') : '';
          let lastName = name ? name[0].trim().split('.')[0] : '';
          let firstName = name && name[1] ? name[1].trim() : '';
          let ed1 = card && card[2] ? card[2].substring(2, 4) + '/' + card[2].substring(0, 2) : '';
          let edf = card && card[2] ? card[2].split(';') : '';
          let eds = edf && edf[1] ? edf[1].split('=') : null;
          let ed = eds && eds[1] ? eds[1].trim() : null;
          let ed2 = ed ? ed.substring(2, 4) + '/' + ed.substring(0, 2) : ed1;
          this.paymentForm.card_number = card[0].substring(2);
          this.paymentForm.expiry_date = ed2 || ed1;
          this.paymentForm.card_holder = `${firstName} ${lastName}`;
          this.$nextTick(function() {
            this.paymentForm.swipe = '';
            document.querySelector('#cvv').focus();
          });
        }, 400);
      } else if (newVal) {
        this.errors.payment.swipe = this.$t('unknown_value');
        document.querySelector('#swipe_holder').classList.add('ivu-form-item-error');
      } else {
        this.errors.payment.swipe = '';
        document.querySelector('#swipe_holder').classList.remove('ivu-form-item-error');
      }
    },
    'order.customer_id': function(newVal, oldVal) {
      if (oldVal) {
        let customer = this.customers.find(c => c.value == oldVal);
        if (customer && customer.customer_group && customer.customer_group.discount) {
          this.order.items.map(item => {
            item.discount = parseFloat(item.discount) - parseFloat(customer.customer_group.discount);
            this.$event.fire('pos:order:update', { item, qty: item.quantity, set: true, force: true, vCheck: false });
            // return item;
          });
        }
      }
      this.saveOrder('customer_id', newVal);
      if (newVal) {
        this.checkCustomerGroup(newVal);
      }
    },
    'order.reference': function(newVal, oldVal) {
      this.saveOrder('reference', newVal);
    },
    'order.details': function(newVal, oldVal) {
      this.saveOrder('details', newVal);
    },
    'order.date': function(newVal, oldVal) {
      this.saveOrder('date', newVal);
    },
    'order.type': function(newVal, oldVal) {
      this.saveOrder('type', newVal);
    },
    'order.taxes': function(newVal, oldVal) {
      this.saveOrder('taxes', newVal);
    },
    'order.discount_method': function(newVal, oldVal) {
      if (this.order.discount_method == 'order') {
        this.order.items = this.order.items.map(i => {
          i.discount = null;
          i.discount_amount = 0;
          return i;
        });
      } else {
        this.order.items = this.order.items.map(i => {
          i.discount = this.order.discount ? this.order.discount : null;
          i.discount_amount = this.order.discount
            ? this.$options.filters.formatDecimal((i.price * i.discount) / 100, this.$store.state.settings.decimals)
            : 0;
          return i;
        });
      }
      this.saveOrder('discount_method', newVal);
    },
    'order.discount': _debounce(function(newVal, oldVal) {
      newVal = newVal ? parseFloat(newVal) : 0;
      if (newVal <= parseFloat(this.$store.state.settings.max_discount)) {
        if (this.order.discount_method == 'items') {
          this.order.items = this.order.items.map(i => {
            i.discount = newVal;
            i.discount_amount = newVal
              ? this.$options.filters.formatDecimal((i.price * i.discount) / 100, this.$store.state.settings.decimals)
              : 0;
            return i;
          });
        }
        this.saveOrder('discount', newVal);
      }
    }, 500),
  },
  created() {
    //POS shortcust
    this.$event.listen('pos:makePayment', this.finalize);
    this.$event.listen('pos:deleteOrder', this.cancelOrder);
    this.$event.listen('pos:printBill', () => this.print('bill'));
    this.$event.listen('pos:printOrder', () => this.print('order'));
    this.$event.listen('pos:printReceipt', () => this.print('receipt'));
    this.$event.listen('pos:updateInfo', () => (this.extra = true));

    this.$event.listen('order:change', () => {
      this.result = [...this.customers];
    });
    this.$event.listen('order:show', () => {
      if (window && document.documentElement.clientWidth <= 768) {
        this.$nextTick(() => {
          if (this.$refs.orderItems) {
            setTimeout(() => (this.$refs.orderItems.scrollTop = this.$refs.orderItems.scrollHeight), 200);
          }
        });
      }
    });
    this.$event.listen('order:scroll', () => {
      this.$nextTick(() => {
        if (this.$refs.orderItems) {
          this.$refs.orderItems.scrollTop = this.$refs.orderItems.scrollHeight;
        }
      });
    });
    this.attributes.map(a => {
      this.cfRule(a);
      this.$watch('order.' + a.slug, function(newVal, oldVal) {
        this.saveOrder(a.slug, newVal);
      });
    });
  },
  methods: {
    addCustomer() {
      this.add_customer = true;
    },
    customerAdded(customer) {
      customer = {
        value: customer.id,
        label: customer.name,
        points: customer.points,
        state: customer.state,
        country: customer.country,
        customer_group: customer.customer_group,
      };
      this.$store.commit('addCustomer', customer);
      this.$nextTick(() => {
        this.result = [...this.result, customer];
        this.saveOrder('customer_id', customer.value);
        setTimeout(() => {
          this.order.customer_id = customer.value;
          // console.log(this.order.customer);
        }, 300);
      });
      this.add_customer = false;
    },
    cash(a, c = false) {
      this.paymentForm.amount = c ? a : parseFloat(this.paymentForm.amount ? parseFloat(this.paymentForm.amount) + a : a);
      document.querySelector('#paymentFormAmount').focus();
    },
    clearCash() {
      this.paymentForm.amount = null;
    },
    updatePaymentFormFocus() {
      if (this.paymentForm.gateway != 'cash') {
        this.paymentForm.amount = this.orderTotalAmount;
      }
      setTimeout(
        () =>
          document.querySelector('#' + this.paymentForm.gateway) ? document.querySelector('#' + this.paymentForm.gateway).focus() : '',
        100
      );
    },
    finalize() {
      if (this.order && this.order.items.length > 0) {
        this.$refs.orderForm
          .validate()
          .then(valid => {
            if (valid) {
              this.extra = false;
              if (!this.paymentForm.gateway) {
                this.paymentForm.gateway = 'cash';
              }
              this.paymentForm.amount = this.orderPayableAmount;
              this.payment = true;
            } else {
              this.extra = true;
              this.$Notice.destroy();
              this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 5 });
            }
          })
          .catch(err => console.log(err));
      } else {
        this.$Notice.destroy();
        this.$Notice.error({ title: this.$t('empty_order'), desc: this.$t('empty_order_error'), duration: 5 });
      }
    },
    print(t, n = 0) {
      if (this.printing) {
        return false;
      }
      this.printing = true;
      setTimeout(() => (this.printing = false), 1000);

      let order = this.order;
      if (t == 'receipt') {
        let sale = this.$store.getters.last_pos_sale;
        if (sale) {
          order = {
            ...sale,
            items: sale.items.map(i => ({
              ...i,
              selected: {
                portions: sale.portions || [],
                modifiers: sale.modifiers || [],
                variations: sale.variations || [],
              },
            })),
          };
        } else {
          order = null;
          console.error('There is no sale recorded yet, please print from list sales page.');
        }
      }
      if (order) {
        if (this.$store.state.settings.pos_server == 1) {
          let items = null;
          let totals = null;
          let headers = null;
          let footers = null;
          let notice = null;
          let table_headers = null;
          let store_details = {};
          let user = order.user ? order.user : this.$store.getters.user;
          let location = order.location ? order.location : this.$store.getters.location;
          store_details['name'] = location.name || location.label;
          let details = [
            [this.$t('order_id'), order.orderId],
            [this.$t('reference'), t == 'receipt' ? order.reference : this.$store.getters.current],
            [this.$t('date'), this.date(order.date)],
            [this.$t('created_by'), user.name + '(' + user.username + ')'],
            [this.$t('created_at'), this.datetime(order.created_at)],
            [this.$t('printed_at'), this.datetime(new Date())],
          ];
          if (t == 'receipt') {
            items = order.items.map(item => {
              let rows = ['feed'];
              let name =
                item.code + '\n' + item.name + (item.alt_name ? '\n' + item.alt_name : '') + (item.comment ? '\n' + item.comment : '');
              if (item.variations && item.variations.length) {
                rows.push({ type: 'text', text: name });
                item.variations.map(sv => {
                  rows.push({
                    type: 'item',
                    name: ' ' + this.metaString(sv.meta, true, true),
                    price: this.formatNumber(parseFloat(sv.pivot.net_price) + parseFloat(sv.pivot.tax_amount)),
                    qty: this.formatQuantity(sv.pivot.quantity),
                    subtotal: this.formatNumber(sv.pivot.total),
                  });
                });
              } else if (item.portions && item.portions.length) {
                rows.push({ type: 'text', text: name });
                item.portions.map(p => {
                  rows.push('feed');
                  rows.push({
                    type: 'item',
                    name: this.$tc('portion') + ': ' + this.$tc(p.name),
                    price: this.formatNumber(parseFloat(p.pivot.price) - parseFloat(p.pivot.discount_amount)),
                    qty: this.formatQuantity(p.pivot.quantity),
                    subtotal: this.formatNumber(p.pivot.total),
                  });
                  if (p.portion_items && p.portion_items.length) {
                    p.portion_items.map(pi => {
                      rows.push({
                        type: 'item',
                        name: ' ' + pi.item.name + (pi.meta ? '\n(' + this.metaString(pi.meta, true, true) + ')' : ''),
                        price: ' ',
                        qty: this.formatQuantity(parseFloat(pi.quantity) * parseFloat(p.pivot.quantity)),
                        subtotal: ' ',
                      });
                    });
                  }
                  if (p.essentials && p.essentials.length) {
                    p.essentials.map(pe => {
                      rows.push({
                        type: 'item',
                        name: ' ' + pe.item.name + (pe.meta ? '\n(' + this.metaString(pe.meta, true, true) + ')' : ''),
                        price: ' ',
                        qty: this.formatQuantity(parseFloat(pe.quantity) * parseFloat(p.pivot.quantity)),
                        subtotal: ' ',
                      });
                    });
                  }
                  if (p.choosables && p.choosables.length) {
                    p.choosables.map(pc => {
                      pc.items.map(pci => {
                        if (p.pivot.choosables.find(c => c.id == pc.id && pci.item_id == c.item_id)) {
                          rows.push({ type: 'text', text: pc.name });
                          rows.push({
                            type: 'item',
                            name: ' ' + pci.item.name + (pc.meta ? '\n(' + this.metaString(pc.meta, true, true) + ')' : ''),
                            price: ' ',
                            qty: this.formatQuantity(parseFloat(pci.quantity) * parseFloat(p.pivot.quantity)),
                            subtotal: ' ',
                          });
                        }
                      });
                    });
                  }
                });
              } else {
                rows.push({
                  name,
                  type: 'item',
                  price: this.formatNumber(item.unit_price),
                  qty: this.formatQuantity(item.quantity),
                  subtotal: this.formatNumber(item.subtotal),
                });
              }
              if (item.modifier_options && item.modifier_options.length) {
                item.modifier_options.map(m => {
                  rows.push({ type: 'text', text: m.modifier.title });
                  rows.push({
                    type: 'item',
                    name: ' ' + m.item.name,
                    price: this.formatNumber(parseFloat(m.pivot.net_price) + parseFloat(m.pivot.tax_amount)),
                    qty: this.formatQuantity(m.pivot.quantity),
                    subtotal: this.formatNumber(m.pivot.total),
                  });
                });
              }
              return rows;
            });
          } else {
            items = order.items.map(item => {
              let rows = ['feed'];
              let name =
                item.code + '\n' + item.name + (item.alt_name ? '\n' + item.alt_name : '') + (item.comment ? '\n' + item.comment : '');
              if (item.selected.variations && item.selected.variations.length) {
                rows.push({ type: 'text', text: name });
                item.selected.variations.map(sv => {
                  rows.push({
                    type: 'item',
                    name: ' ' + this.metaString(sv.meta, true, true),
                    price: this.formatNumber(parseFloat(sv.price - sv.discount_amount) + this.calcItemTax(sv)),
                    qty: this.formatQuantity(sv.quantity),
                    subtotal: this.formatNumber((sv.price - sv.discount_amount + this.calcItemTax(sv)) * sv.quantity),
                  });
                });
              } else if (item.selected.portions && item.selected.portions.length) {
                rows.push({ type: 'text', text: name });
                item.selected.portions.map(p => {
                  let pp = item.portions.find(ip => ip.id == p.id);
                  rows.push('feed');
                  rows.push({
                    type: 'item',
                    name: this.$tc('portion') + ': ' + this.$t(pp.name),
                    price: this.formatNumber(p.price - p.discount_amount + this.calcItemTax(p)),
                    qty: this.formatQuantity(p.quantity),
                    subtotal: this.formatNumber((p.price - p.discount_amount + this.calcItemTax(p)) * p.quantity),
                  });
                  if (pp.portion_items && pp.portion_items.length) {
                    pp.portion_items.map(pi => {
                      rows.push({
                        type: 'item',
                        name: ' ' + pi.item.name + (pi.meta ? '\n(' + this.metaString(pi.meta, true, true) + ')' : ''),
                        price: ' ',
                        qty: this.formatQuantity(pi.quantity * p.quantity),
                        subtotal: ' ',
                      });
                    });
                  }
                  if (pp.essentials && pp.essentials.length) {
                    pp.essentials.map(pe => {
                      rows.push({
                        type: 'item',
                        name: ' ' + pe.item.name + (pe.meta ? '\n(' + this.metaString(pe.meta, true, true) + ')' : ''),
                        price: ' ',
                        qty: this.formatQuantity(parseFloat(pe.quantity) * parseFloat(p.quantity)),
                        subtotal: ' ',
                      });
                    });
                  }
                  if (pp.choosables && pp.choosables.length) {
                    pp.choosables.map(pc => {
                      pc.items.map(pci => {
                        if (this.getPortionChoosable(p, pc.id, pci.item_id)) {
                          rows.push({ type: 'text', text: pc.name });
                          rows.push({
                            type: 'item',
                            name: ' ' + pci.item.name + (pc.meta ? '\n(' + this.metaString(pc.meta, true, true) + ')' : ''),
                            price: ' ',
                            qty: this.formatQuantity(parseFloat(pci.quantity) * parseFloat(p.quantity)),
                            subtotal: ' ',
                          });
                        }
                      });
                    });
                  }
                });
              } else {
                rows.push({
                  name,
                  type: 'item',
                  price: this.formatNumber(item.price - item.discount_amount + this.calcItemTax(item)),
                  qty: this.formatQuantity(item.quantity),
                  subtotal: this.formatNumber(this.calcRowTotal(item)),
                });
              }
              if (item.selected.modifiers && item.selected.modifiers.length) {
                item.selected.modifiers.map(m => {
                  rows.push({ type: 'text', text: m.title });
                  rows.push({
                    type: 'item',
                    name: ' ' + m.option,
                    price: this.formatNumber(m.price - m.discount_amount + this.calcItemTax(m)),
                    qty: this.formatQuantity(m.quantity),
                    subtotal: this.formatNumber((m.price - m.discount_amount + this.calcItemTax(m)) * m.quantity),
                  });
                });
              }
              return rows;
            });
          }
          if (t == 'order') {
            table_headers = [this.$t('name'), this.$t('qty')];
          } else {
            headers = [this.$store.state.settings.header, location.header];
            store_details['email'] = location.email;
            store_details['phone'] = location.phone;
            store_details['company'] = location.company;
            store_details['address'] = location.address;
            store_details['state'] = location.state_name;
            store_details['country'] = location.country_name;
            if (t == 'bill') {
              let customer = order.customer ? order.customer : this.customers.find(c => c.value == order.customer_id);
              details.push([this.$tc('customer'), customer.name + (customer.company ? ' (' + customer.company + ')' : '')]);
              totals = [{ label: this.$t('payable_amount'), value: this.formatNumber(this.orderPayableAmount) }];
              notice = this.$t('only_for_company_record');
            } else {
              details.unshift([this.$tc('id'), order.id]);
              details.push([
                this.$tc('customer'),
                order.customer.name +
                  (order.customer.company ? ' (' + order.customer.company + ')' : '') +
                  (order.customer.email ? ' ' + order.customer.email : '') +
                  (order.customer.phone ? ' ' + order.customer.phone : ''),
              ]);

              totals = [{ label: this.$t('total'), value: this.formatNumber(order.total) }];
              if (order.order_discount_amount) {
                totals.push({ label: this.$t('order_discount'), value: this.formatNumber(order.order_discount_amount) });
              }
              totals.push({ label: this.$t('tax_amount'), value: this.formatNumber(order.total_tax_amount) });
              totals.push({ label: this.$t('grand_total'), value: this.formatNumber(order.grand_total) });
              let total_paid = order && order.payments ? order.payments.reduce((a, p) => a + parseFloat(p.amount), 0) : 0;
              totals.push({ label: this.$t('paid'), value: this.formatNumber(total_paid) });
              totals.push({ label: this.$t('balance_due'), value: this.formatNumber(parseFloat(order.grand_total) - total_paid) });
              notice = this.$t('order_cgd');
            }
            table_headers = [
              { label: this.$t('name') },
              { label: this.$t('price') },
              { label: this.$t('qty') },
              { label: this.$t('subtotal') },
            ];
            footers = [this.$store.state.settings.footer, location.footer];
          }

          let data = {
            logo: location.logo || this.$store.state.settings.default_logo,
            heading: this.$store.state.settings.name,
            store_heading: this.$tc('location'),
            store_details,
            type: this.$tc(t),
            headers,
            info: details,
            table_headers,
            items,
            totals,
            footers,
            notice,
          };
          this.$event.fire('pos:print', { type: 'print-json-' + t, data });
        } else {
          this.printData = {};
          this.printData.type = t;
          this.printData.order = order;
          this.printData.order.oId = this.$store.getters.current;
          this.printData.customer = this.customers.find(c => c.value == order.customer_id);
          this.printData.order.location = order.location ? order.location : this.$store.getters.location;
          this.printData.order.location.name = this.printData.order.location.name || this.printData.order.location.label;
          if (t == 'receipt') {
            this.printData.order.items = this.printData.order.items.map(item => {
              item.selected = { portions: [], variations: [], modifiers: [] };
              item.selected.portions = item.portions;
              item.selected.variations = item.variations;
              item.selected.modifiers = item.modifier_options;
              return item;
            });
          }
          this.$Modal.confirm({
            width: 365,
            closable: true,
            scrollable: true,
            render: h => {
              return h('print-component', { props: { print: this.printData, vm: this } });
            },
            okText: this.$root.$t('print'),
            onOk: () => {
              window.print();
            },
            onCancel: () => {
              setTimeout(() => (this.printData = {}), 200);
            },
          });
        }
      }
    },
    saveOrder(key, value) {
      this.updated = true;
      this.$store.commit('updateOrder', { key, value, oId: this.$store.getters.current });
    },
    deleteOrder() {
      this.$Modal.confirm({
        title: this.$t('deleting') + ' ' + this.$tc('order') + ' ' + this.$store.getters.current,
        content: this.$t('delete_confirm') + '<br><br><strong>' + this.$t('r_u_sure') + '</strong>',
        okText: this.$t('yes'),
        cancelText: this.$t('cancel'),
        onOk: () => {
          this.$event.fire('order:delete');
        },
      });
    },
    cancelOrder() {
      if (this.$store.getters.superAdmin || this.can('delete-orders')) {
        this.deleteOrder();
      } else {
        let value = null;
        this.$Modal.confirm({
          width: 260,
          okText: this.$t('ok'),
          cancelText: this.$t('cancel'),
          title: this.$t('pin_required'),
          render: h => {
            return h('Input', {
              props: {
                value,
                autofocus: true,
                placeholder: this.$t('type_pin'),
              },
              on: {
                input: val => (value = val),
              },
            });
          },
          onOk: () => {
            this.$Message.info(value);
            this.deleteOrder();
          },
        });
      }
    },
    customerChanged(sc) {
      if (sc) {
        let customer = this.result.find(c => c && c.value == sc);
        this.$store.commit('addCustomer', customer);
        this.$nextTick(() => {
          this.result = [...this.customers];
          this.saveOrder('customer_id', sc);
        });
      }
    },
    getCustomer(id) {
      if (id && this.order) {
        let customer = this.customers.find(c => c.value == id);
        if (customer) {
          this.$nextTick(() => {
            this.order.customer_id = customer.value;
          });
        } else {
          this.$http.get(`app/customers/${id}`).then(({ data }) => {
            this.$store.commit('addCustomer', { value: data.id, label: data.name });
            this.$nextTick(() => {
              this.order.customer_id = data.id;
              this.result = [...this.customers];
            });
          });
        }
      }
    },
    searchCustomers(search) {
      if (!this.result.find(c => c.value == search || c.label == search)) {
        this.getCustomers(search, this);
      }
    },
    getCustomers: _debounce((search, vm) => {
      vm.searching = true;
      const search_delay = vm.$store.getters.search_delay;
      vm.$http
        .get('app/customers/search?q=' + search)
        .then(res => (vm.result = res.data))
        .finally(() => (vm.searching = false));
    }, search_delay || 250),
    cfRule(attr) {
      let rules;
      if (attr.type == 'number') {
        rules = [
          {
            type: 'number',
            name: attr.slug,
            trigger: 'change',
            required: attr.required == 1,
            message: this.$t('field_is_required', { field: attr.name }),
          },
        ];
      } else if (attr.type == 'checkbox') {
        rules = [
          {
            min: 1,
            type: 'array',
            name: attr.slug,
            trigger: 'change',
            required: attr.required == 1,
            message: this.$t('field_is_required', { field: attr.name }),
          },
        ];
      } else {
        rules = [
          {
            name: attr.slug,
            trigger: 'change',
            required: attr.required == 1,
            message: this.$t('field_is_required', { field: attr.name }),
          },
        ];
      }
      this.rules[attr.slug] = rules;
      return rules;
    },
    handleSubmit(page, stay = false) {
      this.$refs.paymentForm.validate().then(valid => {
        if (valid) {
          if (this.paymentForm.gateway == 'gift_card' && parseFloat(this.gift_card.balance) < this.paymentForm.amount) {
            this.errors.payment.amount = this.$t('gift_card_payment_error');
            this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('gift_card_payment_error'), duration: 10 });
            return;
          }
          this.loading = true;
          this.submit();
        } else {
          this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
        }
      });
    },
    submit() {
      let post = 'app/sales?with=items';
      let msg = 'added';
      let msg_text = 'record_added';
      if (this.order.id && this.order.id != '') {
        msg = 'updated';
        msg_text = 'record_updated';
        this.order['_method'] = 'PUT';
        post = post + '/' + this.order.id;
      }
      if (this.attributes.length > 0) {
        let extras = this.attributes.map(attr => {
          let extra = {};
          extra[attr.slug] = this.order[attr.slug];
          return extra;
        });
        this.order.extra_attributes = Object.assign(...extras);
      }
      let items = [...this.order.items];
      items = items.map(i => {
        let item = {
          id: i.id,
          name: i.name,
          code: i.code,
          cost: i.cost,
          price: i.price,
          item_id: i.item_id,
          quantity: i.quantity,
          net_cost: i.net_cost,
          net_price: i.net_price,
          tax_amount: i.tax_amount,
          unit_cost: i.unit_cost,
          unit_price: i.unit_price,
          tax_included: i.tax_included,
          taxes: i.taxes,
          allTaxes: i.allTaxes,
          categories: i.categories,
          promotions: i.promotions,
          allPromotions: i.allPromotions,
          item_promotions: i.allPromotions,
          discount_amount: i.discount_amount,
          discount: i.discount ? i.discount : 0,
          // selected: i.selected,
        };
        item.selected = { modifiers: [], variations: [], portions: [] };
        if (i.selected.modifiers.length) {
          item.selected.modifiers = i.selected.modifiers.map(m => ({
            id: m.mId,
            selected: m.id,
            price: m.price,
            quantity: m.quantity,
          }));
        }
        if (i.selected.variations.length) {
          item.selected.variations = i.selected.variations.map(v => ({ id: v.id, price: v.price, quantity: v.quantity }));
        }
        if (i.selected.portions.length) {
          item.selected.portions = i.selected.portions.map(p => ({
            id: p.id,
            cost: p.cost,
            price: p.price,
            quantity: p.quantity,
            essentials: p.essentials.map(e => ({ id: e.id, item_id: e.item_id, variation_id: e.variation_id })),
            choosables: p.choosables.map(g => ({ id: g.id, selected: g.selected, variation_id: g.variation_id })),
            portion_items: p.portion_items.map(e => ({ id: e.id, item_id: e.item_id, variation_id: e.variation_id })),
          }));
        }
        if (item.promotions && item.promotions.length) {
          item.promotions = item.allPromotions.filter(p => item.promotions.includes(p.id));
        }
        if (item.categories && item.categories.length) {
          item.categories = item.categories.map(c => {
            return { id: c.id, promotions: c.promotions && c.promotions.length ? c.promotions.map(p => p.id) : [] };
            // c.promotions = c.promotions && c.promotions.length ? c.promotions.map(p => p.id) : [];
            // return c;
          });
        }
        if (item.selected.variations.length) {
          item.selected.variations = item.selected.variations.map(v => {
            v.taxes = v.taxes ? v.taxes.map(t => t.id) : [];
            v.allTaxes = v.item_taxes = v.allTaxes ? v.allTaxes.map(t => t.id) : [];
            return v;
          });
        }

        item.selected.serials = i.selected.serials;
        item.taxes = item.taxes ? item.taxes.map(t => t.id) : [];
        item.allTaxes = item.item_taxes = item.allTaxes ? item.allTaxes.map(t => t.id) : [];
        item.promotions = i.promotions && i.promotions.length ? i.promotions.map(p => p.id) : [];
        item.allPromotions = item.item_promotions = i.allPromotions && i.allPromotions.length ? i.allPromotions.map(p => p.id) : [];
        return item;
      });

      let order = { ...this.order, items, oId: this.$store.getters.current };
      delete order.user;
      delete order.customer;
      delete order.location;
      order.pos = 1;
      order.register_record_id = this.$store.getters.register.id;
      order.register_id = this.$store.getters.register.register_id;
      order.date = this.$moment(this.order.date).format(this.$moment.HTML5_FMT.DATE);
      let form = { ...order, payment: this.paymentForm };
      let data = this.$form(form);
      this.$http
        .post(post, data)
        .then(res => {
          if (res.data.success) {
            this.$Notice.destroy();
            this.$store.commit('UpdateLastPOSSale', res.data.data);
            this.$Notice.success({ title: this.$tc('sale') + ' ' + this.$t(msg), desc: this.$t(msg_text) });
            this.extra = this.payment = false;
            this.$event.fire('order:delete', true);
            if (this.$store.getters.settings.print_dialog == 1) {
              this.$event.fire('pos:printReceipt');
            }
          } else {
            this.$Notice.error({ title: this.$tc('failed'), desc: this.$t('failed_error_text'), duration: 120 });
          }
        })
        .catch(error => {
          if (!error.payment) {
            error.payment = {};
          }
          this.errors = error;
        })
        .finally(() => (this.loading = false));
    },
    updateCF(field, value) {
      this.order[field] = value;
      setTimeout(() => {
        this.$refs.orderForm.validateField(field);
      }, 1000);
    },
  },
};
</script>

<style lang="scss" scoped>
@import 'scss/order-items.scss';
</style>
