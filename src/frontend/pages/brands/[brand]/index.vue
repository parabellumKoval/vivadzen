<script setup>
import {useCatalog} from '~/composables/product/useCatalog.ts'
import {useBrandStore} from '~/store/brand'

const {t} = useI18n()
const props = defineProps({})
const route = useRoute()


// Attributes
const attributes = ref([])

// Filters
const filters = ref(null)

// Products
const products = ref([])
const meta = ref(null)

// Brand
const brand = ref(null)

const pending = ref(false)
const isLoading = ref(true)

const breadcrumbs = ref([])

// COMPUTEDS
const slug = computed(() => {
  return route?.params?.brand || null
})

// METHODS
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

// WATCHERS

// HOOKS
({attributes: attributes.value} = await useCatalog().getAttributes(getQuery(), true).finally(() => {}));

({products: products.value, meta: meta.value, filters: filters.value} = await useCatalog().getProducts(getQuery(), true).finally(() => {}));

await useAsyncData(`brand-${slug.value}`, () => useBrandStore().show(slug.value)).then(({data}) => {
  if(data.value) {
    brand.value = data.value
    setCrumbs()
  }
})
</script>

<style src='./brand.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <NuxtLayout
    name="catalog"
    :breadcrumbs="breadcrumbs"
    :filters="attributes"
    :filters-meta="filters"
    :products="products"
    :meta="meta"
  >
    <template #title>
      Продукция компании {{ brand.name }}
    </template>

    <template #header>
      <div class="container">
        <div class="brand-content">
          <div></div>
          <catalog-brand :item="brand"></catalog-brand>
        </div>
      </div>
    </template>
  </NuxtLayout>
</template>