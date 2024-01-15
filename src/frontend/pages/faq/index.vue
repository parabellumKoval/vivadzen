<script setup>
const {t} = useI18n()

const breadcrumbs = [
  {
    name: t('title.home'),
    item: '/'
  },{
    name: t('title.catalog'),
    item: '/catalog'
  }
]

// COMPUTEDS
// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="page-base">
    <div class="container">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <div class="title-common">Вопрос / Ответ</div>

      <section-faq></section-faq>
    </div>
  </div>
</template>