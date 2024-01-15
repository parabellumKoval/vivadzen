<script setup>
const props = defineProps({
  modelValue: {
    type: [String, Number]
  },

  values: {
    type: Array
  },

  key: {
    type: String,
    default: null
  },

  value: {
    type: String,
    default: null
  }
})

const emit = defineEmits([
  'update:modelValue'
])

const activeIndex = ref(0)
// const isTabActive = (index) => {
//   if(props.key)
//     return props.values[index][props.key] === props.modelValue
//   else
//     return index === props.modelValue
// }

// const activeTabIndex = computed(() => {
//   if(props.key) {
//     console.log('props.key')
//   } else {
//     console.log('props.modelValue', activeIndex.value)
//     return activeIndex.value
//   }
// })

const selectHandler = (index) => {
  if(props.key)
    emit('update:modelValue', props.values[index][props.key])
  else
    emit('update:modelValue', index) 
}

watch(() => props.modelValue, (v) => {
  console.log('v', v)
  activeIndex.value = v
}, {
  immediate: true
})

console.log('values', props.values)
</script>

<style src="./tabs.scss" lang="sass" scoped />

<template>
  <div class="tabs" scrollable>
    <ul class="list" scrollable>
      <li
        v-for="(tab, index) in values"
        :key="index"
        @click="selectHandler(index)"
        :class="{active: index == activeIndex}"
        class="item"
        clickable
        scrollable
        v-html="tab[value] || tab "
      >
      </li>
    </ul>
  </div>
</template>