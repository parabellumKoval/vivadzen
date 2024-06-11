<script setup>
import { useProductStore } from '~/store/product'

const props = defineProps({
  title: {
    type: String,
    default: "Products"
  },
  query: {
    type: Object
  },
  link: {
    type: String,
    default: ''
  }
})

const {t} = useI18n()

const products = ref([])
const options = ref({
  card: {
    width: {
      mobile: '180px',
      desktop: '285px'
    }
  }
})

await useAsyncData('products-main-' + props.title, () => useProductStore().index(props.query)).then(({data}) => {
  if(data?.value?.products) {
    products.value = data.value.products
  }
})

const productCard = resolveComponent('ProductCard')
</script>

<style src="./products.scss" lang="scss" scoped></style>

<template>
  <section class="main-section">
    <div class="section-title">{{ title }}</div>

    <div v-if="products && products.length">
      <section-snap-slider
        :items="products"
        :component="productCard"
        :gutter="0"
        :options="options"
        :title="$t('button.view_all')"
        :link="link"
        item-data-name="item"
      >
      </section-snap-slider>
    </div>
  </section>
</template>