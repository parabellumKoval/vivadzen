<script setup>
import {useFavorite} from '~/composables/product/useFavorite.ts'

const {t} = useI18n()

const props = defineProps({
  productId: {
    type: Number
  }
})

const {isFavorite, toFavoriteHandler} = useFavorite(props.productId)

// COMPUTEDS
const title = computed(() => {
  if(isFavorite.value) {
    return t('favorite_active')
  }else {
    return t('favorite')
  }
})

const icon = computed(() => {
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

<style src='./favorite.scss' lang='scss' scoped></style>
<i18n src='./lang.yaml' lang='yaml'></i18n>

<template>
  <simple-button-text
    :text="title"
    :icon="icon"
    :callback="toFavoriteHandler"
    :class="{active: isFavorite}"
    class="favorite-link"
  ></simple-button-text>
</template>