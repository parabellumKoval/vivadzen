<script setup>
const props = defineProps({
  label: {
    type: String
  }
})
</script>

<style src="./label.scss" lang="scss" scoped />

<template>
  <div>
    <div v-if="label" class="label">{{ label }}</div>
    <div>
      <slot />
    </div>
  </div>
</template>