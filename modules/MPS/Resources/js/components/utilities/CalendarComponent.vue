<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ $t('calendar') }}</p>
      <Button type="text" size="small" @click="newEventModal = true" slot="extra">
        <Icon type="ios-add-circle-outline" size="16" />
        {{ $t('add_x', { x: $tc('event') }) }}
      </Button>
      <div class="calendar">
        <DatePicker
          type="date"
          :open="true"
          :value="value"
          :transfer="false"
          :clearable="false"
          @on-change="handleChange"
          @on-open-change="handleClick"
        >
          <a href="javascript:void(0)" style="width:0;height:0;padding:0;margin:0;"></a>
        </DatePicker>
        <transition name="event">
          <div class="events" v-if="event">
            <!-- <div class="event">
            <Button shape="circle" icon="md-close" style="top:10px;right:10px;position:absolute;" @click="event = false"></Button>
          </div> -->
            <Card dis-hover style="height:100%">
              <p slot="title">
                <!-- <Icon type="ios-calendar" size="16" /> -->
                {{ $t('events_on', { x: formatedDate }) }}
              </p>
              <a slot="extra" @click="event = false">
                <Icon type="ios-close-circle" size="20" color="#495060"></Icon>
              </a>
              <div class="day-events">
                <Card dis-hover style="margin-right:16px;margin-bottom:16px;" v-for="(event, ei) of dateEvent" :key="'event_' + ei">
                  <div slot="extra" style="min-width:60px;position:absolute;top:-5px;right:0;text-align:right">
                    <a
                      @click="
                        form = event;
                        newEventModal = true;
                      "
                    >
                      <Icon type="ios-create" size="20" color="#495060"></Icon>
                    </a>
                    <span style="margin-left:8px;position:relative;">
                      <span
                        v-if="delete_id && delete_id == event.id"
                        style="color:red;position:absolute;width:150px;top:-5px;right:0;display:flex;align-items:center;"
                      >
                        <span style="margin-right:5px;">{{ $t('deleting_in', { x: deleting }) }}</span>
                        <Button type="error" size="small" class="red-bg" @click="resetDelete()">{{ $t('cancel') }}</Button>
                      </span>
                      <a v-else @click="deleteEvent(ei, event.id)">
                        <Icon type="ios-trash" size="20" color="#495060"></Icon>
                      </a>
                    </span>
                  </div>
                  <div class="event">
                    <h3>{{ event.title }}</h3>
                    <p v-if="event.details">{{ event.details }}</p>
                    <p v-else style="margin-bottom:-16px;"></p>
                    <div class="time">
                      <span v-if="event.start_date">
                        {{ $t('start_date') }}: <strong>{{ event.start_date | date }}</strong>
                      </span>
                      <span v-if="event.start_time">
                        {{ $t('start_time') }}: <strong>{{ event.start_time }}</strong>
                      </span>
                      <span v-if="event.end_date">
                        {{ $t('end_date') }}: <strong>{{ event.end_date | date }}</strong>
                      </span>
                      <span v-if="event.end_time">
                        {{ $t('end_time') }}: <strong>{{ event.end_time }}</strong>
                      </span>
                    </div>
                  </div>
                </Card>
              </div>
            </Card>
            <!-- <Loading v-if="loading" /> -->
          </div>
        </transition>
      </div>
    </Card>
    <Modal footer-hide v-model="newEventModal">
      <p slot="header">
        <span v-if="form.id">
          {{ $t('edit_x', { x: $tc('event') }) }}
        </span>
        <span v-else>
          {{ $t('add_x', { x: $tc('event') }) }}
        </span>
      </p>
      <a slot="close" @click="closeModal()" style="margin-top:6px;display:inline-block;width:2.5em;height:2.5em;">
        <Icon type="md-close" size="20" />
      </a>

      <!-- <Loading v-if="loading" /> -->
      <Form ref="newEvent" :model="form" :rules="rules" label-position="top" class="form-responsive normal">
        <Row :gutter="16">
          <Col :sm="24" :md="24" :lg="24">
            <FormItem class="mb26" :label="$t('title')" prop="title" :error="errors.form.title | a2s">
              <Input v-model="form.title" />
            </FormItem>
          </Col>
          <Col :sm="24" :md="12" :lg="12">
            <FormItem class="mb26" :label="$t('start_date')" prop="start_date" :error="errors.form.start_date | a2s">
              <DatePicker type="date" v-model="form.start_date" format="yyyy-MM-dd" style="width: 100%;" />
            </FormItem>
          </Col>
          <Col :sm="24" :md="12" :lg="12">
            <FormItem class="mb26" :label="$t('end_date')" :error="errors.form.end_date | a2s">
              <DatePicker type="date" v-model="form.end_date" format="yyyy-MM-dd" style="width: 100%;" />
            </FormItem>
          </Col>
          <Col :sm="24" :md="12" :lg="12">
            <FormItem class="mb26" :label="$t('start_time')" prop="start_time" :error="errors.form.start_time | a2s">
              <TimePicker type="time" v-model="form.start_time" format="HH:mm:ss" style="width: 100%;" />
            </FormItem>
          </Col>
          <Col :sm="24" :md="12" :lg="12">
            <FormItem class="mb26" :label="$t('end_time')" :error="errors.form.end_time | a2s">
              <TimePicker type="time" v-model="form.end_time" format="HH:mm:ss" style="width: 100%;" />
            </FormItem>
          </Col>
          <Col :sm="24" :md="24" :lg="24">
            <!-- <FormItem class="mb26" :label="$t('color')" :error="errors.form.color | a2s">
              <ColorPicker v-model="form.color" />
            </FormItem> -->
            <FormItem class="mb26" :label="$t('details')">
              <Input type="textarea" v-model="form.details" :autosize="{ minRows: 2, maxRows: 5 }"></Input>
            </FormItem>
            <FormItem class="mb0">
              <Button type="primary" :loading="saving" :disabled="saving" @click="addEvent()">
                <span v-if="!saving">{{ $t('submit') }}</span>
                <span v-else>{{ $t('saving') }}...</span>
              </Button>
              <Button style="margin-left: 8px" @click="closeModal()">Cancel</Button>
            </FormItem>
          </Col>
        </Row>
      </Form>
    </Modal>
  </div>
</template>
<script>
export default {
  data() {
    return {
      value: '',
      deleting: 0,
      month: null,
      event: false,
      saving: false,
      loading: false,
      interval: null,
      delete_id: null,
      newEventModal: false,
      errors: { message: '', form: {} },
      // open: false,
      // translatedDays: ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'].map(item => {
      //   return this.$t('i.datepicker.weeks.' + item);
      // }),
      translatedMonths: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12].map(m => {
        return this.$t('i.datepicker.month' + parseInt(m));
      }),
      events: {},
      dateEvent: [],
      selected_date: null,
      form: {
        title: '',
        details: '',
        start_date: new Date(),
        end_date: null,
        start_time: new Date(),
        end_time: null,
        color: '#FFFFFF',
      },
      rules: {
        title: [{ required: true, message: this.$t('field_is_required', { field: this.$t('title') }), trigger: 'blur' }],
        start_date: [
          {
            type: 'date',
            required: true,
            trigger: 'change',
            message: this.$t('field_is_required', { field: this.$t('start_date') }),
          },
        ],
      },
    };
  },
  computed: {
    formatedDate() {
      return this.$options.filters.date(this.selected_date);
    },
  },
  mounted() {
    this.setEvents(null, true);
    const cbox = document.querySelectorAll('.ivu-picker-panel-icon-btn');
    for (let i = 0; i < cbox.length; i++) {
      cbox[i].addEventListener('click', this.handleClick);
    }
    document.querySelectorAll('.ivu-date-picker-header-label')[0].addEventListener('click', () => {
      let mbox = document.querySelector('.ivu-date-picker-cells-month').childNodes;
      for (let i = 0; i < mbox.length; i++) {
        mbox[i].addEventListener('click', this.handleClick);
      }
    });
    document.querySelectorAll('.ivu-date-picker-header-label')[1].addEventListener('click', () => {
      let ybox = document.querySelector('.ivu-date-picker-cells-year').childNodes;
      for (let i = 0; i < ybox.length; i++) {
        ybox[i].addEventListener('click', () => {
          let mbox = document.querySelector('.ivu-date-picker-cells-month').childNodes;
          for (let i = 0; i < mbox.length; i++) {
            mbox[i].addEventListener('click', this.handleClick);
          }
        });
      }
    });
  },
  methods: {
    handleClick(e) {
      if (e) {
        let monthN = null;
        let month = document.querySelectorAll('.ivu-date-picker-header-label')[0].outerText;
        let year = document.querySelectorAll('.ivu-date-picker-header-label')[1].outerText;
        for (let i = 1; i <= 12; i++) {
          if (this.$t('i.datepicker.month' + parseInt(i)) == month) {
            monthN = i > 9 ? i : '0' + i;
          }
        }
        let m = year + '-' + monthN;
        // let m = this.$moment(year + '-' + month).format('YYYY-MM');
        // console.log(m, monthN, month, year);
        this.setEvents(m);
      }
    },
    setEvents(month, force) {
      if (month != this.month || force) {
        if (!month) {
          month = this.$moment().format('YYYY-MM');
        }
        this.$http
          .get('app/events?month=' + month)
          .then(res => (this.events = res.data))
          .then(() => {
            this.loading = false;
            const indexes = [];
            for (const [key, value] of Object.entries(this.events)) {
              indexes.push(parseInt(key.substring(key.length - 2, key.length)) - 1);
            }
            let monthDays = [
              ...document.querySelectorAll(
                '.ivu-date-picker-cells-cell:not(.ivu-date-picker-cells-cell-prev-month):not(.ivu-date-picker-cells-cell-next-month)'
              ),
            ].map((el, i) => {
              if (indexes.includes(i)) {
                let ispan = document.createElement('span');
                ispan.style.position = 'absolute';
                // ispan.innerHTML = '<i class="ivu-icon ivu-icon-ios-checkmark-circle-outline" style="font-size: 16px;"></i>';
                ispan.innerHTML = '<div class="got-event"></div>';
                el.firstChild.append(ispan);
              }
            });
          });
      }
    },
    handleChange(date) {
      this.selected_date = date;
      this.dateEvent = this.events[date];
      if (this.dateEvent) {
        this.event = true;
      }
    },
    closeModal() {
      this.newEventModal = false;
      this.form = {
        title: '',
        details: '',
        start_date: this.$moment().format('YYYY-MM-DD'),
        end_date: null,
        start_time: new Date(),
        end_time: null,
        color: '#FFFFFF',
      };
    },
    addEvent() {
      this.$refs.newEvent.validate(valid => {
        if (valid) {
          this.saving = true;
          // this.loading = true;
          let msg = 'added';
          let method = 'post';
          let url = 'app/events';
          this.errors.message = '';
          let msg_text = 'record_added';
          if (this.form.id) {
            method = 'put';
            msg = 'updated';
            msg_text = 'record_updated';
            url = url + '/' + this.form.id;
          }
          let data = { ...this.form };
          if (data.start_date) {
            data.start_date = this.$moment(data.start_date).format(this.$moment.HTML5_FMT.DATE);
          }
          if (data.end_date) {
            data.end_date = this.$moment(data.end_date).format(this.$moment.HTML5_FMT.DATE);
          }
          this.$http[method](url, data)
            .then(res => {
              if (res.data.success) {
                this.newEventModal = false;
                this.setEvents(this.month, true);
                this.$Notice.destroy();
                this.$Notice.success({ title: this.$tc('event') + ' ' + this.$t(msg), desc: this.$t(msg_text) });
                this.form = {
                  title: '',
                  details: '',
                  start_date: this.$moment().format('YYYY-MM-DD'),
                  end_date: null,
                  start_time: new Date(),
                  end_time: null,
                  color: '#FFFFFF',
                };
              } else {
                this.$Notice.error({ title: this.$t('failed'), desc: this.$t('failed_error_text') });
              }
            })
            .catch(error => (this.errors = error))
            .finally(() => {
              this.event = false;
              this.saving = false;
              this.loading = false;
            });
        } else {
          this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
        }
      });
    },
    deleteEvent(eId, delete_id) {
      this.delete_id = delete_id;
      this.deleting = 5;
      this.interval = setInterval(() => {
        this.deleting--;
        if (!this.deleting) {
          this.$http
            .delete('app/events/' + delete_id)
            .then(res => {
              if (res.data.success) {
                this.events[this.selected_date].splice(eId, 1);
                this.$Notice.success({ title: this.$t('deleted'), desc: this.$t('record_deleted') });
              }
            })
            .catch(error => this.$Notice.error({ title: this.$t('failed'), desc: this.$t('failed_error_text') }))
            .finally(() => {
              this.delete_id = null;
              this.resetDelete();
            });
        }
      }, 500);
    },
    resetDelete() {
      this.deleting = 0;
      this.delete_id = null;
      clearInterval(this.interval);
    },
  },
};
</script>

<style lang="scss">
.calendar {
  margin: 0;
  padding: 0;
  position: relative;
  border-radius: 4px;
  box-sizing: content-box;
  border: 1px solid #e8eaec;
  transition: all 5s ease-in-out;

  .events {
    top: 0;
    width: 100%;
    height: 100%;
    color: #fff;
    border-radius: 4px;
    position: absolute;
    transition: all 0.25s ease-in-out;
    // animation: circleAnimation 0.25s ease-in forwards;
  }
  .event-enter-active {
    animation: circleAnimation 0.25s ease-in forwards;
  }
  .event-leave-active {
    animation: circleAnimation 0.25s ease-out reverse;
  }
  @keyframes circleAnimation {
    0% {
      clip-path: circle(0% at 50% 50%);
    }
    100% {
      clip-path: circle(100%);
    }
  }
  .got-event {
    top: 10px;
    left: 5px;
    width: 6px;
    height: 6px;
    position: absolute;
    border-radius: 3px;
    background: #19be6b;
  }
  .day-events {
    width: 100%;
    overflow: hidden;
    overflow-y: auto;
    min-height: 315px;
    padding-bottom: 16px;
    height: calc((100vh / 11.5) * 6.5) !important;
    // box-shadow: inset 0 -10px 5px -10px #495060;
  }
  .event {
    color: #495060;
    h3 {
      margin: -16px;
      padding: 8px 16px;
      margin-bottom: 8px;
      border-bottom: 1px solid #e8eaec;
    }
    p {
      text-align: justify;
      margin-bottom: -8px;
    }
    .time {
      display: flex;
      margin: -16px;
      margin-top: 16px;
      align-items: center;
      border-top: 1px solid #e8eaec;
      span {
        padding: 8px 16px;
        display: inline-block;
      }
      span:not(:first-child) {
        border-left: 1px solid #e8eaec;
      }
      @media only screen and (max-width: 767px) {
        display: block;
        span:not(:first-child) {
          border-left: none;
        }
      }
    }
  }
  .ivu-date-picker {
    width: 100%;
  }
  .ivu-select-dropdown {
    width: 100%;
    display: block;
    box-sizing: content-box;
    position: static !important;
    box-shadow: none !important;
  }
  .ivu-date-picker-header {
    // display: flex;
    padding-bottom: 6px;
    box-sizing: content-box;
  }
  .ivu-date-picker-header,
  .ivu-picker-panel-icon-btn {
    height: 48px;
    line-height: 48px;
  }
  .ivu-picker-panel-icon-btn {
    width: 48px;
  }
  .ivu-date-picker-cells,
  .ivu-date-picker-cells-header,
  .ivu-picker-panel-content,
  .ivu-picker-panel-body {
    margin: 0;
    width: 100%;
  }
  .ivu-date-picker-cells-header span {
    margin: 0;
    color: #495060;
    font-weight: bold;
    height: 36px !important;
    line-height: 36px !important;
  }
  .ivu-date-picker-cells-header {
    display: flex;
    background: #f5f5f5;
    height: 36px !important;
    line-height: 36px !important;
  }
  .ivu-date-picker-cells span {
    width: 14.2857%;
  }
  .ivu-date-picker-cells.ivu-date-picker-cells-year span,
  .ivu-date-picker-cells.ivu-date-picker-cells-month span {
    width: 33.333%;
  }
  .ivu-date-picker-cells-cell {
    margin: 0;
    padding: 0;
    text-align: center;
    border-top: 1px solid #e8eaec;
  }
  .ivu-date-picker-cells-cell-today:not(.ivu-date-picker-cells-focused) em {
    border: 0;
    background: #f5f5f5;
    // border: 1px solid #e8eaec;
  }
  .ivu-date-picker-cells-cell-today em::after {
    display: none;
  }
  span.ivu-date-picker-cells-cell {
    padding: 1px;
    padding-top: 2px;
    position: relative;
    box-sizing: border-box;
    height: 49px !important;
  }
  .ivu-date-picker-header-label {
    font-size: 16px;
    font-weight: bold;
  }
  .ivu-date-picker-cells span {
    display: inline-block;
    min-height: 50px !important;
    height: calc(100vh / 11) !important;
    // line-height: calc(100vh / 12) !important;
  }
  .ivu-date-picker-cells span em {
    margin: -1px 0 2px 0;
    display: inline-block;
    width: 100% !important;
    min-height: 47px !important;
    height: calc(100vh / 11.5) !important;
    line-height: calc(100vh / 11.5) !important;
  }
  .events * {
    color: #ffffff !important;
    background: #515a6e !important;
    border-color: #495060 !important;
    // background: #495060 !important;
    // border-color: #3b414e !important;
  }
  .events * .red-text {
    color: red !important;
  }
  .events * .red-bg,
  .events * .red-bg * {
    background: red !important;
    border-color: red !important;
  }
}
</style>
