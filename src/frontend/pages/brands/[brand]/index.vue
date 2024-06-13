<script setup>
import {useFilter} from './../../composables/product/useFilter'
// import {useCatalog} from './../../../composables/product/useCatalog'
// import {useBrandStore} from './../../../store/brand'
// import {useFilter} from '~~/composables/product/useFilter'
import {useCatalog} from '~~/composables/product/useCatalog'
import {useBrandStore} from '~~/store/brand'

const {t} = useI18n()
const props = defineProps({})
const route = useRoute()

const {getAttributes, prepareFilters, prepareAttrs} = useFilter()
const {getProducts} = useCatalog()

// Attributes
const attributes = ref([])

// Filters
const filtersMeta = ref(null)
const filtersMetaInit = ref(null)

// Products
const products = ref([])
const meta = ref(null)

// Query
const queryObject = ref({
  order: null,
  filters: null,
  price: null,
  selections: null,
  page: 1
})

// Brand
const brand = ref(null)

const pending = ref(false)
const isLoading = ref(true)

const breadcrumbs = ref([])

// COMPUTEDS
const slug = computed(() => {
  return route?.params?.brand || null
})

const query = computed(() => {
  let query = {
    per_page: 20,
    page: queryObject.value.page || 1,
    brand_slug: slug.value
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

// METHODS
const setSeo = () => {
  useHead({
    title: brand.value.seo.meta_title || t('seo_title_template', {brand: brand.value.name}),
    meta: [
      {
        name: 'description',
        content: brand.value.seo.meta_description || t('seo_desc_template', {brand: brand.value.name})
      },
    ],
  })
}

const setCrumbs = () => {
  breadcrumbs.value = [
    {
      name: t('title.home'),
      item: '/'
    },{
      name: t('title.brands'),
      item: '/brands'
    },{
      name: brand.value?.name,
      item: `/brands/${brand.value?.slug}`
    }
  ]
}

const getQuery = () => {
  const query = {
    per_page: 20,
    page: route.query.page || 1,
    brand_slug: slug.value
  }
  return query
}

// HANDLERS
const updateFiltersHandler = async (v) => {
  queryObject.value.page = 1;

  (
    {
      filters: queryObject.value.filters,
      brands: queryObject.value.brands,
      selections: queryObject.value.selections,
      price: queryObject.value.price,
    } = prepareAttrs(v)
  );

  await updateQueryHandler()
}

const updateOrderHandler = (v) => {
  queryObject.value.order = v;
  updateQueryHandler()
}

const updatePageHandler = (v) => {
  queryObject.value.page = v;
  updateQueryHandler()
}

const updateQueryHandler = async (v = null) => {
  pending.value = true;

  ({
    products: products.value,
    meta: meta.value,
    filters: filtersMeta.value
  } = await getProducts(query.value).finally(() => {
    pending.value = false
  }))
}

// WATCHERS

// HOOKS
await useAsyncData('brand_attributes-'+slug.value, async () => getAttributes(getQuery())).then(({data}) => {
  if(data?.value) {
    attributes.value = prepareFilters(data.value)
  }
});

({
  products: products.value,
  meta: meta.value,
  filters: filtersMetaInit.value
} = await getProducts(getQuery(), 'brand-products-' + slug.value).finally(() => {}));

await useAsyncData(`brand-${slug.value}`, () => useBrandStore().show(slug.value)).then(({data}) => {
  if(data.value) {
    brand.value = data.value
    setCrumbs()
  }
});


onServerPrefetch(() => {
  // setSchema(props.product, reviews.value)
  setSeo()
})
</script>

<style src='./brand.scss' lang='scss' scoped></style>
<i18n src='./lang.yaml' lang='yaml'></i18n>

<template>
  <NuxtLayout
    name="catalog"
    :breadcrumbs="breadcrumbs"
    :filters="attributes"
    :filters-meta="filtersMeta"
    :filters-meta-init="filtersMetaInit"
    :products="products"
    :meta="meta"
    :pending="pending"
    :updateFiltersCallback = "updateFiltersHandler"
    :updateOrderCallback = "updateOrderHandler"
    :updatePageCallback = "updatePageHandler"
  >
    <template #title>
      <template v-if="brand.seo.h1">
        {{ brand.seo.h1 }}
      </template>
      <template v-else>
        {{ t('company_prod') }} {{ brand.name }}
      </template>
    </template>

    <template #header>
      <div v-if="brand.content || brand.image" class="container">
        <div class="brand-content">
          <div></div>
          <catalog-brand :item="brand"></catalog-brand>
        </div>
      </div>
    </template>
  </NuxtLayout>
</template>