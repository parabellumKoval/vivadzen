<script setup>
const props = defineProps({
  filter: {
    type: Object
  },

  meta: {
    type: Array,
    default: []
  },

  modelValue: {
    type: Object,
    default: {}
  }
})

const emit = defineEmits(['update:modelValue'])

const checkHandler = (id) => {
  const allFiltersCopy = {...props.modelValue}
  const thisFilter = allFiltersCopy[props.filter.id]

  // if this filter not exists inside selected yet
  if(!allFiltersCopy[props.filter.id]) {
    allFiltersCopy[props.filter.id] = [id]
  // if filter already exists
  }else {
    const findIndex = allFiltersCopy[props.filter.id].indexOf(id)

    // Add
    if(findIndex === -1) {
      allFiltersCopy[props.filter.id].push(id)
    // Remove
    }else {
      allFiltersCopy[props.filter.id].splice(findIndex, 1)
    }   
  }
  
  emit('update:modelValue', allFiltersCopy)
}
</script>

<!-- <style src="checkbox2.scss" lang="scss" scoped></style> -->
<style src="./checkbox.scss" lang="scss" scoped></style>

<template>
  <div class="wrapper">
    <ul class="checkbox-list">
      <li v-for="(value, index) in filter.values"
          :key="value.id"
          :class="[{disabled: !meta[value.id]}, {checked: modelValue[filter.id]?.includes(value.id)}]"
          @click="checkHandler(value.id)"
          class="checkbox-item"
      >
        <button class="checkbox-item-btn">
          <div class="checkbox-input">
            <IconCSS name="iconoir:check" class="checkbox-input-icon"></IconCSS>
          </div>
          <span class="checkbox-content">
            <span class="checkbox-name">{{ value.value }}</span>
            <span class="checkbox-count">{{ meta[value.id] || 0 }}</span>
          </span>
        </button>
      </li>
    </ul>
  </div>
</template>