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
const article = computed(() => {
  return useArticleFaker()(1)[0]
})

const tableOfContents = computed(() => {
  return [
    {
      id: 1,
      title: 'Наиболее распространенные глазные болезни'
    },{
      id: 2,
      title: 'Как выбрать витамины для зрения'
    },{
      id: 3,
      title: 'ТОП-10 витаминных комплексов и бад для глаз'
    },{
      id: 4,
      title: 'Факторы защиты макулы Jarrow Formulas (Macula Protective Factors) 30 капсул'
    }
  ]
})

const content = computed(() => {
  return 'Наши глаза - это путь ко всем краскам окружающего мира, но к сожалению с возрастом, в условиях тяжелого труда, длительного нахождения перед экранами всевозможных гаджетов, зрения может ухудшаться и затруднять повседневные привычные дела, влиять на психоэмоциональное состояние и физическое здоровье. Заболеваний глаз предостаточно, но наиболее распространенными считаются:'
})
// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='./article.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="page-base">
    <div class="container">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <div class="title-common">{{ article.title }}</div>

      <div class="article-wrapper">
        <div class="article-content">

          <div class="article-meta">
            <div class="article-meta-box">
              <div class="article-time">
                <IconCSS name="iconoir:clock" class="article-comments-icon"></IconCSS>
                <span>{{ article.time }} мин. чтения</span>
              </div>

              <div class="article-comments">
                <IconCSS name="iconoir:message" class="article-comments-icon"></IconCSS>
                <button :class="{active: false}" class="text-link article-comments-text">
                  <span>0 комментариев</span>
                </button>
              </div>
            </div>

            <article-share class="article-share"></article-share>
          </div>

          <div class="article-header">
            
            <nuxt-img
              v-if="article.image"
              :src = "article.image.src"
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
          <div class="article-text rich-text" v-html="content"></div>
        </div>
        <div class="article-aside">
          <div class="article-aside-title">Содержание</div>
          <ol class="article-aside-list">
            <li v-for="item in tableOfContents" :key="item.id">
              <a href="#"  class="article-aside-link">{{ item.title }}</a>
            </li>
          </ol>
        </div>
      </div>
    </div>
  </div>
</template>