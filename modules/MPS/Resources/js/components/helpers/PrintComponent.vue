<template>
  <div class="print-modal" v-if="vm.printData && vm.printData.order && (vm.printData.order.oId || vm.printData.order.orderId)">
    <div class="receipt-logo">
      <img :src="vm.printData.order.location.logo || vm.$store.state.settings.default_logo" />
    </div>
    <h3 class="text-center mb16">{{ settings.name }}</h3>
    <div class="text-center mb16">
      {{ vm.$tc('location') }}<br />
      <strong>{{ vm.printData.order.location.name }}</strong>
      <div v-if="vm.printData.type != 'order'">
        <div v-if="vm.printData.order.location.address || vm.printData.order.location.state || vm.printData.order.location.country">
          {{ vm.printData.order.location.address }} {{ vm.printData.order.location.state_name }}
          {{ vm.printData.order.location.country_name }}
        </div>
        <div v-if="vm.printData.order.location.email || vm.printData.order.location.phone">
          {{ vm.printData.order.location.email }} {{ vm.printData.order.location.phone }}
        </div>
        <div class="text-center mt16" v-if="vm.printData.order.location.header || vm.$store.state.settings.header">
          {{ vm.$store.state.settings.header }} <br />{{ vm.printData.order.location.header }}
        </div>
      </div>
    </div>
    <h3 class="text-center mb16">
      {{ vm.$tc(vm.printData.type) }}
      <!-- <span v-if="vm.printData.order.oId">({{ vm.printData.order.oId }})</span>
      <div v-if="vm.printData.order.orderId" style="font-size:0.8em;">{{ vm.printData.order.orderId }}</div> -->
    </h3>
    <div v-if="vm.printData.order.id">{{ vm.$t('id') }}: {{ vm.printData.order.id }}</div>
    <div v-if="vm.printData.order.orderId">{{ vm.$t('order_id') }}: {{ vm.printData.order.orderId }}</div>
    <div v-if="vm.printData.order.oId">{{ vm.$t('reference') }}: {{ vm.printData.order.oId }}</div>
    <div v-if="vm.printData.order.date">{{ vm.$tc('date') }}: {{ vm.printData.order.date | date }}</div>
    <div v-if="vm.printData.order.user">
      {{ vm.$tc('created_by') }}: {{ vm.printData.order.user.name }} ({{ vm.printData.order.user.username }})
    </div>
    <div v-else>{{ vm.$tc('created_by') }}: {{ vm.$store.getters.user.name }} ({{ vm.$store.getters.user.username }})</div>
    <div v-if="vm.printData.order.created_at">{{ vm.$tc('created_at') }}: {{ vm.printData.order.created_at | datetime }}</div>
    <div>{{ vm.$tc('printed_at') }}: {{ new Date() | datetime }}</div>
    <div v-if="bill">
      <div v-if="vm.printData.order.customer && vm.$store.state.settings.default_customer == vm.printData.order.customer.id">
        {{ vm.$tc('customer') + ': ' }}
        <span class="bold">
          {{ vm.printData.order.customer.name || vm.printData.order.customer.label }}
          <span v-if="vm.printData.order.customer.company">({{ vm.printData.order.customer.company }})</span>
        </span>
      </div>
      <div v-else-if="vm.printData.order.customer && vm.$store.state.settings.default_customer != vm.printData.order.customer.id">
        <div>{{ vm.$tc('customer') + ':' }}</div>
        <span class="bold">
          {{ vm.printData.order.customer.name || vm.printData.order.customer.label }}
          <template v-if="vm.printData.order.customer.company">({{ vm.printData.order.customer.company }})</template>
        </span>
        <div>
          <template v-if="vm.printData.order.customer.address">{{ vm.printData.order.customer.address }}</template>
          <template v-if="vm.printData.order.customer.state">{{ vm.printData.order.customer.state }}</template>
          <template v-if="vm.printData.order.customer.country">{{ vm.printData.order.customer.country }}</template>
        </div>
        <div>
          <template v-if="vm.printData.order.customer.email">{{ vm.printData.order.customer.email }}</template>
          <template v-if="vm.printData.order.customer.phone">{{ vm.printData.order.customer.phone }}</template>
        </div>
      </div>
      <div v-else>{{ vm.$tc('customer') + ': ' + vm.printData.customer.label }}</div>
    </div>
    <!-- <div>{{ vm.$tc('order') + ': ' + vm.printData.order.oId }}</div> -->
    <div class="mt16 mb16">
      <List border size="small" class="mt16">
        <ListItem class="heading">
          <div class="item-details">{{ vm.$t('name') }}</div>
          <div v-if="bill" class="item-price text-center">{{ vm.$t('price') }}</div>
          <div class="item-qty text-center">{{ vm.$t('qty') }}</div>
          <div v-if="bill" class="item-total text-center">{{ vm.$t('subtotal') }}</div>
        </ListItem>
        <template v-for="(row, rowi) in vm.printData.order.items">
          <template v-if="row.selected.variations && row.selected.variations.length">
            <ListItem :key="'row_' + rowi" class="bb0">
              <div class="item-details">
                <div class="bold">
                  {{ row.code }}<br />{{ row.name }}
                  <div v-if="row.comment">{{ row.comment }}</div>
                </div>
              </div>
              <!-- <template v-if="!receipt">
                <template v-if="!bill">
                  <div class="item-qty">{{ row.quantity | formatQtyDecimal(settings.quantity_decimals) }}</div>
                </template>
                <template>
                  <div class="item-price">
                      {{ (row.price + vm.calcItemTax(row)) | formatDecimal(settings.decimals) }}
                  </div>
                  <div class="item-qty">{{ row.quantity | formatQtyDecimal(settings.quantity_decimals) }}</div>
                  <div class="item-total">{{ vm.calcRowTotal(row) | formatDecimal(settings.decimals) }}</div>
                </template>
              </template> -->
            </ListItem>
            <template v-for="(sv, svi) in row.selected.variations">
              <ListItem
                :key="'svi_' + sv.sku"
                :class="row.selected.modifiers.length || row.selected.variations.length - 1 != svi ? 'bb0' : ''"
              >
                <div class="item-details">
                  <div v-html="vm.metaString(sv.meta)"></div>
                </div>
                <div v-if="bill && !receipt" class="item-price">
                  {{ (parseFloat(sv.price - sv.discount_amount) + vm.calcItemTax(sv)) | formatNumber(vm.$store.state.settings.decimals) }}
                </div>
                <div v-else-if="receipt" class="item-price">
                  {{ (parseFloat(sv.pivot.net_price) + parseFloat(sv.pivot.tax_amount)) | formatNumber(vm.$store.state.settings.decimals) }}
                </div>
                <div v-if="!receipt" class="item-qty">{{ sv.quantity | formatQtyDecimal(settings.quantity_decimals) }}</div>
                <div v-else class="item-qty">{{ sv.pivot.quantity | formatQtyDecimal(settings.quantity_decimals) }}</div>
                <div v-if="bill && !receipt" class="item-total">
                  {{ (parseFloat(sv.price - sv.discount_amount + vm.calcItemTax(sv)) * sv.quantity) | formatDecimal(settings.decimals) }}
                </div>
                <!-- <div v-if="bill && !receipt" class="item-total"></div> -->
                <div v-else-if="receipt" class="item-total">{{ sv.pivot.total | formatDecimal(settings.decimals) }}</div>
              </ListItem>
            </template>
          </template>
          <template v-else-if="row.selected.portions && row.selected.portions.length">
            <ListItem class="bb0" :key="'row_' + rowi">
              <div class="item-details">
                <div class="bold">
                  {{ row.code }}<br />{{ row.name }}
                  <div v-if="row.comment">{{ row.comment }}</div>
                </div>
              </div>
              <!-- <template v-if="!bill && !receipt">
                <div class="item-qty">{{ row.quantity | formatQtyDecimal(settings.quantity_decimals) }}</div>
              </template>
              <template>
                <div class="item-price">
                  {{ row.unit_price | formatDecimal(settings.decimals) }}
                </div>
                <div class="item-qty">{{ row.quantity | formatQtyDecimal(settings.quantity_decimals) }}</div>
                <div class="item-total">{{ row.subtotal | formatDecimal(settings.decimals) }}</div>
              </template> -->
            </ListItem>
            <template v-for="(sp, spi) in row.selected.portions">
              <template v-if="receipt">
                <ListItem
                  :key="'pd_' + sp.id + '_' + spi"
                  :class="
                    (sp.portion_items && sp.portion_items.length) ||
                    (sp.essentials && sp.essentials.length) ||
                    (sp.choosables && sp.choosables.length)
                      ? 'bb0'
                      : ''
                  "
                >
                  <div class="item-details">
                    #{{ spi + 1 }} {{ vm.$tc('portion') }}:
                    <strong>{{ vm.$t(sp.name) }}</strong>
                  </div>
                  <div class="item-price">
                    {{
                      (parseFloat(sp.pivot.price) - parseFloat(sp.pivot.discount_amount)) | formatNumber(vm.$store.state.settings.decimals)
                    }}
                  </div>
                  <div class="item-qty">{{ sp.pivot.quantity | formatQtyDecimal(settings.quantity_decimals) }}</div>
                  <div class="item-total">
                    {{ sp.pivot.total | formatNumber(vm.$store.state.settings.decimals) }}
                  </div>
                </ListItem>
                <template v-for="(pe, pei) in sp.portion_items">
                  <ListItem :key="'ppi_' + pei" :class="sp.portion_items && sp.portion_items.length ? 'bb0 py0' : ''">
                    <div class="item-details">
                      <span style="font-size:10px;">#{{ pei + 1 }}</span>
                      {{ pe.item.name }}
                      <span v-if="pe.meta"> (<span v-html="metaString(pe.meta)"></span>) </span>
                    </div>
                    <!-- <div class="item-price"></div> -->
                    <div class="item-qty">
                      {{ (parseFloat(pe.quantity) * parseFloat(sp.pivot.quantity)) | formatNumber(2) }}
                    </div>
                    <div class="item-total"></div>
                  </ListItem>
                </template>
                <template v-for="(pe, pei) in sp.essentials">
                  <ListItem :key="'pde_' + pei + '_' + spi" :class="sp.essentials && sp.essentials.length ? 'bb0 py0' : ''">
                    <div class="item-details">
                      <!-- <span style="font-size:10px;">#{{ pei + 1 }}</span> -->
                      {{ pe.item.name }}
                      <span v-if="pe.meta"> (<span v-html="metaString(pe.meta)"></span>) </span>
                    </div>
                    <!-- <div class="item-price"></div> -->
                    <div class="item-qty">
                      {{ parseFloat(pe.quantity * sp.pivot.quantity) | formatQtyDecimal(settings.quantity_decimals) }}
                    </div>
                    <div class="item-total"></div>
                  </ListItem>
                </template>
                <template v-for="(pc, pci) in sp.choosables">
                  <template v-for="(pcitem, pcin) in pc.items">
                    <template v-if="sp.pivot.choosables.find(c => c.id == pc.id && pcitem.item_id == c.item_id)">
                      <ListItem
                        :key="'pci_' + pci + '_' + pcin"
                        :class="row.selected.portions.length - 1 == spi && sp.choosables.length - 1 == pci ? '' : 'bb0 py0'"
                      >
                        <div class="item-details">
                          {{ pc.name }} <br />
                          <!-- <span style="font-size:10px;">#{{ pci + 1 + sp.essentials.length }}</span> -->
                          {{ pcitem.item.name }}
                          <span v-if="pc.meta"> (<span v-html="metaString(pc.meta)"></span>) </span>
                        </div>
                        <div class="item-qty">
                          <br />
                          {{ (parseFloat(pcitem.quantity) * parseFloat(sp.pivot.quantity)) | formatNumber(2) }}
                        </div>
                        <div class="item-total"></div>
                      </ListItem>
                    </template>
                  </template>
                </template>
              </template>
              <template v-else>
                <!-- <ListItem :key="'spi_' + sp.id" class="bb0">
                  <div class="item-details"></div>
                  <div v-if="bill" class="item-price">
                    {{ (sp.price - sp.discount_amount + vm.calcItemTax(sp)) | formatNumber(vm.$store.state.settings.decimals) }}
                  </div>
                  <div class="item-qty">{{ sp.quantity | formatQtyDecimal(settings.quantity_decimals) }}</div>
                  <div v-if="bill" class="item-total">
                    {{
                      ((sp.price - sp.discount_amount + vm.calcItemTax(sp)) * sp.quantity) | formatNumber(vm.$store.state.settings.decimals)
                    }}
                  </div>
                </ListItem> -->

                <template v-for="p in row.portions.filter(ip => ip.id == sp.id)">
                  <ListItem
                    :key="'pd_' + p.id + '_' + spi"
                    :class="(p.essentials && p.essentials.length) || (p.choosables && p.choosables.length) ? 'bb0' : ''"
                  >
                    <div class="item-details">
                      #{{ spi + 1 }} {{ vm.$tc('portion') }}:
                      <strong>{{ vm.$t(p.name) }}</strong>
                    </div>
                    <div v-if="bill" class="item-price">
                      {{ (sp.price - sp.discount_amount + vm.calcItemTax(sp)) | formatNumber(vm.$store.state.settings.decimals) }}
                    </div>
                    <div class="item-qty bold">{{ sp.quantity | formatQtyDecimal(settings.quantity_decimals) }}</div>
                    <div v-if="bill" class="item-total">
                      {{
                        ((sp.price - sp.discount_amount + vm.calcItemTax(sp)) * sp.quantity)
                          | formatNumber(vm.$store.state.settings.decimals)
                      }}
                    </div>
                  </ListItem>
                  <template v-for="(e, ei) in p.essentials">
                    <ListItem :key="'pde_' + ei + '_' + spi" :class="p.essentials && p.essentials.length ? 'bb0 py0' : ''">
                      <div class="item-details">
                        <!-- <span style="font-size:10px;">#{{ ei + 1 }}</span> -->
                        {{ e.item.name }}
                      </div>
                      <!-- <div class="item-price"></div> -->
                      <div class="item-qty">
                        {{ parseFloat(e.quantity * sp.quantity) | formatQtyDecimal(settings.quantity_decimals) }}
                      </div>
                      <div class="item-total" v-if="bill"></div>
                    </ListItem>
                  </template>
                  <template v-for="(g, gi) in p.choosables">
                    <template v-for="(gitem, gii) in g.items">
                      <template v-if="vm.getPortionChoosable(sp, g.id, gitem.item_id)">
                        <ListItem
                          :key="'gii_' + gii + '_' + gi + '_' + spi"
                          :class="row.selected.portions.length - 1 == spi && p.choosables.length - 1 == gi ? 'pt0' : 'bb0 py0'"
                        >
                          <div class="item-details">
                            <!-- <span style="font-size:10px;">#{{ gi + 1 + p.essentials.length }}</span> -->
                            {{ gitem.item.name }}
                          </div>
                          <!-- <div class="item-price"></div> -->
                          <div class="item-qty">
                            {{ parseFloat(gitem.quantity * sp.quantity) | formatQtyDecimal(settings.quantity_decimals) }}
                          </div>
                          <div class="item-total" v-if="bill"></div>
                        </ListItem>
                      </template>
                    </template>
                  </template>
                </template>
              </template>
            </template>
          </template>

          <template v-else>
            <ListItem :key="'row_' + rowi + '_' + row.id" :class="row.selected.modifiers && row.selected.modifiers.length ? 'bb0 py0' : ''">
              <div class="item-details bold">
                {{ row.code }} <br />{{ row.name }}
                <div v-if="row.comment">{{ row.comment }}</div>
              </div>
              <template v-if="!bill && !receipt">
                <div class="item-qty">{{ row.quantity | formatQtyDecimal(settings.quantity_decimals) }}</div>
              </template>
              <template v-else-if="bill && !receipt">
                <div class="item-price">
                  {{ (row.price - row.discount_amount + vm.calcItemTax(row)) | formatDecimal(settings.decimals) }}
                </div>
                <div class="item-qty">{{ row.quantity | formatQtyDecimal(settings.quantity_decimals) }}</div>
                <div class="item-total">{{ vm.calcRowTotal(row) | formatDecimal(settings.decimals) }}</div>
              </template>
              <template v-else-if="receipt">
                <div class="item-price">
                  {{ row.unit_price | formatDecimal(settings.decimals) }}
                </div>
                <div class="item-qty">{{ row.quantity | formatQtyDecimal(settings.quantity_decimals) }}</div>
                <div class="item-total">{{ row.subtotal | formatDecimal(settings.decimals) }}</div>
              </template>
            </ListItem>
          </template>
          <template v-if="row.selected.modifiers && row.selected.modifiers.length">
            <template v-for="(m, mi) in row.selected.modifiers">
              <ListItem :key="'mi_' + mi" :class="row.selected.modifiers.length - 1 == mi ? '' : 'bb0'">
                <div class="item-details" v-if="receipt">
                  <div class="bold">{{ m.modifier.title }}</div>
                  <div>{{ m.item.name }}</div>
                </div>
                <div class="item-details" v-else>
                  <div class="bold">{{ m.title }}</div>
                  <div>{{ m.option }}</div>
                </div>
                <div v-if="bill && !receipt" class="item-price">
                  <br />
                  {{ (m.price - m.discount_amount + vm.calcItemTax(m)) | formatDecimal(settings.decimals) }}
                </div>
                <div v-else-if="receipt" class="item-price">
                  <br />
                  {{ (parseFloat(m.pivot.net_price) + parseFloat(m.pivot.tax_amount)) | formatDecimal(settings.decimals) }}
                </div>
                <div class="item-qty" v-if="!receipt"><br />{{ m.quantity | formatQtyDecimal(settings.quantity_decimals) }}</div>
                <div class="item-qty" v-else><br />{{ m.pivot.quantity | formatQtyDecimal(settings.quantity_decimals) }}</div>
                <div v-if="bill && !receipt" class="item-total">
                  <br />
                  {{ ((m.price - m.discount_amount + vm.calcItemTax(m)) * m.quantity) | formatDecimal(settings.decimals) }}
                </div>
                <!-- <div v-if="bill && !receipt" class="item-total"></div> -->
                <div v-else-if="receipt" class="item-total">
                  <br />
                  {{ m.pivot.total | formatDecimal(settings.decimals) }}
                </div>
              </ListItem>
            </template>
          </template>
        </template>
        <ListItem class="heading last" v-if="bill && !receipt">
          <div class="item-details">{{ vm.$t('payable_amount') }}</div>
          <div class="item-total">{{ vm.orderPayableAmount | formatNumber(vm.$store.state.settings.decimals) }}</div>
        </ListItem>
        <template v-if="receipt">
          <ListItem class="heading last">
            <div class="item-details">{{ vm.$t('total') }}</div>
            <div class="item-total">{{ vm.printData.order.total | formatNumber(vm.$store.state.settings.decimals) }}</div>
          </ListItem>
          <ListItem class="heading last" v-if="vm.printData.order.order_discount_amount">
            <div class="item-details">{{ vm.$t('order_discount') }}</div>
            <div class="item-total">{{ vm.printData.order.order_discount_amount | formatNumber(vm.$store.state.settings.decimals) }}</div>
          </ListItem>
          <ListItem class="heading last">
            <div class="item-details">{{ vm.$t('tax_amount') }}</div>
            <div class="item-total">{{ vm.printData.order.total_tax_amount | formatNumber(vm.$store.state.settings.decimals) }}</div>
          </ListItem>
          <ListItem class="heading last">
            <div class="item-details">{{ vm.$t('grand_total') }}</div>
            <div class="item-total">{{ vm.printData.order.grand_total | formatNumber(vm.$store.state.settings.decimals) }}</div>
          </ListItem>
          <!-- <template v-if="vm.printData.order.payments && vm.printData.order.payments.length"> -->
          <!-- <template v-for="payment in vm.printData.order.payments">
              <ListItem class="heading last" :key="payment.id">
                <div class="item-details">{{ vm.$t('payment_settlement') }}</div>
                <div class="item-total">{{ payment.amount | formatNumber(vm.$store.state.settings.decimals) }}</div>
              </ListItem>
            </template> -->
          <ListItem class="heading last">
            <div class="item-details">{{ vm.$t('paid') }}</div>
            <div class="item-total">
              {{ total_paid | formatNumber(vm.$store.state.settings.decimals) }}
            </div>
          </ListItem>
          <ListItem class="heading last">
            <div class="item-details">{{ vm.$t('balance_due') }}</div>
            <div class="item-total">
              {{ (vm.printData.order.grand_total - total_paid) | formatNumber(vm.$store.state.settings.decimals) }}
            </div>
          </ListItem>
          <!-- </template> -->
        </template>
      </List>
    </div>
    <div class="mt16 mb16" v-if="receipt && vm.$store.getters.settings.show_tax_summary && taxSummary()">
      <List border size="small" class="mt16">
        <ListItem class="heading first">
          <div class="item-details align-center">{{ vm.$tc('tax') }}</div>
          <div class="item-price align-center">{{ vm.$tc('qty') }}</div>
          <div class="item-qty align-center">{{ vm.$tc('tax_ex_amount') }}</div>
          <div class="item-total align-center">{{ vm.$tc('tax_amount') }}</div>
        </ListItem>
        <template v-for="tax in taxSummary()">
          <ListItem :key="'ts_' + tax.id">
            <div class="item-details align-center">{{ tax.name }} ({{ tax.code }})</div>
            <div class="item-price text-center align-center">{{ tax.quantity | formatDecimal(settings.quantity_decimals) }}</div>
            <div class="item-qty  text-right align-center">{{ tax.item_net_amount | formatQtyDecimal(settings.decimals) }}</div>
            <div class="item-total text-right align-center">{{ tax.amount | formatDecimal(settings.decimals) }}</div>
          </ListItem>
        </template>
        <ListItem class="heading last">
          <div class="item-details align-center">{{ vm.$t('total_x', { x: vm.$tc('tax_amount') }) }}</div>
          <div class="item-total align-center">{{ vm.printData.order.total_tax_amount | formatDecimal(settings.decimals) }}</div>
        </ListItem>
      </List>
    </div>
    <div v-if="vm.printData.type != 'order'">
      <div class="text-center mt16 mb16" v-if="vm.printData.order.location.footer || vm.$store.state.settings.footer">
        {{ vm.$store.state.settings.footer }} <br />{{ vm.printData.order.location.footer }}
      </div>
    </div>
    <div v-if="bill" class="mb16 comment">
      <em>{{ vm.$t('only_for_company_record') }}</em>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    vm: { required: true },
  },
  computed: {
    settings() {
      return this.vm.$store.getters.settings;
    },
    bill() {
      return this.vm.printData.type != 'order';
    },
    receipt() {
      return this.vm.printData.type == 'receipt';
    },
    total_paid() {
      return this.vm.printData.order && this.vm.printData.order.payments
        ? this.vm.printData.order.payments.reduce((a, p) => a + p.amount, 0)
        : 0;
    },
  },
  mounted() {
    if (this.vm.$store.getters.settings.print_dialog == 1) {
      setTimeout(() => {
        window.print();
      }, 400);
    }
  },
  methods: {
    taxSummary() {
      let taxes = [];
      if (
        (!this.vm.printData.order.items || !this.vm.printData.order.items.length) &&
        (!this.vm.printData.order.taxes || !this.vm.printData.order.taxes.length)
      ) {
        return false;
      }
      if (this.vm.printData.order.items && this.vm.printData.order.items.length) {
        this.vm.printData.order.items.map(item => {
          if (item.taxes && item.taxes.length) {
            item.taxes.map(tax => {
              let et = taxes.find(t => t.id == tax.id);
              let total_net = this.vm.formatDecimal(parseFloat(item['net_price']) * parseFloat(item.quantity));
              let amount = this.vm.formatDecimal((parseFloat(total_net) * parseFloat(tax.rate)) / 100);
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
      if (this.vm.printData.order.taxes && this.vm.printData.order.taxes.length) {
        this.vm.printData.order.taxes.map(tax => {
          let et = taxes.find(t => t.id == tax.id);
          let amount = this.vm.formatDecimal((parseFloat(this.vm.printData.order.total) * parseFloat(tax.rate)) / 100);
          if (et) {
            et.amount += amount;
            et.item_net_amount += this.vm.printData.order.total; // TODO net total
            et.item_tax_amount += this.vm.printData.order.order_tax_amount;
          } else {
            taxes.push({
              id: tax.id,
              code: tax.code,
              name: tax.name,
              rate: tax.rate,
              amount: amount,
              number: tax.number,
              item_net_amount: this.vm.printData.order.total, // TODO net total
              item_tax_amount: this.vm.printData.order.order_tax_amount,
            });
          }
        });
      }
      return taxes.length ? taxes : false;
    },
  },
};
</script>

<style lang="scss">
.bb0 {
  border-bottom: 0 !important;
}
.bb1 {
  border-bottom: 1px solid #e8eaec !important;
}
.bt0 {
  border-top: 0 !important;
}
.bt1 {
  border-top: 1px solid #e8eaec !important;
}
.print-modal {
  font-size: 13px;
}
.print-modal .receipt-logo {
  max-width: 280px;
  text-align: center;
  margin: 0 auto 16px auto;
  img {
    max-width: 100%;
  }
}
.print-modal th {
  text-align: center;
}
.print-modal .comment {
  font-size: 11px;
  font-weight: bold;
}
.print-modal .ivu-list-item.heading {
  font-weight: bold;
  background: #f5f5f5;
  border-radius: 5px 5px 0 0;
}
.print-modal .ivu-list-item.heading.last {
  border-radius: 0 0 5px 5px;
}
.print-modal .ivu-list-item.heading .item-qty,
.print-modal .ivu-list-item.heading .item-price,
.print-modal .ivu-list-item.heading .item-total {
  text-align: center;
}
.print-modal .ivu-list-item.heading.last .item-total {
  text-align: right;
}
.print-modal .ivu-list-item {
  display: flex;
  padding: 5px 8px !important;
  &.pt0 {
    padding-top: 0 !important;
  }
  &.py0 {
    padding-top: 0 !important;
    padding-bottom: 0 !important;
  }
}
.print-modal .ivu-list-item .item-details {
  flex: 1;
  align-self: flex-start !important;
}
.print-modal .ivu-list-item .item-price {
  flex: 0 0 60px;
  text-align: right;
  align-self: flex-start !important;
}
.print-modal .ivu-list-item .item-qty {
  flex: 0 0 50px;
  text-align: center;
  align-self: flex-start !important;
}
.print-modal .ivu-list-item .item-total {
  flex: 0 0 75px;
  text-align: right;
  align-self: flex-start !important;
}
.print-modal .ivu-list-item .align-center {
  align-self: center !important;
}
@media print {
  .ivu-modal,
  .print-modal,
  .ivu-modal-body,
  .ivu-modal-wrap,
  .ivu-modal-confirm {
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
    height: 100% !important;
    display: block !important;
    overflow: visible !important;
    position: absolute !important;
  }
  .ivu-modal-content {
    margin: 0 !important;
    box-shadow: none !important;
  }
  .print-modal {
    height: 100% !important;
    width: 302px !important;
    margin: 10px auto !important;
  }
  .ivu-notice,
  .ivu-table::after,
  .ivu-table::before,
  .ivu-modal-confirm-footer {
    display: none;
  }
  table,
  .ivu-table-wrapper {
    width: 100% !important;
  }
  th,
  td,
  .ivu-table-wrapper,
  .ivu-table-wrapper div {
    border: none !important;
  }
  .print-modal th,
  .print-modal td {
    border-bottom: 1px solid #aaa !important;
  }
  th {
    text-align: center !important;
  }
  .print-modal .ivu-list {
    border: none !important;
  }
  .print-modal .ivu-list-item {
    padding-left: 0 !important;
    padding-right: 0 !important;
    align-items: flex-start !important;
  }
}
</style>
