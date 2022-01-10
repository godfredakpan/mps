<template>
  <FormItem :label="$tc('attachment', 2)" prop="attachments" :error="error">
    <Upload action multiple type="drag" :max-size="2048" ref="attachments" :before-upload="handleUpload" accept=".png, .jpeg, .jpg, .gif">
      <div style="height:60px;display:flex;align-items:center;justify-content:space-between;padding:0 16px;line-height:1.5;">
        <div style="display:flex;align-items:center;justify-content:center;">
          <Icon type="ios-cloud-upload" size="32" style="color:#3399ff; margin-right:8px;"></Icon>
          <p>
            {{ $t('click_or_drag_files') }}
          </p>
        </div>
        <div v-if="uploadList && uploadList.length" style="margin-left:8px;">
          {{ $t('x_files_selected', { x: uploadList.length }) }}
        </div>
      </div>
    </Upload>
    <div v-if="uploadList && uploadList.length">
      <div style="display:flex;align-items:center;flex-wrap:wrap;">
        <div style="display:inline-block;padding-right:8px;line-height:1.8;" v-for="(a, i) in uploadList" :key="'attachment_' + i">
          <strong>{{ i + 1 }}.</strong> {{ a.name }}
        </div>
      </div>
      <Button type="error" size="small" icon="ios-trash" @click="clearAttachments" style="margin-bottom:3px;">
        {{ $t('clear_x', { x: $tc('file', uploadList.length) }) }}
      </Button>
    </div>
    <slot></slot>
  </FormItem>
</template>

<script>
export default {
  props: ['error'],
  data() {
    return {
      uploadList: [],
      rules: {},
    };
  },
  methods: {
    handleUpload(attachments) {
      this.uploadList.push(attachments);
      this.$emit('selected', attachments);
      return false;
    },
    clearAttachments() {
      this.$emit('clear');
      this.uploadList = [];
    },
    removeAttachment(attachment) {
      const fileList = this.$refs.attachments.fileList;
      this.$refs.attachments.fileList.splice(fileList.indexOf(attachment), 1);
    },
  },
};
</script>
