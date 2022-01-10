<template>
  <div class="order" v-if="delivery">
    <Row :gutter="16">
      <Col :xs="24" :sm="12">
        {{ $t('date') }}: <strong>{{ delivery.created_at | date }}</strong>
      </Col>
      <Col :xs="24" :sm="12">
        {{ $t('reference') }}: <strong>{{ delivery.reference }}</strong>
      </Col>
    </Row>
    <h2 class="text-center my16" style="text-transform: uppercase;">{{ $tc('delivery') }}</h2>
    <Row :gutter="16">
      <Col :xs="24" :sm="12">
        <p>{{ $tc('location') }}:</p>
        <div v-if="delivery.location">
          <strong>{{ delivery.location.name }}</strong
          ><br />
          {{ delivery.location.address + ', ' + delivery.location.state_name + ', ' + delivery.location.country_name }}<br />
          {{ delivery.location.phone }}<br />
          {{ delivery.location.email }}<br />
        </div>
      </Col>
      <Col :xs="24" :sm="12">
        <p>{{ $tc('customer') }}:</p>
        <div v-if="delivery.customer">
          <strong>{{ delivery.customer.name }}</strong
          ><br />
          {{ delivery.customer.address + ', ' + delivery.customer.state_name + ', ' + delivery.customer.country_name }}<br />
          {{ delivery.customer.phone }}<br />
          {{ delivery.customer.email }}<br />
        </div>
      </Col>
    </Row>
    <div class="table-wrapper" v-if="delivery.items">
      <table class="table">
        <thead>
          <tr>
            <th class="text-left">{{ $t('description') }}</th>
            <th style="width: 100px;">{{ $t('quantity') }}</th>
          </tr>
        </thead>
        <tbody>
          <template v-for="item in delivery.items">
            <template v-if="item.variations && item.variations.length">
              <tr :key="item.id" class="border-b-0">
                <td>
                  <strong>{{ item.name }}</strong>
                  <div v-if="item.variation">{{ item.variation.id }}</div>
                </td>
                <td></td>
              </tr>
              <tr :key="v.id" v-for="(v, vi) in item.variations" class="border-y-0">
                <td class="pt0">
                  <span v-html="metaString(v.meta)"></span>
                </td>
                <td class="pt0 text-right">
                  {{ v.pivot.quantity | formatNumber(2) }}
                </td>
              </tr>
            </template>
            <template v-else-if="item.portions && item.portions.length">
              <tr :key="'p_' + item.id" class="border-b-0">
                <td>
                  <strong>{{ item.name }}</strong>
                </td>
                <td></td>
              </tr>
              <template v-for="(p, pi) in item.portions">
                <tr :key="p.id" class="border-y-0">
                  <td class="bold capitalize">
                    {{ p.name }}
                  </td>
                  <td class="text-right">
                    <span v-if="p.essentials.length < 1 && p.choosables.length < 1">
                      {{ p.pivot.quantity | formatNumber(2) }}
                    </span>
                  </td>
                </tr>
                <tr :key="pe.id" v-for="(pe, pei) in p.essentials" class="border-y-0">
                  <td class="pt0">
                    {{ pe.item.name }}
                  </td>
                  <td class="pt0 text-right">
                    {{ pe.quantity | formatNumber(2) }}
                  </td>
                </tr>
                <template v-for="(pc, pci) in p.choosables">
                  <template v-for="(pcitem, pcin) in pc.items">
                    <template v-if="p.pivot.choosables.find(c => c.id == pc.id && pcitem.item_id == c.item_id)">
                      <tr :key="'pci_' + pci + '_' + pcin" class="border-y-0">
                        <td class="pt0">
                          <strong>{{ pc.name }}</strong> {{ pcitem.item.name }}
                        </td>
                        <td class="pt0 text-right">
                          {{ pcitem.quantity | formatNumber(2) }}
                        </td>
                      </tr>
                    </template>
                  </template>
                </template>
              </template>
            </template>
            <template v-else>
              <tr :key="item.id">
                <td :class="!item.modifier_options.length ? 'border-b border-t-0' : 'border-y-0'">
                  {{ item.name }}
                  <div v-if="item.variation">{{ item.variation.id }}</div>
                </td>
                <td class="text-right">{{ item.quantity | formatNumber(2) }}</td>
              </tr>
            </template>
            <template v-if="item.modifier_options && item.modifier_options.length">
              <tr class="border-y-0" :key="'mo_' + item.modifier_options.length">
                <td>
                  <strong>{{ $tc('modifier', 2) }}</strong>
                </td>
                <td></td>
              </tr>
              <tr
                :key="m.id"
                v-for="(m, mi) in item.modifier_options"
                :class="mi == item.modifier_options.length - 1 ? 'border-b border-t-0' : 'border-y-0'"
              >
                <td class="pt0">
                  {{ m.item.name }}
                </td>
                <td class="pt0 text-right">
                  {{ m.pivot.quantity | formatNumber(2) }}
                </td>
              </tr>
            </template>
          </template>
        </tbody>
      </table>
    </div>
    <Row :gutter="16" class="mt16">
      <Col :xs="24" :sm="12">
        {{ $t('status') }}: <strong>{{ delivery.status | capitalize }}</strong>
      </Col>
      <Col :xs="24" :sm="12" v-if="delivery.user">
        {{ $t('created_by') }}: <strong>{{ delivery.user.name }}</strong>
      </Col>
    </Row>
    <Row :gutter="16" class="mt16">
      <Col :xs="24" :sm="12">
        {{ $t('delivered_at') }}: <strong v-if="delivery.delivered_at">{{ delivery.delivered_at | datetime }}</strong>
      </Col>
      <Col :xs="24" :sm="12" v-if="delivery.user">
        {{ $t('delivered_by') }}: <strong>{{ delivery.deliveryMan ? delivery.deliveryMan.name : delivery.delivered_by }}</strong>
      </Col>
    </Row>
    <Row :gutter="16" class="mt16">
      <Col :xs="24" :sm="12">
        {{ $tc('driver') }}: <strong v-if="delivery.driver">{{ delivery.driver | capitalize }}</strong>
      </Col>
      <Col :xs="24" :sm="12">
        {{ $t('received_by') }}: <strong v-if="delivery.received_by">{{ delivery.received_by | capitalize }}</strong>
      </Col>
    </Row>
  </div>
</template>

<script>
export default {
  props: {
    delivery: {
      type: Object,
      required: true,
    },
  },
};
</script>
