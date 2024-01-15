<script setup>
import {useArticleFaker} from '~/composables/fakers/useArticleFaker.ts'

const {t} = useI18n()

const breadcrumbs = [
  {
    name: t('title.home'),
    item: '/'
  },{
    name: t('title.blog'),
    item: '/blog'
  }
]

// COMPUTEDS
const articles = computed(() => {
  return useArticleFaker()(8)
})
// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='./blog.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="page-base">
    <div class="container">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <div class="title-common">Блог</div>

      <div class="articles-wrapper">
        <article-card v-for="article in articles" :key="article.id" :item="article"></article-card>
      </div>
    </div>
  </div>
</template>