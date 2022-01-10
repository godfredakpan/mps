<template>
  <FormItem :label="label || $tc('image')" prop="image" :error="error">
    <Upload :before-upload="handleUpload" action accept="image/png, image/jpeg">
      <Button icon="ios-cloud-upload-outline">{{ $t('select_image_to_upload') }}</Button>
    </Upload>
    <div v-if="image && image.name">
      {{ $t('selected_file') }}: {{ image.name }}
      <Button type="error" size="small" icon="ios-trash" @click="clearImage" style="margin-bottom:3px;">
        {{ $t('clear_selection') }}
      </Button>
    </div>
  </FormItem>
</template>

<script>
export default {
  props: ['error', 'label'],
  data() {
    return { image: null };
  },
  methods: {
    handleUpload(image) {
      this.image = image;
      this.$emit('upload', image);
      return false;
    },
    clearImage() {
      this.image = null;
      this.$emit('clear');
    },
  },
};
</script>
