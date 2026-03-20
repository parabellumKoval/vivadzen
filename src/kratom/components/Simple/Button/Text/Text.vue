<script setup>
const props = defineProps({
  icon: {
    type: String,
    default: null,
    required: false
  },
  text: {
    type: String,
    required: true
  },
  callback: {
    type: Function,
    default: null
  }
})

const clickHandler = () => {
  if(props.callback) {
    props.callback()
  }
}
</script>

<style src="./text.scss" lang="scss" scoped></style>

<template>
  <button @click="clickHandler" class="text-link">
    <IconCSS v-if="icon" :name="icon" class="icon"></IconCSS>
    <span>{{ text }}</span>
  </button>
</template>