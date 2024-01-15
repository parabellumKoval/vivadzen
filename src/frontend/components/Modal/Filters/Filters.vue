<script setup>
const {t} = useI18n()
const props = defineProps({})

// COMPUTEDS
// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='./filters.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <modal-wrapper :title="t('label.filters')" type="full">
    <div>
      <div class="selected-wrapper">
        <filter-selected></filter-selected>
      </div>
      <filter-list class="filters"></filter-list>
      <div class="buttons">
        <button class="button blue apply-btn">
          {{ t('button.apply') }}
        </button>
        <button class="button secondary reset-btn">
          {{ t('button.reset_all') }}
        </button>
      </div>
    </div>
  </modal-wrapper>
</template>