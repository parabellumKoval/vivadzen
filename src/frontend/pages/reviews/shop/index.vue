<script setup>
// import {useReviewFaker} from '~/composables/fakers/useReviewFaker.ts'
import {useFetchReview} from '~/composables/review/useFetchReview.ts'

definePageMeta({
  tab: 0
});

const emit = defineEmits(['set:amount', 'scroll:top'])

const {t} = useI18n()
const reviews = ref([])
const reviewsMeta = ref({})


const getReviewQuery = () => {
  return {
    per_page: 12,
    reviewable_type: null,
  }
}

await useFetchReview().getReviews(getReviewQuery(), true).then(({reviews: r, meta: m}) => {
  reviews.value = r
  reviewsMeta.value = m

  emit('set:amount', {type: 'shop', value: reviewsMeta.value.total})
})

useFetchReview().getReviewsAmount('products').then((v) => {
  emit('set:amount', {type: 'products', value: v})
})

const updatePageHandler = (page) => {
  useFetchReview().loadReviewsHandler(getReviewQuery(), page).then(({reviews: r, meta: m}) => {
    reviews.value = r
    reviewsMeta.value = m
    emit('scroll:top')
  })
}
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
    <simple-pagination
      :total="reviewsMeta.last_page"
      :current="reviewsMeta.current_page"
      @update:current="updatePageHandler"
      class="review-pagination"
    ></simple-pagination>
  </div>
</template>