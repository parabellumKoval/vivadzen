<script setup>
const {t, locale} = useI18n()

const text = await queryContent('faq').locale(locale.value).findOne()

const breadcrumbs = [
  {
    name: t('title.home'),
    item: '/'
  },{
    name: t('title.qa'),
    item: '/faq'
  }
]

// COMPUTEDS
const items = computed(() => {
  return Object.values(text.items)
})

// METHODS
// HANDLERS
// WATCHERS
//
onServerPrefetch(() => {
  useSeo().setPageSeo(t('title.qa'))
})
</script>

<style src='~/assets/scss/page.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="page-base">
    <div class="container">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <div class="title-common">{{ t('title.qa') }}</div>
      <div class="content-wrapper">
        <section-faq :items="items" class="content"></section-faq>
        <aside class="content-aside">
          <checkout-contacts></checkout-contacts>
        </aside>
      </div>
    </div>
  </div>
</template>