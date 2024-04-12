<script setup>
const {t} = useI18n()
</script>

<style src="./skeleton.scss" lang="scss" scoped />
<i18n src="./../lang.yaml" lang="yaml"></i18n>

<template>
  <simple-table-row :show-details="false">
    <template v-slot:columns>
      <simple-table-column :label="t('number')">
        <span class="code"></span>
      </simple-table-column>
      <simple-table-column :label="t('valid_until')">
        <span class="date"></span>
      </simple-table-column>
      <simple-table-column :label="t('status')">
        <span class="status"></span>
      </simple-table-column>
      <simple-table-column :label="t('sale')">
        <span class="price"></span>
      </simple-table-column>
    </template>
  </simple-table-row>
</template>