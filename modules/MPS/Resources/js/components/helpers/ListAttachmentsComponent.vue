<template>
  <Card dis-hover v-if="attachments && attachments.length">
    <List>
      <ListItem v-for="(attachment, index) in attachments" :key="attachment.uuid" style="word-break: break-all;">
        <ListItemMeta :avatar="attachment.url" :title="attachment.title" :description="attachment.filename" />
        <template slot="action">
          <ButtonGroup size="small">
            <Button size="small" type="primary" @click="openAttachment(attachment.url)">
              <Icon type="ios-download" />
              <!-- {{ $t('download') }} -->
            </Button>
            <Button size="small" type="error" :loading="deleting === index" @click="deleteAttachment(attachment, index)">
              <Icon type="ios-trash" />
              <!-- {{ $t('delete') }} -->
            </Button>
          </ButtonGroup>
        </template>
      </ListItem>
    </List>
  </Card>
</template>

<script>
export default {
  props: {
    attachments: {
      type: Array,
    },
  },
  data() {
    return {
      deleting: null,
    };
  },
  methods: {
    openAttachment(url) {
      Object.assign(document.createElement('a'), { target: '_blank', href: url }).click();
    },
    deleteAttachment(attachment, index) {
      this.deleting = index;
      this.$http
        .delete(`app/attachments/${attachment.id}`)
        .then(res => this.$emit('remove', attachment))
        .catch(err => console.log(err))
        .finally(() => (this.deleting = null));
    },
  },
};
</script>
