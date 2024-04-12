<script setup>
// import {useProductFaker} from '~/composables/fakers/useProductFaker.ts'
import { useProductStore } from '~/store/product'

const products = ref([])
const options = ref({
  card: {
    width: {
      mobile: '180px',
      desktop: '285px'
    }
  }
})

await useAsyncData('products-main', () => useProductStore().index({
  per_page: 10
})).then(({data}) => {
  if(data?.value?.products) {
    products.value = data.value.products
  }
})

const productCardComponent = computed(() => {
  // if(props.pending)
  //   return resolveComponent('productCardSkeleton')
  // else
  //   return resolveComponent('productCard')
  // return resolveComponent('ProductCard')
})
// console.log(products.value)
const productCard = resolveComponent('ProductCard')
</script>

<style src="./top-products.scss" lang="scss" scoped></style>

<template>
  <section class="main-section">
    <div class="section-title">топ продажу</div>

    <div v-if="products && products.length">
      <section-snap-slider
        :items="products"
        :component="productCard"
        :gutter="0"
        :options="options"
        :title="$t('button.view_all')"
        link="/shop"
        item-data-name="item"
      >
      </section-snap-slider>
    </div>
  </section>
</template>