<script setup>
import {useFilter} from '~/composables/product/useFilter.ts'
import {useCatalog} from '~/composables/product/useCatalog.ts'
import {useFetchReview} from '~/composables/review/useFetchReview.ts'
import {useCategoryStore} from '~/store/category'

const {t} = useI18n()
const route = useRoute()

const {getFilters, prepareAttrs} = useFilter()
const {getProducts} = useCatalog()

// Attributes
const attributes = ref([])

// Brands
const brands = ref([])

// Filters
const filtersMeta = ref(null)
const filtersMetaInit = ref(null)

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
  brands: null,
  price: null,
  selections: null,
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

  if(queryObject.value.brands) {
    query.brands = queryObject.value.brands
  }

  if(queryObject.value.selections) {
    query.selections = queryObject.value.selections
  }

  if(queryObject.value.filters) {
    query.attrs = queryObject.value.filters
  }

  if(queryObject.value.price) {
    query.price = queryObject.value.price
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

    queryObject.value.page = 1;

    console.log('updateQueryHandler', queryObject.value);
    (
      {
        filters: queryObject.value.filters,
        brands: queryObject.value.brands,
        selections: queryObject.value.selections,
        price: queryObject.value.price,
      } = prepareAttrs(v)
    );
  }
  
  if(key === 'order') {
    queryObject.value.order = v;
  }

  if(key === 'page') {
    queryObject.value.page = v;
  }

  ({
    products: products.value,
    meta: meta.value,
    filters: filtersMeta.value
  } = await getProducts(query.value).finally(() => {}))
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
    query.attrs = prepareAttrs(filters)
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
attributes.value = await getFilters(getQuery()).then(({data}) => {
  return data.value || []
});

// ({brands: brands.value} = await useAsyncData('brands', () => useCatalog().getBrands(getQuery()))
// .then((r) => {
//   console.log('brands response  -- ', r);
//   return r
// }).finally(() => {}));

// ({attributes: attributes.value} = await useCatalog().getAttributes(getQuery())
// .then((r) => {
//   return r
// }).finally(() => {}));

({products: products.value, meta: meta.value, filters: filtersMetaInit.value} = await getProducts(getQuery())
.then((r) => {
  return r
}).finally(() => {}));

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
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <NuxtLayout
    name="catalog"
    :breadcrumbs="breadcrumbs"
    :brands="brands"
    :filters="attributes"
    :filters-meta="filtersMeta"
    :filters-meta-init="filtersMetaInit"
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
        <div class="title-secondary review-title">{{ t('review') }} {{ category.name }}</div>
        <div class="review-header">
          <simple-stars :amount="reviewsMeta?.rating_avg || 0"></simple-stars>
          <div class="review-count">
            {{ t('messages.rates_reviews', {rates: (reviewsMeta?.rating_count || 0),reviews: (reviewsMeta?.total || 0)}) }}
          </div>
        </div>
        <review-product v-for="review in reviews" :key="review.id" :item="review" type="mini" class="review-item"></review-product>
      </div>

      <div v-if="category.content" class="seo-text rich-text" v-html="category.content"></div>

      <section-faq class="faq-section"></section-faq>
    </template>

  </NuxtLayout>
</template>