<script setup>
const {t} = useI18n()

const props = defineProps({
  promocode: {
    type: Object,
    required: true
  }
})

// COMPUTED
</script>

<style src="./card.scss" lang="scss" scoped />
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <simple-table-row :show-details="false" :class="promocode.status.status">
    <template v-slot:columns>
      <simple-table-column :label="t('number')">
        <promocode-code :value="promocode.code"></promocode-code>
      </simple-table-column>
      <simple-table-column :label="t('valid_until')">
        {{ $d(promocode.valid_until, 'short') }}
      </simple-table-column>
      <simple-table-column :label="t('status')">
        <span :class="promocode.status.status" class="status">{{ $t(`status.promocode.${promocode.status.status}`) }}</span>
      </simple-table-column>
      <simple-table-column :label="t('sale')">
        <span v-if="promocode.type === 'value'">
          <simple-price :value="promocode.value"></simple-price>
        </span>
        <span v-else>{{ promocode.value }}%</span>
      </simple-table-column>
    </template>
  </simple-table-row>
</template>