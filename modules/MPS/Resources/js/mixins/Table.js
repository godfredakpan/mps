import inflection from 'inflection';

import ListAttachmentsComponent from '@mpscom/helpers/ListAttachmentsComponent';

function Table(model, url, code = '', name = '', subModel) {
  var models = inflection.pluralize(model);
  var subModels = subModel ? '.' + inflection.pluralize(subModel) : '';
  return {
    data() {
      return {
        refresh: 1,
      };
    },
    created() {
      if (this.$store.state.settings.hide_id != 1) {
        this.columns.unshift({ title: this.$t('id'), sortable: true, key: 'id', width: 275 });
      }
    },
    mounted() {
      if (this.isSmallScreen) {
        this.columns = this.columns.map(c => {
          if (c.fixed) {
            delete c.fixed;
          }
          return c;
        });
      }
    },
    methods: {
      showInfo(row) {
        console.log('show: ', row);
      },
      editRecord(row) {
        this.$router.push({ name: models + subModels + '.edit', params: { id: row.id } });
      },
      renderAttachments(h, params) {
        let vm = this;
        return params.row.attachments && params.row.attachments.length
          ? h('Button', {
              props: {
                size: 'small',
                type: 'primary',
                icon: 'ios-link',
              },
              on: {
                click: function() {
                  vm.viewAttachments(params.row.attachments);
                },
              },
            })
          : '';
      },
      viewAttachments(attachments) {
        this.$Modal.info({
          width: 750,
          closable: true,
          scrollable: true,
          okText: this.$t('close'),
          title: this.$tc('attachment', 2),
          render: h => {
            return h(ListAttachmentsComponent, {
              props: {
                attachments: attachments,
              },
              on: {
                input: val => {
                  this.value = val;
                },
              },
            });
          },
        });
      },
      deleteAttachment(attachment, record = null) {
        if (record) {
          record.attachments = record.attachments.filter(a => a.uuid !== attachment.uuid);
        } else {
          this.attachments = this.attachments.filter(a => a.uuid !== attachment.uuid);
        }
      },
      deleteRecord(row) {
        this.$http
          .delete(url + '/' + row.id)
          .then(res => {
            if (res.data.success) {
              this.$Notice.success({ title: this.$tc(model) + ' ' + this.$t('deleted'), desc: this.$t('record_deleted') });
              this.refresh++;
            } else {
              this.$Notice.error({ title: this.$tc('failed'), desc: res.data.error || this.$t('failed_record_delete_text'), duration: 15 });
            }
          })
          .catch(err => {
            this.$event.fire('appError', err.response);
          })
          .finally(() => this.$Modal.remove());
      },
      deleteRecords(rows) {
        let ids = rows.map(r => r.id);
        this.$http.post(url + '/delete/many', { ids }).then(res => {
          if (res.data.success) {
            this.$Notice.success({
              duration: 10,
              desc: this.$t('records_deleted'),
              title: res.data.count + ' ' + this.$t('deleted') + ', ' + res.data.failed + ' ' + this.$t('failed'),
            });
            this.refresh++;
          } else {
            this.$Notice.error({ title: this.$tc('failed'), desc: this.$t('bulk_failed_error_text'), duration: 15 });
          }
        });
      },
      renderBoolean(h, params, key) {
        return (
          <div style="text-align:center;">
            {params.row[key] == 1 ? (
              <i class="ivu-icon ivu-icon-md-checkmark" style="font-size: 16px; color: #19be6b;" />
            ) : (
              <i class="ivu-icon ivu-icon-md-close" style="font-size: 16px; color: #ed4014;" />
            )}
          </div>
        );
      },
      renderUnlessIcon(h, params, key) {
        return (
          <div style="text-align:center;">
            {params.row[key] ? (
              <i class="ivu-icon ivu-icon-md-close" style="font-size: 16px; color: #ed4014;" />
            ) : (
              <i class="ivu-icon ivu-icon-md-checkmark" style="font-size: 16px; color: #19be6b;" />
            )}
          </div>
        );
      },
      renderExtras(h, params) {
        if (params.row.extra_attributes) {
          let extras = Object.entries(params.row.extra_attributes)
            .map(e => e.join(': '))
            .join(', ');
          return <div>{extras}</div>;
        }
        return '';
      },
      renderActions(h, params) {
        return h('actions-component', {
          props: { params, deleteFn: this.deleteRecord, editFn: this.editRecord, record: { model, code, name } },
        });
      },
      renderNumber(h, params, key, bold, prefix, postfix) {
        return (
          <div class={`text-right ${bold ? 'bold' : ''}`}>
            {(prefix ? prefix : '') +
              this.$options.filters.formatNumber(params.row[key], this.$store.getters.settings.decimals) +
              (postfix ? postfix : '')}
          </div>
        );
      },
      renderMyNumber(number, bold) {
        return <div class={`text-right ${bold ? 'bold' : ''}`}>{this.formatNumber(number)}</div>;
      },
      renderDate(h, params, key) {
        key = key ? key : 'date';
        return <div>{params.row[key] ? this.$options.filters.formatDate(params.row[key], this.$store.state.settings.dateformat) : ''}</div>;
      },
      renderDateTime(h, params) {
        return this.renderCreatedAt(h, params);
      },
      renderCreatedAt(h, params) {
        return <div>{this.$options.filters.formatDate(params.row.created_at, this.$store.state.settings.dateformat + ' HH:mm A')}</div>;
      },
      renderUpdatedAt(h, params) {
        return <div>{this.$options.filters.formatDate(params.row.updated_at, this.$store.state.settings.dateformat + ' HH:mm A')}</div>;
      },
      renderUser(h, params) {
        return <div>{params.row.user ? params.row.user.name : ''}</div>;
      },
      renderAddress(h, params) {
        return (
          <div>{`${params.row.house_no || ''} ${params.row.street_no || ''} ${params.row.address || ''} ${params.row.city || ''} ${params
            .row.postal_code || ''}`}</div>
        );
      },
    },
  };
}

export default Table;
