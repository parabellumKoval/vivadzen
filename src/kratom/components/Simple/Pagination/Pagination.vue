<script setup>
const props = defineProps({
  total: {
    type: Number,
    required: true
  },

  current: {
    type: Number,
    default: 1
  },

  shownPages: {
    type: Number,
    default: 3
  }
})

const emit = defineEmits([
  'update:current'
])

// COMPUTED
const pagination = computed(() => {
  let result = []
  
  if(props.total <=5 )
    return Array.from({length: props.total}, (_, i) => i + 1)

  result = [1, false, props.current - 1, props.current, props.current + 1, false, props.total]

  if(props.current <= 3) {
    result = [1, 2, 3, 4, 5, false, props.total]
  }

  if(props.current >= props.total - 2){ 
    result = [1, false, props.total - 4, props.total - 3, props.total - 2, props.total - 1, props.total]
  }

  return result
})

// HANDLERS
const clickHandler = (index) => {
  emit('update:current', index)
}

const prevHandler = () => {
  const value = props.current - 1
  if(value > 0)
    emit('update:current', value)
}

const nextHandler = () => {
  const value = props.current + 1
  if(value <= props.total)
    emit('update:current', value)
}
</script>
<style src="./pagination.scss" lang="scss" scoped />

<template>
  <div class="pagination-wrapper">

    <button @click="prevHandler" class="button btn item prev">
      <IconCSS name="ph:caret-left" size="20" ></IconCSS>
    </button>

    <div class="pagination">
      <button
        v-for="(i, index) in pagination"
        :key="index"
        :class="{
          active: i === current,
          disabled: i === false,
        }"
        @click="clickHandler(i)"
        class="button item"
      >
        <div v-if="i === false" class="more">...</div>
        <span v-else>{{ i }}</span>
      </button>
    </div>

    <button @click="nextHandler" class="button btn item next">
      <IconCSS name="ph:caret-right" size="20" ></IconCSS>
    </button>

  </div>
</template>