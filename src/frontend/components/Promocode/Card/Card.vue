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

<template>
  <simple-table-row :show-details="false">
    <template v-slot:columns>
      <simple-table-column :label="t('number')">
        <promocode-code :value="promocode.code"></promocode-code>
      </simple-table-column>
      <simple-table-column :label="t('created_at')">
        {{ $d(promocode.created_at, 'short') }}
      </simple-table-column>
      <simple-table-column :label="t('status')">
        <span :class="promocode.status" class="status">{{ $t(`status.${promocode.status}`) }}</span>
      </simple-table-column>
      <simple-table-column :label="t('amount')">
        <span>{{ promocode.value }}</span>
      </simple-table-column>
    </template>
  </simple-table-row>
</template>

<i18n src="./lang.yaml" lang="yaml"></i18n>