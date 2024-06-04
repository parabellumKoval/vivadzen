<script setup>
import {useArticleStore} from '~/store/article'

const {t} = useI18n()
const articles = ref([])

useAsyncData('get-4-articles', () => useArticleStore().index({per_page: 4})).then(({data, error}) => {
  if(data.value) {
    articles.value = data.value.data
  }
})
</script>

<style src="./article.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <section class="main-section">
    <div class="section-title">{{ t('title.articles') }}</div>
    <div v-if="articles" class="articles-wrapper">
      <article-card v-for="article in articles" :key="article.id" :item="article"></article-card>
    </div>
    <div class="footer-wrapper">
      <NuxtLink :to="localePath('/blog')" class="button secondary">{{ t('articles') }}</NuxtLink>
    </div>
  </section>
</template>