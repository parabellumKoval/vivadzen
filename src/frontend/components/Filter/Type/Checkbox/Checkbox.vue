<script setup>
import {useFilterItem} from '~/composables/product/useFilterItem.ts'

const props = defineProps({
  filter: {
    type: Object
  },

  meta: {
    type: Array,
    default: []
  },

  metaInit: {
    type: Array,
    default: null
  },

  modelValue: {
    type: Object,
    default: []
  }
})

const emit = defineEmits(['update:modelValue'])
const meta = ref([])

const {updateCheckboxValue, thisFilter, isMetaBlocked} = useFilterItem(props.modelValue, props.filter.id)

// COMPUTED

// HANDLERS
const checkHandler = (valueId) => {
  const newValue = updateCheckboxValue(valueId)
  emit('update:modelValue', newValue)
}

// METHODS
const isPlus = (valueId) => {
  return thisFilter.value && getMeta(valueId) > 0 && !isValueChecked(valueId)
}

const isValueChecked = (valueId) => {
  if(!thisFilter.value)
    return false

  return thisFilter.value.values.indexOf(valueId) !== -1
}

const isDisabled = (valueId) => {
  if(props.filter.noMeta)
    return false

  if(isValueChecked(valueId))
    return false

  if(meta.value && !meta.value[valueId]) {
    return true
  }
}

const getMeta = (valueId) => {
  if(props.filter.noMeta){
    return null
  }

  if(meta.value && meta.value[valueId]){
    return meta.value[valueId]
  }else {
    return 0
  }
}

// WATCH
watch(() => props.meta, (v) => {
  if(isMetaBlocked.value || !v)
    return

  meta.value = v
}, {
  deep: true
})

watch(() => props.metaInit, (v) => {
  if(!v)
    return

  meta.value = v
}, {
  immediate: true,
  deep: true
})
</script>

<!-- <style src="checkbox2.scss" lang="scss" scoped></style> -->
<style src="./checkbox.scss" lang="scss" scoped></style>

<template>
  <div class="wrapper">
    <ul class="checkbox-list">
      <template v-for="(value, index) in filter.values">
        <li
          v-if="filter.noMeta || metaInit[value.id]"
          :key="value.id"
          :class="[
            {disabled: isDisabled(value.id)},
            {checked: isValueChecked(value.id)}
          ]"
          class="checkbox-item"
        >
          <button @click="checkHandler(value.id)" class="checkbox-item-btn" button>
            <div class="checkbox-input">
              <IconCSS name="iconoir:check" class="checkbox-input-icon"></IconCSS>
            </div>
            <span class="checkbox-content">
              <span class="checkbox-name">{{ value.value }}</span>
              <span
                :class="{plus: isPlus(value.id)}"
                class="checkbox-count">
                <template v-if="!isValueChecked(value.id)">
                  {{ getMeta(value.id) }}
                </template>
              </span>
            </span>
          </button>
        </li>
      </template>
    </ul>
  </div>
</template>