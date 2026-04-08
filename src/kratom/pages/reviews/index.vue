<script setup lang="ts">
const { t } = useI18n()

definePageMeta({
  pageBackground: '#f0e1ca',
})

const breadcrumbs = computed(() => [
  { name: t('title.home'), item: '/' },
  { name: t('title.reviews'), item: '/reviews' },
])

useSeo().setPageSeo('reviews', {
  fallbackTitle: () => t('title.reviews'),
})
</script>

<template>
  <div class="page-base kratom-reviews-page">
    <div class="container kratom-page-container kratom-reviews-page__shell">
      <the-breadcrumbs :crumbs="breadcrumbs" />
    </div>

    <section-landing-google-reviews
      mode="masonry"
      :per-page="12"
      paginated
      data-key="google-reviews-page"
      title-tag="h1"
    />
  </div>
</template>

<style scoped lang="scss">
.kratom-reviews-page__shell {
  padding-top: 24px;
  position: relative;
  z-index: 2;
}
</style>
