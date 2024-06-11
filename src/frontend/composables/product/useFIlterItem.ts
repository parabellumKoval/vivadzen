export const useFilterItem = (filterId: Number) => {
  
  const modelValue = useState('activeFilters', () => {return []})

  //WATCHERS
  // watch(() => modelValue.value, (v) => {
  //   console.log('modelValue', v)
  // }, {
  //   deep: true,
  //   immediate: true
  // })

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
  const setFilters = (params: Array) => {
    if(params.selections) {
      let selections = []
      const selectionsAvailable = ['top_sales', 'in_stock', 'with_sales']

      if(Array.isArray(params.selections)) {
        selections = params.selections.filter(value => selectionsAvailable.includes(value))
      }else {
        if(selectionsAvailable.includes(params.selections)) {
          selections.push(params.selections)
        }
      }
      
      updateModelValue([
        {
          id: 'selections',
          values: [...selections]
        }
      ])

      return selections
    }

    return []
  }

  const updateModelValue = (newModelValue) => {
    modelValue.value = newModelValue
  }

  const isValueChecked = (valueId) => {
    if(!thisFilter.value)
      return false

    return thisFilter.value.values.indexOf(valueId) !== -1
  }

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
    
    // return modelValue.value
  }


  return {
    modelValue,
    updateModelValue,
    updateRangeValue,
    updateCheckboxValue,
    isValueChecked,
    thisFilter,
    isMetaBlocked,
    setFilters
  }
}