<script setup>
const {t} = useI18n()
const props = defineProps({
  product: {
    type: Object
  }
})

// COMPUTEDS
// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='./fixed.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="sale-fixed">
    <button class="sale-fixed-btn comparison-btn">
      <IconCSS name="ph:scales-light" size="24"></IconCSS>
    </button>

    <button class="sale-fixed-btn favorite-btn">
      <IconCSS name="iconoir:heart" size="24"></IconCSS>
    </button>

    <div class="price-block">
      <simple-price :value="product.oldPrice" class="price-old"></simple-price>
      <simple-price :value="product.price" class="price-base"></simple-price>
    </div>

    <button class="button primary buy-btn">{{ t('button.buy') }}</button>
  </div>
</template>