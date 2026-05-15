<script setup lang="ts">
const { t, locale } = useI18n()
const breadcrumbs = computed(() => [
  { name: t('title.home'), item: '/' },
  { name: t('title.terms'), item: '/terms' },
])

useSeo().setPageSeo('terms', {
  fallbackTitle: () => t('title.terms'),
})
</script>

<template>
  <div class="page-base kratom-content-page">
    <div class="container kratom-page-container kratom-content-shell">
      <the-breadcrumbs :crumbs="breadcrumbs" />
      <h1 class="title-common">{{ t('title.terms') }}</h1>
      <ContentQuery path="terms" :locale="locale" find="one">
        <template #default="{ data }">
          <div class="rich-text kratom-content-card" v-html="data.text"></div>
        </template>
      </ContentQuery>
    </div>
  </div>
</template>

<style scoped lang="scss">
@use '@/assets/scss/content-page-rich-text' as *;
</style>
