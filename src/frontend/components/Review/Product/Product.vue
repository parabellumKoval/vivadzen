<script setup>
const props = defineProps({
  item: {
    type: Object
  },
  type: {
    type: String,
    default: 'mini'
  }
})

console.log('PRODUCT REVIEW', props.item, props.item.product)
</script>

<style src="./product.scss" lang="scss" scoped></style>

<template>
  <div v-if="item" class="review-wrapper">
    <div class="product">
      <product-card-micro :item="item.product"></product-card-micro>
    </div>
    <div class="devide"></div>
    <review-card-mini v-if="type === 'mini'" :item="item" class="review-mini"></review-card-mini>
    <review-card-full v-else-if="type === 'full'" :item="item" class="review-mini"></review-card-full>
  </div>
</template>