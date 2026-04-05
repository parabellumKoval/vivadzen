<script setup lang="ts">
const { t } = useI18n()

const breadcrumbs = computed(() => [
  { name: t('title.home'), item: '/' },
  { name: t('title.contacts'), item: '/contacts' },
])

useSeo().setPageSeo(t('title.contacts'))
</script>

<template>
  <div class="page-base kratom-content-page">
    <div class="container kratom-page-container kratom-content-shell">
      <the-breadcrumbs :crumbs="breadcrumbs" />
    </div>

    <div style="margin-top: -32px">
      <section-landing-contacts />
    </div>
  </div>
</template>

<style scoped lang="scss">
.kratom-content-shell { padding-top: 24px; margin-bottom: 24px; }
</style>
