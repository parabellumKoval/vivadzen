<script setup lang="ts">
const { t } = useI18n()

const breadcrumbs = computed(() => [
  { name: t('title.home'), item: '/' },
  { name: t('title.payment'), item: '/payment' },
])

useSeo().setPageSeo('payment', {
  fallbackTitle: () => t('title.payment'),
})
</script>

<template>
  <div class="page-base kratom-method-page">
    <div class="container kratom-page-container kratom-method-page__shell">
      <the-breadcrumbs :crumbs="breadcrumbs" />
      <h1 class="title-common">{{ t('title.payment') }}</h1>
      <div class="kratom-method-page__card">
        <KratomMethodsDetails section="payment" />
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.kratom-method-page__shell {
  padding-top: 24px;
  padding-bottom: 48px;
}

.kratom-method-page__card {
  padding: 28px;
  border-radius: 32px;
  border: 1px solid rgba(74, 91, 68, 0.1);
  background: rgba(255, 250, 244, 0.94);
}

@include mobile {
  .kratom-method-page__card {
    padding: 20px;
    border-radius: 24px;
  }
}
</style>
