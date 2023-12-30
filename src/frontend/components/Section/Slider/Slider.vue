<script setup>
import {useProductFaker} from '~/composables/fakers/useProductFaker.ts'

const products = computed(() => {
  return useProductFaker()(5)
})

console.log(products.value)
</script>

<style src="./slider.scss" lang="scss" scoped></style>

<template>
  <section class="main-section">
    <div class="section-title">топ продажу</div>
    <div class="items-wrapeer">
      <product-card v-for="product in products" :key="product.id" :item="product" class="product-card"></product-card>
    </div>
    <div class="section-nav">
      <simple-slider-nav></simple-slider-nav>
    </div>
  </section>
</template>