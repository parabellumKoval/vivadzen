<script setup>
import {useArticleStore} from '~/store/article'

const {t} = useI18n()
const route = useRoute()

const html = ref(null)

const tableOfContents = ref([])
const article = ref(null)
const articles = ref([])

const breadcrumbs = ref(null)

const { scrollToAnchor } = useAnchorScroll({
  toAnchor: {
    scrollOptions: {
      behavior: 'smooth',
      offsetTop: -90,
    }
  },
})

// COMPUTEDS
const slug = computed(() => {
  return route.params.blog
})

const image = computed(() => {
  if(article.value.image) {
    return '/server/' + article.value.image
  } else {
    return null
  }
})

// METHODS
const setCrumbs = () => {
  breadcrumbs.value = [
    {
      name: t('title.home'),
      item: '/'
    },{
      name: t('title.blog'),
      item: '/blog'
    },{
      name: article.value.title,
      item: article.value.slug
    }
  ]
}

const scrollHandler = (item) => {
  nextTick(() => {
    scrollToAnchor(item)
  });
}

const setSeo = () => {
  useHead({
    title: article.value.seo.meta_title || t('seo_title', {title: article.value.title}),
    meta: [
      {
        name: 'description',
        content: article.value.seo.meta_description
      },
    ],
  })
}

// HANDLERS
// WATCHERS
watch(() => html.value, (v) => {
  if(v) {
    // clear headers
    tableOfContents.value = []
    
    const headers = v.querySelectorAll('h2, h3')
    
    headers.forEach((item, i) => {
      const index = i + 1
      const id = 'header-' + index
      const tagName = item.tagName
      const headerText = item.innerText || item.textContent
      
      if(headerText) {
        tableOfContents.value.push({
          id: id,
          title: headerText,
          tag: tagName
        })
      }

      item.setAttribute("id", id);
    })
  }
}, {
  immediate: true
})


await useAsyncData('get-article', () => useArticleStore().show(slug.value)).then(({data, error}) => {
  if(data.value) {
    article.value = data.value
    setCrumbs()
  }
})

//
onServerPrefetch(() => {
  setSeo()
})
</script>

<style src='./article.scss' lang='scss' scoped></style>
<i18n src='./lang.yaml' lang='yaml'></i18n>

<template>
  <div class="page-base">
    <div v-if="article" class="container">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <div class="title-common">{{ article.title }}</div>

      <div class="article-wrapper">
        <div class="article-content">

          <div class="article-meta">
            <div class="article-meta-box">
              <div class="article-time">
                <IconCSS name="iconoir:clock" class="article-comments-icon"></IconCSS>
                <span>{{ parseFloat(article.time).toFixed(0) }} {{ t('label.min_read') }}</span>
              </div>

              <div class="article-comments">
                <IconCSS name="iconoir:message" class="article-comments-icon"></IconCSS>
                <button :class="{active: false}" class="text-link article-comments-text">
                  <span>0 {{ t('comments') }}</span>
                </button>
              </div>
            </div>

            <article-share class="article-share"></article-share>
          </div>

          <div class="article-header">
            
            <nuxt-img
              v-if="image"
              :src = "image"
              width="800"
              height="400"
              sizes = "mobile:100vw tablet:100vw desktop:800px"
              format = "webp"
              quality = "60"
              loading = "lazy"
              fit="outside"
              class="article-image"
            />
          </div>
          <div class="article-text rich-text" ref="html" v-html="article.content"></div>
        </div>
        
        <div class="article-aside">
          <div class="article-aside-title">{{ t('content') }}</div>
          <ol class="article-aside-list">
            <li
              v-for="item in tableOfContents"
              :key="item.id"
              :class="item.tag"
              class="article-aside-item"
            >
              <button
                @click="scrollHandler(item.id)"
                :class="item.tag"
                class="article-aside-link"
              >{{ item.title }}</button>
            </li>
          </ol>
        </div>
        
      </div>

      <section-article></section-article>
    </div>
  </div>
</template>