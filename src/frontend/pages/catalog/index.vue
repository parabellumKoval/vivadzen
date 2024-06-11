<script setup>
import {useFilter} from '~/composables/product/useFilter.ts'
import {useFilterItem} from '~/composables/product/useFilterItem.ts'
import {useCategoryStore} from '~/store/category'

const {t} = useI18n()

const route = useRoute()

const setCrumbs = () => {
  breadcrumbs.value = [
    {
      name: t('title.home'),
      item: '/'
    },{
      name: t('title.catalog'),
      item: '/catalog'
    }
  ]
}

const {getFilters} = useFilter()
const {setFilters} = useFilterItem()

// REFS
// Attributes
const attributes = ref([])
const breadcrumbs = ref([])

// COMPUTEDS
const categories = computed(() => {
  return useCategoryStore().list
})

// METHODS
const getQuery = () => {
  let query = {
    per_page: 20,
    page: route.query.page || 1
  }

  // Set filters from URL
  if(route.query) {
    query.selections = setFilters(route.query)
  }

  return query
}

// HOOKS
attributes.value = await getFilters(getQuery(), false).then(({data}) => {
  return data.value || []
});

setCrumbs()
</script>

<!-- <style src="./category.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n> -->

<template>
  <NuxtLayout
    name="category"
    :breadcrumbs="breadcrumbs"
    :attributes="attributes"
    :initQuery="getQuery()"
  >
    <template #title>
      {{ t('title.catalog') }}
    </template>

    <template #header>
      <catalog-categories :categories="categories"></catalog-categories>
    </template>
  </NuxtLayout>
</template>