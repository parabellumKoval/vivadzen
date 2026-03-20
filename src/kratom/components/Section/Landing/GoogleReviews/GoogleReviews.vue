<script setup lang="ts">
import { useGoogleReviewsStore, type GoogleReview } from '~/store/googleReviews'
import GoogleReviewsIntro from './GoogleReviewsIntro.vue'

const { t } = useI18n()
const googleReviewsStore = useGoogleReviewsStore()

const isServer = process.server

const {
  data: reviewsData,
  pending: reviewsPending,
} = await useAsyncData(
  'google-reviews-landing',
  async () => {
    return await googleReviewsStore.fetchReviews({
      per_page: 20,
      rating: 5,
      has_comment: true,
    })
  },
  {
    lazy: !isServer,
    server: true
  }
)

const reviews = computed(() => reviewsData.value?.reviews || [])
const meta = computed(() => reviewsData.value?.meta || null)
const averageRating = computed(() => meta.value?.avg_rating || 0)
const totalReviews = computed(() => meta.value?.total || 0)

const formatDate = (dateString: string) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('cs-CZ', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const getStarClass = (rating: number, index: number): string => {
  if (index <= rating) return 'filled'
  return 'empty'
}

const getReviewerInitials = (name: string): string => {
  if (!name) return '?'
  const parts = name.trim().split(' ')
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }
  return name.slice(0, 2).toUpperCase()
}

const truncateComment = (comment: string, maxLength: number = 200): string => {
  if (!comment) return ''
  if (comment.length <= maxLength) return comment
  return comment.slice(0, maxLength).trim() + '...'
}
</script>

<style src="./google-reviews.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <section class="google-reviews">
    <div class="container">

      <div class="google-reviews__header">
        <div class="google-reviews__header-content">
          <div class="google-reviews__google-badge">
            <nuxt-img
              src="/images/company.png"
              :alt="t('company_logo_alt')"
              loading="lazy"
              class="google-reviews__company-logo"
            />
          </div>
          <h2 class="google-reviews__title">
            {{ t('title') }}
          </h2>
          <p class="google-reviews__description">
            {{ t('subtitle') }}
          </p>
        </div>
      </div>

      <GoogleReviewsIntro
        :count="totalReviews"
        :rating="averageRating"
      />

      <div v-if="reviewsPending" class="google-reviews__loading">
        <div class="google-reviews__loading-spinner"></div>
        <p>{{ t('loading') }}</p>
      </div>

      <div v-else-if="reviews.length" class="google-reviews__carousel-wrapper">
        <SnapCarousel
          :items="reviews"
          :items-per-page="{ mobile: 1, tablet: 2, desktop: 3, ld: 4, xld: 4 }"
          :gap="{ mobile: 16, tablet: 20, desktop: 24 }"
          :show-arrows="true"
          :show-dots="true"
          mode="page"
          :loop="true"
          :aria-label="t('carousel_label')"
          class="google-reviews__carousel"
        >
          <template #item="{ item: review }">
            <div class="review-card">
              <div class="review-card__header">
                <div class="review-card__avatar">
                  <img 
                    v-if="review.reviewer?.photo_url" 
                    :src="review.reviewer.photo_url" 
                    :alt="review.reviewer?.name || t('reviewer_alt')"
                    class="review-card__avatar-img"
                  />
                  <span v-else class="review-card__avatar-initials">
                    {{ getReviewerInitials(review.reviewer?.name) }}
                  </span>
                </div>
                <div class="review-card__info">
                  <h4 class="review-card__name">
                    {{ review.reviewer?.is_anonymous ? t('anonymous') : review.reviewer?.name }}
                  </h4>
                  <p class="review-card__date">{{ formatDate(review.review_created_at) }}</p>
                </div>
                <IconCSS name="logos:google-icon" class="review-card__google-icon" />
              </div>

              <div class="review-card__rating">
                <IconCSS 
                  v-for="i in 5" 
                  :key="i"
                  name="ph:star-fill"
                  class="review-card__star"
                  :class="getStarClass(review.rating, i)"
                />
              </div>

              <p v-if="review.comment" class="review-card__comment">
                {{ truncateComment(review.comment, 180) }}
              </p>
              <p v-else class="review-card__no-comment">
                {{ t('no_comment') }}
              </p>

              <div v-if="review.reply" class="review-card__reply">
                <div class="review-card__reply-header">
                  <IconCSS name="ph:arrow-bend-down-right" />
                  <span>{{ t('owner_response') }}</span>
                </div>
                <p class="review-card__reply-text">
                  {{ truncateComment(review.reply.comment, 100) }}
                </p>
              </div>
            </div>
          </template>

          <template #prev>
            <IconCSS name="ph:caret-left-bold" />
          </template>
          <template #next>
            <IconCSS name="ph:caret-right-bold" />
          </template>
        </SnapCarousel>
      </div>

      <div v-else class="google-reviews__empty">
        <IconCSS name="ph:chat-circle-text" class="google-reviews__empty-icon" />
        <p>{{ t('no_reviews') }}</p>
      </div>
    </div>
  </section>
</template>
