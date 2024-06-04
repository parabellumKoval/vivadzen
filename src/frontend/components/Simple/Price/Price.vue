<script setup>
const props = defineProps({
  value: {
    type: Number
  },
  currency: {
    type: Boolean,
    default: true
  }
})
</script>

<style src="./price.scss" lang="scss" scoped />

<template>
  <div class="price" v-if="value !== null">
    <span class="value">
      {{ currency? $n(value, 'currency'): $n(value, 'cur') }}
    </span>
  </div>
</template>