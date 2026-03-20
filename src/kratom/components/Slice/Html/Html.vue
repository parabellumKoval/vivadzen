<script setup>
const props = defineProps({
  html: {
    type: String,
    default: ''
  },
  tag: {
    type: String,
    default: 'article'
  }
})
const tagName = computed(() => props.tag || 'article')
const htmlContent = computed(() => props.html || '')
</script>

<template>
  <div
    class="slice-html rich-text"
    v-html="htmlContent"
  ></div>
</template>

<style src="./html.scss" lang="scss" scoped />
