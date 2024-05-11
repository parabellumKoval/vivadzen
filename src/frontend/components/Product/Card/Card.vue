<script setup>
import {useComparison} from '~/composables/product/useComparison.ts'
import {useFavorite} from '~/composables/product/useFavorite.ts'
import {useCart} from '~/composables/product/useCart.ts'
import {useCard} from '~/composables/product/useCard.ts'

const props = defineProps({
  item: {
    type: Object,
    required: true
  }
})

const { t } = useI18n()
const localePath = useLocalePath()

const isHover = ref(false)

const {isComparison, toComparisonHandler} = useComparison(props.item.id)
const {isFavorite, toFavoriteHandler} = useFavorite(props.item.id)
const {photos, stock, label} = useCard(props.item)
const {toCartHandler} = useCart(props.item)

// COMPUTEDS
const activePhoto = computed(() => {
  if(!isHover.value) {
    return photos.value[0]
  }else {
    return photos.value[1] || photos.value[0]
  }
})

const reviewsCount = computed(() => {
  return props.item?.reviews_rating_detailes?.reviews_count || 0
})

const favoriteIcon = computed(() => {
  if(isFavorite.value) {
    return 'iconoir:heart-solid'
  }else {
    return 'iconoir:heart'
  }
})

// METHODS

// HANDLERS
const toReviewsHandler = () => {
  navigateTo(localePath('/' + props.item.slug + '#reviews'))
}
</script>

<style src="./card.scss" lang="scss" scoped />
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <div v-if="item" :class="stock" class="card">

    <div v-if="label" :class="label?.class" class="label">
      {{ label.text }}
    </div>

    <div :class="{active: isComparison || isFavorite}" class="hover">
      <button
        @click="toComparisonHandler"
        :class="{active: isComparison}"
        class="hover-btn comparison-btn"
        clickable
      >
        <IconCSS name="ph:scales-light" size="24"></IconCSS>
      </button>

      <button
        @click="toFavoriteHandler"
        :class="{active: isFavorite}"
        class="hover-btn favorite-btn"
        clickable
      >
        <IconCSS :name="favoriteIcon" size="24"></IconCSS>
      </button>
    </div>

    <NuxtLink
      :to="localePath('/' + item.slug)"
      :aria-label="item.name"
      clickable
      class="image-wrapper"
    >
      <transition name="opacity">
        <nuxt-img
          :key = "isHover"
          :src = "activePhoto.src"
          :alt = "activePhoto.alt"
          :title = "activePhoto.title"
          @mouseover="() => isHover = true"
          @mouseleave="() => isHover = false"
          width="290"
          height="260"
          sizes = "mobile:100vw tablet:230px desktop:300px"
          format = "webp"
          quality = "60"
          loading = "lazy"
          fit="outside"
          placeholder="./images/noimage.png"
          class="image"
        />
      </transition>
    </NuxtLink>
    
    <div class="reviews">
      <simple-stars :amount="item?.rating || 0" class="rating"></simple-stars>
      
      <button v-if="reviewsCount" @click="toReviewsHandler" class="reviews-btn">
        {{ t('label.reviews', {reviews: reviewsCount}, reviewsCount) }}
      </button>
      <button v-else @click="toReviewsHandler" class="reviews-btn">
        <IconCSS name="iconoir:message" size="16"></IconCSS>
        {{ t('button.leave_review') }}
      </button>
    </div>

    <div class="name-wrapper">
      <NuxtLink
        :to="localePath(`/${item.slug}`)"
        class="name"
        clickable
      >
        {{ item.name }}
      </NuxtLink>
    </div>

    <hr class="line">

    <product-available :in-stock="item.inStock" class="amount"></product-available>

    <div class="sale">
      <button @click="toCartHandler" type="button" class="button primary small buy-btn">
        <span class="buy-btn-name">{{ t('button.buy') }}</span>
        <IconCSS name="iconoir:shopping-bag" class="buy-btn-icon"></IconCSS>
      </button>
      <product-price :price="item.price" :old-price="item.oldPrice"></product-price>
    </div>

  </div>
</template>