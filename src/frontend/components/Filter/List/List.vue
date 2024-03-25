<script setup>
const props = defineProps({
  filters: {
    typy: Array,
    required: true
  },
  meta: {
    typy: Array,
    required: false
  },
  modelValue: {
    type: Object
  }
});

const emit = defineEmits([
  'update:modelValue'
])

const opened = ref([])

const updateSelectedHandler = (v) => {
  emit('update:modelValue', v)
}

const toggleFilter = (key) => {
  const findIndex = opened.value.indexOf(key)
  if(findIndex === -1){
    opened.value.push(key)
  }else {
    opened.value.splice(findIndex, 1)
  }
}

const getMeta = (id) => {
  if(!props.meta)
    return null

  return props.meta[id] || null
}

const setOpened = () => {
  for(var i = 0; i < props.filters.length; i++){
    if(props.filters[i].isOpen) {
      opened.value.push(i)
    }
  }

  // opened.value = [
  //   0, 1, 2, props.filters.length - 1
  // ]
}

setOpened()

const filterDoubleslider = resolveComponent('filter-type-doubleslider')
const filterCheckbox = resolveComponent('filter-type-checkbox')
const filterList = resolveComponent('filter-type-list')
const filterTree = resolveComponent('filter-type-tree')

console.log('LIST', props.filters)
</script>

<style src="./list.scss" lang="scss" scoped></style>

<template>
  <div class="filter-wrapper">
    <template v-for="(filter, index) in filters" >
      <div v-if="filter.noMeta || getMeta(filter.id)" :key="filter.id" :class="{active: opened.includes(index)}" class="filter-item">
        <button @click="toggleFilter(index)" class="filter-header">
          <div class="filter-name">{{ filter.name }}{{ filter.si? `, ${filter.si}`: '' }}</div>
          <IconCSS name="iconoir:nav-arrow-down" class="filter-header-icon"></IconCSS>
        </button>
        <div class="filter-values">
          <component
            v-if="filter.type === 'number'"
            :modelValue="modelValue"
            @update:modelValue="updateSelectedHandler"
            :is="filterDoubleslider"
            :filter="filter"
            :meta="getMeta(filter.id)"
          ></component>
          <component v-else-if="filter.type === 'checkbox' || filter.type === 'radio'"
            :modelValue="modelValue"
            @update:modelValue="updateSelectedHandler"
            :is="filterCheckbox"
            :filter="filter"
            :meta="getMeta(filter.id)"
          ></component>
          <component v-else-if="filter.type === 'list'"
            :is="filterList"
            :filter="filter"
          ></component>
          <component v-else-if="filter.type === 'tree'"
            :is="filterTree"
            :filter="filter"
          ></component>
        </div>
      </div>
    </template>
  </div>
</template>