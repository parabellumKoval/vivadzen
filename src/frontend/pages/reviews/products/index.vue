<script setup>
import {useFetchReview} from '~/composables/review/useFetchReview.ts'

const {t} = useI18n()
const reviews = ref([])
const reviewsMeta = ref({})
const isLoading = ref(false)

definePageMeta({
  tab: 1
});

const emit = defineEmits(['set:amount', 'scroll:top'])

const getReviewQuery = () => {
  return {
    per_page: 12,
    reviewable_type: String.raw`Backpack\Store\app\Models\Product`,
    resource: 'large'
  }
}

await useFetchReview().getReviews(getReviewQuery(), true).then(({reviews: r, meta: m}) => {
  reviews.value = r
  reviewsMeta.value = m

  emit('set:amount', {type: 'products', value: reviewsMeta.value.total})
})

useFetchReview().getReviewsAmount('shop').then((v) => {
  emit('set:amount', {type: 'shop', value: v})
})

const updatePageHandler = (page) => {
  useFetchReview().loadReviewsHandler(getReviewQuery(), page).then(({reviews: r, meta: m}) => {
    reviews.value = r
    reviewsMeta.value = m
    emit('scroll:top')
  })
}
</script>

<style src="./products.scss" lang="scss" scoped></style>

<template>
  <div class="review-wrapper">
    <div class="review-grid">
      <review-product v-for="review in reviews" :key="review.id" :item="review" type="full" class="shop-review-card"></review-product>
    </div>
    
    <simple-pagination
      :total="reviewsMeta.last_page"
      :current="reviewsMeta.current_page"
      @update:current="updatePageHandler"
      class="review-pagination"
    ></simple-pagination>
  </div>
</template>