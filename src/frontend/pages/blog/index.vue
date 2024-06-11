<script setup>
import {useArticleStore} from '~/store/article'

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

const articles = ref([])
const articlesMeta = ref(null)
const isLoading = ref(false)

// COMPUTEDS
// METHODS
const setSeo = () => {
  useHead({
    title: t('seo_title'),
    meta: [
      {
        name: 'description',
        content: t('seo_desc')
      },
    ],
  })
}

// HANDLERS
const loadmoreHandler = async () => {
  let nextPage = articlesMeta.value.current_page + 1
  isLoading.value = true

  await useArticleStore().index({per_page: 12, page: nextPage}).then(({data, meta}) => {

    if(data) {
      articles.value = [
        ...articles.value,
        ...data
      ]
    }

    if(meta) {
      articlesMeta.value = meta
    }

  }).finally(() => {
    isLoading.value = false
  })
}

// WATCHERS
await useAsyncData('get-articles', () => useArticleStore().index({per_page: 12})).then(({data, error}) => {
  if(data.value) {
    articles.value = data.value.data
    articlesMeta.value = data.value.meta
  }
})


//
onServerPrefetch(() => {
  setSeo()
})
</script>

<style src='./blog.scss' lang='scss' scoped></style>
<i18n src='./lang.yaml' lang='yaml'></i18n>

<template>
  <div class="page-base">
    <div class="container">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <div class="title-common">{{ t('title.blog') }}</div>

      <div v-if="articles" class="articles-wrapper">
        <article-card v-for="article in articles" :key="article.id" :item="article"></article-card>
      </div>

      <div v-if="articlesMeta && articlesMeta.current_page !== articlesMeta.last_page" @click="loadmoreHandler" class="load-more-wrapper">
        <button :class="{loading: isLoading}" class="button secondary can-loading">
          <span>{{ t('button.load_more') }}</span>
        </button>
      </div>

    </div>
  </div>
</template>