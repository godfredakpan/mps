<template>
  <Card :dis-hover="true">
    <p slot="title">{{ form.id ? $t('edit') : $t('add') }} {{ $tc('modifier') }}</p>
    <router-link to="/modifiers" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list') }} {{ $tc('modifier', 2) }}
    </router-link>
    <div>
      <Form ref="modifier" :model="form" :rules="rules" :label-width="150" class="form-responsive">
        <Row :gutter="16">
          <Col :sm="24" :md="24" :lg="24">
            <Loading v-if="loading" />
            <Alert type="error" show-icon class="mb26" v-if="errors.message">
              <div v-html="errors.message"></div>
            </Alert>
            <Row :gutter="16">
              <Col :sm="24" :md="18" :lg="12">
                <FormItem :label="$t('code')" prop="code" :error="errors.form.code | a2s">
                  <Input v-model="form.code" />
                </FormItem>
                <FormItem :label="$t('title')" prop="title" :error="errors.form.title | a2s">
                  <Input v-model="form.title" />
                </FormItem>
                <FormItem :label="$t('show_as')" prop="show_as" :error="errors.form.show_as | a2s">
                  <Select v-model="form.show_as">
                    <Option value="radio">{{ $t('radio') }}</Option>
                    <Option value="checkbox">{{ $t('checkbox') }}</Option>
                    <Option value="select">{{ $t('select') }}</Option>
                    <Option value="select_multiple">{{ $t('select_multiple') }}</Option>
                  </Select>
                </FormItem>
                <FormItem :label="$t('details')" prop="details" :error="errors.form.details | a2s">
                  <Input type="textarea" v-model="form.details" :autosize="{ minRows: 2, maxRows: 8 }" />
                </FormItem>
                <!-- <FormItem :label="$t('add_to_x', { x: $tc('modifier') })">
                                    <AutoComplete
                                        v-model="item"
                                        :data="items"
                                        @on-select="itemSelected"
                                        @on-change="searchItems"
                                        :placeholder="$t('search_x', { x: $tc('item') })"
                                    ></AutoComplete>
                                </FormItem> -->
              </Col>
              <Col :sm="24" :md="6" :lg="12"> </Col>
            </Row>
            <FormItem label="">
              <Card dis-hover>
                <p slot="title">{{ $t('options') }}</p>
                <Button size="small" type="success" @click="addOption" slot="extra">
                  <Icon type="ios-options" />
                  {{ $t('add_x', { x: $tc('option') }) }}
                </Button>
                <div class="variants-form">
                  <div class="variant-table">
                    <table class="table">
                      <thead>
                        <tr>
                          <th style="padding: 0; min-width: 150px;">{{ $t('x_name', { x: $tc('item') }) }}</th>
                          <!-- <th style="padding:0;min-width:100px;">{{ $t('cost') }}</th>
                                                    <th style="padding:0;min-width:100px;">{{ $t('price') }}</th> -->
                          <!-- <th style="padding:0;min-width:100px;max-width:200px;" v-if="$store.getters.stock"> -->
                          <th style="padding: 0; min-width: 100px; width: 200px;">
                            {{ $t('quantity') }}
                          </th>
                          <th style="padding: 0; max-width: 40px; min-width: 40px; width: 40px;">
                            <icon type="md-trash" />
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(option, index) in form.options" :key="'opt_' + index">
                          <td>
                            <!-- <Input v-model="option.name" /> -->
                            <Select
                              remote
                              clearable
                              filterable
                              :loading="searching"
                              v-model="option.item_id"
                              :label="option.item_name"
                              :remote-method="searchItems"
                            >
                              <Option :value="item.id" :label="item.name" :key="'item_' + i" v-for="(item, i) in result"></Option>
                            </Select>
                          </td>
                          <!-- <td><InputNumber v-model="option.cost" /></td><td><InputNumber v-model="option.price" /></td> -->
                          <td><InputNumber v-model="option.quantity" /></td>
                          <td class="text-center">
                            <Icon size="16" v-if="index" class="pointer" color="#ed3f14" type="md-trash" @click="deleteOption(index)" />
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
                <!-- <div class="ivu-form-label-top ivu-form-inline form-responsive">
                  <CellGroup>
                    <Cell v-for="(option, index) in form.options" :key="'opt_' + index" style="padding-bottom:20px;border-radius:4px;">
                      <FormItem :label="$t('name')" prop="name">
                        <Input v-model="option.name" />
                      </FormItem>
                      <FormItem :label="$t('cost')" prop="cost">
                        <InputNumber v-model="option.cost" />
                      </FormItem>
                      <FormItem :label="$t('price')" prop="price">
                        <InputNumber v-model="option.price" />
                      </FormItem>
                      <span v-if="$store.getters.stock">
                        <FormItem :label="$t('quantity')" prop="stock">
                          <InputNumber v-model="option.quantity" />
                        </FormItem>
                      </span>
                      <Button size="small" type="error" @click="deleteOption(index)" slot="extra" v-if="index">
                        <Icon type="ios-trash" />
                      </Button>
                    </Cell>
                  </CellGroup>
                </div> -->
              </Card>
            </FormItem>

            <form-custom-fields v-model="form" :attributes="attributes" @update="updateCF" />

            <FormItem>
              <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('modifiers')">
                <span v-if="!saving">{{ $t('submit') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button
                ghost
                type="primary"
                :loading="saving"
                :disabled="saving"
                style="margin-left: 8px;"
                @click="handleSubmit('modifiers', true)"
              >
                <span v-if="!saving">{{ $t('save_n_stay') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button v-if="!form.id" @click="handleReset()" style="margin-left: 8px;">{{ $t('reset') }}</Button>
            </FormItem>
          </Col>
        </Row>
      </Form>
    </div>
  </Card>
</template>

<script>
import Form from '@mpsjs/mixins/Form';
import _debounce from 'lodash/debounce';
const formatRes = (data, vm) => {
  if (data.attributes) {
    vm.attributes = data.attributes;
    delete data.attributes;
  }
  data.extra_attributes = vm.formatAttributes(vm.attributes, data.extra_attributes);
  data.options = data.options.map(o => {
    vm.result.push(o.item);
    o.item_name = o.item.name;
    o.quantity = o.quantity ? parseFloat(o.quantity) : null;
    delete o.item;
    return o;
  });
  vm.form = { ...data, ...data.extra_attributes };
  return vm.form;
};
export default {
  mixins: [Form('modifier', 'app/modifiers', true, formatRes)],
  data() {
    return {
      item: '',
      items: [],
      result: [],
      attributes: [],
      searching: false,
      form: {
        id: '',
        code: '',
        name: '',
        show_as: 'radio',
        details: '',
        options: [{ sku: this.sku(), item_id: '', item_name: '', quantity: 1 }],
      },
      rules: {
        code: [{ required: true, message: this.$t('field_is_required', { field: this.$t('code') }), trigger: 'blur' }],
        title: [{ required: true, message: this.$t('field_is_required', { field: this.$t('title') }), trigger: 'blur' }],
        name: [{ required: true, message: this.$t('field_is_required', { field: this.$t('name') }), trigger: 'blur' }],
        show_as: [{ required: true, message: this.$t('field_is_required', { field: this.$t('show_as') }), trigger: 'change' }],
        cost: [
          {
            required: true,
            type: 'number',
            trigger: 'change',
            message: this.$t('field_is_required', { field: this.$t('cost') }),
          },
        ],
        price: [
          {
            required: true,
            type: 'number',
            trigger: 'change',
            message: this.$t('field_is_required', { field: this.$t('price') }),
          },
        ],
      },
    };
  },
  methods: {
    create() {
      this.$http
        .get('app/modifiers/create')
        .then(res => (this.attributes = res.data))
        .finally(() => (this.loading = false));
    },
    fetch(id) {
      this.$http
        .get(`app/modifiers/${id}`)
        .then(res => formatRes(res.data, this))
        .finally(() => (this.loading = false));
    },
    searchItems(search) {
      search = search.trim();
      if (search !== '' && !this.result.find(r => r.id == search || r.name == search)) {
        this.getItems(search, this);
      }
    },
    getItems: _debounce((search, vm) => {
      vm.searching = true;
      const search_delay = vm.$store.getters.search_delay;
      vm.$http
        .get('app/items/search?q=' + search)
        .then(res => (vm.result = res.data))
        .finally(() => (vm.searching = false));
    }, search_delay || 250),
    addOption() {
      this.form.options.push({ sku: this.sku(), item_id: '', item_name: '', quantity: 1 });
    },
    deleteOption(index) {
      this.form.options.splice(index, 1);
    },
  },
};
</script>
