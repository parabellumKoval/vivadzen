<script setup>
const props = defineProps({
  modelValue: {
    type: Object
  },
  filters: {
    type: Array
  }
})

const emit = defineEmits([
  'update:modelValue'
])

const {t} = useI18n()


const selected = computed(() => {
  const values = []

  // For each selected filter
  for (const [key, value] of Object.entries(props.modelValue)) {

    // Find this iterration filter by ID
    let filter = props.filters.find((filter) => {
      return filter.id == key
    })

    // console.log('selected', props.modelValue, filter)

    if(!filter)
      continue
    
    // For lists
    if(filter.type === 'checkbox' || filter.type === 'radio') {
      // For each values
      value.forEach((selectedValue) => {
        let filterValue = filter.values.find(v => v.id == selectedValue)
        let filterValueName = filterValue?.value || null

        values.push({
          filterId: filter.id,
          valueId: filterValue.id,
          name: filter.name + ': ' + filterValueName
        })
      })
    }else if(filter.type === 'number') {
      values.push({
        filterId: filter.id,
        valueId: null,
        name: filter.name + ': ' + value.min + '-' + value.max
      })
    }
  }
  
  return values
})


const deleteHandler = (filter) => {
  const modelValueCopy = {...props.modelValue}
  const filterModel = props.filters.find(item => item.id === filter.filterId)

  // For doubleslider, number - type etc.
  if(filterModel.type === 'number') {
    delete modelValueCopy[filter.filterId]
  // For lists checkbox, radio
  }else if(filterModel.type === 'checkbox' || filterModel.type === 'radio') {
    const valueIndex = modelValueCopy[filter.filterId].indexOf(filter.valueId)

    if(valueIndex !== -1){
      modelValueCopy[filter.filterId].splice(valueIndex, 1)

      // if no values remove property at all
      if(!modelValueCopy[filter.filterId].length)
        delete modelValueCopy[filter.filterId]
    }
  }

  emit('update:modelValue', modelValueCopy)
}

const removeAllHandler = () => {
  emit('update:modelValue', {})
}
</script>

<style src="./selected.scss" lang="scss" scoped></style>

<template>
  <div class="filter-wrapper">
    <div class="filter-label">Примененные фильтры:</div>
    <div class="filter-list">
      <button @click="removeAllHandler" class="button small light filter-remove-all-btn">Сбросить все</button>
      <div v-for="filter in selected" :key="filter.filterId" class="filter-item">
        <span class="filter-name">{{ filter.name }}</span>
        <button @click="deleteHandler(filter)" class="filter-remove-btn">
          <IconCSS name="iconoir:cancel"></IconCSS>
        </button>
      </div>
    </div>
  </div>
</template>