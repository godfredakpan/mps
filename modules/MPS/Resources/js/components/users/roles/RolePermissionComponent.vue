<template>
  <Card :dis-hover="true">
    <p slot="title">
      {{ $t('edit_x', { x: $tc('permission', 2) }) }}
      <span v-if="role">({{ $tc('role') }}: {{ role.name }})</span>
    </p>
    <router-link to="/users/roles" slot="extra">
      <Icon type="ios-grid-outline" />
      {{ $t('list') }} {{ $tc('role', 2) }}
    </router-link>
    <div>
      <Form ref="permissions" :model="form" :rules="rules" :label-width="150" class="form-responsive">
        <Loading v-if="loading" />
        <Alert type="error" show-icon class="mb26" v-if="errors.message">
          <div v-html="errors.message"></div>
        </Alert>
        <Row :gutter="16">
          <Col :sm="24" :md="24" :lg="12">
            <div class="mb16">
              <List border class="permissions">
                <ListItem>
                  <ListItemMeta :title="$t('pos')" :description="$t('permissions_for', { x: $t('pos') })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['pos-sales']">{{ $t('access_x', { x: $t('pos') }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-orders']">{{ $t('add_x', { x: $tc('order', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-orders']">{{ $t('edit_x', { x: $tc('order', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-orders']">{{ $t('delete_x', { x: $tc('order', 2) }) }}</Checkbox>
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('sale', 2)" :description="$t('permissions_for', { x: $tc('sale', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-sales']">{{ $t('view_x', { x: $tc('sale', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-sales']">{{ $t('add_x', { x: $tc('sale', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-sales']">{{ $t('edit_x', { x: $tc('sale', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-sales']">{{ $t('delete_x', { x: $tc('sale', 2) }) }}</Checkbox>
                    <!-- <Checkbox class="v-check" v-model="form['pdf-sales']">{{ $t('pdf_x', { x: $tc('sale',2) }) }}</Checkbox> -->
                    <Checkbox class="v-check" v-model="form['email-sales']">{{ $t('email_x', { x: $tc('sale', 2) }) }}</Checkbox>
                    <!-- <Checkbox class="v-check" v-model="form['import-sales']">{{ $t('import_x', { x: $tc('sale', 2) }) }}</Checkbox> -->
                    <!-- <Checkbox class="v-check" v-model="form['export-sales']">{{ $t('export_x', { x: $tc('sale', 2) }) }}</Checkbox> -->
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('delivery', 2)" :description="$t('permissions_for', { x: $tc('delivery', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-deliveries']">{{ $t('view_x', { x: $tc('delivery', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-deliveries']">{{ $t('add_x', { x: $tc('delivery', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-deliveries']">{{ $t('edit_x', { x: $tc('delivery', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-deliveries']">{{ $t('delete_x', { x: $tc('delivery', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['email-deliveries']">{{ $t('email_x', { x: $tc('delivery', 2) }) }}</Checkbox>
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('recurring_sale', 2)" :description="$t('permissions_for', { x: $tc('recurring_sale', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-recurring-sales']">{{
                      $t('view_x', { x: $tc('recurring_sale', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-recurring-sales']">{{
                      $t('add_x', { x: $tc('recurring_sale', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-recurring-sales']">{{
                      $t('edit_x', { x: $tc('recurring_sale', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-recurring-sales']">{{
                      $t('delete_x', { x: $tc('recurring_sale', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['email-recurring-sales']">{{
                      $t('email_x', { x: $tc('recurring_sale', 2) })
                    }}</Checkbox>
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('customer', 2)" :description="$t('permissions_for', { x: $tc('customer', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-customers']">{{ $t('view_x', { x: $tc('customer', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-customers']">{{ $t('add_x', { x: $tc('customer', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-customers']">{{ $t('edit_x', { x: $tc('customer', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-customers']">{{ $t('delete_x', { x: $tc('customer', 2) }) }}</Checkbox>
                    <!-- <Checkbox class="v-check" v-model="form['import-customers']">{{ $t('import_x', { x: $tc('customer', 2) }) }}</Checkbox> -->
                    <!-- <Checkbox class="v-check" v-model="form['export-customers']">{{ $t('export_x', { x: $tc('customer', 2) }) }}</Checkbox> -->
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('purchase', 2)" :description="$t('permissions_for', { x: $tc('purchase', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-purchases']">{{ $t('view_x', { x: $tc('purchase', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-purchases']">{{ $t('add_x', { x: $tc('purchase', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-purchases']">{{ $t('edit_x', { x: $tc('purchase', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-purchases']">{{ $t('delete_x', { x: $tc('purchase', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['email-purchases']">{{ $t('email_x', { x: $tc('purchase', 2) }) }}</Checkbox>
                    <!-- <Checkbox class="v-check" v-model="form['pdf-purchase']">{{ $t('pdf_x', { x: $tc('purchase',2) }) }}</Checkbox> -->
                    <!-- <Checkbox class="v-check" v-model="form['import-purchases']">{{ $t('import_x', { x: $tc('purchase', 2) }) }}</Checkbox> -->
                    <!-- <Checkbox class="v-check" v-model="form['export-purchases']">{{ $t('export_x', { x: $tc('purchase', 2) }) }}</Checkbox> -->
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('supplier', 2)" :description="$t('permissions_for', { x: $tc('supplier', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-suppliers']">{{ $t('view_x', { x: $tc('supplier', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-suppliers']">{{ $t('add_x', { x: $tc('supplier', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-suppliers']">{{ $t('edit_x', { x: $tc('supplier', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-suppliers']">{{ $t('delete_x', { x: $tc('supplier', 2) }) }}</Checkbox>
                    <!-- <Checkbox class="v-check" v-model="form['email-suppliers']">{{ $t('email_x', { x: $tc('supplier', 2) }) }}</Checkbox> -->
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('quotation', 2)" :description="$t('permissions_for', { x: $tc('quotation', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-quotations']">{{ $t('view_x', { x: $tc('quotation', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-quotations']">{{ $t('add_x', { x: $tc('quotation', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-quotations']">{{ $t('edit_x', { x: $tc('quotation', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-quotations']">{{
                      $t('delete_x', { x: $tc('quotation', 2) })
                    }}</Checkbox>
                    <!-- <Checkbox class="v-check" v-model="form['email-quotations']">{{ $t('email_x', { x: $tc('quotation', 2) }) }}</Checkbox> -->
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('return_order', 2)" :description="$t('permissions_for', { x: $tc('return_order', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-return-orders']">{{
                      $t('view_x', { x: $tc('return_order', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-return-orders']">{{
                      $t('add_x', { x: $tc('return_order', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-return-orders']">{{
                      $t('edit_x', { x: $tc('return_order', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-return-orders']">{{
                      $t('delete_x', { x: $tc('return_order', 2) })
                    }}</Checkbox>
                    <!-- <Checkbox class="v-check" v-model="form['email-return-orders']">{{ $t('email_x', { x: $tc('return_order', 2) }) }}</Checkbox> -->
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('payment', 2)" :description="$t('permissions_for', { x: $tc('payment', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-payments']">{{ $t('view_x', { x: $tc('payment', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-payments']">{{ $t('add_x', { x: $tc('payment', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-payments']">{{ $t('edit_x', { x: $tc('payment', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-payments']">{{ $t('delete_x', { x: $tc('payment', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['email-payments']">{{ $t('email_x', { x: $tc('payment', 2) }) }}</Checkbox>
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('income', 2)" :description="$t('permissions_for', { x: $tc('income', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-incomes']">{{ $t('view_x', { x: $tc('income', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-incomes']">{{ $t('add_x', { x: $tc('income', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-incomes']">{{ $t('edit_x', { x: $tc('income', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-incomes']">{{ $t('delete_x', { x: $tc('income', 2) }) }}</Checkbox>
                    <!-- <Checkbox class="v-check" v-model="form['email-incomes']">{{ $t('email_x', { x: $tc('income', 2) }) }}</Checkbox> -->
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('expense', 2)" :description="$t('permissions_for', { x: $tc('expense', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-expenses']">{{ $t('view_x', { x: $tc('expense', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-expenses']">{{ $t('add_x', { x: $tc('expense', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-expenses']">{{ $t('edit_x', { x: $tc('expense', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-expenses']">{{ $t('delete_x', { x: $tc('expense', 2) }) }}</Checkbox>
                    <!-- <Checkbox class="v-check" v-model="form['email-expenses']">{{ $t('email_x', { x: $tc('expense', 2) }) }}</Checkbox> -->
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('item', 2)" :description="$t('permissions_for', { x: $tc('item', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-items']">{{ $t('view_x', { x: $tc('item', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-items']">{{ $t('add_x', { x: $tc('item', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-items']">{{ $t('edit_x_all', { x: $tc('item', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-items']">{{ $t('delete_x_all', { x: $tc('item', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['label-items']">{{ $t('view_x', { x: $t('item_label') }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['trail-items']">{{ $t('trail_x', { x: $tc('item', 2) }) }}</Checkbox>
                    <!-- <Checkbox class="v-check" v-model="form['import-items']">{{ $t('import_x', { x: $tc('item', 2) }) }}</Checkbox> -->
                    <!-- <Checkbox class="v-check" v-model="form['export-items']">{{ $t('export_x', { x: $tc('item', 2) }) }}</Checkbox> -->
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('modifier', 2)" :description="$t('permissions_for', { x: $tc('modifier', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-modifiers']">{{ $t('view_x', { x: $tc('modifier', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-modifiers']">{{ $t('add_x', { x: $tc('modifier', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-modifiers']">{{
                      $t('edit_x_all', { x: $tc('modifier', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-modifiers']">{{
                      $t('delete_x_all', { x: $tc('modifier', 2) })
                    }}</Checkbox>
                    <!-- <Checkbox class="v-check" v-model="form['import-modifiers']">{{ $t('import_x', { x: $tc('modifier', 2) }) }}</Checkbox> -->
                    <!-- <Checkbox class="v-check" v-model="form['export-modifiers']">{{ $t('export_x', { x: $tc('modifier', 2) }) }}</Checkbox> -->
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('gift_card', 2)" :description="$t('permissions_for', { x: $tc('gift_card', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-gift-cards']">{{ $t('view_x', { x: $tc('gift_card', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-gift-cards']">{{ $t('add_x', { x: $tc('gift_card', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-gift-cards']">{{
                      $t('edit_x_all', { x: $tc('gift_card', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-gift-cards']">{{
                      $t('delete_x_all', { x: $tc('gift_card', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['log-gift-cards']">{{ $t('log_x', { x: $tc('gift_card', 2) }) }}</Checkbox>
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta
                    :title="$tc('stock_adjustment', 2)"
                    :description="$t('permissions_for', { x: $tc('stock_adjustment', 2) })"
                  />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-stock-adjustments']">{{
                      $t('view_x', { x: $tc('stock_adjustment', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-stock-adjustments']">{{
                      $t('add_x', { x: $tc('stock_adjustment', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-stock-adjustments']">{{
                      $t('edit_x', { x: $tc('stock_adjustment', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-stock-adjustments']">{{
                      $t('delete_x', { x: $tc('stock_adjustment', 2) })
                    }}</Checkbox>
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('asset_transfer', 2)" :description="$t('permissions_for', { x: $tc('asset_transfer', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-asset-transfers']">{{
                      $t('view_x', { x: $tc('asset_transfer', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-asset-transfers']">{{
                      $t('add_x', { x: $tc('asset_transfer', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-asset-transfers']">{{
                      $t('edit_x', { x: $tc('asset_transfer', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-asset-transfers']">{{
                      $t('delete_x', { x: $tc('asset_transfer', 2) })
                    }}</Checkbox>
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('stock_transfer', 2)" :description="$t('permissions_for', { x: $tc('stock_transfer', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-stock-transfers']">{{
                      $t('view_x', { x: $tc('stock_transfer', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-stock-transfers']">{{
                      $t('add_x', { x: $tc('stock_transfer', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-stock-transfers']">{{
                      $t('edit_x', { x: $tc('stock_transfer', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-stock-transfers']">{{
                      $t('delete_x', { x: $tc('stock_transfer', 2) })
                    }}</Checkbox>
                  </template>
                </ListItem>
              </List>
            </div>
          </Col>
          <Col :sm="24" :md="24" :lg="12">
            <div class="mb16">
              <List border class="permissions">
                <ListItem>
                  <ListItemMeta :title="$tc('account', 2)" :description="$t('permissions_for', { x: $tc('account', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-accounts']">{{ $t('view_x', { x: $tc('account', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-accounts']">{{ $t('add_x', { x: $tc('account', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-accounts']">{{ $t('edit_x_all', { x: $tc('account', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-accounts']">{{
                      $t('delete_x_all', { x: $tc('account', 2) })
                    }}</Checkbox>
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('location', 2)" :description="$t('permissions_for', { x: $tc('location', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-locations']">{{ $t('view_x', { x: $tc('location', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-locations']">{{ $t('add_x', { x: $tc('location', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-locations']">{{
                      $t('edit_x_all', { x: $tc('location', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-locations']">{{
                      $t('delete_x_all', { x: $tc('location', 2) })
                    }}</Checkbox>
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('user', 2)" :description="$t('permissions_for', { x: $tc('user', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-users']">{{ $t('view_x', { x: $tc('user', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-users']">{{ $t('add_x', { x: $tc('user', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-users']">{{ $t('edit_x_all', { x: $tc('user', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-users']">{{ $t('delete_x_all', { x: $tc('user', 2) }) }}</Checkbox>
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('role', 2)" :description="$t('permissions_for', { x: $tc('user_role', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-roles']">{{ $t('view_x', { x: $tc('role', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-roles']">{{ $t('add_x', { x: $tc('role', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-roles']">{{ $t('edit_x_all', { x: $tc('role', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-roles']">{{ $t('delete_x_all', { x: $tc('role', 2) }) }}</Checkbox>
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('salary', 2)" :description="$t('permissions_for', { x: $tc('salary', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-salaries']">{{ $t('view_x', { x: $tc('salary', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-salaries']">{{ $t('add_x', { x: $tc('salary', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-salaries']">{{ $t('edit_x_all', { x: $tc('salary', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-salaries']">{{ $t('delete_x_all', { x: $tc('salary', 2) }) }}</Checkbox>
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('promo', 2)" :description="$t('permissions_for', { x: $tc('promo', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-promos']">{{ $t('view_x', { x: $tc('promo', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-promos']">{{ $t('add_x', { x: $tc('promo', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-promos']">{{ $t('edit_x_all', { x: $tc('promo', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-promos']">{{ $t('delete_x_all', { x: $tc('promo', 2) }) }}</Checkbox>
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('brand', 2)" :description="$t('permissions_for', { x: $tc('brand', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-brands']">{{ $t('view_x', { x: $tc('brand', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-brands']">{{ $t('add_x', { x: $tc('brand', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-brands']">{{ $t('edit_x_all', { x: $tc('brand', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-brands']">{{ $t('delete_x_all', { x: $tc('brand', 2) }) }}</Checkbox>
                    <!-- <Checkbox class="v-check" v-model="form['import-brands']">{{ $t('import_x', { x: $tc('brand', 2) }) }}</Checkbox> -->
                    <!-- <Checkbox class="v-check" v-model="form['export-brands']">{{ $t('export_x', { x: $tc('brand', 2) }) }}</Checkbox> -->
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('category', 2)" :description="$t('permissions_for', { x: $tc('category', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-categories']">{{ $t('view_x', { x: $tc('category', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-categories']">{{ $t('add_x', { x: $tc('category', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-categories']">{{
                      $t('edit_x_all', { x: $tc('category', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-categories']">{{
                      $t('delete_x_all', { x: $tc('category', 2) })
                    }}</Checkbox>
                    <!-- <Checkbox class="v-check" v-model="form['import-categories']">{{ $t('import_x', { x: $tc('category', 2) }) }}</Checkbox> -->
                    <!-- <Checkbox class="v-check" v-model="form['export-categories']">{{ $t('export_x', { x: $tc('category', 2) }) }}</Checkbox> -->
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('tax', 2)" :description="$t('permissions_for', { x: $tc('tax', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-taxes']">{{ $t('view_x', { x: $tc('tax', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-taxes']">{{ $t('add_x', { x: $tc('tax', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-taxes']">{{ $t('edit_x_all', { x: $tc('tax', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-taxes']">{{ $t('delete_x_all', { x: $tc('tax', 2) }) }}</Checkbox>
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('field', 2)" :description="$t('permissions_for', { x: $tc('field', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-fields']">{{ $t('view_x', { x: $tc('field', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-fields']">{{ $t('add_x', { x: $tc('field', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-fields']">{{ $t('edit_x_all', { x: $tc('field', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-fields']">{{ $t('delete_x_all', { x: $tc('field', 2) }) }}</Checkbox>
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('customer_group', 2)" :description="$t('permissions_for', { x: $tc('customer_group', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-customer-groups']">{{
                      $t('view_x', { x: $tc('customer_group', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-customer-groups']">{{
                      $t('add_x', { x: $tc('customer_group', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-customer-groups']">{{
                      $t('edit_x_all', { x: $tc('customer_group', 2) })
                    }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-customer-groups']">{{
                      $t('delete_x_all', { x: $tc('customer_group', 2) })
                    }}</Checkbox>
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('hall', 2)" :description="$t('permissions_for', { x: $tc('hall', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-halls']">{{ $t('view_x', { x: $tc('hall', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-halls']">{{ $t('add_x', { x: $tc('hall', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-halls']">{{ $t('edit_x_all', { x: $tc('hall', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-halls']">{{ $t('delete_x_all', { x: $tc('hall', 2) }) }}</Checkbox>
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('table', 2)" :description="$t('permissions_for', { x: $tc('table', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-tables']">{{ $t('view_x', { x: $tc('table', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-tables']">{{ $t('add_x', { x: $tc('table', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-tables']">{{ $t('edit_x_all', { x: $tc('table', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-tables']">{{ $t('delete_x_all', { x: $tc('table', 2) }) }}</Checkbox>
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('unit', 2)" :description="$t('permissions_for', { x: $tc('unit', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-units']">{{ $t('view_x', { x: $tc('unit', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-units']">{{ $t('add_x', { x: $tc('unit', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-units']">{{ $t('edit_x_all', { x: $tc('unit', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-units']">{{ $t('delete_x_all', { x: $tc('unit', 2) }) }}</Checkbox>
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('report', 2)" :description="$t('permissions_for', { x: $tc('report', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['read-report']">{{ $tc('report', 2) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['registers-report']">{{ $tc('register', 2) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['expiry-alerts-report']">{{ $t('expiry_alerts') }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['quantity-alerts-report']">{{ $t('quantity_alerts') }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['items-report']">{{ $t('x_report', { x: $tc('item', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['sales-report']">{{ $t('x_report', { x: $tc('sale', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['purchases-report']">{{ $t('x_report', { x: $tc('purchase', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['payments-report']">{{ $t('x_report', { x: $tc('payment', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['expenses-report']">{{ $t('x_report', { x: $tc('expense', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['incomes-report']">{{ $t('x_report', { x: $tc('income', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['taxes-report']">{{ $t('x_report', { x: $tc('tax', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['stock-transfers-report']">{{ $tc('stock_transfer', 2) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['stock-adjustments-report']">{{ $tc('stock_adjustment', 2) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['time-clock-report']">{{ $tc('time_clock', 2) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['activity-logs-report']">{{ $t('activity_logs') }}</Checkbox>
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('event', 2)" :description="$t('permissions_for', { x: $tc('event', 2) })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['calendar']">{{ $t('calendar') }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['read-events']">{{ $t('view_x', { x: $tc('event', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['create-events']">{{ $t('add_x', { x: $tc('event', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['update-events']">{{ $t('edit_x_all', { x: $tc('event', 2) }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-events']">{{ $t('delete_x_all', { x: $tc('event', 2) }) }}</Checkbox>
                  </template>
                </ListItem>
                <ListItem>
                  <ListItemMeta :title="$tc('misc', 2)" :description="$t('permissions_for', { x: $t('misc') })" />
                  <template slot="action">
                    <Checkbox class="v-check" v-model="form['alerts-report']">{{ $tc('alert', 2) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['delete-media']">{{ $t('delete_x', { x: $t('media') }) }}</Checkbox>
                    <Checkbox class="v-check" v-model="form['item-label-design']">{{ $t('item_label_design') }}</Checkbox>
                  </template>
                </ListItem>
              </List>
            </div>
          </Col>
        </Row>
        <Button type="primary" :loading="saving" :disabled="saving" @click="setPermissions('roles')">
          <span v-if="!saving">{{ $t('submit') }}</span>
          <span v-else>{{ $t('saving') }}...</span>
        </Button>
        <Button ghost type="primary" :loading="saving" :disabled="saving" style="margin-left: 8px;" @click="setPermissions('roles', true)">
          <span v-if="!saving">{{ $t('save_n_stay') }}</span>
          <span v-else>{{ $t('saving') }}...</span>
        </Button>
        <!-- <Checkbox class="v-check" style="margin-left: 8px;" :indeterminate="indeterminate" :value="checkAll" @click.prevent.native="handleCheckAll">
              {{ $t('enable_all') }}
            </Checkbox> -->
      </Form>
    </div>
  </Card>
</template>

<script>
import Form from '@mpsjs/mixins/Form';
const formatRes = (data, vm) => {
  vm.role = { ...data };
  vm.form = vm.role.all_permissions.reduce((ac, a) => {
    return { ...ac, [a]: vm.role.permissions.includes(a) };
  }, {});
  return true;
};
export default {
  mixins: [Form('role', 'app/roles', false, formatRes)],
  data() {
    return {
      form: {},
      rules: {},
      role: null,
      saving: false,
      loading: true,
    };
  },
  methods: {
    fetch(id) {
      this.$http
        .get(`app/roles/${id}?permissions=yes`)
        .then(res => formatRes(res.data, this))
        .finally(() => (this.loading = false));
    },
    setPermissions(page, stay) {
      this.saving = true;
      let permissions = [];
      for (const [key, value] of Object.entries(this.form)) {
        if (value) {
          permissions.push(key);
        }
      }
      this.$http
        .post(`app/roles/${this.role.id}/permissions`, permissions)
        .then(res => {
          this.$Notice.success({ title: this.$tc('permission', 2), desc: this.$t('record_updated'), duration: 10 });
          if (!stay) {
            this.$router.push({ name: page + '.list' });
          }
        })
        .finally(() => (this.saving = false));
    },
  },
};
</script>

<style>
.permissions .v-check {
  display: flex;
}
.permissions .v-check .ivu-checkbox {
  margin-right: 8px;
}
.permissions .ivu-list-item-action {
  min-width: 50%;
}
</style>
