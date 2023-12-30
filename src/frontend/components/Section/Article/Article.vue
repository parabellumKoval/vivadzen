<script setup>
import {useArticleFaker} from '~/composables/fakers/useArticleFaker.ts'

const articles = computed(() => {
  return useArticleFaker()(4)
})
</script>

<style src="./article.scss" lang="scss" scoped></style>

<template>
  <section class="main-section">
    <div class="section-title">Статьи</div>
    <div class="articles-wrapper">
      <article-card v-for="article in articles" :key="article.id" :item="article"></article-card>
    </div>
  </section>
</template>