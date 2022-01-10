<template>
  <div v-if="item" class="table-responsive">
    <div class="ivu-table-wrapper ivu-table-wrapper-with-border">
      <div class="ivu-table ivu-table-default ivu-table-border">
        <div class="ivu-table-body">
          <table cellspacing="0" cellpadding="0" border="0" style="width:100%;min-width:300px">
            <tbody class="ivu-table-tbody">
              <tr class="ivu-table-row">
                <td style="width:40%;min-width:100px">
                  <div class="ivu-table-cell">
                    <div class="ivu-table-cell-slot">{{ $t('code') }}</div>
                  </div>
                </td>
                <td style="width:60%;min-width:200px">
                  <div class="ivu-table-cell">
                    <strong>{{ item.code }}</strong> <span style="text-transform:uppercase;">({{ item.symbology }})</span>
                  </div>
                </td>
              </tr>
              <tr class="ivu-table-row">
                <td style="width:40%;min-width:100px">
                  <div class="ivu-table-cell">
                    <div class="ivu-table-cell-slot">{{ $t('name') }}</div>
                  </div>
                </td>
                <td style="width:60%;min-width:200px">
                  <div class="ivu-table-cell">
                    <strong>{{ item.name }}</strong> <span v-if="item.alt_name">({{ item.alt_name }})</span>
                  </div>
                </td>
              </tr>
              <tr class="ivu-table-row">
                <td style="width:40%;min-width:100px">
                  <div class="ivu-table-cell">
                    <div class="ivu-table-cell-slot">{{ $t('sku') }}</div>
                  </div>
                </td>
                <td style="width:60%;min-width:200px">
                  <div class="ivu-table-cell">{{ item.sku }}</div>
                </td>
              </tr>
              <tr class="ivu-table-row" v-if="item.photo || item.video">
                <td style="width:40%;min-width:100px">
                  <div class="ivu-table-cell">
                    <div class="ivu-table-cell-slot">{{ $t('photo') }} / {{ $t('video') }}</div>
                  </div>
                </td>
                <td style="width:60%;min-width:200px">
                  <div class="ivu-table-cell" style="display:flex;align-items:center;justify-content:space-between;">
                    <a v-if="item.photo" :href="item.photo" target="_blank">
                      <img :alt="item.name" :src="item.photo" style="max-width:100px;max-height:50px;margin-top:5px;border-radius:5px;" />
                    </a>
                    <Button v-if="item.video" :to="item.video" target="_blank">{{ $t('view_x', { x: $t('video') }) }}</Button>
                  </div>
                </td>
              </tr>
              <tr class="ivu-table-row">
                <td style="width:40%;min-width:100px">
                  <div class="ivu-table-cell">
                    <div class="ivu-table-cell-slot">{{ $t('cost') }}</div>
                  </div>
                </td>
                <td style="width:60%;min-width:200px">
                  <div class="ivu-table-cell">
                    {{ formatNumber(item.cost) }}
                  </div>
                </td>
              </tr>
              <tr class="ivu-table-row">
                <td style="width:40%;min-width:100px">
                  <div class="ivu-table-cell">
                    <div class="ivu-table-cell-slot">{{ $t('price') }}</div>
                  </div>
                </td>
                <td style="width:60%;min-width:200px">
                  <div class="ivu-table-cell">
                    {{ formatNumber(item.price) }}
                    <small v-if="item.min_price || item.max_price">
                      ({{ `${$t('min_price')}: ${formatNumber(item.min_price)} - ${$t('max_price')}: ${formatNumber(item.max_price)}` }})
                    </small>
                  </div>
                </td>
              </tr>
              <tr class="ivu-table-row">
                <td style="width:40%;min-width:100px">
                  <div class="ivu-table-cell">
                    <div class="ivu-table-cell-slot">{{ $t('max_discount') }}</div>
                  </div>
                </td>
                <td style="width:60%;min-width:200px">
                  <div class="ivu-table-cell">{{ formatNumber(item.max_discount) }}%</div>
                </td>
              </tr>
              <tr class="ivu-table-row" v-if="item.categories && item.categories.length">
                <td style="width:40%;min-width:100px">
                  <div class="ivu-table-cell">
                    <div class="ivu-table-cell-slot">{{ $tc('category') }}</div>
                  </div>
                </td>
                <td style="width:60%;min-width:200px">
                  <div class="ivu-table-cell">
                    {{ item.categories[0].name }}
                  </div>
                </td>
              </tr>
              <template v-if="item.unit">
                <tr class="ivu-table-row">
                  <td style="width:40%;min-width:100px">
                    <div class="ivu-table-cell">
                      <div class="ivu-table-cell-slot">{{ $tc('unit') }}</div>
                    </div>
                  </td>
                  <td style="width:60%;min-width:200px">
                    <div class="ivu-table-cell">{{ item.unit.name }} ({{ item.unit.code }})</div>
                  </td>
                </tr>
              </template>
              <template v-if="item.type == 'recipe' && item.portions">
                <tr class="ivu-table-row">
                  <td style="width:40%;min-width:100px">
                    <div class="ivu-table-cell">
                      <div class="ivu-table-cell-slot">{{ $tc('portion', 2) }}</div>
                    </div>
                  </td>
                  <td style="width:60%;min-width:200px">
                    <div class="ivu-table-cell" style="margin: 5px 0;">
                      <div v-for="portion in item.portions" :key="portion.id">
                        <div>
                          <strong>{{ $t(portion.name) }}</strong>
                          <div>
                            <div style="padding-left:16px" v-for="(pi, i) in portion.portion_items" :key="pi.id">
                              #{{ i + 1 }} &#8674; {{ pi.item.name }} &#8611; {{ $t('qty') }} {{ formatQuantity(pi.quantity) }}
                              <!-- <span v-if="pi.variation_id"></span> -->
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
              </template>
              <template v-if="item.type == 'combo' && item.portions">
                <tr class="ivu-table-row">
                  <td style="width:40%;min-width:100px">
                    <div class="ivu-table-cell">
                      <div class="ivu-table-cell-slot">{{ $tc('portion', 2) }}</div>
                    </div>
                  </td>
                  <td style="width:60%;min-width:200px">
                    <div class="ivu-table-cell" style="margin: 5px 0;">
                      <div v-for="portion in item.portions" :key="portion.id">
                        <div>
                          <strong>{{ $t(portion.name) }}</strong> ({{ $t('price') }} {{ formatNumber(portion.price) }})
                          <div>
                            <div style="padding-left:16px" v-for="(pi, i) in portion.essentials" :key="pi.id">
                              #{{ i + 1 }} &#8674; {{ pi.item.name }} &#8611; {{ $t('qty') }} {{ formatQuantity(pi.quantity) }}
                              <!-- <span v-if="pi.variation_id"></span> -->
                            </div>
                          </div>
                          <div>
                            <div style="padding-left:16px" v-for="(pi, ci) in portion.choosables" :key="pi.id">
                              #{{ portion.essentials.length + ci + 1 }} &#8674; {{ pi.name }}
                              <div style="padding-left:16px" v-for="(pitem, i) in pi.items" :key="pitem.id">
                                <small>
                                  #{{ i + 1 }} &#8674; {{ pitem.item.name }} &#8611; {{ $t('qty') }}
                                  {{ formatQuantity(pitem.quantity) }}
                                  <!-- <span v-if="pi.variation_id"></span> -->
                                </small>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
              </template>
              <template v-if="item.taxes && item.taxes.length">
                <tr class="ivu-table-row">
                  <td style="width:40%;min-width:100px">
                    <div class="ivu-table-cell">
                      <div class="ivu-table-cell-slot">{{ $tc('tax', 2) }}</div>
                    </div>
                  </td>
                  <td style="width:60%;min-width:200px">
                    <div class="ivu-table-cell">
                      {{ item.taxes.map(t => t.name).join(', ') }}
                    </div>
                  </td>
                </tr>
                <tr class="ivu-table-row">
                  <td style="width:40%;min-width:100px">
                    <div class="ivu-table-cell">
                      <div class="ivu-table-cell-slot">{{ $tc('tax_method') }}</div>
                    </div>
                  </td>
                  <td style="width:60%;min-width:200px">
                    <div class="ivu-table-cell">
                      <div v-if="item.tax_included == 1" class="ivu-tag ivu-tag-success ivu-tag-border ivu-tag-checked">
                        <span class="ivu-tag-text ivu-tag-color-success">{{ $t('inclusive') }}</span>
                      </div>
                      <div v-else class="ivu-tag ivu-tag-primary ivu-tag-border ivu-tag-checked">
                        <span class="ivu-tag-text ivu-tag-color-primary">{{ $t('exclusive') }}</span>
                      </div>
                    </div>
                  </td>
                </tr>
              </template>
              <tr class="ivu-table-row">
                <td style="width:40%;min-width:100px">
                  <div class="ivu-table-cell">
                    <div class="ivu-table-cell-slot">{{ $t('misc') }}</div>
                  </div>
                </td>
                <td style="width:60%;min-width:200px">
                  <div class="ivu-table-cell" style="margin: 5px 0;">
                    <div>
                      <Icon v-if="item.changeable == 1" type="md-checkmark" size="16" color="#19be6b" />
                      <Icon v-else type="md-close" size="16" color="#ed4014" />
                      {{ $t('allow_price_change') }}
                    </div>
                    <div v-if="item.type == 'standard'">
                      <Icon v-if="item.has_variants == 1" type="md-checkmark" size="16" color="#19be6b" />
                      <Icon v-else type="md-close" size="16" color="#ed4014" />
                      {{ $t('item_has_variants') }}
                    </div>
                    <div v-if="item.type == 'standard'">
                      <Icon v-if="item.has_serials == 1" type="md-checkmark" size="16" color="#19be6b" />
                      <Icon v-else type="md-close" size="16" color="#ed4014" />
                      {{ $t('item_has_serial') }}
                    </div>
                  </div>
                </td>
              </tr>
              <tr class="ivu-table-row" v-if="item.is_stock && item.type == 'standard' && item.stock">
                <td style="width:40%;min-width:100px">
                  <div class="ivu-table-cell">
                    <div class="ivu-table-cell-slot">{{ $t('current_stock') }}</div>
                  </div>
                </td>
                <td style="width:60%;min-width:200px">
                  <div class="ivu-table-cell" style="margin: 5px 0;">
                    <div v-for="stock in item.stock" :key="stock.id">
                      <div>
                        {{ stock.location.name }}: <strong>{{ formatQuantity(stock.quantity) }}</strong>
                        <span v-if="item.unit">{{ item.unit.name }}</span>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
              <tr class="ivu-table-row">
                <td style="width:40%;min-width:100px">
                  <div class="ivu-table-cell">
                    <div class="ivu-table-cell-slot">{{ $t('created_at') }}</div>
                  </div>
                </td>
                <td style="width:60%;min-width:200px">
                  <div class="ivu-table-cell" style="margin: 5px 0;">{{ datetime(item.created_at) }}</div>
                </td>
              </tr>
              <tr class="ivu-table-row">
                <td style="width:40%;min-width:100px">
                  <div class="ivu-table-cell">
                    <div class="ivu-table-cell-slot">{{ $t('updated_at') }}</div>
                  </div>
                </td>
                <td style="width:60%;min-width:200px">
                  <div class="ivu-table-cell" style="margin: 5px 0;">{{ datetime(item.updated_at) }}</div>
                </td>
              </tr>
              <tr class="ivu-table-row" v-if="item.summary">
                <td style="width:40%;min-width:100px">
                  <div class="ivu-table-cell">
                    <div class="ivu-table-cell-slot">{{ $t('summary') }}</div>
                  </div>
                </td>
                <td style="width:60%;min-width:200px">
                  <div class="ivu-table-cell" style="margin: 5px 0;">{{ item.summary }}</div>
                </td>
              </tr>
              <tr class="ivu-table-row" v-if="item.details">
                <td style="width:40%;min-width:100px">
                  <div class="ivu-table-cell">
                    <div class="ivu-table-cell-slot">{{ $t('details') }}</div>
                  </div>
                </td>
                <td style="width:60%;min-width:200px">
                  <div class="ivu-table-cell" style="margin: 5px 0;">{{ item.details }}</div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    item: {
      required: true,
    },
  },
  // watch: {
  //   'item.id': function(v, o) {
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
  //     this.$http.get('app/stock/' + this.item.id).then(res => (this.stock = res.data));
  //   },
  // },
};
</script>
