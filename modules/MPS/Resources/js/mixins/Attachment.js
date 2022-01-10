import AttachmentsComponent from '@mpscom/helpers/AttachmentsComponent';
import ListAttachmentsComponent from '@mpscom/helpers/ListAttachmentsComponent';

export default {
  components: {
    AttachmentsComponent,
    ListAttachmentsComponent,
  },
  data() {
    return {
      attachments: null,
      form: { attachments: [] },
    };
  },
  methods: {
    handleUpload(attachments) {
      this.form.attachments.push(attachments);
    },
    clearAttachments() {
      this.form.attachments = [];
      this.form.new_attachments = [];
    },
    deleteAttachment(attachment) {
      this.attachments = this.attachments.filter(a => a.uuid !== attachment.uuid);
    },
  },
};
