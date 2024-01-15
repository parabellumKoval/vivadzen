<script setup>
import {useBrandFaker} from '~/composables/fakers/useBrandFaker.ts'

const {t} = useI18n()
const props = defineProps({})

const breadcrumbs = [
  {
    name: t('title.home'),
    item: '/'
  },{
    name: t('title.blog'),
    item: '/blog'
  }
]

const search = ref(null)

// COMPUTEDS
const alphaList = computed(() => {
  return [
    '0-9',
    'A',
    'B',
    'C',
    'D',
    'E',
    'R',
    'T',
    'Y',
    'U',
  ]
})

const alphaList2 = computed(() => {
  return [
    'A',
    'B',
    'C',
    'D',
    'E',
    'R',
    'T',
    'Y',
    'U',
    'O',
    'P',
    'F',
    'A',
    'S',
    'C',
  ]
})

const popular = computed(() => {
  return [
    {
      id: 1,
      image: '/images/categories/category-1.png'
    },{
      id: 2,
      image: '/images/categories/category-2.png'
    }
  ]
})


const brands = computed(() => {
  return useBrandFaker().groups
})
// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='./brands.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="page-base">
    <div class="container">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <div class="title-common">Бренды</div>

      <div>
        <form-text v-model="search" placeholder="Найти бренд по названию" class="search-input">
          <template #right>
            <IconCSS name="iconoir:search"></IconCSS>
          </template>
        </form-text>

        <ul class="alpha-list">
          <li v-for="item in alphaList" :key="item" class="alpha-item">
            <NuxtLink to="/" class="alpha-link">{{ item }}</NuxtLink>
          </li>
        </ul>

        <ul class="alpha-list">
          <li v-for="item in alphaList2" :key="item" class="alpha-item">
            <NuxtLink to="/" class="alpha-link">{{ item }}</NuxtLink>
          </li>
        </ul>
      </div>

      <div class="brand-box">
        <div class="title-secondary">Популярные бренды</div>
        <div class="popular">
          <NuxtLink v-for="item in popular" :key="item.id" :to="localePath('/')" class="popular-item">
            <nuxt-img
              :src = "item.image"
              width="254"
              height="150"
              sizes = "mobile:300px tablet:300px desktop:300px"
              format = "webp"
              quality = "60"
              loading = "lazy"
              fit="outside"
              class="popular-image"
            />
          </NuxtLink>
        </div>
      </div>

      <div class="brand-box">
        <div v-for="(brand, alpha) in brands" :key="alpha" class="brand-group">
          <div class="brand-alpha">
            <IconCSS name="iconoir:hashtag" class="brand-alpha-icon"></IconCSS>
            <span class="brand-alpha-value title-secondary">{{ alpha }}</span>
          </div>
          <ul class="brand-list">
            <li v-for="item in brand" :key="item.id" class="brand-item">
              <NuxtLink :to="localePath('/' + item.slug)" class="brand-link">{{ item.name }}</NuxtLink>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>