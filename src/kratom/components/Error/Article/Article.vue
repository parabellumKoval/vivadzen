<script setup>
import {useArticleStore} from '~/store/article'

const {t} = useI18n()
const regionPath = useToLocalePath()

const props = defineProps({
  error: {
    type: Object
  },
  message: {
    type: String,
    default: ''
  }
})

// Fetch random articles for suggestions
const articles = ref([])

const {data: articlesData} = await useAsyncData('error-page-articles', () => 
  useArticleStore().index({per_page: 8})
)

if (articlesData.value?.data) {
  articles.value = articlesData.value.data
}

const blogPath = computed(() => {
  return regionPath('/blog')
})

const messageText = computed(() => {
  return props.message || t('error.article_not_found')
})
</script>

<style src="./article.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <div class="error error-article">
    <div class="status">{{ error?.statusCode || 404 }}</div>
    <div class="text">{{ messageText }}</div>
    
    <NuxtLink :to="blogPath" class="button primary action-btn">
      <IconCSS name="iconoir:book" size="20px" class="icon"></IconCSS>
      <span>{{ t('view_all_articles') }}</span>
    </NuxtLink>
    
    <div v-if="articles.length" class="error-articles">
      <div class="error-articles-title">{{ t('article_suggestions') }}</div>
      <div class="error-articles-wrapper">
        <article-card 
          v-for="(article, index) in articles" 
          :key="article.id" 
          :index="index" 
          :item="article" 
          class="error-articles-item"
        ></article-card>
      </div>
    </div>
  </div>
</template>
