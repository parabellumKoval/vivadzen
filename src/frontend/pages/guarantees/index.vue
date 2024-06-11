<script setup>
const {t, locale} = useI18n()

const text = await queryContent('guarantees').locale(locale.value).findOne()

const breadcrumbs = [
  {
    name: t('title.home'),
    item: '/'
  },{
    name: t('title.guarantees'),
    item: '/guarantees'
  }
]
// COMPUTEDS
// METHODS
// HANDLERS
// WATCHERS
//
onServerPrefetch(() => {
  useSeo().setPageSeo(t('title.guarantees'))
})
</script>

<style src='~/assets/scss/page.scss' lang='scss' scoped></style>
<style src='~/assets/scss/_rich-text.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="page-base">
    <div class="container">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <div class="title-common">{{ t('title.guarantees') }}</div>

      <div class="content-wrapper">
        <div class="rich-text">
          <p><b>{{ text.text }}</b></p>

          <ul>
            <li>{{ text.item1 }}</li>
            <li>{{ text.item2 }}</li>
            <li>{{ text.item3 }}</li>
            <li>{{ text.item4 }}</li>
          </ul>
        </div>

        <aside class="content-aside">
          <checkout-contacts></checkout-contacts>
        </aside>
      </div>

    </div>
  </div>
</template>