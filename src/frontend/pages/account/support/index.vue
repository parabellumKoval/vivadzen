<script setup>
const {t} = useI18n()
const props = defineProps({})

definePageMeta({
  crumb: {
    name: 'title.account.support',
    item: '/'
  },
  tab: 'support'
});

// COMPUTEDS
// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='./support.scss' lang='scss' scoped></style>
<style src='./../account-page.scss' lang='scss' scoped></style>
<!-- <i18n src='./lang.yaml' lang='yaml'></i18n> -->

<template>
  <div>
    <div class="title-secondary">{{ t('title.account.support') }}</div>

    <checkout-contacts class="box-wrapper"></checkout-contacts>
  </div>
</template>