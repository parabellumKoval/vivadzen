<script setup>
const props = defineProps({
  label: {
    type: String
  },
  value: {
    type: [String, Number, Boolean],
    default: null
  }
})
</script>
<style src="./column.scss" lang="scss" scoped />

<template>
  <div class="column">
    <div class="label">{{ label }}</div>
    <div class="value">
      <template v-if="value !== null && value !== undefined">
        {{ value }}
      </template>
      <template v-else>
        <slot />
      </template>
    </div>
  </div>
</template>