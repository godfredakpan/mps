<template>
  <div class="text-center">
    <Button type="primary" size="small" @click="approve()">
      <Icon type="ios-checkmark-circle" />
    </Button>
  </div>
</template>

<script>
export default {
  props: {
    expense: {
      required: true,
    },
    updateFn: {
      required: true,
    },
  },
  methods: {
    approve() {
      this.$Modal.confirm({
        title: this.$t('approve_x', { x: this.$tc('expense') }) + ' (' + this.expense.reference + ')',
        content: this.$t('approve_text') + '<br><br><strong>' + this.$t('r_u_sure') + '</strong>',
        okText: this.$t('yes'),
        cancelText: this.$t('cancel'),
        loading: true,
        onOk: () => {
          // setTimeout(() => this.$Modal.remove(), 2000);
          this.$http
            .post(`/app/expenses/${this.expense.id}/approve`)
            .then(res => {
              if (res.data.success) {
                this.updateFn();
                this.$Notice.success({ title: this.$t('success'), desc: res.data.message || this.$t('expense_approved_text') });
              } else {
                this.$Notice.error({ title: this.$t('failed'), desc: res.data.message || this.$t('no_permissions'), duration: 10 });
              }
            })
            .finally(() => this.$Modal.remove());
        },
      });
    },
  },
};
</script>
