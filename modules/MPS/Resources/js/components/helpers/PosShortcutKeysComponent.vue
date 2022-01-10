<template>
  <List border size="small">
    <ListItem><Tag class="key">ctrl+alt+f</Tag> {{ $t('focus_scan_barcode') }}</ListItem>
    <ListItem><Tag class="key">ctrl+alt+o</Tag> {{ $t('new_order') }}</ListItem>
    <ListItem><Tag class="key">ctrl+alt+d</Tag> {{ $t('delete_x', { x: $tc('order') }) }}</ListItem>
    <ListItem><Tag class="key">ctrl+alt+i</Tag> {{ $t('order_form') }}</ListItem>
    <ListItem><Tag class="key">ctrl+alt+p</Tag> {{ $tc('payment') }}</ListItem>
    <ListItem><Tag class="key">ctrl+alt+k</Tag> {{ $t('print_x', { x: $tc('order') }) }}</ListItem>
    <ListItem><Tag class="key">ctrl+alt+b</Tag> {{ $t('print_x', { x: $tc('bill') }) }}</ListItem>
    <ListItem><Tag class="key">ctrl+alt+l</Tag> {{ $t('print_x', { x: $tc('last_pos_sale') }) }}</ListItem>
    <ListItem><Tag class="key">ctrl+alt+s</Tag> {{ $t('toggle_x', { x: $t('sidebar') }) }}</ListItem>
    <ListItem><Tag class="key">ctrl+alt+c</Tag> {{ $t('toggle_x', { x: $t('calculator') }) }}</ListItem>
    <ListItem><Tag class="key">ctrl+alt+a</Tag> {{ $t('alerts_title') }}</ListItem>
  </List>
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
