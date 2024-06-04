<script setup>
const {t} = useI18n()
</script>

<style src="./skeleton.scss" lang="scss" scoped />

<template>
  <simple-table-row :show-details="false">
    <template v-slot:columns>
      <simple-table-column :label="t('number')">
        <span class="code"></span>
      </simple-table-column>
      <simple-table-column :label="t('created_at')">
        <span class="date"></span>
      </simple-table-column>
      <simple-table-column :label="t('label.status')">
        <span class="status"></span>
      </simple-table-column>
      <simple-table-column :label="t('amount')">
        <span class="price"></span>
      </simple-table-column>
    </template>
  </simple-table-row>
</template>