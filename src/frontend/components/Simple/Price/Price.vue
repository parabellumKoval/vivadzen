<script setup>
const props = defineProps({
  value: {
    type: Number
  },
  currency: {
    type: [String, Boolean],
    default: 'uah'
  }
})
</script>

<style src="./price.scss" lang="scss" scoped />

<template>
  <div class="price" v-if="value">
    <span class="value">{{ $n(value, 'currency') }}</span>
    <!-- <span v-if="currency" class="currency">&nbsp;{{ currency }}</span> -->
  </div>
</template>