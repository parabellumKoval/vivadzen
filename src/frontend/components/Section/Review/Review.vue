<script setup>
import {useReviewFaker} from '~/composables/fakers/useReviewFaker.ts'

const reviews = computed(() => {
  return useReviewFaker()(4)
})


const getPhotoSrc = (image) => {
  if(image?.src) {
    return image.src
    // return '/server/' + image.src
  } else {
    return null
  }
}

const feedback = computed(() => {
  return [
    {
      id: 1,
      author: {
        name: 'Натали Кыргызтан',
        photo: '/images/avatars/4.jpg',
      },
      created_at: new Date(), 
      text: 'Я хотел бы поделиться своим положительным опытом покупок в магазине биодобавок djini.com.ua. Этот магазин предлагает широкий выбор высококачественных биологических добавок,помогают поддерживать мое здоровье.'
    }
  ]
})
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