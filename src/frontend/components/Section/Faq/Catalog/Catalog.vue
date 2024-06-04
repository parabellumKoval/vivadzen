<script setup>
import {useCatalog} from '~/composables/product/useCatalog.ts'
const {t} = useI18n()

const props = defineProps({
  category: {
    type: Object
  }
})

const {getProducts} = useCatalog()
const chipProducts = ref([])
const popularProducts = ref([])

// COMPUTEDS
const faq = computed(() =>{
  let list = []

  if(popularProducts.value?.length) {
    list.push({
      id: 1,
      q: t('popular_q', {category: props.category?.name?.toLowerCase()}),
      a: [
        t('popular_a', {category: props.category?.name?.toLowerCase()}) + ':',
        popularProducts.value
      ]
    })
  }

  list.push({
      id: 2,
      q: t('delivery_q', {category: props.category?.name?.toLowerCase()}),
      a: [
        t('delivery_a', {category: props.category?.name?.toLowerCase()}) + ':',
        [
          t('delivery_cond1'),
          t('delivery_cond2'),
          t('delivery_cond3')
        ]
      ]
    })

  if(chipProducts.value?.length) {
    list.push({
      id: 3,
      q: t('chip_q', {category: props.category?.name?.toLowerCase()}),
      a: [
        t('chip_a', {category: props.category?.name?.toLowerCase()}) + ':',
        chipProducts.value
      ]
    })
  }

  return list
})

// METHODS
const getChipProducts = async () => {
  const query = {
    category_id: props.category.id,
    price: [5, 100000000],
    order_by: 'price',
    order_dir: 'ASC',
    selections: ['in_stock'],
    per_page: 5,
    with_filters: 0
  }

  await getProducts(query, 'chip_products', false)
  .then((data) => {
    chipProducts.value = data.products
  })

}


const getPopularProducts = async () => {
  const query = {
    category_id: props.category.id,
    order_by: 'sales',
    order_dir: 'DESC',
    selections: ['in_stock'],
    per_page: 5,
    with_filters: 0
  }

  await getProducts(query, 'popular_products', false)
  .then((data) => {
    popularProducts.value = data.products
  })

}
// HANDLERS
// WATCHERS

await getChipProducts()
await getPopularProducts()
</script>

<!-- <style src='' lang='scss' scoped></style> -->
<i18n src='./lang.yaml' lang='yaml'></i18n>

<template>
  <section-faq :items="faq"></section-faq>
</template>