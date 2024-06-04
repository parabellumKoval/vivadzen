<script setup>
const {t, locale} = useI18n()

const text = await queryContent('about').locale(locale.value).findOne()

const breadcrumbs = [
  {
    name: t('title.home'),
    item: '/'
  },{
    name: t('title.about'),
    item: '/about'
  }
]
// COMPUTEDS
// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='~/assets/scss/page.scss' lang='scss' scoped></style>
<style src='~/assets/scss/_rich-text.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="page-base">
    <div class="container">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <div class="title-common">{{ t('title.about') }}</div>

      <div class="content-wrapper">
        <div class="rich-text">

          <h2>{{ text.title1 }}</h2>
          <p>{{ text.desc1 }}</p>

          <h2>{{ text.title2 }}</h2>
          <p>{{ text.desc2 }}</p>
          <p>{{ text.desc22 }}</p>

          <h2>{{ text.title3 }}</h2>
          <p>{{ text.desc3 }}</p>

          <h2>{{ text.title4 }}</h2>
          <p>{{ text.desc4 }}</p>
          <p>{{ text.desc42 }}</p>

          <h3>{{ text.finish }}</h3>

        </div>

        <aside class="content-aside">
          <checkout-contacts></checkout-contacts>
        </aside>
      </div>

    </div>
  </div>
</template>