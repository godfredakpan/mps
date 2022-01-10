<template>
  <div>
    <Tag>ALT = Alt / Option</Tag>
    <Tag>META = Windows / Command</Tag>
    <Row :gutter="16">
      <Col :xs="24" :sm="12" :lg="$store.getters.superAdmin ? 8 : 12">
        <List border size="small" class="mt16">
          <ListItem><Tag class="key">ctrl+shift+h</Tag> {{ $t('dashboard') }}</ListItem>
          <ListItem><Tag class="key">ctrl+shift+p</Tag> {{ $t('pos') }}</ListItem>
          <ListItem><Tag class="key">ctrl+shift+o</Tag> {{ $t('list_x', { x: $tc('order', 2) }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+shift+s</Tag> {{ $t('list_x', { x: $tc('sale', 2) }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+shift+d</Tag> {{ $t('list_x', { x: $tc('recurring_sale', 2) }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+shift+b</Tag> {{ $t('list_x', { x: $tc('purchase', 2) }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+shift+q</Tag> {{ $t('list_x', { x: $tc('quotation', 2) }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+shift+r</Tag> {{ $t('list_x', { x: $tc('return_order', 2) }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+shift+a</Tag> {{ $t('list_x', { x: $tc('payment', 2) }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+shift+i</Tag> {{ $t('list_x', { x: $tc('item', 2) }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+shift+x</Tag> {{ $t('list_x', { x: $tc('recipe', 2) }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+shift+y</Tag> {{ $t('list_x', { x: $tc('service', 2) }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+shift+z</Tag> {{ $t('list_x', { x: $tc('combo', 2) }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+shift+m</Tag> {{ $t('list_x', { x: $tc('modifier', 2) }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+shift+g</Tag> {{ $t('list_x', { x: $tc('gift_card', 2) }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+shift+n</Tag> {{ $t('list_x', { x: $tc('income', 2) }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+shift+e</Tag> {{ $t('list_x', { x: $tc('expense', 2) }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+shift+c</Tag> {{ $t('list_x', { x: $tc('customer', 2) }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+shift+v</Tag> {{ $t('list_x', { x: $tc('supplier', 2) }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+shift+t</Tag> {{ $t('list_x', { x: $tc('stock_transfer', 2) }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+meta+r</Tag> {{ $t('list_x', { x: $tc('report', 2) }) }}</ListItem>
        </List>
      </Col>
      <Col :xs="24" :sm="12" :lg="$store.getters.superAdmin ? 8 : 12">
        <List border size="small" class="mt16">
          <ListItem><Tag class="key">ctrl+alt+x</Tag> {{ $t('fullscreen') }}</ListItem>
          <ListItem><Tag class="key">ctrl+alt+a</Tag> {{ $t('alerts_title') }}</ListItem>
          <ListItem><Tag class="key">ctrl+alt+f</Tag> {{ $t('focus_scan_barcode') }}</ListItem>
          <ListItem><Tag class="key">alt+shift+s</Tag> {{ $t('add_x', { x: $tc('sale') }) }}</ListItem>
          <ListItem><Tag class="key">alt+shift+d</Tag> {{ $t('add_x', { x: $tc('recurring_sale') }) }}</ListItem>
          <ListItem><Tag class="key">alt+shift+b</Tag> {{ $t('add_x', { x: $tc('purchase') }) }}</ListItem>
          <ListItem><Tag class="key">alt+shift+q</Tag> {{ $t('add_x', { x: $tc('quotation') }) }}</ListItem>
          <ListItem><Tag class="key">alt+shift+r</Tag> {{ $t('add_x', { x: $tc('return_order') }) }}</ListItem>
          <ListItem><Tag class="key">alt+shift+a</Tag> {{ $t('add_x', { x: $tc('payment') }) }}</ListItem>
          <ListItem><Tag class="key">alt+shift+i</Tag> {{ $t('add_x', { x: $tc('item') }) }}</ListItem>
          <ListItem><Tag class="key">alt+shift+x</Tag> {{ $t('add_x', { x: $tc('recipe') }) }}</ListItem>
          <ListItem><Tag class="key">alt+shift+y</Tag> {{ $t('add_x', { x: $tc('service') }) }}</ListItem>
          <ListItem><Tag class="key">alt+shift+z</Tag> {{ $t('add_x', { x: $tc('combo') }) }}</ListItem>
          <ListItem><Tag class="key">alt+shift+m</Tag> {{ $t('add_x', { x: $tc('modifier') }) }}</ListItem>
          <ListItem><Tag class="key">alt+shift+g</Tag> {{ $t('add_x', { x: $tc('gift_card') }) }}</ListItem>
          <ListItem><Tag class="key">alt+shift+n</Tag> {{ $t('add_x', { x: $tc('income') }) }}</ListItem>
          <ListItem><Tag class="key">alt+shift+e</Tag> {{ $t('add_x', { x: $tc('expense') }) }}</ListItem>
          <ListItem><Tag class="key">alt+shift+c</Tag> {{ $t('add_x', { x: $tc('customer') }) }}</ListItem>
          <ListItem><Tag class="key">alt+shift+v</Tag> {{ $t('add_x', { x: $tc('supplier') }) }}</ListItem>
          <ListItem><Tag class="key">alt+shift+t</Tag> {{ $t('add_x', { x: $tc('stock_transfer') }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+shift+k</Tag> {{ $t('shortcuts') }}</ListItem>
        </List>
      </Col>
      <Col :xs="24" :sm="12" :lg="8" v-if="this.$store.getters.superAdmin">
        <List border size="small" class="mt16">
          <ListItem><Tag class="key">ctrl+meta+p</Tag> {{ $t('list_x', { x: $tc('promo', 2) }) }}</ListItem>
          <ListItem><Tag class="key">alt+meta+p</Tag> {{ $t('add_x', { x: $tc('promo') }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+meta+b</Tag> {{ $t('list_x', { x: $tc('brand', 2) }) }}</ListItem>
          <ListItem><Tag class="key">alt+meta+b</Tag> {{ $t('add_x', { x: $tc('brand') }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+meta+c</Tag> {{ $t('list_x', { x: $tc('category', 2) }) }}</ListItem>
          <ListItem><Tag class="key">alt+meta+c</Tag> {{ $t('add_x', { x: $tc('category') }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+meta+t</Tag> {{ $t('list_x', { x: $tc('tax', 2) }) }}</ListItem>
          <ListItem><Tag class="key">alt+meta+t</Tag> {{ $t('add_x', { x: $tc('tax') }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+meta+f</Tag> {{ $t('list_x', { x: $tc('field', 2) }) }}</ListItem>
          <ListItem><Tag class="key">alt+meta+f</Tag> {{ $t('add_x', { x: $tc('field') }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+meta+g</Tag> {{ $t('list_x', { x: $tc('customer_group', 2) }) }}</ListItem>
          <ListItem><Tag class="key">alt+meta+g</Tag> {{ $t('add_x', { x: $tc('customer_group') }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+meta+h</Tag> {{ $t('list_x', { x: $tc('hall', 2) }) }}</ListItem>
          <ListItem><Tag class="key">alt+meta+h</Tag> {{ $t('add_x', { x: $tc('hall') }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+meta+x</Tag> {{ $t('list_x', { x: $tc('unit', 2) }) }}</ListItem>
          <ListItem><Tag class="key">alt+meta+x</Tag> {{ $t('add_x', { x: $tc('unit') }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+meta+y</Tag> {{ $t('list_x', { x: $t('activity_logs') }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+meta+z</Tag> {{ $t('list_x', { x: $tc('asset_transfer', 2) }) }}</ListItem>
          <ListItem><Tag class="key">alt+meta+z</Tag> {{ $t('add_x', { x: $tc('asset_transfer') }) }}</ListItem>
        </List>
      </Col>
    </Row>
    <Row :gutter="16" v-if="this.$store.getters.superAdmin">
      <Col :xs="24" :sm="12" :lg="8">
        <List border size="small" class="mt16">
          <ListItem><Tag class="key">ctrl+meta+,</Tag> {{ $tc('setting', 2) }}</ListItem>
          <ListItem><Tag class="key">ctrl+meta+u</Tag> {{ $t('list_x', { x: $tc('user', 2) }) }}</ListItem>
          <ListItem><Tag class="key">alt+meta+u</Tag> {{ $t('add_x', { x: $tc('user') }) }}</ListItem>
        </List>
      </Col>
      <Col :xs="24" :sm="12" :lg="8">
        <List border size="small" class="mt16">
          <ListItem><Tag class="key">ctrl+meta+a</Tag> {{ $t('list_x', { x: $tc('account', 2) }) }}</ListItem>
          <ListItem><Tag class="key">alt+meta+a</Tag> {{ $t('add_x', { x: $tc('account') }) }}</ListItem>
          <ListItem><Tag class="key">ctrl+meta+l</Tag> {{ $t('list_x', { x: $tc('location', 2) }) }}</ListItem>
          <ListItem><Tag class="key">alt+meta+l</Tag> {{ $t('add_x', { x: $tc('location') }) }}</ListItem>
        </List>
      </Col>
    </Row>
  </div>
</template>

<style>
.key {
  font-weight: bold;
  margin-right: 10px;
  letter-spacing: 1px;
  text-transform: uppercase;
  font-family: 'Lucida Console', Monaco, 'Courier New', Courier, monospace;
}
</style>
