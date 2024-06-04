<script setup>
const {t, locale} = useI18n()

const breadcrumbs = [
  {
    name: t('title.home'),
    item: '/'
  },{
    name: t('title.policy'),
    item: '/policy'
  }
]
// COMPUTEDS
// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='~/assets/scss/_rich-text.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="page-base">
    <div class="container">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <div class="title-common">{{ t('title.policy') }}</div>

      <ContentQuery path="policy" :locale="locale" find="one">
        <template #default="{ data }">
          <div class="rich-text" v-html="data.text"></div>
        </template>
      </ContentQuery>

    </div>
  </div>
</template>