<script setup>
import {useFilterItem} from "~/composables/product/useFilterItem"

const {modelValue, updateModelValue} = useFilterItem()

const props = defineProps({
  filters: {
    type: Array
  }
})

const {t} = useI18n()

const selected = computed(() => {
  const values = []

  // For each selected filter
  for(const index in modelValue.value) {
    const key = modelValue.value[index].id
    const value = modelValue.value[index].values

    // Find this iterration filter by ID
    let filter = props.filters.find((filter) => {
      return filter.id == key
    })

    if(!filter)
      continue
    
    // For lists
    if(filter.type === 'checkbox' || filter.type === 'radio' || filter.type === 'brand') {
      // For each values
      value.forEach((selectedValue) => {
        let filterValue = filter.values.find(v => v.id == selectedValue)
        let filterValueName = filterValue?.value || filterValue?.name || null

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
  const modelValueCopy = [...modelValue.value]
  const filterModel = props.filters.find(item => item.id === filter.filterId)
  const thisModelValueIndex = modelValueCopy.findIndex((item) => {
    if(item.id === filter.filterId) {
      return item
    }
  })

  if(thisModelValueIndex === undefined)
    return

  // For doubleslider, number - type etc.
  if(filterModel.type === 'number') {
    modelValueCopy.splice(thisModelValueIndex, 1)
  // For lists checkbox, radio
  }else if(filterModel.type === 'checkbox' || filterModel.type === 'radio' || filterModel.type === 'brand') {
    const valueIndex = modelValueCopy[thisModelValueIndex].values.indexOf(filter.valueId)

    if(valueIndex !== -1){
      modelValueCopy[thisModelValueIndex].values.splice(valueIndex, 1)

      // if no values remove property at all
      if(!modelValueCopy[thisModelValueIndex].values.length)
        modelValueCopy.splice(thisModelValueIndex, 1)
    }
  }

  updateModelValue(modelValueCopy)
}

const removeAllHandler = () => {
  updateModelValue([])
}
</script>

<style src="./selected.scss" lang="scss" scoped></style>

<template>
  <div class="filter-wrapper">
    <div class="filter-label">{{ t('label.active_filters') }}:</div>
    <div class="filter-list">
      <button @click="removeAllHandler" class="button small light filter-remove-all-btn" button>{{ t('button.reset_all') }}</button>
      <transition-group name="move-x">
        <div v-for="filter in selected" :key="filter.filterId" class="filter-item">
          <span class="filter-name">{{ filter.name }}</span>
          <button @click="deleteHandler(filter)" class="filter-remove-btn">
            <IconCSS name="iconoir:cancel"></IconCSS>
          </button>
        </div>
      </transition-group>
    </div>
  </div>
</template>