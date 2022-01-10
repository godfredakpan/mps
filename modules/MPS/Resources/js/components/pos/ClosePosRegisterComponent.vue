<template>
  <div class="order register" v-if="record">
    <List border>
      <ListItem class="table-wrapper mt-0">
        <table class="table">
          <tr>
            <td class="bold bg-gray" style="border-radius: 5px 0 0 0;">{{ $tc('register') }}</td>
            <td class="bold bg-gray" style="border-radius: 0 5px 0 0;">{{ record.register.name }}</td>
          </tr>
          <tr>
            <td>{{ $t('opened_at') }}</td>
            <td class="bold">{{ record.created_at | datetime }}</td>
          </tr>
          <tr>
            <td>{{ $t('total_sales_amount') }}</td>
            <td class="bold" style="border-radius: 0 0 5px 0;">
              {{ record.total_sales_amount | formatNumber($store.state.settings.decimals) }}
            </td>
          </tr>
        </table>
      </ListItem>
    </List>
    <List border class="mt16">
      <ListItem class="table-wrapper mt-0">
        <table class="table">
          <tr>
            <td>{{ $t('cash_in_hand') }}</td>
            <td class="bold text-right">{{ record.cash_in_hand | formatNumber($store.state.settings.decimals) }}</td>
          </tr>
          <tr>
            <td>{{ $t('x_amount', { x: $t('cash') }) }}</td>
            <td class="bold text-right">{{ record.total_cash_amount | formatNumber($store.state.settings.decimals) }}</td>
          </tr>
          <tr>
            <td>{{ $t('x_amount', { x: $t('credit_card') }) }}</td>
            <td class="bold text-right">{{ record.total_cc_slips_amount | formatNumber($store.state.settings.decimals) }}</td>
          </tr>
          <tr>
            <td>{{ $t('x_amount', { x: $t('cheque') }) }}</td>
            <td class="bold text-right">{{ record.total_cheques_amount | formatNumber($store.state.settings.decimals) }}</td>
          </tr>
          <tr>
            <td>{{ $t('x_amount', { x: $t('other') }) }}</td>
            <td class="bold text-right">{{ record.total_other_amount | formatNumber($store.state.settings.decimals) }}</td>
          </tr>
          <tr>
            <td>{{ $t('x_amount', { x: $tc('gift_card') }) }}</td>
            <td class="bold text-right">{{ record.total_gift_card_amount | formatNumber($store.state.settings.decimals) }}</td>
          </tr>
          <tr>
            <td>{{ $t('x_amount', { x: $tc('return_order', 2) }) }}</td>
            <td class="bold text-right">{{ record.total_return_orders_amount | formatNumber($store.state.settings.decimals) }}</td>
          </tr>
          <tr>
            <td>{{ $t('x_amount', { x: $tc('refund') }) }}</td>
            <td class="bold text-right">{{ record.total_refunds_amount | formatNumber($store.state.settings.decimals) }}</td>
          </tr>
          <tr>
            <td>{{ $t('x_amount', { x: $tc('expense', 2) }) }}</td>
            <td class="bold text-right">{{ record.total_expenses_amount | formatNumber($store.state.settings.decimals) }}</td>
          </tr>
        </table>
      </ListItem>
    </List>
    <transition name="slide-fade">
      <div class="mt16" v-if="show">
        <Alert type="error" show-icon class="mb26" v-if="errors.message">
          <div v-html="errors.message"></div>
        </Alert>
        <Form ref="registerRecordForm" :model="recordForm" :rules="formRules" :label-width="140">
          <FormItem :label="$t('total_cash')">
            <Row>
              <Col span="14">
                <FormItem prop="cash" :error="errors.form.total_cash_submitted | a2s">
                  <InputNumber :placeholder="$t('total_cash')" v-model="recordForm.cash"></InputNumber>
                </FormItem>
              </Col>
              <Col span="1" style="text-align: center">&nbsp;</Col>
              <Col span="9">
                <code>=</code>
                <strong>
                  {{ total_cash | formatNumber($store.state.settings.decimals) }}
                </strong>
              </Col>
            </Row>
          </FormItem>
          <FormItem :label="$t('total_slips')">
            <Row>
              <Col span="14">
                <FormItem prop="slips" :error="errors.form.total_cc_slips_submitted | a2s">
                  <InputNumber :placeholder="$t('total_slips')" v-model="recordForm.slips"></InputNumber>
                </FormItem>
              </Col>
              <Col span="1" style="text-align: center">&nbsp;</Col>
              <Col span="9">
                <code>=</code>
                <strong>
                  {{ record.total_cc_slips | formatNumber(0) }}
                </strong>
              </Col>
            </Row>
          </FormItem>
          <FormItem :label="$t('total_cheques')">
            <Row>
              <Col span="14">
                <FormItem prop="cheques" :error="errors.form.total_cheques_submitted | a2s">
                  <InputNumber :placeholder="$t('total_cheques')" v-model="recordForm.cheques"></InputNumber>
                </FormItem>
              </Col>
              <Col span="1" style="text-align: center">&nbsp;</Col>
              <Col span="9">
                <code>=</code>
                <strong>
                  {{ record.total_cheques | formatNumber(0) }}
                </strong>
              </Col>
            </Row>
          </FormItem>
          <FormItem :label="$t('transfer_orders')" :error="errors.form.transferred_to | a2s">
            <Select v-model="recordForm.transferred_to" :placeholder="$t('select_x', { x: $tc('user') })">
              <template v-if="users.length > 0">
                <Option :key="index" :value="option.value" v-for="(option, index) in users">
                  {{ option.label.trim() }}
                </Option>
              </template>
            </Select>
          </FormItem>
          <FormItem :label="$t('comment')" :error="errors.form.comment | a2s">
            <Input type="textarea" :placeholder="$t('comment')" v-model="recordForm.comment" :autosize="{ minRows: 2, maxRows: 5 }"></Input>
          </FormItem>
          <FormItem class="mb0">
            <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit()">{{ $t('submit') }}</Button>
            <!-- <Button @click="handleReset()" style="margin-left: 8px">Reset</Button> -->
            <Button @click="show = false" style="margin-left: 8px">{{ $t('close_x', { x: $tc('form') }) }}</Button>
          </FormItem>
        </Form>
      </div>
      <Button v-else type="dashed" long class="mt16 np" @click="show = true">{{ $t('close_x', { x: $tc('register') }) }}</Button>
    </transition>
  </div>
</template>
<script>
export default {
  props: {
    close: { required: true },
    record: { required: true },
  },
  data() {
    return {
      users: [],
      show: false,
      saving: false,
      loading: false,
      errors: { message: '', form: {} },
      recordForm: {
        comment: '',
        transferred_to: '',
        slips: this.record ? parseFloat(this.formatNumber(this.record.total_cc_slips)) : null,
        cheques: this.record ? parseFloat(this.formatNumber(this.record.total_cheques)) : null,
        cash: this.record
          ? parseFloat(this.formatNumber(parseFloat(this.record.total_cash_amount) + parseFloat(this.record.cash_in_hand)))
          : null,
      },
      formRules: {
        cash: [
          {
            required: true,
            type: 'number',
            trigger: 'blur',
            message: this.$t('field_is_required', { field: this.$t('total_cash') }),
          },
        ],
        slips: [
          {
            required: true,
            type: 'number',
            trigger: 'blur',
            message: this.$t('field_is_required', { field: this.$t('total_slips') }),
          },
        ],
        cheques: [
          {
            required: true,
            type: 'number',
            trigger: 'blur',
            message: this.$t('field_is_required', { field: this.$t('total_cheques') }),
          },
        ],
      },
    };
  },
  computed: {
    total_cash() {
      return this.record
        ? parseFloat(this.record.total_cash_amount) +
            parseFloat(this.record.cash_in_hand) -
            parseFloat(this.record.total_refunds_amount) -
            parseFloat(this.record.total_expenses_amount)
        : 0;
    },
  },
  created() {
    this.$http
      .get('app/users/search?roles=admin,staff')
      .then(res => (this.users = res.data))
      .catch(err => console.log(err));
  },
  methods: {
    handleSubmit() {
      this.$refs.registerRecordForm.validate(valid => {
        if (valid) {
          this.loading = true;
          this.record.comment = this.recordForm.comment;
          this.record.total_cash_submitted = this.recordForm.cash;
          this.record.total_cheques_submitted = this.recordForm.cheques;
          this.record.total_cc_slips_submitted = this.recordForm.slips;
          this.record.transferred_to = this.recordForm.transferred_to;
          this.$http
            .post(`app/pos/register/${this.record.id}/close`, this.record)
            .then(res => {
              if (res.data.success) {
                this.$Notice.destroy();
                this.$Notice.success({ title: this.$t('register_closed_msg') });
                this.close();
              } else {
                this.$Notice.error({ title: this.$t('failed'), desc: this.$t('failed_error_text') });
              }
            })
            .catch(error => (this.errors = error))
            .finally(() => (this.loading = false));
        } else {
          this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
        }
      });
    },
    handleReset() {
      this.$refs.registerRecordForm.resetFields();
    },
  },
};
</script>

<style>
.register {
  width: auto !important;
  min-width: 100% !important;
}
.register .table-wrapper {
  width: 100%;
  padding: 0 !important;
}
.register .table th,
.register .table td {
  padding: 5px 10px;
  border-bottom: 1px solid #e8eaec;
}
</style>
