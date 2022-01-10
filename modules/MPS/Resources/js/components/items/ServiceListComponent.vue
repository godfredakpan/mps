<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ $tc('service', 2) }}</p>
      <div>
        <table-component
          url="app/items?type=service"
          :columns="columns"
          :options="options"
          :dblClickCB="showInfo"
          :bulkDelCB="deleteRecords"
          :refresh="refresh"
        ></table-component>
      </div>
    </Card>
    <Modal
      width="500"
      v-model="barModal"
      :footer-hide="true"
      :mask-closable="false"
      class="np-header-footer"
      :title="$t('print_x', { x: $t('barcode') })"
    >
      <div class="label text-center" v-html="barcodeSVG"></div>

      <div class="np">
        <Divider dashed />
        <div class="text-center" style="display:none;">
          <vue-barcode
            :width="1"
            :margin="0"
            :height="30"
            :fontSize="12"
            :margin-top="0"
            id="drawBarcode"
            :value="row.code"
            :margin-bottom="4"
            :format="row.symbology"
          >
            {{ $t('barcode_error') }}
          </vue-barcode>
        </div>
        <Alert v-html="$t('label_print_instructions')" />
        <Button @click="print()" type="primary" icon="ios-print" class="np" long>{{ $t('print') }}</Button>
      </div>
    </Modal>
    <Modal footer-hide scrollable v-model="view" :width="750">
      <p slot="header">
        <span>
          {{ row.name }}
          ({{ row.code }})
        </span>
      </p>
      <item-view-component :item="row" />
    </Modal>
  </div>
</template>

<script>
import VueBarcode from 'vue-barcode';
import Table from '@mpsjs/mixins/Table';
import ItemViewComponent from '@mpscom/items/ItemViewComponent';
export default {
  components: { ItemViewComponent, VueBarcode },
  mixins: [Table('item', 'app/items', 'name', 'code', 'services')],
  data() {
    return {
      row: {},
      view: false,
      barModal: false,
      barcodeSVG: null,
      columns: [
        { type: 'selection', width: 50, align: 'center', fixed: 'left' },
        { title: this.$t('name'), sortable: true, key: 'name', width: 200 },
        { title: this.$t('code'), sortable: true, key: 'code', sortType: 'asc', minWidth: 150 },
        { title: this.$t('cost'), sortable: true, key: 'cost', minWidth: 125, render: this.renderCost },
        { title: this.$t('price'), sortable: true, key: 'price', minWidth: 125, render: this.renderPrice },
        // { title: this.$t('quantity'), sortable: true, key: 'stock', minWidth: 125, render: this.renderStock },
        // { title: this.$t('rack_location'), sortable: true, key: 'location', minWidth: 130, render: this.renderRack },
        { title: this.$t('alt_name'), sortable: true, key: 'alt_name', minWidth: 175 },
        { title: this.$tc('category'), sortable: false, key: 'categories', minWidth: 175, render: this.renderCategory },
        { title: this.$tc('taxes'), sortable: false, key: 'taxes', minWidth: 200, render: this.renderTaxes },
        { title: this.$tc('field', 2), sortable: false, key: 'extra_attributes', minWidth: 300, render: this.renderExtras },
        { title: this.$t('tax_method'), sortable: true, key: 'tax_included', minWidth: 120, render: this.renderTaxMethod },
        { title: this.$t('changeable'), sortable: true, key: 'changeable', minWidth: 120, render: this.renderChangeable },
        { title: this.$t('min_price'), sortable: true, key: 'min_price', minWidth: 150, render: this.renderMinPrice },
        { title: this.$t('max_price'), sortable: true, key: 'max_price', minWidth: 150, render: this.renderMaxPrice },
        { title: this.$t('max_discount'), sortable: true, key: 'max_discount', minWidth: 150, render: this.renderMaxDiscount },
        { title: this.$t('summary'), sortable: true, key: 'summary', ellipsis: true, minWidth: 400 },
        // { title: this.$t('details'), sortable: true, key: 'details', ellipsis: true, ellipsis: true, minWidth: 400 },
        { title: this.$t('created_at'), sortable: true, key: 'created_at', minWidth: 175, render: this.renderCreatedAt },
        { title: this.$t('updated_at'), sortable: true, key: 'updated_at', minWidth: 175, render: this.renderUpdatedAt },
        {
          width: 120,
          fixed: 'right',
          key: 'actions',
          align: 'center',
          title: this.$t('actions'),
          render: (h, params) => {
            return h('actions-component', {
              props: {
                params,
                editFn: this.editRecord,
                labelFn: this.labelModal,
                deleteFn: this.deleteRecord,
                record: { model: 'item', name: 'name' },
                trailsFn: id => {
                  this.$router.push('/items/trails/' + id);
                },
              },
            });
          },
        },
      ],
      options: {
        orderBy: 'code',
        perPage: this.$store.state.settings.rows,
      },
    };
  },
  created() {
    this.$event.listen('location:changed', id => this.refresh++);
  },
  mounted() {
    if (!this.$store.getters.stock) {
      this.columns = this.columns.filter(c => c.key != 'stock');
    }
  },
  methods: {
    showInfo(row) {
      this.row = row;
      this.view = true;
    },
    labelModal(row) {
      this.row = row;
      this.barModal = true;
      this.barcodeSVG = this.$store.state.settings.svg_string
        .replace('--- Item Name ---', this.row.name)
        .replace('--- Other info for item. ---', this.row.alt_name)
        .replace('--- Price: 1,000.00 ---', this.$t('price') + ': ' + this.formatNumber(this.row.price));
      this.$nextTick(() => {
        document.getElementById('replace-barcode').innerHTML = '';
        document.getElementById('replace-barcode').removeAttribute('transform');
        var clone = document.getElementById('drawBarcode').childNodes[0].cloneNode(true);
        document
          .getElementById('replace-barcode')
          .setAttribute('transform', 'matrix(1 0 0 1 10 ' + (this.$store.state.settings.label_height - 60) + ')');
        document.getElementById('replace-barcode').appendChild(clone);
        if (this.$store.getters.settings.print_dialog == 1) {
          setTimeout(() => {
            window.print();
          }, 400);
        }
      });
    },
    print() {
      window.print();
    },
    renderChangeable(h, params) {
      return this.renderBoolean(h, params, 'changeable');
    },
    renderMinPrice(h, params) {
      return this.renderNumber(h, params, 'min_price');
    },
    renderMaxPrice(h, params) {
      return this.renderNumber(h, params, 'max_price');
    },
    renderMaxDiscount(h, params) {
      return this.renderNumber(h, params, 'max_discount');
    },
    renderCategory(h, params) {
      return <div>{params.row.categories.map(c => c.name).join('')}</div>;
    },
    renderCost(h, params) {
      return (
        <div class="text-right">
          {this.$options.filters.formatNumber(
            params.row.location_stock.length > 0 && params.row.location_stock[0].cost ? params.row.location_stock[0].cost : params.row.cost,
            this.$store.getters.settings.decimals
          )}
        </div>
      );
    },
    renderPrice(h, params) {
      return (
        <div class="text-right">
          {this.$options.filters.formatNumber(
            params.row.location_stock.length > 0 && params.row.location_stock[0].price
              ? params.row.location_stock[0].price
              : params.row.price,
            this.$store.getters.settings.decimals
          )}
        </div>
      );
    },
    renderStock(h, params) {
      return (
        <div class="text-center">
          {params.row.location_stock.length > 0
            ? this.$options.filters.formatNumber(params.row.location_stock[0].quantity, this.$store.getters.settings.decimals)
            : this.$options.filters.formatNumber(
                params.row.stock.reduce((a, s) => a + parseFloat(s.quantity), 0),
                this.$store.getters.settings.decimals
              )}
        </div>
      );
    },
    renderRack(h, params) {
      return (
        <div>
          {params.row.location_stock.length > 0 && params.row.location_stock[0].rack
            ? params.row.location_stock[0].rack
            : params.row.location}
        </div>
      );
    },
    renderTaxMethod(h, params) {
      if (params.row.tax_included == 1) {
        return (
          <div class="text-center">
            <div class="ivu-tag ivu-tag-success ivu-tag-border ivu-tag-checked">
              <span class="ivu-tag-text ivu-tag-color-success">{this.$t('inclusive')}</span>
            </div>
          </div>
        );
      } else {
        return (
          <div class="text-center">
            <div class="ivu-tag ivu-tag-primary ivu-tag-border ivu-tag-checked">
              <span class="ivu-tag-text ivu-tag-color-primary">{this.$t('exclusive')}</span>
            </div>
          </div>
        );
      }
    },
    renderTaxes(h, params) {
      return <div>{params.row.taxes.map(t => t.name).join(', ')}</div>;
    },
  },
};
</script>
