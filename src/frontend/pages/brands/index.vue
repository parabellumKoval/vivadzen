<script setup>
import {useBrandStore} from '~/store/brand'

const { scrollToAnchor } = useAnchorScroll({
  toAnchor: {
    scrollOptions: {
      behavior: 'smooth',
      offsetTop: -90,
    }
  },
})

const {t} = useI18n()

const brands_grouped = ref([])
const breadcrumbs = [
  {
    name: t('title.home'),
    item: '/'
  },{
    name: t('title.brands'),
    item: '/brands'
  }
]

const search = ref(null)

// COMPUTEDS
const alphaList = computed(() => {
  let list = []

  if(brands_grouped.value[1]) {
    list = [
      ...Object.keys(brands_grouped.value[1])
    ]
  }

  if(brands_grouped.value[0]) {
    list = [
      ...list,
      ...Object.keys(brands_grouped.value[0])
    ]
  }

  return list
})

const alphaList2 = computed(() => {
  let list = []

  if(brands_grouped.value[2]) {
    list = [
      ...Object.keys(brands_grouped.value[2])
    ]
  }

  return list
})

const numbersList = computed(() => {
  if(!brands_grouped.value[1])
    return []

  if(search.value) {
    return filterValues(brands_grouped.value[1])
  }else {
    return brands_grouped.value[1]
  }

})

const latinList = computed(() => {
  if(!brands_grouped.value[2])
    return []

  if(search.value) {
    return filterValues(brands_grouped.value[2])
  }else {
    return brands_grouped.value[2]
  }
  
})

const cyrilicList = computed(() => {
  if(!brands_grouped.value[0])
    return []

  if(search.value) {
    return filterValues(brands_grouped.value[0])
  }else {
    return brands_grouped.value[0]
  }
})

const brands = computed(() => {

  let values = {
    ...numbersList.value,
    ...cyrilicList.value,
    ...latinList.value
  }

  return values
})

const populars = computed(() => {

  return Object.values(brands.value).reduce((prev, item) => {
    return [
      ...prev,
      ...item
    ]
  }, []).filter(item => item?.extras?.is_popular == 1)
})

// METHODS
const filterValues = (values) => {
  let filteredValues = {}

  for (const [key, value] of Object.entries(values)) {
    let list = value.filter((item) =>  {
      return item.name.search(search.value) !== -1
    })

    if(list && list.length) {
      filteredValues[key] = list
    }
  }

  return filteredValues
}

// HANDLERS
const scrollHandler = (item) => {
  search.value = null

  nextTick(() => {
    scrollToAnchor(item)
  });
}

// WATCHERS

await useAsyncData('brands', () => useBrandStore().index({alpha_grouped: 1})).then(({data}) => {
  if(data.value) {
    brands_grouped.value = data.value
  }

  console.log('brand page', data.value)
})
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
            <button @click="scrollToAnchor('brand-box')">
              <IconCSS name="iconoir:search"></IconCSS>
            </button>
          </template>
        </form-text>

        <ul class="alpha-list">
          <li v-for="item in alphaList" :key="item" class="alpha-item">
            <button
              @click="scrollHandler(item)"
              class="alpha-link"
            >{{ item }}</button>
          </li>
        </ul>

        <ul class="alpha-list">
          <li v-for="item in alphaList2" :key="item" class="alpha-item">
            <button
              @click="scrollHandler(item)"
              class="alpha-link"
            >{{ item }}</button>
          </li>
        </ul>
      </div>

      <div v-if="populars?.length" class="brand-box">
        <div class="title-secondary">Популярные бренды</div>
        <div class="popular">
          <NuxtLink v-for="item in populars" :key="item.id" :to="localePath('/brands/' + item.slug)" class="popular-item">
            <nuxt-img
              :src = "item.image.src"
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

      <div id="brand-box" class="brand-box">
        <div v-for="(brand, alpha) in brands" :key="alpha" class="brand-group">
          <div :id="alpha" class="brand-alpha">
            <IconCSS name="iconoir:hashtag" class="brand-alpha-icon"></IconCSS>
            <span class="brand-alpha-value title-secondary">{{ alpha }}</span>
          </div>
          <ul class="brand-list">
            <li v-for="item in brand" :key="item.id" class="brand-item">
              <NuxtLink :to="localePath('/brands/' + item.slug)" class="brand-link">{{ item.name }}</NuxtLink>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>