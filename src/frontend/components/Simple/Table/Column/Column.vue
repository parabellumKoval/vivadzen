<script setup>
const props = defineProps({
  label: {
    type: String
  },
  value: {
    type: [String, Number, Boolean]
  }
})
</script>
<style src="./column.scss" lang="scss" scoped />

<template>
  <div class="column">
    <div class="label">{{ label }}</div>
    <div class="value">
      <template v-if="value">
        {{ value }}
      </template>
      <template v-else>
        <slot />
      </template>
    </div>
  </div>
</template>