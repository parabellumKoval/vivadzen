<script setup>
import {useSearchStore} from '~/store/search'

const {t} = useI18n()
const route = useRoute()

const searchInput = ref(null)

// Attributes
const attributes = ref([])

// Filters
const filters = ref(null)

// Products
const products = ref(null)
const meta = ref(null)

// Categories
const categories = ref([])

// Brands
const brands = ref([])

const pending = ref(false)
const isLoading = ref(true)

// Breadcrumbs
const breadcrumbs = ref(null)

// COMPUTEDS
const search = computed(() => {
  return route.query.q
})

// METHODS
const setCrumbs = (title) => {
  breadcrumbs.value = [
    {
      name: t('title.home'),
      item: '/'
    },{
      name: title,
      item: '/search'
    }
  ]
}

const getQuery = () => {
  return {
    per_page: 20,
    page: 1
  }
}


const setAttributes = () => {
  // attributes.value = []
  if(categories.value) {
    attributes.value.push(
      {
        id: 'categories',
        name: 'Категории',
        type: 'tree',
        noMeta: true,
        isOpen: true,
        values: [...categories.value]
      }
    )
  }

  if(brands.value) {
    attributes.value.push(
      {
        id: 'brands',
        name: 'Бренды',
        type: 'list',
        noMeta: true,
        isOpen: true,
        values: [...brands.value]
      }
    )

  }
}

const fetchSearch = async (search) => {

  const params = {
    search: search
  }

  if(!params.search?.length) {
    return
  }

  isLoading.value = true

  await useAsyncData('search', () => useSearchStore().index(params)).then(({data, error}) => {

    if(data?.value?.products) {
      products.value = data.value.products.data || null
      meta.value = data.value.products.meta || null
    }

  }).finally(() => {
    isLoading.value = false
  })
}

// HANDLERS
const searchHandler = () => {
  useRouter().replace({
    query: {
      q: searchInput.value
    }
  })
}

// WATCH
watch(search, (v) => {
  searchInput.value = v
  if(v) {
    setCrumbs(t('title.search_results') + ` «${v}»`)
    fetchSearch(v)
  }else {
    setCrumbs(t('title.search'))
  }
}, {
  immediate: false
})

await fetchSearch(search.value)
</script>

<style src="./search.scss" lang="scss" scoped></style>

<template>

<NuxtLayout
  name="catalog"
  :breadcrumbs="breadcrumbs"
  :products="products"
  :meta="meta"
  no-filters
>
  <template #title>
    <template v-if="search">
      {{ t('title.search_results') }} «<span class="title-search">{{ search }}</span>»
    </template>
    <template v-else>
      {{ t('title.search') }}
    </template>
  </template>

  <template #header>
    <div class="container">
      <simple-search
        v-model="searchInput"
        @keyup.enter="searchHandler"
        @btn:click="searchHandler"
        class="simple-search"
      ></simple-search>
    </div>
  </template>
</NuxtLayout>
</template>