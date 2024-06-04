<script setup>
import {useComparisonStore} from '~/store/comparison.ts'
const {t} = useI18n()
// COMPUTEDS
const productsCount = computed(() => {
  return useComparisonStore().ids?.length
})
// METHODS
// HANDLERS
const closeModalHandler = () => {
  useModal().close()
}
// WATCHERS
</script>

<style src='./btn.scss' lang='scss' scoped></style>
<i18n src='./lang.yaml' lang='yaml'></i18n>

<template>
  <transition name="move-y">
    <div v-if="productsCount" class="comparison-wrapper">
      <NuxtLink
        :to="localePath('/comparison')"
        @click="closeModalHandler"
        class="button blue comparison-btn"
        clickable
      >
        <IconCSS name="ph:scales-light" size="24" class="icon"></IconCSS>
        <span>{{ productsCount }} {{ t('product', productsCount) }} {{ t('comparison') }}</span>
      </NuxtLink>
    </div>
  </transition>
</template>