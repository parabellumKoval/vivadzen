<script setup>
import {useComparison} from '~/composables/product/useComparison.ts'

const {t} = useI18n()

const props = defineProps({
  productId: {
    type: Number
  }
})

const {isComparison, toComparisonHandler} = useComparison(props.productId)

// COMPUTEDS
const title = computed(() => {
  if(isComparison.value) {
    return t('comparision_active')
  }else {
    return t('comparision')
  }
})

const icon = computed(() => {
  if(isComparison.value) {
    return 'ph:scales-light'
  }else {
    return 'ph:scales-light'
  }
})

// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='./comparison.scss' lang='scss' scoped></style>
<i18n src='./lang.yaml' lang='yaml'></i18n>

<template>
  <simple-button-text
    :text="title"
    :icon="icon"
    :callback="toComparisonHandler"
    :class="{active: isComparison}"
    class="comparison-link"
  ></simple-button-text>
</template>