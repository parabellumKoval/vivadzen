<script setup>
const { t } = useI18n()
// COMPUTEDS
// METHODS
// HANDLERS
// WATCHERS
</script>

<style src="./bonus.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <modal-wrapper :title="t('review')">
    <div class="modal-wrapper">
      <review-bonus></review-bonus>
    </div>
  </modal-wrapper>
</template>