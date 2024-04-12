<script setup>
import {useCatalog} from '~/composables/product/useCatalog.ts'
import {useFetchReview} from '~/composables/review/useFetchReview.ts'
import {useCategoryStore} from '~/store/category'

const {t} = useI18n()
const route = useRoute()

// Attributes
const attributes = ref([])

// Filters
const filters = ref(null)

// Products
const products = ref([])
const meta = ref(null)

// Reviews
const reviews = ref([])
const reviewsMeta = ref({})

// Category
const category = ref({})

// Query
const queryObject = ref({
  order: null,
  filters: null,
  page: 1
})

const pending = ref(false)
const isLoading = ref(true)

const breadcrumbs = ref([])

// COMPUTED
const nextPage = computed(() => {
  return meta.value && meta.value.current_page !== meta.value.last_page
})

const slug = computed(() => {
  // console.log('route.path', route.path, route.path.substring(route.path.lastIndexOf('/') + 1))
  return route.path.substring(route.path.lastIndexOf('/') + 1) || null 
})

const query = computed(() => {
  let query = {
    per_page: 20,
    page: queryObject.value.page || 1
  }

  if(queryObject.value.filters) {
    query.attrs = queryObject.value.filters
  }

  if(queryObject.value.order) {
    query = {
      ...query,
      ...queryObject.value.order
    }
  }

  if(slug.value) {
    query.category_slug = slug.value
  }

  return query
})

// WATCH
watch(() => meta.value, (v) => {
  if(v.current_page === 1){
    useRouter().replace()
  }else {
    useRouter().replace({ query: {page: v.current_page} })
  }
}, {
  deep: true
})

// HANDLERS

// const loadmoreHandler = async() => {
//   meta.value.current_page++

//   const queryObject = {
//     ...query.value,
//     page: meta.value.current_page
//   }

//   getProducts(queryObject, false)
// }

const updateQueryHandler = async (key = null, v = null) => {
  if(key === 'filters') {
    queryObject.value.page = 1
    queryObject.value.filters = useCatalog().prepareAttrs(v)
  }
  
  if(key === 'order') {
    queryObject.value.order = v;
  }

  if(key === 'page') {
    queryObject.value.page = v;
  }

  ({products: products.value, meta: meta.value, filters: filters.value} = await useCatalog().getProducts(query.value, true).finally(() => {}))
}

// METHODS
const getQuery = (filters = null, order = null) => {
  let query = {
    per_page: 20,
    page: route.query.page || 1
  }

  if(slug.value)
    query.category_slug = slug.value

  if(filters) {
    query.attrs = useCatalog().prepareAttrs(filters)
  }

  if(order) {
    query = {
      ...query,
      ...order
    }
  }

  return query
}

const setCrumbs = () => {
  breadcrumbs.value = [
    {
      name: t('title.home'),
      item: '/'
    },{
      name: category.value?.name,
      item: `/${category.value?.slug}`
    }
  ]
}

const getCategory = async (query) => {
  return useAsyncData(`categories`, () => useCategoryStore().show(slug.value)).then(({data, error}) => {
    
    if(data.value) {
      category.value = data.value
      setCrumbs()
    }else {
      throw createError({ statusCode: 404, message: 'Page Not Found' })
    }

  }).finally(() => {
  })
}

// HOOKS
({attributes: attributes.value} = await useCatalog().getAttributes(getQuery(), true).finally(() => {}));

({products: products.value, meta: meta.value, filters: filters.value} = await useCatalog().getProducts(getQuery(), true).finally(() => {}));

await getCategory()

await useFetchReview().getReviews({
  per_page: 6,
  category_slug: slug.value,
  resource: 'large'
}, true).then(({reviews: r, meta: m}) => {
  reviews.value = r
  reviewsMeta.value = m
})
</script>

<style src="./category.scss" lang="scss" scoped></style>

<template>
  <NuxtLayout
    name="catalog"
    :breadcrumbs="breadcrumbs"
    :filters="attributes"
    :filters-meta="filters"
    :products="products"
    :meta="meta"
    @update:filters="(v) => updateQueryHandler('filters', v)"
    @update:order="(v) => updateQueryHandler('order', v)"
    @update:page="(v) => updateQueryHandler('page', v)"
  >

    <template #title>
      {{ category.name }}
    </template>

    <template #footer> 
      <div v-if="reviewsMeta?.total" class="review">
        <div class="title-secondary review-title">Отзывы о {{ category.name }}</div>
        <div class="review-header">
          <simple-stars :amount="reviewsMeta?.rating_avg || 0"></simple-stars>
          <div class="review-count">{{ reviewsMeta?.rating_count || 0 }} оценок и {{ reviewsMeta?.total || 0 }} отзывов</div>
        </div>
        <review-product v-for="review in reviews" :key="review.id" :item="review" type="mini" class="review-item"></review-product>
      </div>

      <div v-if="category.content" class="seo-text rich-text" v-html="category.content"></div>

      <section-faq class="faq-section"></section-faq>
    </template>

  </NuxtLayout>
</template>