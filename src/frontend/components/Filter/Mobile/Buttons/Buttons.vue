<script setup>
const {t} = useI18n()
const props = defineProps({
  product: {
    type: Object
  }
})

// COMPUTEDS
const activeFiltersLength = computed(() => {
  return 4
})
// METHODS
// HANDLERS
const openFiltersHandler = () => {
  useModal().open(resolveComponent('ModalFilters'), null, null)
}

const openSortingHandler = () => {

}
// WATCHERS
</script>

<style src='./buttons.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="buttons-fixed">
    <button @click="openFiltersHandler" class="button blue lowcase buttons-fixed-btn filter-btn">
      <IconCSS name="iconoir:filter" size="20" class="inline-icon"></IconCSS>
      <span>{{ t('button.filters') }} ({{ activeFiltersLength }})</span>
    </button>

    <button @click="openSortingHandler" class="button secondary lowcase buttons-fixed-btn sorting-btn">
      <IconCSS name="iconoir:filter-list" size="20" class="inline-icon"></IconCSS>
      <span>{{ t('button.sorting') }}</span>
    </button>
  </div>
</template>