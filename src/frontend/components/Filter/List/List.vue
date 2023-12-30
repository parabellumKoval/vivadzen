<script setup>
import {useFilterFaker} from '~/composables/fakers/useFilterFaker.ts'

const opened = ref([0, 1, 2])

const filters = computed(() => {
  return useFilterFaker()(8)
})

const toggleFilter = (key) => {
  const findIndex = opened.value.indexOf(key)
  if(findIndex === -1){
    opened.value.push(key)
  }else {
    opened.value.splice(findIndex, 1)
  }
}

const filterDoubleslider = resolveComponent('filter-type-doubleslider')
const filterCheckbox = resolveComponent('filter-type-checkbox')
</script>

<style src="./list.scss" lang="scss" scoped></style>

<template>
  <div class="filter-wrapper">
    <div v-for="(filter, index) in filters" :key="filter.id" :class="{active: opened.includes(index)}" class="filter-item">
      <button @click="toggleFilter(index)" class="filter-header">
        <div class="filter-name">{{ filter.name }}</div>
        <IconCSS name="iconoir:nav-arrow-down" class="filter-header-icon"></IconCSS>
      </button>
      <div class="filter-values">
        <!-- <filter-type-checkbox :values="filter.values"></filter-type-checkbox> -->
        
        <component v-if="filter.type === 'Doubleslider'" :is="filterDoubleslider" :filter="filter"></component>
        <component v-else-if="filter.type === 'Checkbox'" :is="filterCheckbox" :filter="filter"></component>
      </div>
    </div>
  </div>
</template>