<template>
  <div class="center-fixed">
    <Spin fix v-if="$store.state.settings.loader == 'circle'" class="rounded">
      <div class="loader">
        <svg class="circular" viewBox="25 25 50 50">
          <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="5" stroke-miterlimit="10"></circle>
        </svg>
      </div>
    </Spin>
    <Spin fix v-else size="large" style="border-radius: 6px;"></Spin>
  </div>
</template>

<script>
export default {};
</script>

<style scoped>
.center-fixed {
  top: 50%;
  left: 50%;
  width: 50px;
  height: 50px;
  display: block;
  position: fixed;
  margin: 10px auto;
  margin-top: -25px;
  margin-left: -25px;
  border-radius: 6px;
  /* border: 1px solid #e8eaec; */
  box-shadow: inset 0 -3em 3em rgba(0, 0, 0, 0.1), 0 0 0 1px rgb(255, 255, 255), 0.3em 0.3em 1em rgba(0, 0, 0, 0.3);
}
.rounded {
  border-radius: 6px;
}
</style>
