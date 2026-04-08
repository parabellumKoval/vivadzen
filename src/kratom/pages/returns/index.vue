<script setup lang="ts">
const { t, locale } = useI18n()
const data = await queryContent('returns').locale(locale.value).findOne()
const breadcrumbs = computed(() => [
  { name: t('title.home'), item: '/' },
  { name: t('title.returns'), item: '/returns' },
])

useSeo().setPageSeo('returns', {
  fallbackTitle: () => t('title.returns'),
})
</script>

<template>
  <div class="page-base kratom-content-page">
    <div class="container kratom-page-container kratom-content-shell">
      <the-breadcrumbs :crumbs="breadcrumbs" />
      <h1 class="title-common">{{ t('title.returns') }}</h1>
      <div class="rich-text kratom-content-card" v-html="data.text"></div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.kratom-content-shell { padding-top: 24px; }
.kratom-content-card { padding: 28px; border-radius: 32px; background: rgba(255,250,244,.92); border: 1px solid rgba(74,91,68,.1); }
</style>
