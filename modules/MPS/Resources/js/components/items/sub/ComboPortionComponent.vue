<template>
  <div>
    <Row :gutter="16">
      <Col :sm="24" :md="12" :lg="12" v-for="(portion, index) in portions" :key="'portion_' + index">
        <Card dis-hover style="line-height: 1em;" class="portion-card dark-border">
          <p slot="title">{{ portion.name == 'regular' ? $t('regular') : portion.name }}</p>
          <Button v-if="!index" size="small" type="success" @click="option = true" slot="extra">
            <Icon type="ios-options" />
            <span class="hidden-sm">{{ $t('add_x', { x: $tc('portion') }) }}</span>
          </Button>
          <Button v-else size="small" type="error" @click="removePortion(index)" slot="extra">
            <Icon type="ios-trash" />
            <span class="hidden-sm">{{ $t('delete_x', { x: $tc('portion') }) }}</span>
          </Button>
          <Card dis-hover class="mb16">
            <p slot="title">{{ $t('essential_items') }}</p>
            <Button size="small" type="primary" @click="addItemRow(portion, index, true)" slot="extra">
              <Icon type="ios-add-circle" />
              <span class="hidden-sm">{{ $t('add_x', { x: $tc('item') }) }}</span>
            </Button>
            <div class="variant-table mb16">
              <table class="table">
                <thead>
                  <tr>
                    <th>{{ $t('name') }}</th>
                    <th>{{ $tc('variation') }}</th>
                    <th>{{ $t('quantity') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr :key="'ei_' + ei" v-for="(essential, ei) in portion.essentials">
                    <td width="40%">
                      <!-- <div v-if="index" class="text-field">{{ essential.name }}</div>
                        <span v-else> -->
                      <!-- v-model="portions[index].essentials[ei].id" -->
                      <Select
                        remote
                        clearable
                        filterable
                        :loading="searching"
                        v-model="essential.id"
                        :remote-method="searchItems"
                        :label="portion.essentials.map(e => e.name)"
                        @on-change="v => itemChanged(v, portion, ei, false)"
                      >
                        <Option :value="option.id" :label="option.name" :key="'eioi_' + eioi" v-for="(option, eioi) in result"></Option>
                      </Select>
                      <!-- </span> -->
                    </td>
                    <td width="40%">
                      <template v-if="essential.variations && essential.variations.length">
                        <!-- <Select filterable v-model="portions[index].essentials[ei].variation_id"> -->
                        <Select filterable clearable v-model="essential.variation_id">
                          <Option
                            :value="option.id"
                            :key="'eivoi_' + eivoi"
                            :label="metaString(option.meta, true)"
                            v-for="(option, eivoi) in essential.variations"
                          ></Option>
                        </Select>
                      </template>
                    </td>
                    <!-- <td><InputNumber v-model="portions[index].essentials[ei].quantity" /></td> -->
                    <td><InputNumber v-model="essential.quantity" /></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </Card>

          <Card dis-hover class="mb16">
            <p slot="title">{{ $t('choosable_items') }}</p>
            <Button size="small" type="primary" @click="group = true" slot="extra">
              <Icon type="ios-folder" />
              <span class="hidden-sm">{{ $t('add_x', { x: $tc('group_type') }) }}</span>
            </Button>
            <Card dis-hover class="mb16" :key="'gi_' + gi" v-for="(group, gi) in portion.choosables">
              <p slot="title">{{ group.name }}</p>
              <Button size="small" type="primary" @click="addChoosableRow(portion, group, gi)" slot="extra">
                <Icon type="ios-add-circle" />
                <span class="hidden-sm">{{ $t('add_x', { x: $tc('item') }) }}</span>
              </Button>
              <div class="variant-table mb16">
                <table class="table">
                  <thead>
                    <tr>
                      <th>{{ $t('name') }}</th>
                      <th>{{ $tc('variation') }}</th>
                      <th>{{ $t('quantity') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr :key="'gii_' + gii" v-for="(gItem, gii) in group.items">
                      <td width="40%">
                        <!-- <div v-if="index" class="text-field">{{ gItem.name }}</div>
                          <span v-else> -->
                        <!-- v-model="portions[index].choosables[gi].items[gii].id" -->
                        <Select
                          remote
                          clearable
                          filterable
                          v-model="gItem.id"
                          :loading="searching"
                          :filter-by-label="true"
                          :remote-method="searchItems"
                          @on-change="v => itemChanged(v, portion, gi, gii)"
                        >
                          <!-- :label="portion.choosables[gi].items.map(i => i.name)" -->
                          <Option
                            :value="option.id"
                            :label="option.name"
                            :key="'giioi_' + giioi"
                            v-for="(option, giioi) in result"
                          ></Option>
                        </Select>
                        <!-- </span> -->
                      </td>
                      <td width="40%">
                        <template v-if="gItem.variations && gItem.variations.length">
                          <!-- <Select filterable v-model="portions[index].choosables[gi].items[gii].variation_id"> -->
                          <Select filterable v-model="gItem.variation_id">
                            <Option
                              :value="option.id"
                              :key="'giivoi_' + giivoi"
                              :label="metaString(option.meta, true)"
                              v-for="(option, giivoi) in gItem.variations"
                            ></Option>
                          </Select>
                        </template>
                      </td>
                      <!-- <td><InputNumber v-model="portions[index].choosables[gi].items[gii].quantity" /></td> -->
                      <td><InputNumber v-model="gItem.quantity" /></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </Card>
          </Card>
          <Row :gutter="16">
            <Col :sm="24" :md="12" :lg="12">
              <FormItem :label="$t('cost')" class="input-tip smaller-label">
                <InputNumber v-model="portion.cost" />
                <!-- @on-focus="minCost = portionCost(portion, index)"
                  @on-change="minCost = portionCost(portion, index)" -->
                <small class="text-primary">{{ $t('min_cost_error', { min: portionCost(portion) }) }}</small>
              </FormItem>
            </Col>
            <Col :sm="24" :md="12" :lg="12">
              <FormItem :label="$t('price')" class="input-tip smaller-label">
                <InputNumber v-model="portion.price" />
                <!-- @on-focus="minCost = portionCost(portion, index)"
                  @on-change="minCost = portionCost(portion, index)" -->
                <small class="text-primary">{{ $t('price_tip') }}</small>
              </FormItem>
            </Col>
          </Row>
        </Card>
      </Col>
    </Row>
    <div class="mb16">
      <Button type="primary" :loading="saving" :disabled="saving" @click="onSubmit('items')">
        <span v-if="!saving">{{ $t('submit') }}</span>
        <span v-else>{{ $t('saving') }}...</span>
      </Button>
      <Button ghost type="primary" :loading="saving" :disabled="saving" style="margin-left: 8px;" @click="onSubmit('items', true)">
        <span v-if="!saving">{{ $t('save_n_stay') }}</span>
        <span v-else>{{ $t('saving') }}...</span>
      </Button>
      <Button type="warning" ghost @click="goToStep(0)" style="margin-left: 8px;">{{ $t('back') }}</Button>
      <Button type="error" ghost @click="onReset" style="margin-left: 8px;">{{ $t('reset') }}</Button>
    </div>
    <Modal footer-hide v-model="option" width="300" :title="$t('add_x', { x: $tc('portion') })" @on-visible-change="modalChanged">
      <Form ref="current" :model="current" :rules="portionRules" label-position="top" @submit.native.prevent="addPortion">
        <FormItem :label="$t('name')" prop="name">
          <Input v-model="current.name" :placeholder="$t('name')" element-id="portion_name" />
        </FormItem>
        <FormItem class="mb0">
          <Button type="primary" long @click="addPortion">{{ $t('add') }}</Button>
        </FormItem>
      </Form>
    </Modal>
    <Modal footer-hide v-model="group" width="300" :title="$t('add_x', { x: $t('group_type') })" @on-visible-change="modalChanged">
      <Form
        ref="group"
        :rules="groupRules"
        label-position="top"
        :model="choosable_group_type"
        @submit.native.prevent="addChoosableTypeGroup"
      >
        <FormItem :label="$t('group_type')" prop="name">
          <Input v-model="choosable_group_type.name" :placeholder="$t('group_type')" element-id="group_name" />
        </FormItem>
        <FormItem class="mb0">
          <Button type="primary" long @click="addChoosableTypeGroup">{{ $t('add') }}</Button>
        </FormItem>
      </Form>
    </Modal>
  </div>
</template>

<script>
import _debounce from 'lodash/debounce';
export default {
  props: {
    pportions: {
      type: Array,
      required: true,
    },
    loading: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      local: [],
      result: [],
      portions: [],
      group: false,
      minCost: null,
      saving: false,
      option: false,
      searching: false,
      current: { name: '' },
      choosable_group_type: { name: '' },
      portionRules: {
        name: [
          {
            required: true,
            message: this.$t('field_is_required', { field: this.$t('name') }),
            trigger: 'blur',
          },
        ],
      },
      groupRules: {
        name: [
          {
            required: true,
            message: this.$t('field_is_required', { field: this.$t('name') }),
            trigger: 'blur',
          },
        ],
      },
    };
  },
  created() {
    this.pportions.map(p => {
      p.essentials.map(e => {
        e.item
          ? this.local.push({ ...e.item, id: e.item_id, value: e.item_id, label: e.item.name })
          : this.local.push({ ...e, id: e.item_id, value: e.item_id, label: e.name });
      });
      p.choosables.map(g =>
        g.items.map(e => {
          e.item
            ? this.local.push({ ...e.item, id: e.item_id, value: e.item_id, label: e.item.name })
            : this.local.push({ ...e, id: e.item_id, value: e.item_id, label: e.name });
        })
      );
    });
    this.result = [...this.local];

    this.portions = this.pportions.map(p => {
      p.cost = p.cost ? parseFloat(p.cost) : null;
      p.price = p.price ? parseFloat(p.price) : null;
      if (p.essentials && p.essentials.length) {
        p.essentials = p.essentials.map(e => {
          e.quantity = e.quantity ? parseFloat(e.quantity) : null;
          if (e.item) {
            // e.id = e.item.id;
            e.sku = e.item.sku;
            e.name = e.item.name;
            e.cost = e.item.cost;
            e.item_id = e.item.id;
            // e.variants = e.item.variants;
            // e.variations = e.item.variations.map(v => ({ id: v.id, variation_id: v.id, sku: v.sku, meta: v.meta }));
            delete e.item;
          }
          return e;
        });
      }
      if (p.choosables && p.choosables.length) {
        p.choosables = p.choosables.map(g => {
          if (g.items && g.items.length) {
            g.items = g.items.map(e => {
              e.quantity = e.quantity ? parseFloat(e.quantity) : null;
              if (e.item) {
                e.id = e.item.id;
                e.sku = e.item.sku;
                e.name = e.item.name;
                e.cost = e.item.cost;
                e.item_id = e.item.id;
                // e.variants = e.item.variants;
                // e.variations = e.item.variations.map(v => ({ id: v.id, variation_id: v.id, sku: v.sku, meta: v.meta }));
                delete e.item;
              }
              return e;
            });
          }
          return g;
        });
      }
      return p;
    });
  },
  methods: {
    modalChanged(v) {
      if (!v) {
        this.$refs['group'].resetFields();
        this.$refs['current'].resetFields();
      } else if (window) {
        this.$nextTick(() => {
          document.querySelector('#group_name').focus();
          document.querySelector('#portion_name').focus();
        });
      }
    },
    addItemRow(portion, i, d) {
      portion.essentials.push({ quantity: 1 });
    },
    addPortion() {
      this.$refs['current'].validate(valid => {
        if (valid) {
          if (this.current.name) {
            let regular = this.portions.find(p => p.name == 'regular');
            let portion = JSON.parse(JSON.stringify({ ...regular, name: this.current.name }));
            portion.id = null;
            portion.sku = this.sku();
            this.portions.push(portion);
            this.$refs['current'].resetFields();
            this.option = false;
          }
        } else {
          this.$Notice.error({ title: this.$t('invalid_input'), desc: this.$t('invalid_input_error'), duration: 10 });
        }
      });
    },
    addChoosableTypeGroup() {
      this.$refs['group'].validate(valid => {
        if (valid) {
          if (this.choosable_group_type.name) {
            this.portions = this.portions.map(p => {
              p.choosables.push({ name: this.choosable_group_type.name, items: [{ quantity: 1 }] });
              return p;
            });
            this.$refs['group'].resetFields();
            this.group = false;
          }
        } else {
          this.$Notice.error({ title: this.$t('invalid_input'), desc: this.$t('invalid_input_error'), duration: 10 });
        }
      });
    },
    addChoosableRow(portion, group, gi) {
      if (portion && group) {
        group.items.push({ quantity: 1 });
      }
    },
    removePortion(index) {
      this.portions.splice(index, 1);
    },
    portionCost(portion, i) {
      let total = 0;
      if (portion.essentials.length) {
        total += portion.essentials.reduce((a, pi) => a + (pi.cost ? parseFloat(pi.cost) * parseFloat(pi.quantity) : 0), 0);
      }
      if (portion.choosables.length) {
        let max_item_cost = 0;
        let max_cost_item = portion.choosables.map(g => {
          let mi = g.items.reduce((i, c) => (i.cost > c.cost ? i : c), { cost: 0 });
          max_item_cost += mi.cost ? parseFloat(mi.cost) : 0;
        });
        total += max_item_cost;
      }
      return total;
    },
    searchItems(search) {
      search = search.trim();
      // this.result = [...this.local];
      if (search !== '' && !this.result.find(r => r.id == search || r.name == search)) {
        this.getItems(search, this);
      }
    },
    getItems: _debounce((search, vm) => {
      vm.searching = true;
      const search_delay = vm.$store.getters.search_delay;
      vm.$http
        .get('app/items/search?with=variations&cost=yes&q=' + search)
        .then(res => {
          vm.result = res.data;
          vm.result = [...vm.result, ...vm.local];
        })
        .finally(() => (vm.searching = false));
    }, search_delay || 250),
    itemChanged(v, portion, ei, ci) {
      if (v) {
        let fitem = this.result.find(i => i.id == v);
        let item = {
          quantity: 1,
          id: fitem.id,
          sku: fitem.sku,
          cost: fitem.cost,
          name: fitem.name,
          variation_id: null,
          item_id: fitem.item_id ? fitem.item_id : fitem.id,
          variations: fitem.variations.map(v => ({ id: v.id, variation_id: v.id, sku: v.sku, meta: v.meta })),
        };
        if (ci !== false) {
          portion.choosables[ei].items.splice(ci, 1, item);
        } else {
          portion.essentials.splice(ei, 1, item);
        }
      }
      this.portionCost(portion);
    },
    goToStep(step) {
      this.$emit('step-changed', step);
    },
    onReset() {
      this.$emit('on-reset');
    },
    onSubmit(page, stay) {
      this.$emit('portions-changed', this.portions);
      this.$nextTick(() => {
        this.$emit('on-submit', { page, stay });
      });
    },
  },
};
</script>
