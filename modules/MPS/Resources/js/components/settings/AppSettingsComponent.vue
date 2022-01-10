<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ $t('app_settings') }}</p>
      <div slot="extra">
        <a href="/modules" target="_blank">Manage Modules</a>
        <a href="#" slot="extra">
          <Dropdown style="margin: 0 -5px 0 20px;" placement="bottom-end" @on-click="onSelect">
            <a href="javascript:void(0)" style="padding-right: 10px;">
              <Icon type="md-menu" size="18"></Icon>
            </a>
            <DropdownMenu slot="list">
              <DropdownItem name="mail_settings">{{ $t('mail_settings') }}</DropdownItem>
              <DropdownItem name="scale_barcode">{{ $t('scale_barcode') }}</DropdownItem>
              <DropdownItem name="payment_settings">{{ $t('payment_settings') }}</DropdownItem>
              <template v-if="settings_routes.length > 0">
                <template v-for="route in settings_routes">
                  <template v-if="!route.meta.hideInMenu && route.path != '/settings'">
                    <DropdownItem :name="route.name" :key="route.name" :divided="route.meta.divided">
                      {{ route.meta.title }}
                    </DropdownItem>
                  </template>
                </template>
              </template>
            </DropdownMenu>
          </Dropdown>
        </a>
      </div>
      <div>
        <p style="margin-bottom: 20px;">{{ $t('settings_heading_text') }}</p>
        <Form ref="settings" :model="settings" :rules="rules" :label-width="150" class="form-responsive">
          <Row :gutter="16">
            <Col :sm="24" :md="24" :lg="24">
              <Loading v-if="loading" />
              <Row :gutter="16">
                <Col :sm="24" :md="12" :lg="12">
                  <FormItem :label="$t('app_name')" prop="name">
                    <Input v-model="settings.name" />
                  </FormItem>
                  <FormItem :label="$t('short_name')" prop="short_name">
                    <Input v-model="settings.short_name" />
                  </FormItem>
                  <FormItem :label="$tc('company')" prop="company">
                    <Input v-model="settings.company" />
                  </FormItem>
                  <FormItem :label="$t('reg_number')" prop="number">
                    <Input v-model="settings.number" />
                  </FormItem>
                  <FormItem :label="$t('default_x', { x: $tc('account') })" prop="default_account">
                    <Select v-model="settings.default_account">
                      <Option v-for="item in accounts" :value="item.value" :key="item.value">{{ item.label }}</Option>
                    </Select>
                  </FormItem>
                  <FormItem :label="$t('default_x', { x: $tc('category') })" prop="default_category">
                    <Select v-model="settings.default_category">
                      <Option v-for="item in categories" :value="item.value" :key="item.value">{{ item.label }}</Option>
                    </Select>
                  </FormItem>
                  <FormItem :label="$t('default_x', { x: $tc('customer') })" prop="default_customer">
                    <Select v-model="settings.default_customer">
                      <Option v-for="item in customers" :value="item.value" :key="item.value">{{ item.label }}</Option>
                    </Select>
                  </FormItem>
                  <FormItem :label="$t('timezone')" prop="timezone">
                    <!-- <Input v-model="settings.timezone" /> -->
                    <Select
                      clearable
                      filterable
                      style="width: 100%;"
                      v-model="settings.timezone"
                      :placeholder="$t('select_x', { x: $t('timezone') })"
                    >
                      <Option v-for="tm in timezones" :value="tm" :key="tm">{{ tm }}</Option>
                    </Select>
                  </FormItem>
                  <FormItem :label="$t('language')" prop="language">
                    <Select v-model="settings.language">
                      <Option v-for="lang in $store.state.languages" :value="lang" :key="lang">{{ lang | regionName }} ({{ lang }})</Option>
                    </Select>
                  </FormItem>
                  <FormItem :label="$tc('user_locale')" prop="user_locale">
                    <Input v-model="settings.user_locale" />
                  </FormItem>
                  <FormItem :label="$t('inventory_accounting')" prop="inventory_accounting">
                    <Select v-model="settings.inventory_accounting">
                      <Option value="FIFO">{{ $t('FIFO') }}</Option>
                      <Option value="LIFO">{{ $t('LIFO') }}</Option>
                      <Option value="AVCO">{{ $t('AVCO') }}</Option>
                      <Option value="ITEX">{{ $t('ITEX') }}</Option>
                    </Select>
                  </FormItem>
                  <FormItem :label="$t('max_discount')" prop="max_discount">
                    <InputNumber
                      v-model="settings.max_discount"
                      :formatter="value => `${value}%`"
                      :parser="value => value.replace('%', '')"
                    />
                    <!-- <small class="warning">{{ $t('max_discount_settings_tip') }}</small> -->
                  </FormItem>
                  <FormItem :label="$t('search_delay')" prop="search_delay">
                    <InputNumber v-model="settings.search_delay" />
                  </FormItem>
                </Col>
                <Col :sm="24" :md="12" :lg="12">
                  <FormItem :label="$t('email')" prop="email">
                    <Input v-model="settings.email" />
                  </FormItem>
                  <FormItem :label="$t('phone')" prop="phone">
                    <Input v-model="settings.phone" />
                  </FormItem>
                  <FormItem :label="$t('address')" prop="address">
                    <Input type="textarea" v-model="settings.address" :autosize="{ minRows: 2, maxRows: 5 }" />
                  </FormItem>
                  <FormItem :label="$tc('country')" prop="country">
                    <Select v-model="settings.country" placeholder filterable @on-change="countryChange">
                      <Option v-for="(option, index) in countries" :value="option.value" :key="index" v-html="option.label"></Option>
                    </Select>
                  </FormItem>
                  <FormItem :label="$tc('state')" prop="state">
                    <Select v-model="settings.state" placeholder filterable :loading="searching">
                      <Option v-for="(option, index) in states" :value="option.value" :key="index" v-html="option.label"></Option>
                    </Select>
                  </FormItem>
                  <FormItem :label="$tc('reference')" prop="reference">
                    <Select v-model="settings.reference" placeholder :loading="searching">
                      <Option value="yearly">{{ $t('employee_number') + '/' + $t('year') + '/' + $t('sequence_number') }}</Option>
                      <Option value="monthly" selected="selected">{{
                        $t('employee_number') + '/' + $t('year') + '/' + $t('month') + '/' + $t('sequence_number')
                      }}</Option>
                      <Option value="sequence">{{ $t('employee_number') + '/' + $t('sequence_number') }}</Option>
                      <Option value="random">{{ $t('employee_number') + '/' + $t('random_number') }}</Option>
                    </Select>
                  </FormItem>
                  <FormItem :label="$t('overselling')" prop="overselling">
                    <Select v-model="settings.overselling">
                      <Option value="0">{{ $t('disable') }}</Option>
                      <Option value="1">{{ $t('enable') }}</Option>
                    </Select>
                  </FormItem>
                  <FormItem :label="$t('receipt_header')" prop="header">
                    <Input type="textarea" v-model="settings.header" :autosize="{ minRows: 3, maxRows: 5 }" />
                  </FormItem>
                  <FormItem :label="$t('receipt_footer')" prop="footer">
                    <Input type="textarea" v-model="settings.footer" :autosize="{ minRows: 3, maxRows: 5 }" />
                  </FormItem>
                </Col>
              </Row>
              <FormItem :label="$t('loyalty_points')">
                <Card dis-hover style="overflow-x:auto;">
                  <div style="min-width:700px;">
                    <p class="bold mb8">{{ $t('for_x', { x: $tc('customer', 2) }) }}</p>
                    <div class="flex-gap">
                      <span>{{ $t('each_spent') }}</span>
                      <InputNumber v-model="settings.loyalty.customer.spent" />
                      <span>{{ $t('get_points') }}</span>
                      <InputNumber v-model="settings.loyalty.customer.points" />
                      <span>{{ $t('points') }}</span>
                    </div>
                    <Divider dashed />
                    <p class="bold mb8">{{ $t('for_x', { x: $t('staff') }) }}</p>
                    <div class="flex-gap">
                      <span>{{ $t('each_spent') }}</span>
                      <InputNumber v-model="settings.loyalty.staff.spent" />
                      <span>{{ $t('get_points') }}</span>
                      <InputNumber v-model="settings.loyalty.staff.points" />
                      <span>{{ $t('points') }}</span>
                    </div>
                  </div>
                </Card>
              </FormItem>
              <FormItem :label="$t('quick_cash')" prop="quick_cash">
                <CheckboxGroup v-model="settings.quick_cash">
                  <Checkbox label="10"></Checkbox>
                  <Checkbox label="20"></Checkbox>
                  <Checkbox label="50"></Checkbox>
                  <Checkbox label="100"></Checkbox>
                  <Checkbox label="500"></Checkbox>
                  <Checkbox label="1000"></Checkbox>
                  <Checkbox label="5000"></Checkbox>
                  <Checkbox label="10000"></Checkbox>
                  <Checkbox label="20000"></Checkbox>
                  <Checkbox label="50000"></Checkbox>
                  <Checkbox label="100000"></Checkbox>
                </CheckboxGroup>
              </FormItem>
              <FormItem :label="$t('dimension_unit')" prop="dimension_unit" class="mb0">
                <RadioGroup v-model="settings.dimension_unit">
                  <Radio label="mm">Millimeter</Radio>
                  <Radio label="cm">Centimeter</Radio>
                  <Radio label="in">Inch</Radio>
                  <Radio label="foot">Foot</Radio>
                  <Radio label="yd">Yard</Radio>
                  <Radio label="m">Meter</Radio>
                </RadioGroup>
              </FormItem>
              <FormItem :label="$t('weight_unit')" prop="weight_unit" class="mb0">
                <RadioGroup v-model="settings.weight_unit">
                  <Radio label="g">Gram</Radio>
                  <Radio label="kg">Kilogram</Radio>
                  <Radio label="oz">Ounce</Radio>
                  <Radio label="lb">Pound</Radio>
                  <Radio label="t">Tonne</Radio>
                </RadioGroup>
              </FormItem>
              <FormItem :label="$t('theme')" prop="theme" class="mb0">
                <RadioGroup v-model="settings.theme">
                  <Radio label="dark">{{ $t('default') }}</Radio>
                  <Radio label="light">{{ $t('light') }}</Radio>
                  <Radio label="primary">{{ $t('primary') }}</Radio>
                  <Checkbox v-model="settings.fixed_layout" true-value="1" false-value="0">
                    <span>{{ $t('fixed_layout') }}</span>
                  </Checkbox>
                </RadioGroup>
              </FormItem>
              <FormItem :label="$t('loader')" prop="loader" class="mb0">
                <RadioGroup v-model="settings.loader">
                  <Radio label="circle">{{ $t('rotating_circle') }}</Radio>
                  <Radio label="dot">{{ $t('blinking_dot') }}</Radio>
                </RadioGroup>
              </FormItem>
              <FormItem :label="$t('confirmation')" prop="confirmation" class="mb0">
                <RadioGroup v-model="settings.confirmation">
                  <Radio label="modal">{{ $t('yes') }}</Radio>
                  <!-- <Radio label="poptip">{{ $t('poptip') }}</Radio> -->
                  <Radio label>{{ $t('no') }} ({{ $t('not_recommended') }})</Radio>
                </RadioGroup>
              </FormItem>
              <FormItem :label="$t('decimals')" prop="decimals" class="mb0">
                <RadioGroup v-model="settings.decimals">
                  <Radio label="0">0</Radio>
                  <Radio label="1">1</Radio>
                  <Radio label="2">2</Radio>
                  <Radio label="3">3</Radio>
                  <Radio label="4">4</Radio>
                </RadioGroup>
              </FormItem>
              <FormItem :label="$t('quantity_decimals')" prop="quantity_decimals" class="mb0">
                <RadioGroup v-model="settings.quantity_decimals">
                  <Radio label="0">0</Radio>
                  <Radio label="1">1</Radio>
                  <Radio label="2">2</Radio>
                  <Radio label="3">3</Radio>
                  <Radio label="4">4</Radio>
                </RadioGroup>
              </FormItem>
              <FormItem :label="$t('initial_table_rows')" prop="rows" class="mb0">
                <RadioGroup v-model="settings.rows">
                  <Radio label="10">10</Radio>
                  <Radio label="25">25</Radio>
                  <Radio label="50">50</Radio>
                  <Radio label="100">100</Radio>
                </RadioGroup>
              </FormItem>
              <FormItem prop="hideId" class="mb0">
                <Checkbox v-model="settings.hide_id" true-value="1" false-value="0">
                  <span>{{ $t('hide_id') }}</span>
                </Checkbox>
              </FormItem>
              <FormItem prop="stock" class="mb0">
                <Checkbox v-model="settings.stock" true-value="1" false-value="0">
                  <span>{{ $t('track_stock') }}</span>
                </Checkbox>
              </FormItem>
              <FormItem prop="impersonation" class="mb0">
                <Checkbox v-model="settings.impersonation" true-value="1" false-value="0">
                  <span>{{ $t('x_impersonation', { x: $t('enable') }) }}</span>
                </Checkbox>
              </FormItem>
              <FormItem prop="play_sound" class="mb0">
                <Checkbox v-model="settings.play_sound" true-value="1" false-value="0">
                  <span>{{ $t('play_sound_on_pos') }}</span>
                </Checkbox>
              </FormItem>
              <FormItem prop="onscreen_keyboard" class="mb0">
                <Checkbox v-model="settings.onscreen_keyboard" true-value="1" false-value="0">
                  <span>{{ $t('x_onscreen_keyboard', { x: $t('enable') }) }}</span>
                </Checkbox>
              </FormItem>
              <FormItem prop="pos_server" class="mb0">
                <Checkbox v-model="settings.pos_server" true-value="1" false-value="0">
                  <span>{{ $t('pos_server_text') }}</span>
                </Checkbox>
              </FormItem>
              <FormItem prop="auto_open_order" class="mb0">
                <Checkbox v-model="settings.auto_open_order" true-value="1" false-value="0">
                  <span>{{ $t('auto_open_order_text') }}</span>
                </Checkbox>
              </FormItem>
              <FormItem prop="print_dialog" class="mb0">
                <Checkbox v-model="settings.print_dialog" true-value="1" false-value="0">
                  <span>{{ $t('auto_open_print_dialog_on_pos') }}</span>
                </Checkbox>
              </FormItem>
              <FormItem prop="categories_sidebar" class="mb0">
                <Checkbox v-model="settings.categories_sidebar" true-value="1" false-value="0">
                  <span>{{ $t('categories_sidebar_initial') }}</span>
                </Checkbox>
              </FormItem>
              <FormItem prop="restaurant" class="mb0">
                <Checkbox v-model="settings.restaurant" true-value="1" false-value="0">
                  <span>{{ $t('pos_for_restaurant') }}</span>
                </Checkbox>
              </FormItem>
              <FormItem prop="requireCountry">
                <Checkbox v-model="settings.require_country" true-value="1" false-value="0">
                  <span>{{ $t('require_country') }}</span>
                  (<span>{{ $t('require_country_tip') }}</span
                  >)
                </Checkbox>
              </FormItem>
              <Divider dashed orientation="left">
                <small style="color: #aaa;">
                  {{ $t('order_settings') }}
                </small>
              </Divider>
              <FormItem prop="showImage" class="mb0">
                <Checkbox v-model="settings.show_image" true-value="1" false-value="0">
                  <span>{{ $t('show_item_image') }}</span>
                </Checkbox>
              </FormItem>
              <FormItem prop="showTax" class="mb0">
                <Checkbox v-model="settings.show_tax" true-value="1" false-value="0">
                  <span>{{ $t('show_tax') }}</span>
                </Checkbox>
              </FormItem>
              <FormItem prop="showTaxSummary" class="mb0">
                <Checkbox v-model="settings.show_tax_summary" true-value="1" false-value="0">
                  <span>{{ $t('show_tax_summary') }}</span>
                </Checkbox>
              </FormItem>
              <FormItem prop="showDiscount">
                <Checkbox v-model="settings.show_discount" true-value="1" false-value="0">
                  <span>{{ $t('show_discount') }}</span>
                </Checkbox>
              </FormItem>
              <Divider dashed orientation="left">
                <small style="color: #aaa;">
                  {{ $t('app_updates') }}
                </small>
              </Divider>
              <FormItem prop="auto_update">
                <Checkbox v-model="settings.auto_update" true-value="1" false-value="0">
                  <span>{{ $t('auto_update_text') }}</span>
                </Checkbox>
              </FormItem>
              <template v-if="settings.auto_update == 1">
                <transition
                  appear
                  name="fade"
                  mode="out-in"
                  enter-active-class="animate__animated fast animate__fadeInDown"
                  leave-active-class="animate__animated faster animate__fadeOutRight"
                >
                  <div>
                    <FormItem style="margin-top:-16px;">
                      <Select v-model="settings.auto_update_time.day" style="width: 200px">
                        <Option value="mondays">{{ getWeekDayString('monday') }}</Option>
                        <Option value="tuesdays">{{ getWeekDayString('tuesday') }}</Option>
                        <Option value="wednesdays">{{ getWeekDayString('wednesday') }}</Option>
                        <Option value="thursdays">{{ getWeekDayString('thursday') }}</Option>
                        <Option value="fridays">{{ getWeekDayString('friday') }}</Option>
                        <Option value="saturdays">{{ getWeekDayString('saturday') }}</Option>
                        <Option value="sundays">{{ getWeekDayString('sunday') }}</Option>
                      </Select>
                      <TimePicker
                        format="HH:mm"
                        type="timerange"
                        :clearable="false"
                        style="width: 200px"
                        :disabled-hours="[0, 1, 2, 21, 22, 23]"
                        v-model="settings.auto_update_time.time"
                      ></TimePicker>
                    </FormItem>
                  </div>
                </transition>
              </template>
              <FormItem class="spaced-buttons">
                <Button type="primary" :loading="saving" :disabled="saving" @click="handleSubmit('settings')">
                  <span v-if="!saving">{{ $t('save') }}</span>
                  <span v-else>{{ $t('saving') }}...</span>
                </Button>
                <Button @click="email = true">{{ $t('mail_settings') }}</Button>
                <Button @click="payment = true">{{ $t('payment_settings') }}</Button>
                <Button @click="scale_barcode_show = true">{{ $t('scale_barcode') }}</Button>
                <Button @click="logo = true">{{ $t('change_logo') }}</Button>
              </FormItem>
            </Col>
          </Row>
        </Form>
      </div>
    </Card>
    <Drawer :title="$t('mail_settings')" width="300" v-model="email" class="drawer-fixed">
      <Form v-if="mail_settings" ref="mail_settings" :model="mail_settings" :rules="rulesEmail">
        <!-- <vue-perfect-scrollbar class="drawer-content"> -->
        <div class="drawer-content">
          <FormItem :label="$t('from_name')" prop="MAIL_FROM_NAME">
            <Input v-model="mail_settings.MAIL_FROM_NAME" />
          </FormItem>
          <FormItem :label="$t('from_address')" prop="MAIL_FROM_ADDRESS">
            <Input v-model="mail_settings.MAIL_FROM_ADDRESS" />
          </FormItem>
          <FormItem :label="$t('mail_driver')" prop="MAIL_MAILER">
            <Select v-model="mail_settings.MAIL_MAILER">
              <Option value="smtp">SMTP</Option>
              <Option value="sendmail">SendMail</Option>
              <Option value="mailgun">Mailgun</Option>
              <Option value="sparkpost">SpartPost</Option>
            </Select>
          </FormItem>
          <span v-if="mail_settings.MAIL_MAILER == 'smtp'">
            <FormItem :label="$t('smtp_host')" prop="MAIL_HOST">
              <Input v-model="mail_settings.MAIL_HOST" />
            </FormItem>
            <FormItem :label="$t('smtp_port')" prop="MAIL_PORT">
              <Input v-model="mail_settings.MAIL_PORT" />
            </FormItem>
            <FormItem :label="$t('smtp_username')" prop="MAIL_USERNAME">
              <Input v-model="mail_settings.MAIL_USERNAME" />
            </FormItem>
            <FormItem :label="$t('smtp_password')" prop="MAIL_PASSWORD">
              <Input v-model="mail_settings.MAIL_PASSWORD" />
            </FormItem>
            <FormItem :label="$t('smtp_encryption')" prop="MAIL_ENCRYPTION">
              <Input v-model="mail_settings.MAIL_ENCRYPTION" />
            </FormItem>
          </span>
          <span v-if="mail_settings.MAIL_MAILER == 'mailgun'">
            <FormItem :label="$t('mailgun_doamin')" prop="MAILGUN_DOMAIN">
              <Input v-model="mail_settings.MAILGUN_DOMAIN" />
            </FormItem>
            <FormItem :label="$t('mailgun_secret')" prop="MAILGUN_SECRET">
              <Input v-model="mail_settings.MAILGUN_SECRET" />
            </FormItem>
            <FormItem :label="$t('mailgun_secret')" prop="MAILGUN_ENDPOINT">
              <Input v-model="mail_settings.MAILGUN_ENDPOINT" />
            </FormItem>
          </span>
          <span v-if="mail_settings.MAIL_MAILER == 'sparkpost'">
            <FormItem :label="$t('sparkpost_endpoint')" prop="SPARKPOST_SECRET">
              <Input v-model="mail_settings.SPARKPOST_SECRET" />
            </FormItem>
            <FormItem :label="$t('sparkpost_secret')" prop="SPARKPOST_ENDPOINT">
              <Input v-model="mail_settings.SPARKPOST_ENDPOINT" />
            </FormItem>
          </span>
        </div>
        <!-- </vue-perfect-scrollbar> -->
        <div class="drawer-footer">
          <Button long type="primary" @click="updateSettings('mail_settings')">
            <span v-if="!saving">{{ $t('save') }}</span>
            <span v-else>{{ $t('saving') }}...</span>
          </Button>
        </div>
      </Form>
    </Drawer>
    <Drawer :title="$t('payment_settings')" width="300" v-model="payment" class="drawer-fixed">
      <Form v-if="payment_settings" ref="payment_settings" :model="payment_settings" :rules="rulesPayment">
        <!-- <vue-perfect-scrollbar class="drawer-content"> -->
        <div class="drawer-content">
          <FormItem :label="$t('currency_code')" prop="CURRENCY_CODE">
            <Input v-model="payment_settings.CURRENCY_CODE" />
          </FormItem>
          <FormItem>
            <Checkbox v-model="payment_settings.PAYPAL_ENABLED">
              <span>{{ $t('enable_paypal') }}</span>
            </Checkbox>
          </FormItem>
          <span v-if="payment_settings.PAYPAL_ENABLED || payment_settings.CARD_GATEWAY == 'PayPal_Pro'">
            <FormItem :label="$t('paypal_api_username')" prop="PAYPAL_USERNAME">
              <Input v-model="payment_settings.PAYPAL_USERNAME" />
            </FormItem>
            <FormItem :label="$t('paypal_api_password')" prop="PAYPAL_PASSWORD">
              <Input v-model="payment_settings.PAYPAL_PASSWORD" />
            </FormItem>
            <FormItem :label="$t('paypal_api_signature')" prop="PAYPAL_SIGNATURE">
              <Input v-model="payment_settings.PAYPAL_SIGNATURE" />
            </FormItem>
          </span>
          <FormItem :label="$t('accept_card_with')" prop="CARD_GATEWAY">
            <Select v-model="payment_settings.CARD_GATEWAY">
              <Option value>{{ $t('disable') }}</Option>
              <Option value="Stripe">{{ $t('Stripe') }}</Option>
              <Option value="PayPal_Pro">{{ $t('PayPal_Pro') }}</Option>
              <Option value="PayPal_Rest">{{ $t('PayPal_Rest') }}</Option>
              <Option value="AuthorizeNetApi_Api">{{ $t('AuthorizeNetApi_Api') }}</Option>
            </Select>
          </FormItem>
          <span v-if="payment_settings.CARD_GATEWAY == 'Stripe'">
            <FormItem :label="$t('stripe_key')" prop="STRIPE_KEY">
              <Input v-model="payment_settings.STRIPE_KEY" />
            </FormItem>
            <FormItem :label="$t('stripe_secret')" prop="STRIPE_SECRET">
              <Input v-model="payment_settings.STRIPE_SECRET" />
            </FormItem>
          </span>
          <span v-if="payment_settings.CARD_GATEWAY == 'PayPal_Rest'">
            <FormItem :label="$t('paypal_client_id')" prop="PAYPAL_CLIENT_ID">
              <Input v-model="payment_settings.PAYPAL_CLIENT_ID" />
            </FormItem>
            <FormItem :label="$t('paypal_secret')" prop="PAYPAL_SECRET">
              <Input v-model="payment_settings.PAYPAL_SECRET" />
            </FormItem>
          </span>
          <span v-if="payment_settings.CARD_GATEWAY == 'AuthorizeNetApi_Api'">
            <FormItem :label="$t('authorize_login')" prop="AUTHORIZE_LOGIN">
              <Input v-model="payment_settings.AUTHORIZE_LOGIN" />
            </FormItem>
            <FormItem :label="$t('authorize_transaction_key')" prop="AUTHORIZE_TRANSACTION_KEY">
              <Input v-model="payment_settings.AUTHORIZE_TRANSACTION_KEY" />
            </FormItem>
          </span>
          <!-- </vue-perfect-scrollbar> -->
        </div>
        <div class="drawer-footer">
          <Button long type="primary" @click="updateSettings('payment_settings')">
            <span v-if="!saving">{{ $t('save') }}</span>
            <span v-else>{{ $t('saving') }}...</span>
          </Button>
        </div>
      </Form>
    </Drawer>
    <Modal :width="600" v-model="scale_barcode_show">
      <div slot="header">
        <h3>
          {{ $t('scale_barcode') }}
          <Button type="info" size="small" @click="scale_barcode_guide = true" style="margin-top:-0.25rem;float:right;margin-right:2rem;">{{
            $t('guide')
          }}</Button>
        </h3>
      </div>
      <Form ref="barcodeForm" :model="scale_barcode" label-position="top">
        <Row :gutter="16" class="mt16">
          <Col :sm="24" :md="12" :lg="12">
            <FormItem
              :label="$t('barcode_type')"
              prop="type"
              :rules="{
                required: true,
                trigger: 'change',
                message: this.$t('field_is_required', { field: this.$t('barcode_type') }),
              }"
            >
              <Select v-model="scale_barcode.type">
                <Option value="price">{{ $t('price') }}</Option>
                <Option value="weight">{{ $t('weight') }}</Option>
              </Select>
            </FormItem>
          </Col>
          <Col :sm="24" :md="12" :lg="12">
            <FormItem
              prop="total_digits"
              :label="$t('total_digits')"
              :rules="{
                required: true,
                type: 'number',
                trigger: 'blur',
                message: this.$t('field_is_required', { field: this.$t('total_digits') }),
              }"
            >
              <InputNumber v-model="scale_barcode.total_digits" />
            </FormItem>
          </Col>
          <!-- <Col :sm="24" :md="8" :lg="8">
            <FormItem
              prop="flag_digits"
              :label="$t('flag_digits')"
              :rules="{
                required: true,
                type: 'number',
                trigger: 'blur',
                message: this.$t('field_is_required', { field: this.$t('flag_digits') }),
              }"
            >
              <InputNumber v-model="scale_barcode.flag_digits" />
            </FormItem>
          </Col> -->
        </Row>
        <Row :gutter="16">
          <Col :sm="24" :md="12" :lg="12">
            <FormItem
              prop="item_code_start"
              :label="$t('item_code_start')"
              :rules="{
                required: true,
                type: 'number',
                trigger: 'blur',
                message: this.$t('field_is_required', { field: this.$t('item_code_start') }),
              }"
            >
              <InputNumber v-model="scale_barcode.item_code_start" />
            </FormItem>
          </Col>
          <Col :sm="24" :md="12" :lg="12">
            <FormItem
              prop="item_code_digits"
              :label="$t('item_code_digits')"
              :rules="{
                required: true,
                type: 'number',
                trigger: 'blur',
                message: this.$t('field_is_required', { field: this.$t('item_code_digits') }),
              }"
            >
              <InputNumber v-model="scale_barcode.item_code_digits" />
            </FormItem>
          </Col>
        </Row>
        <Row :gutter="16" v-if="scale_barcode.type == 'price'">
          <Col :sm="24" :md="8" :lg="8">
            <FormItem
              prop="price_start"
              :label="$t('price_start')"
              :rules="{
                type: 'number',
                trigger: 'blur',
                message: this.$t('field_is_required', { field: this.$t('price_start') }),
                required: scale_barcode.weight_start && scale_barcode.weight_digits && scale_barcode.weight_divide_by ? false : true,
              }"
            >
              <InputNumber v-model="scale_barcode.price_start" />
            </FormItem>
          </Col>
          <Col :sm="24" :md="8" :lg="8">
            <FormItem
              prop="price_digits"
              :label="$t('price_digits')"
              :rules="{
                type: 'number',
                trigger: 'blur',
                message: this.$t('field_is_required', { field: this.$t('price_digits') }),
                required: scale_barcode.weight_start && scale_barcode.weight_digits && scale_barcode.weight_divide_by ? false : true,
              }"
            >
              <InputNumber v-model="scale_barcode.price_digits" />
            </FormItem>
          </Col>
          <Col :sm="24" :md="8" :lg="8">
            <FormItem
              prop="price_divide_by"
              :label="$t('price_divide_by')"
              :rules="{
                required: true,
                type: 'number',
                trigger: 'blur',
                message: this.$t('field_is_required', { field: this.$t('price_divide_by') }),
                required: scale_barcode.weight_start && scale_barcode.weight_digits && scale_barcode.weight_divide_by ? false : true,
              }"
            >
              <InputNumber v-model="scale_barcode.price_divide_by" />
            </FormItem>
          </Col>
        </Row>
        <Row :gutter="16" v-else>
          <Col :sm="24" :md="8" :lg="8">
            <FormItem
              prop="weight_start"
              :label="$t('weight_start')"
              :rules="{
                type: 'number',
                trigger: 'blur',
                message: this.$t('field_is_required', { field: this.$t('weight_start') }),
                required: scale_barcode.price_start && scale_barcode.price_digits && scale_barcode.price_divide_by ? false : true,
              }"
            >
              <InputNumber v-model="scale_barcode.weight_start" />
            </FormItem>
          </Col>
          <Col :sm="24" :md="8" :lg="8">
            <FormItem
              prop="weight_digits"
              :label="$t('weight_digits')"
              :rules="{
                type: 'number',
                trigger: 'blur',
                message: this.$t('field_is_required', { field: this.$t('weight_digits') }),
                required: scale_barcode.price_start && scale_barcode.price_digits && scale_barcode.price_divide_by ? false : true,
              }"
            >
              <InputNumber v-model="scale_barcode.weight_digits" />
            </FormItem>
          </Col>
          <Col :sm="24" :md="8" :lg="8">
            <FormItem
              prop="weight_divide_by"
              :label="$t('weight_divide_by')"
              :rules="{
                type: 'number',
                trigger: 'blur',
                message: this.$t('field_is_required', { field: this.$t('weight_divide_by') }),
                required: scale_barcode.price_start && scale_barcode.price_digits && scale_barcode.price_divide_by ? false : true,
              }"
            >
              <InputNumber v-model="scale_barcode.weight_divide_by" />
            </FormItem>
          </Col>
        </Row>
        <!-- <Button type="dashed" size="large" long @click="scale_barcode_guide = true">{{ $t('guide') }}</Button> -->
      </Form>
      <div slot="footer">
        <Button type="primary" html-type="button" size="large" long :loading="saving_barcode" @click="saveBarcode('barcodeForm')">{{
          $t('save')
        }}</Button>
      </div>
    </Modal>
    <Modal :width="600" v-model="scale_barcode_guide" footer-hide :title="$t('guide')">
      <img src="/images/scale_barcode.png" alt="" style="max-width:100%;" />
    </Modal>
    <Modal :width="600" v-model="logo" footer-hide :title="$t('change_logo')">
      <change-logo-component @close="() => (logo = false)" />
    </Modal>
  </div>
</template>
<script>
import { routes } from '@mpsjs/routes/index';
import ChangeLogoComponent from '@mpscom/helpers/ChangeLogoComponent.vue';

export default {
  components: { ChangeLogoComponent },
  data() {
    return {
      logo: false,
      accounts: [],
      email: false,
      saving: false,
      loading: true,
      countries: [],
      customers: [],
      timezones: [],
      payment: false,
      categories: [],
      searching: false,
      settings_routes: [],
      mail_settings: null,
      payment_settings: null,
      saving_barcode: false,
      scale_barcode_show: false,
      scale_barcode_guide: false,
      errors: { message: '', form: {} },
      states: [{ value: '', label: this.$t('select_country') }],
      settings: {
        name: '',
        rows: 10,
        email: '',
        theme: '',
        timezone: '',
        dateformat: '',
        hide_id: false,
        quick_cash: [],
        overselling: '',
        confirmation: '',
        search_delay: 250,
        max_discount: null,
        fixed_layout: false,
        impersonation: false,
        onscreen_keyboard: true,
        inventory_accounting: 'FIFO',
        inventory_accounting: 'FIFO',
        loyalty: {
          staff: { spent: null, points: null },
          customer: { spent: null, points: null },
        },
      },
      scale_barcode: {
        type: 'weight',
        total_digits: null,
        flag_digits: null,
        item_code_start: null,
        item_code_digits: null,
        price_start: null,
        price_digits: null,
        price_divide_by: null,
        weight_start: null,
        weight_digits: null,
        weight_divide_by: null,
      },
      rules: {
        name: [{ required: true, message: this.$t('field_is_required', { field: this.$t('app_name') }), trigger: 'blur' }],
        email: [
          { required: true, message: this.$t('field_is_required', { field: this.$t('email') }), trigger: 'blur' },
          { type: 'email', message: 'Email format is incorrect', trigger: 'blur' },
        ],
        timezone: [{ required: true, message: this.$t('field_is_required', { field: this.$t('timezone') }), trigger: 'blur' }],
        language: [{ required: true, message: this.$t('field_is_required', { field: this.$t('language') }), trigger: 'change' }],
        dateformat: [{ required: true, message: this.$t('field_is_required', { field: this.$t('date_format') }), trigger: 'change' }],
        inventory_accounting: [
          { required: true, message: this.$t('field_is_required', { field: this.$t('inventory_accounting') }), trigger: 'change' },
        ],
        theme: [{ required: true, message: this.$t('field_is_required', { field: this.$t('theme') }), trigger: 'change' }],
        loader: [{ required: true, message: this.$t('field_is_required', { field: this.$t('loader') }), trigger: 'change' }],
        rows: [{ required: true, message: this.$t('field_is_required', { field: this.$t('initial_table_rows') }), trigger: 'change' }],
        short_name: [{ required: true, message: this.$t('field_is_required', { field: this.$t('short_name') }), trigger: 'change' }],
        company: [{ required: true, message: this.$t('field_is_required', { field: this.$t('company') }), trigger: 'blur' }],
        phone: [{ required: true, message: this.$t('field_is_required', { field: this.$t('phone') }), trigger: 'blur' }],
        address: [{ required: true, message: this.$t('field_is_required', { field: this.$t('address') }), trigger: 'blur' }],
        default_account: [
          {
            required: true,
            trigger: 'change',
            message: this.$t('field_is_required', { field: this.$t('default_x', { x: this.$tc('account') }) }),
          },
        ],
        default_customer: [
          {
            required: true,
            trigger: 'change',
            message: this.$t('field_is_required', { field: this.$t('default_x', { x: this.$tc('customer') }) }),
          },
        ],
        default_category: [
          {
            required: true,
            trigger: 'change',
            message: this.$t('field_is_required', { field: this.$t('default_x', { x: this.$tc('category') }) }),
          },
        ],
        country: [{ required: true, message: this.$t('field_is_required', { field: this.$t('country') }), trigger: 'change' }],
        state: [{ required: true, message: this.$t('field_is_required', { field: this.$t('state') }), trigger: 'change' }],
        reference: [{ required: true, message: this.$t('field_is_required', { field: this.$t('reference') }), trigger: 'change' }],
        max_discount: [
          {
            required: true,
            type: 'number',
            trigger: ['blur', 'change'],
            message: this.$t('field_is_required', { field: this.$t('max_discount') }),
          },
        ],
        search_delay: [
          {
            min: 200,
            max: 1000,
            required: true,
            type: 'number',
            trigger: ['blur', 'change'],
            message: this.$t('field_min_max_error', { field: this.$t('search_delay'), min: 200, max: 1000 }),
          },
        ],
        quick_cash: [
          {
            min: 3,
            max: 5,
            type: 'array',
            required: true,
            trigger: 'change',
            message: this.$t('quick_cash_error'),
          },
        ],
      },
      rulesEmail: {},
      rulesPayment: {},
    };
  },
  created() {
    this.fetch();
    this.settings_routes = this.routes = routes(this.$store.state.user_language)[0].children.find(r => r.path == 'settings').children;
  },
  methods: {
    getWeekDayString(day) {
      return new Date(
        this.$moment()
          .day(day)
          .format('YYYY-MM-DD')
      ).toLocaleString('default', { weekday: 'long' });
    },
    fetch() {
      this.$http
        .get('app/settings')
        .then(res => {
          this.accounts = res.data.accounts;
          this.customers = res.data.customers;
          this.categories = res.data.categories;
          this.countries = res.data.countries;
          this.timezones = res.data.timezones;
          this.mail_settings = res.data.mail_settings;
          res.data.payment_settings.PAYPAL_ENABLED = !!res.data.payment_settings.PAYPAL_ENABLED;
          this.payment_settings = res.data.payment_settings;
          this.getStates(res.data.settings.country, res.data.settings.state);
          res.data.settings.state = '';
          res.data.settings.inventory_accounting = res.data.settings.inventory_accounting || 'FIFO';
          res.data.settings.quick_cash = res.data.settings.quick_cash ? res.data.settings.quick_cash.split('|') : [];
          res.data.settings.search_delay = res.data.settings.search_delay ? parseFloat(res.data.settings.search_delay) : 250;
          res.data.settings.max_discount = res.data.settings.max_discount ? parseFloat(res.data.settings.max_discount) : null;
          this.scale_barcode = res.data.settings.scale_barcode || this.scale_barcode;
          res.data.settings.loyalty = res.data.settings.loyalty || {
            staff: { spent: null, points: null },
            customer: { spent: null, points: null },
          };
          res.data.settings.auto_update_time = res.data.settings.auto_update_time || { day: 'saturdays', time: ['03:00', '22:00'] };
          res.data.settings.loyalty.staff.spent = res.data.settings.loyalty.staff.spent
            ? parseFloat(res.data.settings.loyalty.staff.spent)
            : null;
          res.data.settings.loyalty.staff.points = res.data.settings.loyalty.staff.points
            ? parseFloat(res.data.settings.loyalty.staff.points)
            : null;
          res.data.settings.loyalty.customer.spent = res.data.settings.loyalty.customer.spent
            ? parseFloat(res.data.settings.loyalty.customer.spent)
            : null;
          res.data.settings.loyalty.customer.points = res.data.settings.loyalty.customer.points
            ? parseFloat(res.data.settings.loyalty.customer.points)
            : null;
          delete res.data.settings.svg_string;
          delete res.data.settings.json_string;
          delete res.data.settings.scale_barcode;
          this.settings = res.data.settings;
        })
        .finally(() => (this.loading = false));
    },
    handleSubmit() {
      this.$refs.settings.validate(valid => {
        if (valid) {
          this.saving = true;
          this.settings.quick_cash = this.settings.quick_cash.join('|');
          let data = this.$form(this.settings);
          this.settings.quick_cash = this.settings.quick_cash ? this.settings.quick_cash.split('|') : [];
          this.$http
            .post('app/settings', data)
            .then(res => {
              if (this.$store.state.user_language != res.data.language) {
                window.location.reload();
              }
              this.$store.commit('setSettings', res.data);
              this.$Notice.success({
                title: this.$t('settings') + ' ' + this.$t('saved'),
                desc: this.$t('settings_saved_text'),
              });
              if (window) {
                document.body.className = this.$store.getters.settings.theme == 'dark' ? 'bg-dark' : 'bg-light';
              }
            })
            .finally(() => (this.saving = false));
        } else {
          this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
        }
      });
    },
    updateSettings(form) {
      this.$refs[form].validate(valid => {
        if (valid) {
          this.saving = true;
          let data = { ...this.mail_settings, ...this.payment_settings };
          this.$http
            .put('app/settings', data)
            .then(res => {
              this.$Notice.success({
                title: this.$t('settings') + ' ' + this.$t('saved'),
                desc: this.$t('settings_saved_text'),
              });
              this.$store.commit('setPayment', {
                gateway: this.payment_settings.CARD_GATEWAY,
                public_key: this.payment_settings.STRIPE_KEY,
              });
              this.email = false;
              this.payment = false;
            })
            .catch(error => (this.errors = error))
            .finally(() => (this.saving = false));
        } else {
          this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
        }
      });
    },
    saveBarcode(form) {
      this.$refs[form].validate(valid => {
        if (valid) {
          this.saving_barcode = true;
          let data = { scale_barcode: { ...this.scale_barcode } };
          this.$http
            .post('app/settings/barcode', data)
            .then(res => {
              this.$Notice.success({
                title: this.$t('settings') + ' ' + this.$t('saved'),
                desc: this.$t('settings_saved_text'),
              });
              this.scale_barcode_show = false;
              this.$store.commit('setScaleBarcode', this.scale_barcode);
            })
            .catch(error => (this.errors = error))
            .finally(() => (this.saving_barcode = false));
        } else {
          this.$Notice.error({ title: this.$t('invalid_form'), desc: this.$t('invalid_form_error'), duration: 10 });
        }
      });
    },
    countryChange(code, state = '') {
      if (code) {
        this.searching = true;
        this.getStates(code, state);
        this.settings.state = '';
      } else {
        this.settings.state = '';
        this.settings.country = '';
        this.states = [{ value: '', label: this.$t('select_country') }];
      }
    },
    getStates(country, selected) {
      this.$http
        .get('app/states', { params: { country } })
        .then(res => {
          this.states = res.data;
          if (selected) {
            this.settings.state = this.states.find(state => state.value == selected).value;
          }
          this.searching = false;
        })
        .catch(err => this.$event.fire('appError', err.response));
    },
    handleReset(name) {
      this.$refs[name].resetFields();
    },
    onSelect(name) {
      if (name) {
        if (name == 'mail_settings') {
          this.email = true;
        } else if (name == 'scale_barcode') {
          this.scale_barcode_show = true;
        } else if (name == 'payment_settings') {
          this.payment = true;
        } else {
          this.$router.push({ name });
        }
      }
    },
  },
};
</script>

<style>
.flex-gap {
  --gap: 12px;
  display: flex;
  align-items: center;
  display: inline-flex;
  width: calc(100% + var(--gap));
  margin: calc(-1 * var(--gap)) 0 0 calc(-1 * var(--gap));
}
.flex-gap > * {
  display: inline-block;
  margin: var(--gap) 0 0 var(--gap);
}
.flex-gap .ivu-input-number {
  min-width: 75px;
  max-width: 150px;
}
</style>
