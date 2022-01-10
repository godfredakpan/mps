<template>
  <Card :bordered="false" class="pos-grid-item">
    <img :src="item.photo ? item.photo : '/images/default.svg'" />
    <div class="pos-grid-item-name">
      <h4 style="word-break: break-word;">
        {{ item.name | trimString }}<br />
        @<strong>{{ item.price | formatNumber($store.state.settings.decimals) }}</strong>
      </h4>
    </div>
  </Card>
</template>

<script>
export default {
  props: {
    item: { type: Object, required: true },
  },
};
</script>

<style lang="scss">
@import 'scss/pos-grid-item.scss';
</style>
