<template>
  <div>
    <Card :dis-hover="true">
      <p slot="title">{{ $tc('notification', 2) }}</p>
      <span slot="extra">
        <Button type="text" size="small" @click="readAllNotification()">
          {{ $t('mark_all_as_x', { x: $t('read') }) }}
        </Button>
        <Button type="error" size="small" @click="deleteAllNotification()">
          <Icon type="ios-trash" size="16" />
          {{ $t('delete_x', { x: $t('all') }) }}
        </Button>
      </span>
      <div v-if="notifications.data && notifications.data.length">
        <List>
          <ListItem v-for="n in notifications.data" :key="n.id">
            <ListItemMeta>
              <div slot="title">
                {{ n.data.title }}
                <Icon type="md-checkmark" v-if="n.read_at" />
              </div>
              <div slot="description">
                {{ n.data.description }}
                <div style="font-weight:normal;margin-top:5px;">
                  <Icon type="md-time" />
                  {{ datetime(n.created_at) }}
                </div>
              </div>
            </ListItemMeta>
            <template slot="action">
              <li v-if="n.read_at">
                <Button type="text" size="small" @click="markAsUnread(n.id)">{{ $t('mark_as_x', { x: $t('unread') }) }}</Button>
              </li>
              <li v-else>
                <Button type="text" size="small" @click="markAsRead(n.id)">{{ $t('mark_as_x', { x: $t('read') }) }}</Button>
              </li>
              <li>
                <Button ghost type="error" size="small" @click="deleteNotification(n.id)">{{ $t('delete') }}</Button>
              </li>
            </template>
          </ListItem>
        </List>
        <Row style="padding-top:16px;border-top:1px solid #e8eaec;">
          <Col :sm="24" :md="8" class="table-info">
            <span style="line-height: 32px;">
              <span v-if="notifications.total > 0">{{
                $t('table_info', { start: notifications.from, end: notifications.to, total: notifications.total })
              }}</span>
              <span v-else>{{ $t('zero_records') }}</span>
            </span>
          </Col>
          <Col :sm="24" :md="16" class="table-page" v-if="page > 0">
            <span class="page">
              <Page
                :total="notifications.total"
                :page-size="notifications.per_page"
                :current="notifications.current_page"
                @on-change="pageChanged"
              ></Page>
            </span>
          </Col>
        </Row>
      </div>
      <Alert v-else type="warning" show-icon>
        {{ $t('no_data') }}
      </Alert>
    </Card>
  </div>
</template>
<script>
export default {
  data() {
    return {
      page: 1,
      loading: false,
      notifications: {},
    };
  },
  watch: {
    '$route.query.reload': function() {
      console.log('reload');
      this.getNotifications();
    },
  },
  mounted() {
    this.getNotifications();
  },
  methods: {
    getNotifications() {
      this.$http.get('app/notifications', { params: { page: this.page } }).then(res => (this.notifications = res.data));
    },
    pageChanged(page) {
      this.page = page;
      return;
    },
    markAsRead(id) {
      this.$http.put('app/notifications/' + id).then(() => this.getNotifications());
    },
    markAsUnread(id) {
      this.$http.post('app/notifications/' + id).then(() => this.getNotifications());
    },
    deleteNotification(id) {
      this.$http.delete('app/notifications/' + id).then(() => this.getNotifications());
    },
    readAllNotification() {
      this.$http.post('app/notifications/read/all').then(() => this.getNotifications());
    },
    deleteAllNotification() {
      this.$http.post('app/notifications/delete/all').then(() => this.getNotifications());
    },
  },
};
</script>
