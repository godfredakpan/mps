<template>
  <!-- <Spin fix>
        <div class="loader">
            <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="5" stroke-miterlimit="10"></circle>
            </svg>
        </div>
    </Spin>-->
  <!-- <Spin fix>
        <Icon type="ios-loading" size=18 class="spin-icon-load"></Icon>
        <div>{{ $t('loading') }}</div>
    </Spin>-->
  <Spin fix v-if="$store.state.settings.loader == 'circle'" class="rounded">
    <div class="loader">
      <svg class="circular" viewBox="25 25 50 50">
        <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="5" stroke-miterlimit="10"></circle>
      </svg>
    </div>
  </Spin>
  <Spin fix v-else size="large" style="border-radius: 6px;"></Spin>
</template>

<script>
export default {};
</script>

<style>
.rounded {
  border-radius: 6px;
}
</style>
