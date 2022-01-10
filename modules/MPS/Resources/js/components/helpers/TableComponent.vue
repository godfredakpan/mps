<template>
  <div>
    <Loading v-if="loading" />
    <Form :model="form" :label-width="50">
      <Row type="flex" justify="space-between" style="margin-bottom: 8px;" v-if="!options.hideSearch">
        <Col :md="12" class="table-show">
          <span class="show">
            <FormItem :label="$t('show')" style="margin-bottom: 0;">
              <Select v-model="form.limit" style="width: 60px;" placeholder>
                <Option value="10">10</Option>
                <Option value="25">25</Option>
                <Option value="50">50</Option>
                <Option value="100">100</Option>
              </Select>
              <span v-if="selection.length > 0">
                <!-- <Button type="primary" @click="exportData">
                  <Icon type="md-download" size="18" />
                </Button> -->
                <Button v-if="$store.state.settings.confirmation != 'poptip'" type="error" @click="deleteSelection">
                  <Icon type="md-trash" size="16" />
                  {{ $t('delete') }}
                </Button>
                <Poptip
                  confirm
                  placement="bottom"
                  :ok-text="$t('yes')"
                  @on-ok="deleteSelection"
                  :cancel-text="$t('cancel')"
                  v-if="selection.length > 0 && $store.state.settings.confirmation == 'poptip'"
                >
                  <template slot="title">
                    <span v-html="dlMsg"></span>
                  </template>
                  <Button type="error">
                    <Icon type="md-trash" size="16" />
                    {{ $t('delete') }}
                  </Button>
                </Poptip>
              </span>
            </FormItem>
          </span>
        </Col>
        <Col :md="12" class="table-search">
          <span class="search">
            <FormItem :label="$t('search')" style="margin-bottom: 0;">
              <Input v-model="query" debounce="500" :placeholder="$t('search')" />
            </FormItem>
          </span>
        </Col>
      </Row>
      <div class="table-responsive">
        <Table
          border
          ref="table"
          :data="data"
          :stripe="stripe"
          :columns="columns"
          @on-row-dblclick="showInfo"
          @on-sort-change="sortTable"
          :no-data-text="$t('no_data')"
          :row-class-name="rowClassName"
          @on-selection-change="selectionChange"
          :no-filtered-data-text="$t('no_result')"
          :show-summary="options.showSummary || false"
          :summary-method="options.summaryMethod || null"
        ></Table>
      </div>
      <Row type="flex" justify="space-between" style="margin-top: 8px;">
        <Col :md="8" class="table-info">
          <span style="line-height: 32px;">
            <span v-if="total > 0">{{ $t('table_info', { start, end, total }) }}</span>
            <span v-else>{{ $t('zero_records') }}</span>
          </span>
        </Col>
        <Col :md="16" class="table-page" v-if="page > 0">
          <span class="table-page-select visible-sm" style="display:flex;">
            <FormItem label="Page" style="margin-bottom: 0;">
              <Select v-model="page" style="width: 60px;" @on-change="changePage" placeholder>
                <Option v-for="p in pages" :value="p" :key="p">{{ p }}</Option>
              </Select>
            </FormItem>
          </span>
          <span class="page hidden-sm">
            <!-- show-elevator show-sizer -->
            <Page :total="total" :page-size="form.limit * 1" :current="page * 1" @on-change="pageChanged"></Page>
          </span>
        </Col>
      </Row>
    </Form>
  </div>
</template>

<script>
import _d from 'lodash/debounce';
export default {
  props: {
    url: {
      type: String,
      required: true,
    },
    columns: {
      type: Array,
      required: true,
    },
    dblClickCB: {
      type: Function,
      required: false,
    },
    bulkDelCB: {
      type: Function,
      required: false,
    },
    options: {
      type: Object,
      required: true,
    },
    refresh: {
      type: Number,
      required: false,
    },
    rowClassName: {
      type: Function,
      required: false,
    },
    stripe: {
      type: Boolean,
      default: true,
    },
    subData: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      data: [],
      total: 0,
      query: '',
      loading: true,
      selection: [],
      page: this.$route.query.page || 1,
      form: {
        limit: this.options.perPage,
        byColumn: this.options.byColumn ? this.options.byColumn : false,
        orderBy: Array.isArray(this.options.orderBy) ? this.options.orderBy.join(',') : this.options.orderBy,
      },
    };
  },
  computed: {
    start() {
      return this.page * this.form.limit - this.form.limit + 1;
    },
    pages() {
      return this.total > 0 ? Array.from({ length: Math.ceil(this.total / this.form.limit) }, (v, i) => i + 1) : [''];
    },
    end() {
      return this.total < this.form.limit || parseInt(this.start) + parseInt(this.form.limit) > this.total
        ? this.total
        : parseInt(this.start) + parseInt(this.form.limit) - 1;
    },
    dlMsg() {
      return this.$t('delete_confirm') + '<br><br><strong>' + this.$t('bulk_delete') + '</strong><br>' + this.$t('r_u_sure');
    },
  },
  watch: {
    query() {
      this.page = 1;
      this.searchData();
    },
    refresh() {
      if (this.page == this.pages.length) {
        this.page = 1;
      }
      this.getData();
    },
    page() {
      this.getData();
    },
    form: {
      handler() {
        this.page = 1;
        this.getData();
      },
      deep: true,
    },
  },
  created() {
    this.getData();
  },
  mounted() {
    this.columns.map(column => {
      column.sortable = column.sortable ? 'custom' : false;
      column.sortMethod = 'sortTable';
    });
  },
  methods: {
    changePage(page) {
      this.page = page;
      // this.getData();
    },
    pageChanged(page) {
      this.page = page;
    },
    sortTable(col) {
      this.form.page = 1;
      this.form.orderBy = col.key + ' ' + col.order;
    },
    showInfo(row) {
      if (this.dblClickCB) {
        this.dblClickCB(row);
      }
    },
    getData() {
      this.loading = true;
      this.$Loading.start();
      this.page = this.page ? this.page : 1;
      this.$http
        .get(this.url, { params: { ...this.form, query: this.query, page: this.page } })
        .then(res => {
          if (this.subData) {
            this.total = res.data[this.subData].count;
            this.data = res.data[this.subData].data;
            if (res.data.start_date && res.data.end_date) {
              this.$event.fire('table:loaded', [res.data.start_date, res.data.end_date]);
            }
          } else {
            this.total = res.data.count;
            this.data = res.data.data;
          }
          this.$Loading.finish();
          this.loading = false;
        })
        .catch();
      // .catch(err => (err.response ? this.$event.fire('appError', err.response) : ''));
    },
    searchData: _d(function() {
      this.getData();
    }, 500),
    selectionChange(selection) {
      this.selection = selection;
    },
    deleteSelection() {
      if (this.$store.state.settings.confirmation == 'poptip') {
        this.bulkDelCB(this.selection);
      } else {
        this.$Modal.confirm({
          title: this.$t('bulk_delete'),
          content: this.$t('delete_confirm') + '<br><br><strong>' + this.$t('r_u_sure') + '</strong>',
          okText: this.$t('yes'),
          cancelText: this.$t('cancel'),
          onOk: () => {
            this.bulkDelCB(this.selection);
          },
        });
      }
    },
    exportData() {
      this.$refs.table.exportCsv({ filename: 'Export', quoted: true });
    },
  },
};
</script>
