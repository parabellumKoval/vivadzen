<script setup>
const props = defineProps({
  price: {
    type: [Number, String]
  },
  oldPrice: {
    type: [Number, String]
  },
  dir: {
    type: String,
    default: 'right'
  },
  currencyCode: { type: String, default: null },
})

</script>

<style src='./price.scss' lang='scss' scoped></style>

<template>
  <div class="price" :class="dir">
    <simple-price v-if="oldPrice" :value="oldPrice" :currency="false" class="old-pr"></simple-price>
    <simple-price v-if="price" :value="price" :currency-code="currencyCode" class="pr"></simple-price>
  </div>
</template>