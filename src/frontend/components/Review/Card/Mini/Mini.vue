<script setup>
const props = defineProps({
  item: {
    type: Object
  }
})
</script>

<style src="./mini.scss" lang="scss" scoped></style>

<template>
  <div class="review">
    <div class="review-header">
      <div class="review-name">{{ item.author.name }}</div>
      <div class="review-date">{{ $d(item.created_at, 'short') }}</div>
    </div>
    <div class="review-content">
      {{ item.text }}
    </div>
  </div>
</template>