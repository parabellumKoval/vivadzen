<script setup>
import {useFetchReview} from '~/composables/review/useFetchReview.ts'

const reviews = ref([])
const feedback = ref([])

const simpleSnapSliderRef = ref(null)
const component = resolveComponent('ReviewPersonal')

const options = ref({
  card: {
    width: {
      mobile: 'calc(100% - 10px)',
      desktop: '100%'
    }
  }
})

const pagination = ref({
  isActive: false,
  activeIndex: 0,
  total: 0,
  progress: 0
})

// COMPUTED

// HANDLERS
const setPaginationHandler = (val) => {
  pagination.value.isActive = val.isActive
  pagination.value.total = val.total
}

const prevHandler = () => {
  simpleSnapSliderRef.value.prevHandler()
}
const nextHandler = () => {
  simpleSnapSliderRef.value.nextHandler()
}

const selectHandler = (v) => {
  simpleSnapSliderRef.value.selectHandler(v)
  setActiveIndex(v)
}

// METHODS
const setProgress = (val) => {
  pagination.value.progress = val
}

const setActiveIndex = (val) => {
  pagination.value.activeIndex = val
}

const getProductReviewQuery = () => {
  return {
    per_page: 3,
    reviewable_type: String.raw`Backpack\Store\app\Models\Product`,
    resource: 'large'
  }
}

const getShopReviewQuery = () => {
  return {
    per_page: 3,
    reviewable_type: null,
  }
}

// FETCH
await useFetchReview().getReviews(getProductReviewQuery(), true, 'product-reviews').then(({reviews: r, meta: m}) => {
  reviews.value = r
})

await useFetchReview().getReviews(getShopReviewQuery(), true, 'shop-reviews').then(({reviews: r, meta: m}) => {
  feedback.value = r
})

</script>

<style src="./review.scss" lang="scss" scoped></style>

<template>
  <section class="main-section">
    <div class="section-title">Отзывы</div>
    <div class="wrapper">
      
      <div class="review">
        <review-product
          v-for="review in reviews"
          :key="review.id"
          :item="review"
          class="review-card"
        ></review-product>
      </div>

      <div class="feedback">
        <simple-snap-slider
          :values="feedback"
          :component="component"
          :gutter="40"
          :options="options"
          item-data-name="item"
          @setPagination="setPaginationHandler"
          @setProgress="setProgress"
          @setIndex="setActiveIndex"
          class="slider"
          ref="simpleSnapSliderRef"
        >
        </simple-snap-slider>

        
        <simple-slider-btns
          :items="pagination.total"
          :active-index="pagination.activeIndex"
          :is-arrows="false"
          @select="selectHandler"
          @prev="prevHandler"
          @next="nextHandler"
          class="nav-btns"
        >
        </simple-slider-btns>

        <div class="feedback-info">
          <span class="feedback-info-text">🎁 Купон на -5% за верифицированный отзыв о магазине</span>
          &nbsp;<NuxtLink :to="localePath('/')">Подробнее...</NuxtLink>
        </div>

      </div>
    </div>
  </section>
</template>