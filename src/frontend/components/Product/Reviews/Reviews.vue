<script setup>
import {useReviewFaker} from '~/composables/fakers/useReviewFaker.ts'

const {t} = useI18n()

const reviews = computed(() => {
  return useReviewFaker()(4)
})
</script>

<style src="./reviews.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <div class="reviews">
    <div class="review-form">
      <div class="review-form-title">{{ t('messages.leave_review') }}<br>{{ t('about_product') }}</div>
      <button class="button violet wide large-icon inline-icon">
        <IconCSS name="iconoir:message-text" class="icon"></IconCSS>
        <span>{{ t('messages.leave_review') }}</span>
      </button>
    </div>
    <div class="review-grid">
      <review-card-full v-for="review in reviews" :key="review.id" :item="review" class="review-card"></review-card-full>
    </div>
  </div>
</template>