<script setup>
import {useFilterItem} from '~/composables/product/useFilterItem.ts'
import {useCatalog} from '~/composables/product/useCatalog.ts'

const {t} = useI18n()
const {modelValue} = useFilterItem()
const {catalogMeta, pending} = useCatalog()

// COMPUTEDS
const data = computed(() => {
  return useModal().active.data
})

const productsLength = computed(() => {
  return catalogMeta?.value?.total || 0 
})

const filtersLength = computed(() => {
  return modelValue?.value?.length
})
// METHODS
// HANDLERS
const applyHandler = () => {
  useModal().close()
}
// WATCHERS
</script>

<style src='./filters.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <modal-wrapper :title="t('label.filters')" type="full">
    <div>
      <div v-if="filtersLength" class="selected-wrapper">
        <filter-selected :filters="data.filters" ></filter-selected>
      </div>
      
      <filter-list
        :filters="data.filters"
        :meta="data.meta"
        :meta-init="data.metaInit"
        :class="{pending: pending}"
        class="filters"
      ></filter-list>

      <div class="buttons">
        <div class="label">
          {{ t('label.show') }} {{ t('label.products', {products: productsLength}) }}
        </div>
        <button @click="applyHandler" class="button blue apply-btn">
          {{ t('button.apply') }}
          <template v-if="filtersLength">
            {{ ` (${filtersLength})` }}
          </template>
        </button>
      </div>
    </div>
  </modal-wrapper>
</template>