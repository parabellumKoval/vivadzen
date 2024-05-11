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
  metaInit: {
    typy: Array,
    required: false
  },
  modelValue: {
    type: Array,
    default: []
  }
});


const {t} = useI18n()

const emit = defineEmits([
  'update:modelValue'
])

const opened = ref([])
const activeFilters = ref([])
const search = ref({
  index: null,
  value: null
})

// COMPUTEDS
const filtersComputed = computed(() => {
  if(!search.value.value || search.value.index === null) {
    return props.filters
  }

  const filters = JSON.parse(JSON.stringify(props.filters))
  const valuesCopy = [...filters[search.value.index].values]

  filters[search.value.index].values = valuesCopy.filter((item) => {
    if(item.value.toLowerCase()
        .includes(search.value.value.toLowerCase())) {
      return item
    }
  })

  return filters
})

// HANDLERS
const updateSelectedHandler = (v) => {
  emit('update:modelValue', v)
}

const searchHandler = (index, event) => {
  search.value.value = event
  search.value.index = index
}

// METHODS
const isSearch = (filter) => {
  if(filter.noSearch)
    return false

  if(filter.type === 'checkbox' || filter.type === 'radio') {
    return true
  }else {
    return false
  }
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

const getMetaInit = (id) => {
  if(!props.metaInit)
    return null

  return props.metaInit[id] || null
}

const setOpened = () => {
  for(var i = 0; i < props.filters.length; i++){
    if(props.filters[i].isOpen) {
      opened.value.push(i)
    }
  }
}

setOpened()

const filterDoubleslider = resolveComponent('filter-type-doubleslider')
const filterCheckbox = resolveComponent('filter-type-checkbox')
const filterList = resolveComponent('filter-type-list')
const filterTree = resolveComponent('filter-type-tree')
const filterBrand = resolveComponent('filter-type-brand')
</script>

<style src="./list.scss" lang="scss" scoped></style>

<template>
  <div class="filter-wrapper">
    <template v-for="(filter, index) in filtersComputed" >
      <div
        v-if="filter.noMeta || getMetaInit(filter.id)"
        :key="filter.id"
        :class="{active: opened.includes(index)}"
        class="filter-item"
      >
        <button @click="toggleFilter(index)" class="filter-header">
          <div class="filter-name">{{ filter.name }}{{ filter.si? `, ${filter.si}`: '' }}</div>
          <IconCSS name="iconoir:nav-arrow-down" class="filter-header-icon"></IconCSS>
        </button>

        <div class="filter-values">
          <div v-if="isSearch(filter)" class="search-wrapper">
            <input
              type="text"
              @input="(event) => searchHandler(index, event.target.value)"
              class="search-input"
              placeholder="Поиск значений"
            />
          </div>

          <div 
            v-if="search.index == index && search.value && !filter.values?.length"
            class="no-results"  
          >
            {{ t('messages.no_results') }}
          </div>

          <component
            v-if="filter.type === 'number'"
            :modelValue="modelValue"
            @update:modelValue="updateSelectedHandler"
            :is="filterDoubleslider"
            :filter="filter"
            :meta="getMeta(filter.id)"
            :meta-init="getMetaInit(filter.id)"
          ></component>
          <component v-else-if="filter.type === 'checkbox' || filter.type === 'radio'"
            :modelValue="modelValue"
            @update:modelValue="updateSelectedHandler"
            :is="filterCheckbox"
            :filter="filter"
            :meta="getMeta(filter.id)"
            :meta-init="getMetaInit(filter.id)"
          ></component>
          <component v-else-if="filter.type === 'list'"
            :is="filterList"
            :filter="filter"
          ></component>
          <component v-else-if="filter.type === 'tree'"
            :is="filterTree"
            :filter="filter"
          ></component>
          <component v-else-if="filter.type === 'brand'"
            :modelValue="modelValue"
            @update:modelValue="updateSelectedHandler"
            :is="filterBrand"
            :filter="filter"
          ></component>
        </div>
      </div>
    </template>
  </div>
</template>