<script setup>
import {useFilterItem} from '~/composables/product/useFilterItem.ts'
import {useSort} from '~/composables/product/useSort.ts'

const {t} = useI18n()

const props = defineProps({
  data: {
    type: Object,
    default: {}
  },
  updateOrderCallback: {
    default: null
  },
})

const {modelValue} = useFilterItem()

const {options: sortingOptions} = useSort()
const sortSelectedIndex = ref(1)
const sort = ref({order_by: 'created_at', order_dir: 'desc'})

// COMPUTEDS
const activeFiltersLength = computed(() => {
  return modelValue.value?.length || null
})

// METHODS
// HANDLERS
const openFiltersHandler = () => {
  useModal().open(resolveComponent('ModalFilters'), props.data, null)
}

// WATCH
watch(sortSelectedIndex, (v) => {
  if(sortingOptions.value[v]) {
    sort.value = {
      order_by: sortingOptions.value[v].by,
      order_dir: sortingOptions.value[v].dir
    }

    if(props.updateOrderCallback) {
      props.updateOrderCallback(sort.value)
    }
  }
})

</script>

<style src='./buttons.scss' lang='scss' scoped></style>

<template>
  <div class="buttons-fixed">
    <button @click="openFiltersHandler" class="button blue lowcase buttons-fixed-btn filter-btn">
      <IconCSS name="iconoir:filter" size="20" class="inline-icon"></IconCSS>
      <span>
        {{ t('button.filters') }} 
        <template v-if="activeFiltersLength !== null">
          ({{ activeFiltersLength }})
        </template>
      </span>
    </button>

    <div class="sorting-wrapper">
      <button class="button secondary lowcase buttons-fixed-btn sorting-btn">
        <IconCSS name="iconoir:filter-list" size="20" class="inline-icon"></IconCSS>
        <span>{{ t('button.sorting') }}</span>
      </button>

      <select v-model="sortSelectedIndex" class="sorting-select">
        <option
          v-for="(srt, key) in sortingOptions" 
          :key="key"
          :value="key"
        >
          {{ srt.caption }}
        </option>
      </select>
    </div>

  </div>
</template>