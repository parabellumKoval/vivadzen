<script setup>
const props = defineProps({
  title: {
    type: String
  },
  desc: {
    type: String
  },
  buttonLink: {
    type: String,
    default: '/'
  },
  buttonText: {
    type: String
  },
  isLoading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits([
  'action'
])

const actionHandler = () => {
  emit('action')
}
</script>
<style src="./block.scss" lang="scss" scoped />

<template>
  <div class="block">
    <div>
      <div class="title">{{ title }}</div>
      <div class="desc">{{ desc }}</div>
    </div>
    <button
      @click="actionHandler"
      :class="{loading: isLoading}"
      class="button secondary btn"
      type="button"
    >
      <span>{{ buttonText }}</span>
    </button>
  </div>
</template>