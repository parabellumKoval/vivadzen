<script setup>
import {useFetchReview} from '~/composables/review/useFetchReview.ts'

const reviews = ref([])
const feedback = ref([])

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

await useFetchReview().getReviews(getProductReviewQuery(), true).then(({reviews: r, meta: m}) => {
  reviews.value = r
})

await useFetchReview().getReviews(getShopReviewQuery(), true).then(({reviews: r, meta: m}) => {
  feedback.value = r
})

const getPhotoSrc = (image) => {
  if(image?.src) {
    return image.src
    // return '/server/' + image.src
  } else {
    return null
  }
}

// const feedback = computed(() => {
//   return [
//     {
//       id: 1,
//       author: {
//         name: 'Натали Кыргызтан',
//         photo: '/images/avatars/4.jpg',
//       },
//       created_at: new Date(), 
//       text: 'Я хотел бы поделиться своим положительным опытом покупок в магазине биодобавок djini.com.ua. Этот магазин предлагает широкий выбор высококачественных биологических добавок,помогают поддерживать мое здоровье.'
//     }
//   ]
// })
</script>

<style src="./review.scss" lang="scss" scoped></style>

<template>
  <section class="main-section">
    <div class="section-title">Отзывы</div>
    <div class="wrapper">
      <div class="review">
        <review-product v-for="review in reviews" :key="review.id" :item="review" class="review-card"></review-product>
      </div>
      <div class="feedback">
        <review-personal v-for="item in feedback" :key="item.id" :item="item" class="feedback-card"></review-personal>
        <div class="feedback-info">
          <span class="feedback-info-text">🎁 Купон на -5% за верифицированный отзыв о магазине</span>
          &nbsp;<NuxtLink :to="localePath('/')">Подробнее...</NuxtLink>
        </div>
      </div>
    </div>
  </section>
</template>