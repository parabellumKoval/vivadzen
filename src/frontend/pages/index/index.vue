<script setup>
const {t} = useI18n()

const topQuery = ref({
  per_page: 10,
  selections: ['top_sales', 'in_stock']
})

const saleQuery = ref({
  per_page: 10,
  selections: ['with_sales', 'in_stock']
})
</script>

<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <div>
    <section-banner></section-banner>
    
    <section-slider-top-products></section-slider-top-products>

    <section-slider-products :title="t('top')" :query="topQuery"></section-slider-products>

    <section-slider-products :title="t('sale')" :query="saleQuery"></section-slider-products>

    <section-category></section-category>
    
    <div class="container">
      <section-review></section-review>
      <section-adv></section-adv>
    </div>
    
    <div class="container">
      <section-article></section-article>
    </div>
    
    <section-insta></section-insta>
  </div>
</template>