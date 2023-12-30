<script setup>
const {t} = useI18n()
const props = defineProps({
  label: {
    type: String
  },
  value: {
    type: [String, Number]
  }
})
</script>

<style src="./static.scss" lang="scss" scoped />

<template>
  <form-label :label="label">
    <div class="value">{{ value }}</div>
  </form-label>
</template>