<script setup>
const {t} = useI18n()
const {address} = useContacts()
// const props = defineProps({})
// COMPUTEDS
// METHODS
// HANDLERS
// WATCHERS
</script>

<!-- <style src='' lang='scss' scoped></style> -->
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="form-static">
    <div class="label">{{ t('label.our_address') }}</div>
    <div>{{ address }}</div>
  </div>
</template>