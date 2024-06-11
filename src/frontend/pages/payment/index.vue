<script setup>
const {t} = useI18n()

const breadcrumbs = [
  {
    name: t('title.home'),
    item: '/'
  },{
    name: t('title.payment'),
    item: '/payment'
  }
]
// COMPUTEDS
// METHODS
// HANDLERS
// WATCHERS
//
onServerPrefetch(() => {
  useSeo().setPageSeo(t('title.payment'))
})
</script>

<style src='~/assets/scss/page.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="page-base">
    <div class="container">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <div class="title-common">{{ t('title.payment') }}</div>
      
      <div class="content-wrapper">
        <product-payment-info></product-payment-info>
        <aside class="content-aside">
          <checkout-contacts></checkout-contacts>
        </aside>
      </div>

    </div>
  </div>
</template>