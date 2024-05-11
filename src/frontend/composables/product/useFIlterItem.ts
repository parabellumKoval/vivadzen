export const useFilterItem = (vModel: [], filterId: Number) => {
  
  const modelValue = ref([...vModel])

  // COMPUTEDS
  const thisFilter = computed(() => {
    return modelValue.value.find((item) => {
      return item.id === filterId
    })
  })

  const isMetaBlocked = computed(() => {
    if(!modelValue)
      return false 

    const index = modelValue.value.findIndex((item) => {
      return item.id === filterId 
    })

    if(index !== -1 && (index + 1) === modelValue.value.length){
      return true
    }else {
      return false
    }
  })

  // METHODS

  const updateRangeValue = (data: any) => {
    const filter = thisFilter.value

    if(!filter) {
      modelValue.value.push({
        id: filterId,
        values: {min: data[0], max: data[1]}
      })
    }else {
      filter.values.min = data[0]
      filter.values.max = data[1]
    }

    return modelValue.value
  }

  const updateCheckboxValue = (data: any) => {
    const filter = thisFilter.value

    // if this filter not exists inside selected yet
    if(!filter) {
      modelValue.value.push({
        id: filterId,
        values: [data]
      })
    // if filter already exists
    }else {
      const findIndex = filter.values.indexOf(data)

      // Add
      if(findIndex === -1) {
        filter.values.push(data)
      // Remove
      }else {
        filter.values.splice(findIndex, 1)
      } 
    }
    
    return modelValue.value
    // emit('update:modelValue', modelValue)
  }


  return {
    updateRangeValue,
    updateCheckboxValue,
    thisFilter,
    isMetaBlocked
  }
}