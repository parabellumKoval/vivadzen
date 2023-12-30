<script setup>
import {useReviewFaker} from '~/composables/fakers/useReviewFaker.ts'

definePageMeta({
  tab: 0
});

const {t} = useI18n()

const reviews = computed(() => {
  return useReviewFaker()(4)
})
</script>

<style src="./shop.scss" lang="scss" scoped></style>

<template>
  <div class="review-wrapper">
    <div class="review-grid">
      <review-card-personal
        v-for="review in reviews"
        :key="review.id"
        :item="review"
        class="shop-review-card"
      ></review-card-personal>
    </div>
    <simple-pagination :total="8" class="review-pagination"></simple-pagination>
  </div>
</template>