<script setup>
import {useReviewFaker} from '~/composables/fakers/useReviewFaker.ts'

definePageMeta({
  tab: 1
});

const {t} = useI18n()

const reviews = computed(() => {
  return useReviewFaker()(4)
})
</script>

<style src="./products.scss" lang="scss" scoped></style>

<template>
  <div class="review-wrapper">
    <div class="review-grid">
      <review-product v-for="review in reviews" :key="review.id" :item="review" type="full" class="shop-review-card"></review-product>
    </div>
    <simple-pagination :total="8" class="review-pagination"></simple-pagination>
  </div>
</template>