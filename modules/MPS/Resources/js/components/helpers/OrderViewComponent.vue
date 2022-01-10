<template>
  <div style="order-container">
    <div class="order" v-if="irecord.id">
      <div class="order-header">
        <div class="details">
          <img class="logo" :src="irecord.location && irecord.location.logo ? irecord.location.logo : $store.state.settings.default_logo" />
          <div class="header-text">
            <ul>
              <li>
                <strong>{{ $store.getters.settings.name }} ({{ $store.getters.settings.short_name }})</strong>
              </li>
              <li>
                <strong>{{ $store.getters.settings.company }}</strong>
              </li>
              <li>{{ $store.getters.settings.email }} <span class="text-muted">|</span> {{ $store.getters.settings.phone }}</li>
              <li>{{ $store.getters.settings.address }}</li>
            </ul>
          </div>
        </div>
        <vue-barcode
          :width="1"
          :margin="0"
          :height="50"
          :fontSize="12"
          :margin-top="0"
          format="code39"
          class="barcode"
          :margin-bottom="2"
          :value="irecord.reference"
        >
          {{ $t('barcode_error') }}
        </vue-barcode>
      </div>

      <Row v-if="heading" :gutter="16" class="border-t">
        <Col :xs="24" class="border-y py-2">
          <h3 class="text-center" style="text-transform: uppercase;">
            {{ heading }}
          </h3>
        </Col>
      </Row>
      <Row :gutter="16" class="border-y py-2">
        <Col :xs="24" :sm="12">
          <span v-if="irecord.date">
            {{ $t('date') }}: <strong>{{ irecord.date | date }}</strong>
          </span>
          <span v-if="recurring">
            {{ $t('start_date') }}: <strong>{{ irecord.start_date | date }}</strong>
            <br />
            {{ $t('created_at') }}: <strong>{{ irecord.created_at | datetime }}</strong>
          </span>
        </Col>
        <Col :xs="24" :sm="12">
          {{ $t('reference') }}: <strong>{{ irecord.reference }}</strong>
          <span v-if="recurring">
            <br />
            {{ $t('interval') }}: <strong>{{ $t(irecord.repeat) }}</strong>
          </span>
        </Col>
      </Row>

      <Alert v-if="irecord.void" type="error" show-icon class="mt16">{{ $t('order_void_text') }}</Alert>
      <Alert v-if="irecord.draft" type="warning" show-icon class="mt16">{{ $t('order_draft_text') }}</Alert>
      <Alert show-icon type="warning" class="mt16 np" v-if="irecord.recurring_sale_id">
        {{ $t('auto_generated_with_recurring', { x: irecord.recurring_sale_id }) }}
      </Alert>
      <Row :gutter="16" class="mt16">
        <Col :xs="24" :sm="12">
          <p>{{ $tc('store') }}:</p>
          <div v-if="irecord[from]">
            <strong>{{ irecord[from].name }}</strong
            ><br />
            <span v-if="irecord[from].address">{{ irecord[from].address }}</span>
            <span v-if="irecord[from].state_name">{{ irecord[from].state_name }}</span>
            <span v-if="irecord[from].country_name">{{ irecord[from].country_name }}</span
            ><br />
            <span v-if="irecord[from].phone">{{ irecord[from].phone }}</span
            ><br />
            <span v-if="irecord[from].email">{{ irecord[from].email }}</span
            ><br />
          </div>
        </Col>
        <Col :xs="24" :sm="12" v-if="to">
          <p>{{ toText }}:</p>
          <div v-if="irecord[to]">
            <strong>{{ irecord[to].name }}</strong
            ><br />
            <span v-if="irecord[to].address">{{ irecord[to].address }}</span>
            <span v-if="irecord[to].state_name">{{ irecord[to].state_name }}</span>
            <span v-if="irecord[to].country_name">{{ irecord[to].country_name }}</span
            ><br />
            <span v-if="irecord[to].phone">{{ irecord[to].phone }}</span
            ><br />
            <span v-if="irecord[to].email">{{ irecord[to].email }}</span
            ><br />
          </div>
        </Col>
      </Row>
      <div
        class="mt16 table-wrapper"
        v-if="extra && checkExtraAttributes(irecord.extra_attributes)"
        v-html="renderExtraAttributes(irecord.extra_attributes, irecord.attributes)"
      ></div>

      <div class="table-wrapper" v-if="irecord.items">
        <table class="table">
          <thead>
            <tr>
              <th class="text-left">{{ $t('description') }}</th>
              <th style="width: 90px;">{{ $t('quantity') }}</th>
              <template v-if="!onlyQty">
                <th style="width: 90px;">{{ $t(field) }}</th>
                <template v-if="payment && $store.getters.settings.show_discount == 1">
                  <th style="width: 90px;">{{ $t('discount') }}</th>
                </template>
                <template v-if="$store.getters.settings.show_tax == 1">
                  <th style="width: 100px;">{{ $t('taxes') }}</th>
                </template>
                <th style="width: 100px;">{{ $t('subtotal') }}</th>
              </template>
            </tr>
          </thead>
          <tbody>
            <template v-for="item in irecord.items">
              <template v-if="item.variations && item.variations.length">
                <tr :key="'i_' + item.id" class="border-b-0">
                  <td>
                    <strong>{{ item.name }} [{{ item.code }}]</strong>
                    <div v-if="item.variation">{{ item.variation.id }}</div>
                  </td>
                  <td></td>
                  <template v-if="!onlyQty">
                    <td></td>
                    <template v-if="payment && $store.getters.settings.show_discount == 1">
                      <td></td>
                    </template>
                    <template v-if="$store.getters.settings.show_tax == 1">
                      <td></td>
                    </template>
                    <td></td>
                  </template>
                </tr>
                <tr :key="'v_' + v.id + vi" class="border-y-0" v-for="(v, vi) in item.variations">
                  <td>
                    <span v-html="metaString(v.meta)"></span>
                  </td>
                  <td class="text-right">
                    {{ formatQuantity(v.pivot.quantity) }}
                  </td>
                  <template v-if="!onlyQty">
                    <td class="text-right">{{ formatNumber(v.pivot[field]) }}</td>
                    <template v-if="payment && $store.getters.settings.show_discount == 1">
                      <td class="text-right">
                        {{ formatQuantity(v.pivot.total_discount_amount) }}
                      </td>
                    </template>
                    <template v-if="$store.getters.settings.show_tax == 1">
                      <td class="text-right">
                        {{ formatNumber(v.pivot.total_tax_amount) }}
                      </td>
                    </template>
                    <td class="text-right">{{ formatNumber(v.pivot.total) }}</td>
                  </template>
                </tr>
              </template>
              <template v-else-if="item.portions && item.portions.length">
                <tr :key="'pi_' + item.id" class="border-b-0">
                  <td>
                    <strong>{{ item.name }}</strong>
                  </td>
                  <td></td>
                  <template v-if="!onlyQty">
                    <td></td>
                    <template v-if="payment && $store.getters.settings.show_discount == 1">
                      <td></td>
                    </template>
                    <template v-if="$store.getters.settings.show_tax == 1">
                      <td></td>
                    </template>
                    <td></td>
                  </template>
                </tr>
                <template v-for="(p, pi) in item.portions">
                  <tr :key="'p_' + p.id + pi" class="bold border-y-0">
                    <td class="capitalize">
                      {{ p.name }}
                    </td>
                    <td class="text-right">
                      {{ p.pivot.quantity | formatNumber(2) }}
                    </td>
                    <template v-if="!onlyQty">
                      <td class="text-right">
                        <span v-if="$store.getters.settings.show_tax == 1">
                          {{ formatNumber(p.pivot['net_' + field]) }}
                        </span>
                        <span v-else-if="$store.getters.settings.show_discount == 1">
                          {{ formatNumber(parseFloat(p.pivot[field])) }}
                        </span>
                        <span v-else>
                          {{
                            formatNumber(parseFloat(p.pivot[field]) - parseFloat(p.pivot.discount_amount) + parseFloat(p.pivot.tax_amount))
                          }}
                        </span>

                        <!-- {{ p.pivot['net_'+field] | formatNumber(2) }} -->
                      </td>
                      <template v-if="payment && $store.getters.settings.show_discount == 1">
                        <td class="text-right">
                          {{ formatQuantity(p.pivot.total_discount_amount) }}
                        </td>
                      </template>
                      <template v-if="$store.getters.settings.show_tax == 1">
                        <td class="text-right">
                          {{ formatNumber(p.pivot.total_tax_amount) }}
                        </td>
                      </template>
                      <td class="text-right">
                        {{ formatNumber(p.pivot.total) }}
                      </td>
                    </template>
                  </tr>
                  <tr :key="'pe_' + pe.id" v-for="(pe, pei) in p.portion_items" class="border-y-0">
                    <td class="pt-0">
                      {{ pe.item.name }}
                      <span v-if="pe.meta"> (<span v-html="metaString(pe.meta)"></span>) </span>
                    </td>
                    <td class="pt-0 text-right">
                      {{ (parseFloat(pe.quantity) * parseFloat(p.pivot.quantity)) | formatNumber(2) }}
                    </td>
                    <template v-if="!onlyQty">
                      <td class="pt-0 text-right"></td>
                      <template v-if="payment && $store.getters.settings.show_discount == 1">
                        <td class="text-right"></td>
                      </template>
                      <template v-if="$store.getters.settings.show_tax == 1">
                        <td class="text-right"></td>
                      </template>
                      <td class="pt-0 text-right"></td>
                    </template>
                  </tr>
                  <tr :key="'pei_' + pei + '_' + pi" v-for="(pe, pei) in p.essentials" class="border-y-0">
                    <td class="pt-0">
                      {{ pe.item.name }}
                      <span v-if="pe.meta"> (<span v-html="metaString(pe.meta)"></span>) </span>
                    </td>
                    <td class="pt-0 text-right">
                      {{ (parseFloat(pe.quantity) * parseFloat(p.pivot.quantity)) | formatNumber(2) }}
                    </td>
                    <template v-if="!onlyQty">
                      <td class="pt-0 text-right">
                        <!-- {{ pe[field] | formatNumber(2) }} -->
                      </td>
                      <template v-if="payment && $store.getters.settings.show_discount == 1">
                        <td class="text-right">
                          <!-- {{ formatNumber(pe.pivot.total_discount_amount) }} -->
                        </td>
                      </template>
                      <template v-if="$store.getters.settings.show_tax == 1">
                        <td class="text-right">
                          <!-- {{ formatNumber(pe.pivot.total_tax_amount) }} -->
                        </td>
                      </template>
                      <td class="pt-0 text-right">
                        <!-- {{ pe.total | formatNumber(2) }} -->
                      </td>
                    </template>
                  </tr>
                  <template v-for="(pc, pci) in p.choosables">
                    <template v-for="(pcitem, pcin) in pc.items">
                      <template v-if="p.pivot.choosables.find(c => c.id == pc.id && pcitem.item_id == c.item_id)">
                        <tr :key="'pci_' + pci + '_' + pcin" class="border-y-0">
                          <td class="pt-0">
                            <strong>{{ pc.name }}</strong> {{ pcitem.item.name }}
                            <span v-if="pc.meta"> (<span v-html="metaString(pc.meta)"></span>) </span>
                          </td>
                          <td class="pt-0 text-right">
                            {{ (parseFloat(pcitem.quantity) * parseFloat(p.pivot.quantity)) | formatNumber(2) }}
                          </td>
                          <template v-if="!onlyQty">
                            <td class="pt-0 text-right">
                              <!-- {{ pcitem[field] | formatNumber(2) }} -->
                            </td>
                            <template v-if="payment && $store.getters.settings.show_discount == 1">
                              <td class="text-right">
                                <!-- {{ formatNumber(pcitem.pivot.total_discount_amount) }} -->
                              </td>
                            </template>
                            <template v-if="$store.getters.settings.show_tax == 1">
                              <td class="text-right">
                                <!-- {{ formatNumber(pcitem.pivot.total_tax_amount) }} -->
                              </td>
                            </template>
                            <td class="pt-0 text-right">
                              <!-- {{ pcitem.total | formatNumber(2) }} -->
                            </td>
                          </template>
                        </tr>
                      </template>
                    </template>
                  </template>
                </template>
              </template>
              <template v-else>
                <tr :key="'item_' + item.id">
                  <td>
                    <Avatar v-if="$store.getters.settings.show_image && item.item.photo" :src="item.item.photo" />
                    {{ item.item.name }} [{{ item.item.code }}]
                    <div v-if="item.item.alt_name">{{ item.item.alt_name }}</div>
                  </td>
                  <td class="text-right">{{ formatQuantity(item.quantity) }}</td>
                  <template v-if="!onlyQty">
                    <td class="text-right">
                      <span v-if="$store.getters.settings.show_tax == 1">
                        {{ formatNumber(item['net_' + field]) }}
                      </span>
                      <span v-else>
                        {{ formatNumber(item['unit_' + field]) }}
                      </span>
                    </td>
                    <template v-if="payment && $store.getters.settings.show_discount == 1">
                      <td class="text-right">{{ formatQuantity(item.total_discount_amount) }}</td>
                    </template>
                    <template v-if="$store.getters.settings.show_tax == 1">
                      <td class="text-right">{{ formatQuantity(item.total_tax_amount) }}</td>
                    </template>
                    <td class="text-right">{{ formatNumber(item.subtotal) }}</td>
                  </template>
                </tr>
              </template>
              <template v-if="item.modifier_options && item.modifier_options.length">
                <!-- <tr class="border-y-0" :key="'mo_' + item.modifier_options.length">
                      <td>
                        <strong>{{ $tc('modifier', 2) }}</strong>
                      </td>
                      <td></td>
                      <template v-if="!onlyQty">
                        <td></td>
                        <template v-if="payment && $store.getters.settings.show_discount == 1">
                          <td></td>
                        </template>
                        <template v-if="$store.getters.settings.show_tax == 1">
                          <td></td>
                        </template>
                        <td></td>
                      </template>
                    </tr> -->
                <tr
                  :key="'m_' + m.id"
                  v-for="(m, mi) in item.modifier_options"
                  :class="mi == item.modifier_options.length - 1 ? 'border-b border-t-0' : 'border-y-0'"
                >
                  <td class="pt0">
                    <strong>{{ m.modifier.title }}</strong>
                    <br />
                    {{ m.item.name }}
                  </td>
                  <td class="pt0 text-right">
                    <br />
                    {{ m.pivot.quantity | formatNumber(2) }}
                  </td>
                  <template v-if="!onlyQty">
                    <td class="text-right">
                      <br />
                      <span v-if="$store.getters.settings.show_tax == 1">
                        {{ formatNumber(m.pivot[field]) }}
                      </span>
                      <span v-else>
                        {{
                          formatNumber(parseFloat(m.pivot[field]) - parseFloat(m.pivot.discount_amount) + parseFloat(m.pivot.tax_amount))
                        }}
                      </span>
                    </td>
                    <template v-if="payment && $store.getters.settings.show_discount == 1">
                      <td class="text-right">
                        <br />
                        {{ formatQuantity(m.pivot.total_discount_amount) }}
                      </td>
                    </template>
                    <template v-if="$store.getters.settings.show_tax == 1">
                      <td class="text-right">
                        <br />
                        {{ formatNumber(m.pivot.total_tax_amount) }}
                      </td>
                    </template>
                    <td class="text-right">
                      <br />
                      {{ formatNumber(m.pivot.total) }}
                    </td>
                  </template>
                </tr>
              </template>
            </template>
          </tbody>
          <tfoot v-if="!onlyQty">
            <tr>
              <th class="text-left" :colspan="cols - ($store.getters.settings.show_tax == 1 ? 1 : 0)">{{ $t('total') }}</th>
              <th class="text-right" v-if="$store.getters.settings.show_tax == 1">
                {{ formatNumber(irecord.item_tax_amount) }}
              </th>
              <th class="text-right">{{ formatNumber(irecord.total) }}</th>
            </tr>
            <tr v-if="irecord.order_tax_amount > 0">
              <th class="text-left" :colspan="cols">{{ $t('order_tax') }}</th>
              <th class="text-right">{{ formatNumber(irecord.order_tax_amount) }}</th>
            </tr>
            <tr v-if="irecord.order_discount_amount > 0">
              <th class="text-left" :colspan="cols">{{ $t('order_discount') }}</th>
              <th class="text-right">{{ formatNumber(irecord.order_discount_amount) }}</th>
            </tr>
            <template v-if="$store.getters.settings.show_tax != 1">
              <tr v-if="irecord.total_tax_amount > 0">
                <th class="text-left" :colspan="cols">{{ $t('total_x', { x: $t('tax_amount') }) }}</th>
                <th class="text-right">{{ formatQuantity(irecord.total_tax_amount) }}</th>
              </tr>
            </template>
            <tr v-if="irecord.shipping && irecord.shipping > 0">
              <th class="text-left" :colspan="cols">{{ $t('shipping') }}</th>
              <th class="text-right">{{ formatNumber(irecord.shipping) }}</th>
            </tr>
            <tr v-if="irecord.total != irecord.grand_total">
              <th class="text-left" :colspan="cols">{{ $t('grand_total') }}</th>
              <th class="text-right">{{ formatNumber(irecord.grand_total) }}</th>
            </tr>
            <template v-if="payment">
              <template v-if="irecord.payments && irecord.payments.length">
                <tr v-for="payment in irecord.payments" :key="'p_' + payment.id">
                  <td :colspan="cols">
                    <strong>{{ $t('payment_settlement') }}</strong
                    ><br />
                    ({{ $t('date') }}: {{ payment.created_at | date }}, {{ $t('reference') }}: {{ payment.reference }})
                  </td>
                  <td class="text-right bold">{{ formatNumber(payment.pivot.amount) }}</td>
                </tr>
                <tr>
                  <th class="text-left" :colspan="cols">{{ $t('balance_due') }}</th>
                  <th class="text-right">{{ formatNumber(irecord.grand_total - total_paid) }}</th>
                </tr>
              </template>
              <template v-else>
                <tr>
                  <th class="text-left" :colspan="cols">{{ $t('paid') }}</th>
                  <th class="text-right">{{ formatNumber(0) }}</th>
                </tr>
                <tr>
                  <th class="text-left" :colspan="cols">{{ $t('balance_due') }}</th>
                  <th class="text-right">{{ formatNumber(irecord.grand_total) }}</th>
                </tr>
              </template>
            </template>
          </tfoot>
        </table>
      </div>

      <div class="table-wrapper npb" v-if="summary && $store.getters.settings.show_tax_summary && taxSummary()">
        <table class="table">
          <thead>
            <tr>
              <th colspan="5" class="text-left">{{ $t('tax_summary') }}</th>
            </tr>
            <tr>
              <th>{{ $t('name') }}</th>
              <th style="width: 90px;">{{ $t('code') }}</th>
              <th style="width: 90px;">{{ $t('qty_weight') }}</th>
              <th style="width: 150px;">{{ $t('tax_ex_amount') }}</th>
              <th style="width: 120px;">{{ $t('tax_amount') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="tax in taxSummary()" :key="'ts_' + tax.id">
              <td>{{ tax.name }}</td>
              <td class="text-center">{{ tax.code }}</td>
              <td class="text-center">{{ formatQuantity(tax.quantity) }}</td>
              <td class="text-right">{{ formatNumber(tax.item_net_amount) }}</td>
              <td class="text-right">{{ formatNumber(tax.amount) }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="4" class="text-right">{{ $t('total_x', { x: $tc('tax_amount') }) }}</th>
              <th class="text-right">{{ formatNumber(irecord.total_tax_amount) }}</th>
            </tr>
          </tfoot>
        </table>
      </div>

      <Card
        dis-hover
        class="mt16 npb"
        v-if="irecord.details || (irecord.location && irecord.location.footer) || $store.getters.settings.footer"
      >
        <p v-if="irecord.details" v-html="irecord.details"></p>
        <p v-if="irecord.location.footer" v-html="irecord.location.footer"></p>
        <p v-if="$store.getters.settings.footer" v-html="$store.getters.settings.footer"></p>
      </Card>

      <list-attachments-component :attachments="irecord.attachments" @remove="deleteAttachment" class="mt16" />
      <Row :gutter="16" class="mt16">
        <Col :xs="24" :sm="12">
          <!-- {{ $t('status') }}: <strong>{{ irecord.status | capitalize }}</strong> -->
        </Col>
        <Col :xs="24" :sm="12" v-if="irecord.user">
          {{ $t('created_by') }}: <strong>{{ irecord.user.name }}</strong> <br />{{ $t('created_at') }}:
          <strong>{{ irecord.created_at | datetime }}</strong>
        </Col>
      </Row>

      <span v-if="delivery" class="mb16">
        <Row :gutter="16" class="mt16">
          <Col :xs="24" :sm="12">
            {{ $t('status') }}: <strong>{{ irecord.status | capitalize }}</strong>
          </Col>
        </Row>
        <Row :gutter="16" class="mt16">
          <Col :xs="24" :sm="12">
            {{ $t('delivered_at') }}: <strong v-if="irecord.delivered_at">{{ irecord.delivered_at | datetime }}</strong>
          </Col>
          <Col :xs="24" :sm="12" v-if="irecord.user">
            {{ $t('delivered_by') }}: <strong>{{ irecord.deliveryMan ? irecord.deliveryMan.name : irecord.delivered_by }}</strong>
          </Col>
        </Row>
        <Row :gutter="16" class="mt16">
          <Col :xs="24" :sm="12">
            {{ $tc('driver') }}: <strong v-if="irecord.driver">{{ irecord.driver | capitalize }}</strong>
          </Col>
          <Col :xs="24" :sm="12">
            {{ $t('received_by') }}: <strong v-if="irecord.received_by">{{ irecord.received_by | capitalize }}</strong>
          </Col>
        </Row>
      </span>
      <p v-else class="cgd">{{ $t('order_cgd') }}</p>
    </div>

    <div class="np text-center">
      <Button class="mt16 mx8" type="primary" :loading="loading" :disabled="loading" icon="md-print" @click="print()">
        {{ $t('print') }}
      </Button>
    </div>
  </div>
</template>

<script>
import VueBarcode from 'vue-barcode';
import ListAttachmentsComponent from '@mpscom/helpers/ListAttachmentsComponent';
export default {
  props: {
    heading: {
      type: String,
      default: '',
    },
    record: {
      type: Object,
      required: true,
    },
    to: {
      type: [String, Boolean],
      default: 'customer',
    },
    from: {
      type: [String, Boolean],
      default: 'location',
    },
    toText: {
      type: String,
      default: 'bill_to',
    },
    field: {
      type: String,
      default: 'price',
    },
    delivery: {
      type: Boolean,
      default: false,
    },
    onlyQty: {
      type: Boolean,
      default: false,
    },
    extra: {
      type: Boolean,
      default: true,
    },
    payment: {
      type: Boolean,
      default: true,
    },
    summary: {
      type: Boolean,
      default: true,
    },
    recurring: {
      type: Boolean,
      default: false,
    },
  },
  components: {
    VueBarcode,
    ListAttachmentsComponent,
  },
  data() {
    return {
      irecord: {},
      loading: false,
    };
  },
  created() {
    this.reDo();
  },
  watch: {
    record() {
      this.reDo();
    },
  },
  methods: {
    reDo() {
      if (this.record.id) {
        this.irecord = this.record;
        this.irecord.items = this.irecord.items.map(i => {
          if (i.portions && i.portions.length) {
            i.portions = i.portions.map(p => {
              p.choosables = p.choosables.map(e => {
                let se = p.pivot && p.pivot.choosables ? p.pivot.choosables.find(es => es.id == e.id) : false;
                if (se) {
                  let item = e.items.find(i => i.item_id == se.item_id).item;
                  e.meta = item.has_variants ? item.variations.find(v => v.id == se.variation_id).meta : null;
                }
                return e;
              });
              p.essentials = p.essentials.map(e => {
                let se = p.pivot && p.pivot.essentials ? p.pivot.essentials.find(es => es.id == e.id) : false;
                e.meta = se && e.item.has_variants ? e.item.variations.find(v => v.id == se.variation_id).meta : null;
                return e;
              });
              p.portion_items = p.portion_items.map(e => {
                if (p.pivot.portion_items) {
                  let se = p.pivot.portion_items ? p.pivot.portion_items.find(es => es.id == e.id) : false;
                  e.meta = se && e.item.has_variants ? e.item.variations.find(v => v.id == se.variation_id).meta : null;
                }
                return e;
              });
              return p;
            });
          }
          return i;
        });
      }
    },
    print() {
      window.print();
    },
    deleteAttachment(attachment) {
      this.$emit('remove', attachment);
    },
    taxSummary() {
      let taxes = [];
      if ((!this.irecord.items || !this.irecord.items.length) && (!this.irecord.taxes || !this.irecord.taxes.length)) {
        return false;
      }
      if (this.irecord.items && this.irecord.items.length) {
        this.irecord.items.map(item => {
          if (item.taxes && item.taxes.length) {
            item.taxes.map(tax => {
              let et = taxes.find(t => t.id == tax.id);
              let total_net = this.formatDecimal(parseFloat(item['net_' + this.field]) * parseFloat(item.quantity));
              let amount = this.formatDecimal((parseFloat(total_net) * parseFloat(tax.rate)) / 100);
              if (et) {
                et.amount += amount;
                et.quantity += parseFloat(item.quantity);
                et.item_net_amount += parseFloat(total_net);
                et.item_tax_amount += parseFloat(item.total_tax_amount);
              } else {
                taxes.push({
                  id: tax.id,
                  code: tax.code,
                  name: tax.name,
                  rate: tax.rate,
                  amount: amount,
                  number: tax.number,
                  quantity: parseFloat(item.quantity),
                  item_net_amount: parseFloat(total_net),
                  item_tax_amount: parseFloat(item.total_tax_amount),
                });
              }
            });
          }
        });
      }
      if (this.irecord.taxes && this.irecord.taxes.length) {
        this.irecord.taxes.map(tax => {
          let et = taxes.find(t => t.id == tax.id);
          let amount = this.formatDecimal((parseFloat(this.irecord.total) * parseFloat(tax.rate)) / 100);
          if (et) {
            et.amount += amount;
            et.item_net_amount += this.irecord.total; // TODO net total
            et.item_tax_amount += this.irecord.order_tax_amount;
          } else {
            taxes.push({
              id: tax.id,
              code: tax.code,
              name: tax.name,
              rate: tax.rate,
              amount: amount,
              number: tax.number,
              item_net_amount: this.irecord.total, // TODO net total
              item_tax_amount: this.irecord.order_tax_amount,
            });
          }
        });
      }
      return taxes.length ? taxes : false;
    },
  },
  computed: {
    total_paid() {
      return this.irecord && this.irecord.payments ? this.irecord.payments.reduce((a, p) => a + p.amount, 0) : 0;
    },
    cols() {
      if (this.onlyQty) {
        return 1;
      }
      let col = 3;
      if (this.payment && this.$store.getters.settings.show_discount == 1) {
        col++;
      }
      if (this.$store.getters.settings.show_tax == 1) {
        col++;
      }
      return col;
    },
  },
};
</script>
