<script setup>
import {useComparison} from '~/composables/product/useComparison.ts'
import {useFavorite} from '~/composables/product/useFavorite.ts'
import {useCart} from '~/composables/product/useCart.ts'

const {t} = useI18n()
const props = defineProps({
  product: {
    type: Object
  }
})

const {isComparison, toComparisonHandler} = useComparison(props.product.id)
const {isFavorite, toFavoriteHandler} = useFavorite(props.product.id)
const {toCartHandler} = useCart(props.product)

// COMPUTEDS
const favoriteIcon = computed(() => {
  if(isFavorite.value) {
    return 'iconoir:heart-solid'
  }else {
    return 'iconoir:heart'
  }
})

// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='./fixed.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="sale-fixed">
    <button @click="toComparisonHandler" :class="{active: isComparison}" class="sale-fixed-btn comparison-btn">
      <IconCSS name="ph:scales-light" size="24"></IconCSS>
    </button>

    <button @click="toFavoriteHandler" :class="{active: isFavorite}" class="sale-fixed-btn favorite-btn">
      <IconCSS :name="favoriteIcon" size="24"></IconCSS>
    </button>

    <div class="price-block">
      <simple-price :value="product.oldPrice" class="price-old"></simple-price>
      <simple-price :value="product.price" class="price-base"></simple-price>
    </div>

    <button @click="toCartHandler" class="button primary buy-btn">{{ t('button.buy') }}</button>
  </div>
</template>