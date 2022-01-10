<template>
  <ButtonGroup size="small" style="font-size: 16px;">
    <Button
      size="small"
      type="primary"
      icon="ios-list-box"
      @click="listFn(params.row)"
      v-if="(can('read-' + models) || $store.getters.user.view_all) && listFn"
    ></Button>
    <Button icon="ios-cash" type="primary" size="small" v-if="can('read-payments') && payFn" @click="payFn(params.row)"></Button>
    <Button
      size="small"
      type="success"
      icon="md-document"
      @click="viewFn(params.row)"
      v-if="(can('read-s' + models) || $store.getters.user.view_all) && viewFn"
    ></Button>
    <Button icon="ios-mail" type="primary" size="small" v-if="can('email-' + models) && emailFn" @click="emailFn(params.row)"></Button>
    <Button
      type="info"
      size="small"
      icon="md-analytics"
      @click="trailsFn(params.row.id)"
      v-if="can('trail ' + models) && trailsFn"
    ></Button>
    <Button size="small" type="primary" icon="ios-barcode" @click="labelFn(params.row)" v-if="can('label-settings') && labelFn"></Button>
    <Button
      size="small"
      type="warning"
      icon="md-create"
      @click="editFn(params.row)"
      v-if="(can('update-' + models) || $store.getters.user.edit_all) && editFn"
    ></Button>
    <template v-if="can('delete-' + models) && deleteFn">
      <Button size="small" type="error" icon="md-trash" @click="deleteFn(params.row)" v-if="!$store.state.settings.confirmation"></Button>
      <Button
        size="small"
        type="error"
        icon="md-trash"
        @click="handleRender(params.row)"
        v-if="$store.state.settings.confirmation == 'modal'"
      ></Button>
    </template>
  </ButtonGroup>
</template>

<script>
const inflection = require('inflection');
export default {
  props: {
    deleteFn: {
      type: Function,
      required: false,
    },
    editFn: {
      type: Function,
      required: false,
    },
    labelFn: {
      type: Function,
      required: false,
    },
    viewFn: {
      type: Function,
      required: false,
    },
    viewLink: {
      type: String,
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
    emailFn: {
      type: Function,
      required: false,
    },
    trailsFn: {
      type: Function,
      required: false,
    },
    params: {
      required: false,
    },
    record: {
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
