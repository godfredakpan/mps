<template>
  <div v-if="customer">
    <div class="table-responsive mb16">
      <div class="ivu-table-wrapper ivu-table-wrapper-with-border">
        <div class="ivu-table ivu-table-default ivu-table-border">
          <div class="ivu-table-body">
            <table cellspacing="0" cellpadding="0" border="0" style="width:100%;min-width:300px">
              <tbody class="ivu-table-tbody">
                <tr class="ivu-table-row">
                  <td style="width:40%;min-width:100px">
                    <div class="ivu-table-cell">
                      <div class="ivu-table-cell-slot">{{ $t('name') }}</div>
                    </div>
                  </td>
                  <td style="width:60%;min-width:200px">
                    <div class="ivu-table-cell">
                      <strong>{{ customer.name }}</strong> <span v-if="customer.company">({{ customer.company }})</span>
                    </div>
                  </td>
                </tr>
                <tr class="ivu-table-row">
                  <td style="width:40%;min-width:100px">
                    <div class="ivu-table-cell">
                      <div class="ivu-table-cell-slot">{{ $t('phone') }}</div>
                    </div>
                  </td>
                  <td style="width:60%;min-width:200px">
                    <div class="ivu-table-cell">
                      <strong>{{ customer.phone }}</strong> <span v-if="customer.email">({{ customer.email }})</span>
                    </div>
                  </td>
                </tr>
                <tr class="ivu-table-row" v-if="customer.journal && customer.journal.balance">
                  <td style="width:40%;min-width:100px">
                    <div class="ivu-table-cell">
                      <div class="ivu-table-cell-slot">{{ customer.journal.balance.amount > 0 ? $t('due') : $t('deposit') }}</div>
                    </div>
                  </td>
                  <td style="width:60%;min-width:200px">
                    <div class="ivu-table-cell">
                      {{ customer.journal.balance.amount > 0 ? $t('due') : $t('advance') }}:
                      <strong>{{
                        customer.journal
                          ? formatJournalBalance(
                              customer.journal.balance.amount > 0 ? customer.journal.balance.amount : 0 - customer.journal.balance.amount,
                              $store.getters.settings.decimals
                            )
                          : ''
                      }}</strong>
                    </div>
                  </td>
                </tr>
                <tr class="ivu-table-row" v-if="customer.customer_group">
                  <td style="width:40%;min-width:100px">
                    <div class="ivu-table-cell">
                      <div class="ivu-table-cell-slot">{{ $tc('customer_group') }}</div>
                    </div>
                  </td>
                  <td style="width:60%;min-width:200px">
                    <div class="ivu-table-cell">
                      <strong>{{ customer.customer_group ? customer.customer_group.name : '' }}</strong>
                    </div>
                  </td>
                </tr>
                <tr class="ivu-table-row" v-if="customer.points">
                  <td style="width:40%;min-width:100px">
                    <div class="ivu-table-cell">
                      <div class="ivu-table-cell-slot">{{ $t('loyalty_points') }}</div>
                    </div>
                  </td>
                  <td style="width:60%;min-width:200px">
                    <div class="ivu-table-cell">
                      <strong>{{ formatNumber(customer.points) }}</strong>
                    </div>
                  </td>
                </tr>
                <tr class="ivu-table-row">
                  <td style="width:40%;min-width:100px">
                    <div class="ivu-table-cell">
                      <div class="ivu-table-cell-slot">{{ $t('address') }}</div>
                    </div>
                  </td>
                  <td style="width:60%;min-width:200px">
                    <div class="ivu-table-cell">
                      {{ `${customer.address} ${customer.state_name} ${customer.country_name}` }}
                    </div>
                  </td>
                </tr>
                <tr class="ivu-table-row" v-if="customer.extra_attributes && customer.extra_attributes.length">
                  <td style="width:40%;min-width:100px">
                    <div class="ivu-table-cell">
                      <div class="ivu-table-cell-slot">{{ $tc('field', 2) }}</div>
                    </div>
                  </td>
                  <td style="width:60%;min-width:200px">
                    <div class="ivu-table-cell">
                      {{
                        Object.entries(customer.extra_attributes)
                          .map(e => e.join(': '))
                          .join(', ')
                      }}
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="text-center">
      <Button v-if="addUserFn" @click="addUserFn(customer)">{{ $t('add_x', { x: $tc('user') }) }}</Button>
      <Button v-if="listUsersFn" @click="listUsersFn(customer)">{{ $t('list_x', { x: $tc('user', 2) }) }}</Button>
      <Button v-if="addAddressFn" @click="addAddressFn(customer)">{{ $t('add_x', { x: $t('address') }) }}</Button>
      <Button v-if="listAddressesFn" @click="listAddressesFn(customer)">{{ $t('list_x', { x: $t('addresses') }) }}</Button>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    customer: {
      required: true,
    },
    addUserFn: {
      type: Function,
      required: false,
    },
    addAddressFn: {
      type: Function,
      required: false,
    },
    listUsersFn: {
      type: Function,
      required: false,
    },
    listAddressesFn: {
      type: Function,
      required: false,
    },
  },
  // watch: {
  //   'customer.id': function(v, o) {
  //     this.getStock();
  //   },
  // },
  // data() {
  //   return {
  //     stock: [],
  //   };
  // },
  // methods: {
  //   getStock() {
  //     this.$http.get('app/stock/' + this.customer.id).then(res => (this.stock = res.data));
  //   },
  // },
};
</script>
