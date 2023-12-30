<script setup>
const props = defineProps({
  total: {
    type: Number,
    required: true
  },

  current: {
    type: Number,
    default: 0
  },

  progress: {
    type: Number,
    default: 0
  }
})

const safeProgress = ref(0)

const emit = defineEmits([
  'select',
  'prev',
  'next',
])

// COMPUTED

// METHODS
const getProgress = (index) => {
  if(props.current === index){
    if(safeProgress.value >= 0)
      return 100 - safeProgress.value
    else
      return 100 + safeProgress.value
  }

  if(props.current - 1 === index && safeProgress.value < 0){
      return -safeProgress.value + 16
  }

  if(props.current + 1 === index && safeProgress.value > 0){
      return safeProgress.value + 16
  }
}

const getStyle = (index) => {
  const progress = getProgress(index)

  const widthCss = progress / 2
  const opacityCss = 0.4 + progress * 0.6 / 100

  return `width: ${widthCss}px; opacity: ${opacityCss};`
}

// WATCH
watch(() => props.progress, (val) => {
  // console.log('progress', val);
  requestAnimationFrame(() => {
    safeProgress.value = val
  })
})

watch(() => props.current, (val) => {
  // console.log('current', val);
  safeProgress.value = 0
})
</script>
<style src="./line.scss" lang="scss" scoped />


<template>
  <ul class="pagination-wrapper">

    <li
      v-for="i in total"
      :key="i"
      :class="{active: i - 1 === current, prev: i === current, next: i - 2 === current}"
      :style="getStyle(i - 1)"
      class="pagination-item"
    >
    </li>

  </ul>
</template>