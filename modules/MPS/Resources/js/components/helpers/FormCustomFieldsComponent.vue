<template>
  <div>
    <div v-if="attributes && attributes.length">
      <Divider dashed orientation="left">
        <small style="color: #aaa;">
          {{ $t('start_custom_fields') }}
        </small>
      </Divider>
      <custom-field-component
        :attr="attr"
        :key="attr.slug"
        :prop="attr.slug"
        :rules="cfRule(attr)"
        v-for="attr in attributes"
        v-model="value[attr.slug]"
      />
      <!-- @input="val => update(attr.slug, val)"
        :value="attr.type == 'number' ? (value[attr.slug] = value[attr.slug] ? parseFloat(value[attr.slug]) : null) : value[attr.slug]" -->
      <Divider dashed orientation="left">
        <small style="color: #aaa;">
          {{ $t('end_custom_fields') }}
        </small>
      </Divider>
    </div>
    <div v-else style="margin-bottom: 16px;"></div>
  </div>
</template>

<script>
import CustomFieldComponent from '@mpscom/helpers/CustomFieldComponent.vue';
export default {
  components: { CustomFieldComponent },
  props: {
    value: { type: Object, required: true },
    attributes: { type: Array, required: true },
  },
  methods: {
    update(field, value) {
      this.$emit('update', field, value);
    },
    cfRule(attr) {
      if (attr.type == 'number') {
        return [
          {
            type: 'number',
            trigger: 'change',
            required: attr.required == 1,
            message: this.$t('field_is_required', { field: attr.name }),
          },
        ];
      } else if (attr.type == 'checkbox') {
        return [
          {
            min: 1,
            type: 'array',
            trigger: 'change',
            required: attr.required == 1,
            message: this.$t('field_is_required', { field: attr.name }),
          },
        ];
        // } else if (attr.type == 'date' || attr.type == 'datetime') {
        //   return {
        //     type: 'date',
        //     trigger: 'change',
        //     required: attr.required == 1,
        //     message: this.$t('field_is_required', { field: attr.name }),
        //   };
      } else {
        return [{ required: attr.required == 1, message: this.$t('field_is_required', { field: attr.name }), trigger: ['blur', 'change'] }];
      }
    },
  },
};
</script>
