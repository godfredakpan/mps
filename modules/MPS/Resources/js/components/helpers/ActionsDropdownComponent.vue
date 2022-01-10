<template>
  <Dropdown transfer placement="bottom-end">
    <a href="javascript:void(0)">
      {{ $t('actions') }}
      <Icon type="ios-arrow-down"></Icon>
    </a>
    <DropdownMenu slot="list">
      <DropdownItem v-if="(can('read-' + models) || $store.getters.user.view_all) && viewLink">
        <a :href="viewLink" target="_blank" style="color: #515a6e;">
          <Icon type="ios-list-box" size="16" />
          {{ $t('view_x', { x: record.title }) }}
        </a>
      </DropdownItem>
      <DropdownItem v-if="viewFn">
        <a @click="(can('read-' + models) || $store.getters.user.view_all) && viewFn(params.row)" style="color: #515a6e;">
          <Icon type="md-document" size="16" />
          {{ $t('view_x', { x: record.title }) }}
        </a>
      </DropdownItem>
      <DropdownItem v-if="receiptFn && params.row.pos == 1">
        <a @click="(can('read-' + models) || $store.getters.user.view_all) && receiptFn(params.row)" style="color: #515a6e;">
          <Icon type="md-document" size="16" />
          {{ $t('view_x', { x: $tc('receipt') }) }}
        </a>
      </DropdownItem>
      <DropdownItem v-if="params.row.active == 1 && params.row.can_impersonate == 1 && viewCardFn">
        <a @click="(can('read-' + models) || $store.getters.user.view_all) && viewCardFn(params.row)" style="color: #515a6e;">
          <Icon type="md-document" size="16" />
          {{ $t('view_x', { x: $t('card') }) }}
        </a>
      </DropdownItem>
      <DropdownItem v-if="(can('read-' + models) || $store.getters.user.view_all) && logsFn">
        <a @click="logsFn(params.row)" style="color: #515a6e;">
          <Icon type="ios-list-box" size="16" />
          {{ $tc('log', 2) }}
        </a>
      </DropdownItem>
      <DropdownItem v-if="can('create-users') && addUserFn">
        <a @click="addUserFn(params.row)" style="color: #515a6e;">
          <Icon type="md-people" size="16" />
          {{ $t('add_x', { x: $tc('user') }) }}
        </a>
      </DropdownItem>
      <DropdownItem v-if="can('read-users') && listUsersFn">
        <a @click="listUsersFn(params.row)" style="color: #515a6e;">
          <Icon type="md-people" size="16" />
          {{ $t('list_x', { x: $tc('user', 2) }) }}
        </a>
      </DropdownItem>
      <DropdownItem v-if="(can('read-' + models) || $store.getters.user.view_all) && addAddressFn">
        <a @click="addAddressFn(params.row)" style="color: #515a6e;">
          <Icon type="ios-navigate" size="16" />
          {{ $t('add_x', { x: $tc('address') }) }}
        </a>
      </DropdownItem>
      <DropdownItem v-if="(can('read-' + models) || $store.getters.user.view_all) && listAddressesFn">
        <a @click="listAddressesFn(params.row)" style="color: #515a6e;">
          <Icon type="ios-navigate" size="16" />
          {{ $t('list_x', { x: $tc('addresses') }) }}
        </a>
      </DropdownItem>
      <DropdownItem v-if="(can('read-' + models) || $store.getters.user.view_all) && listFn">
        <a @click="listFn(params.row)" style="color: #515a6e;">
          <Icon type="ios-list-box" size="16" />
          {{ $t('list_x', { x: $tc('transaction', 2) }) }}
        </a>
      </DropdownItem>
      <DropdownItem v-if="can('create-payments') && payFn" :disabled="params.row.paid == 1">
        <a @click="payFn(params.row)" :disabled="params.row.paid == 1" :style="{ color: params.row.paid == 1 ? '#999999' : '#515a6e' }">
          <Icon type="ios-cash-outline" size="16" :style="{ opacity: 1, color: params.row.paid == 1 ? '#999999' : '#000000' }" />
          {{ $t('add_x', { x: $tc('payment') }) }}
        </a>
      </DropdownItem>
      <DropdownItem v-if="can('read-payments') && listPayFn">
        <a @click="listPayFn(params.row)" style="color: #515a6e;">
          <Icon type="ios-cash" size="16" />
          {{ $t('list_x', { x: $tc('payment', 2) }) }}
        </a>
      </DropdownItem>
      <DropdownItem v-if="can('create-salaries') && addSalaryFn">
        <a @click="addSalaryFn(params.row)" style="color: #515a6e;">
          <Icon type="ios-cash" size="16" />
          {{ $t('add_x', { x: $tc('salary') }) }}
        </a>
      </DropdownItem>
      <DropdownItem v-if="can('read-salaries') && salaryFn && !params.row.customer_id">
        <a @click="salaryFn(params.row)" style="color: #515a6e;">
          <Icon type="ios-cash" size="16" />
          {{ $tc('salary', 2) }}
        </a>
      </DropdownItem>
      <DropdownItem
        v-if="can('create-deliveries') && deliveryFn && params.row.deliveries && !params.row.deliveries.length"
        :disabled="params.row.delivered == 1"
      >
        <a
          @click="deliveryFn(params.row)"
          :disabled="params.row.draft == 1"
          :style="{ color: params.row.draft == 1 ? '#999999' : '#515a6e' }"
        >
          <Icon type="ios-bus-outline" size="16" :style="{ opacity: 1, color: params.row.draft == 1 ? '#999999' : '#000000' }" />
          {{ $t('add_x', { x: $tc('delivery') }) }}
        </a>
      </DropdownItem>
      <DropdownItem v-if="can('update-deliveries') && deliveryFn && params.row.deliveries && params.row.deliveries.length">
        <a @click="deliveryFn(params.row)" style="color:#515a6e;">
          <Icon type="ios-bus-outline" size="16" />
          {{ $t('edit_x', { x: $tc('delivery') }) }}
        </a>
      </DropdownItem>
      <DropdownItem v-if="can('read-deliveries') && viewDeliveriesFn && params.row.deliveries && params.row.deliveries.length">
        <a @click="viewDeliveriesFn(params.row)" style="color: #515a6e;">
          <Icon type="ios-bus" size="16" />
          {{ $t('view_x', { x: $tc('delivery') }) }}
        </a>
      </DropdownItem>
      <!-- <DropdownItem v-if="can('read-deliveries') && listDeliveriesFn">
        <a @click="listDeliveriesFn(params.row)" style="color: #515a6e;">
          <Icon type="ios-bus" size="16" />
          {{ $t('list_x', { x: $tc('delivery', 2) }) }}
        </a>
      </DropdownItem> -->
      <DropdownItem v-if="can('email-' + models) && emailFn">
        <a @click="emailFn(params.row)" style="color: #515a6e;">
          <Icon type="ios-mail" size="16" />
          {{ $t('email_x', { x: record.title }) }}
        </a>
      </DropdownItem>
      <DropdownItem v-if="can('read-' + models) && downloadFn">
        <a @click="downloadFn(params.row)" style="color: #515a6e;">
          <Icon type="ios-download" size="16" />
          {{ $t('download_x', { x: record.title }) }}
        </a>
      </DropdownItem>
      <DropdownItem v-if="can('create-sales') && createSaleFn">
        <a @click="createSaleFn(params.row)" style="color: #515a6e;">
          <Icon type="ios-basket-outline" size="16" />
          {{ $t('create_x', { x: $tc('sale') }) }}
        </a>
      </DropdownItem>
      <DropdownItem v-if="can('create-' + models) && duplicateFn">
        <a @click="duplicateFn(params.row)" style="color: #515a6e;">
          <Icon type="md-copy" size="16" />
          {{ $t('duplicate_x', { x: record.title }) }}
        </a>
      </DropdownItem>
      <DropdownItem
        v-if="can('update-' + models) && reviewFn && params.row.review == 1 && !params.row.reviewed_by && params.row.received != 1"
      >
        <a @click="reviewFn(params.row)" style="color: #ff9900;">
          <Icon type="ios-information-circle" size="16" />
          {{ $t('accept_x', { x: record.title }) }}
        </a>
      </DropdownItem>
      <DropdownItem v-if="(can('update-' + models) || $store.getters.user.edit_all) && editFn">
        <a @click="editFn(params.row)" style="color: #ff9900;">
          <Icon type="md-create" size="16" />
          {{ $t('edit_x', { x: record.title }) }}
        </a>
      </DropdownItem>
      <template v-if="menus">
        <DropdownItem v-for="(menu, index) in menus" :key="'menu_' + index" :divided="menu.divided">
          <a @click="menu.confirm ? handleRender(params.row, menu.Fn) : menu.Fn(params.row)" style="color: #515a6e;">
            <Icon :type="menu.iconType" size="14" style="margin-right: 8px;" />
            {{ menu.title }}
          </a>
        </DropdownItem>
      </template>
      <template v-if="can('delete-' + models) && toggleVoidFn">
        <DropdownItem v-if="$store.state.settings.confirmation == 'modal'">
          <a
            style="color: #ed4014;"
            @click="handleRender(params.row, toggleVoidFn, $t('mark_x', { x: params.row.void == 1 ? $t('valid') : $t('void') }))"
          >
            <Icon v-if="params.row.void == 1" type="md-checkmark-circle" size="16" />
            <Icon v-else type="md-close-circle" size="16" />
            {{ $t('mark_x', { x: params.row.void == 1 ? $t('valid') : $t('void') }) }}
          </a>
        </DropdownItem>
        <DropdownItem v-if="!$store.state.settings.confirmation">
          <a @click="toggleVoidFn(params.row)" style="color: #ed4014;">
            <Icon v-if="params.row.void == 1" type="md-checkmark-circle" size="16" />
            <Icon v-else type="md-close-circle" size="16" />
            {{ $t('mark_x', { x: params.row.void == 1 ? $t('valid') : $t('void') }) }}
          </a>
        </DropdownItem>
      </template>
      <template v-if="can('delete-' + models) && deleteFn">
        <DropdownItem v-if="$store.state.settings.confirmation == 'modal'">
          <a @click="handleRender(params.row)" style="color: #ed4014;">
            <Icon type="md-trash" size="16" />
            {{ $t('delete_x', { x: record.title }) }}
          </a>
        </DropdownItem>
        <DropdownItem v-if="!$store.state.settings.confirmation">
          <a @click="deleteFn(params.row)" style="color: #ed4014;">
            <Icon type="md-trash" size="16" />
            {{ $t('delete_x', { x: record.title }) }}
          </a>
        </DropdownItem>
      </template>
    </DropdownMenu>
  </Dropdown>
</template>

<script>
const inflection = require('inflection');
export default {
  props: {
    viewFn: {
      type: Function,
      required: false,
    },
    viewLink: {
      type: String,
      required: false,
    },
    receiptFn: {
      type: Function,
      required: false,
    },
    listFn: {
      type: Function,
      required: false,
    },
    payFn: {
      type: Function,
      required: false,
    },
    listPayFn: {
      type: Function,
      required: false,
    },
    deliveryFn: {
      type: Function,
      required: false,
    },
    viewDeliveriesFn: {
      type: Function,
      required: false,
    },
    listDeliveriesFn: {
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
    addUserFn: {
      type: Function,
      required: false,
    },
    addAddressFn: {
      type: Function,
      required: false,
    },
    emailFn: {
      type: Function,
      required: false,
    },
    downloadFn: {
      type: Function,
      required: false,
    },
    reviewFn: {
      type: Function,
      required: false,
    },
    createSaleFn: {
      type: Function,
      required: false,
    },
    logsFn: {
      type: Function,
      required: false,
    },
    salaryFn: {
      type: Function,
      required: false,
    },
    addSalaryFn: {
      type: Function,
      required: false,
    },
    viewCardFn: {
      type: Function,
      required: false,
    },
    menus: {
      required: false,
    },
    params: {
      required: false,
    },
    record: {
      required: false,
    },
    editFn: {
      type: Function,
      required: false,
    },
    duplicateFn: {
      type: Function,
      required: false,
    },
    deleteFn: {
      type: Function,
      required: false,
    },
    toggleVoidFn: {
      type: Function,
      required: false,
    },
  },
  computed: {
    models() {
      return inflection.pluralize(this.record.model);
    },
    title() {
      return (
        this.$t('delete_confirm') +
        '<br><br><strong>' +
        this.$t('deleting') +
        ' ' +
        this.$tc('category') +
        ' (' +
        this.params.row.code +
        ' - ' +
        this.params.row.name +
        ')</strong><br>' +
        this.$t('r_u_sure')
      );
    },
  },
  methods: {
    handleRender(row, del, del_txt) {
      let details = '';
      if (this.record.code && this.record.name) {
        details = ' (' + row[this.record.code] + ' - ' + row[this.record.name] + ')';
      } else if (this.record.name) {
        details = ' (' + row[this.record.name] + ')';
      } else if (this.record.code) {
        details = ' (' + row[this.record.code] + ')';
      }
      this.$Modal.confirm({
        title: (del_txt ? del_txt : this.$t('deleting') + ' ' + this.$tc(this.record.model)) + details,
        content: (del_txt ? '' : this.$t('delete_confirm') + '<br>') + '<br><strong>' + this.$t('r_u_sure') + '</strong>',
        okText: this.$t('yes'),
        cancelText: this.$t('cancel'),
        loading: true,
        onOk: () => {
          del ? del(row) : this.deleteFn(row);
          setTimeout(() => this.$Modal.remove(), 2000);
        },
      });
    },
  },
};
</script>

<style>
.ivu-btn-group-small .ivu-btn-icon-only .ivu-icon {
  font-size: 16px;
}
</style>
