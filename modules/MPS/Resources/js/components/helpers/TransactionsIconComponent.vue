<template>
  <div class="text-center">
    <router-link :to="url + id">
      <Icon type="ios-list-box" size="18" />
    </router-link>
  </div>
</template>

<script>
export default {
  props: {
    id: {
      required: true,
    },
    url: {
      required: true,
    },
  },
};
</script>
