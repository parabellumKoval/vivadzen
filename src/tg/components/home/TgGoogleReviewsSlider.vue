<script setup lang="ts">
import type { TgGoogleReview } from '~/composables/useTgGoogleReviews'

const props = withDefaults(defineProps<{
  perPage?: number
  commentLimit?: number
}>(), {
  perPage: 12,
  commentLimit: 200
})

const { t, locale } = useTgI18n()
const { reviews, meta, loading, fetchReviews } = useTgGoogleReviews()

const initialLoading = ref(true)

onMounted(async () => {
  await fetchReviews({
    page: 1,
    per_page: props.perPage,
    rating: 5,
    has_comment: true
  })
  initialLoading.value = false
})

const averageRating = computed(() => Number(meta.value?.avg_rating || 0))
const totalReviews = computed(() => Number(meta.value?.total || 0))
const ratingLabel = computed(() => averageRating.value ? averageRating.value.toFixed(1) : '5.0')

const formatDate = (dateString: string) => {
  if (!dateString) return ''
  try {
    return new Intl.DateTimeFormat(locale.value, {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    }).format(new Date(dateString))
  } catch {
    return new Date(dateString).toLocaleDateString()
  }
}

const getInitials = (name?: string | null) => {
  const value = (name || '').trim()
  if (!value) return '?'
  const parts = value.split(/\s+/)
  if (parts.length >= 2) return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  return value.slice(0, 2).toUpperCase()
}

const truncate = (text: string, max: number) => {
  if (!text) return ''
  if (text.length <= max) return text
  return `${text.slice(0, max).trim()}...`
}

const reviewerName = (review: TgGoogleReview) => {
  if (review.reviewer?.is_anonymous) return t('anonymous')
  return review.reviewer?.name || t('anonymous')
}
</script>

<template>
  <section class="reviews-slider">
    <div class="reviews-slider__head">
      <div class="reviews-slider__title-wrap">
        <h2 class="tg-title reviews-slider__title">{{ t('reviews_title') }}</h2>
        <p v-if="totalReviews" class="tg-subtitle">
          {{ t('reviews_subtitle', { count: totalReviews, rating: ratingLabel }) }}
        </p>
        <p v-else class="tg-subtitle">{{ t('reviews_subtitle_empty') }}</p>
      </div>
      <div class="reviews-slider__badge" :aria-label="`Google ${ratingLabel}`">
        <svg viewBox="0 0 48 48" width="20" height="20" aria-hidden="true">
          <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3c-1.6 4.7-6.1 8-11.3 8-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 8 3l5.7-5.7C34 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.4-.4-3.5z"/>
          <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 16 19 13 24 13c3.1 0 5.8 1.2 8 3l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/>
          <path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2c-2 1.5-4.5 2.4-7.2 2.4-5.2 0-9.6-3.3-11.2-7.9l-6.5 5C9.5 39.6 16.2 44 24 44z"/>
          <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.3-2.2 4.2-4.1 5.6l6.2 5.2C41.8 35.8 44 30.3 44 24c0-1.3-.1-2.4-.4-3.5z"/>
        </svg>
        <span>{{ ratingLabel }}</span>
      </div>
    </div>

    <div v-if="(loading || initialLoading) && !reviews.length" class="reviews-slider__loading">
      <div v-for="n in 3" :key="n" class="review-card review-card--skeleton">
        <div class="skeleton review-card__skeleton-line review-card__skeleton-line--header" />
        <div class="skeleton review-card__skeleton-line" />
        <div class="skeleton review-card__skeleton-line" />
        <div class="skeleton review-card__skeleton-line review-card__skeleton-line--short" />
      </div>
    </div>

    <div v-else-if="reviews.length" class="reviews-slider__track">
      <article
        v-for="review in reviews"
        :key="review.review_id || review.id"
        class="review-card"
      >
        <header class="review-card__head">
          <div class="review-card__avatar">
            <img
              v-if="review.reviewer?.photo_url"
              :src="review.reviewer.photo_url"
              :alt="reviewerName(review)"
              class="review-card__avatar-img"
              loading="lazy"
              referrerpolicy="no-referrer"
            >
            <span v-else class="review-card__avatar-initials">
              {{ getInitials(review.reviewer?.name) }}
            </span>
          </div>
          <div class="review-card__meta">
            <div class="review-card__name">{{ reviewerName(review) }}</div>
            <div class="review-card__date">{{ formatDate(review.review_created_at) }}</div>
          </div>
        </header>

        <div class="review-card__rating" :aria-label="`Rating ${review.rating} of 5`">
          <span
            v-for="i in 5"
            :key="i"
            class="review-card__star"
            :class="{ 'review-card__star--filled': i <= review.rating }"
          >★</span>
        </div>

        <p v-if="review.comment" class="review-card__comment">
          {{ truncate(review.comment, props.commentLimit) }}
        </p>
      </article>
    </div>

    <div v-else class="reviews-slider__empty">
      {{ t('reviews_empty') }}
    </div>
  </section>
</template>

<style scoped>
.reviews-slider {
  display: grid;
  gap: 12px;
  padding: 0 16px;
}

.reviews-slider__head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}

.reviews-slider__title-wrap {
  display: grid;
  gap: 2px;
  flex: 1;
  min-width: 0;
}

.reviews-slider__title {
  font-size: 18px;
}

.reviews-slider__badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 10px;
  border-radius: var(--radius-full);
  background: var(--color-white);
  box-shadow: var(--shadow-card);
  font-size: 13px;
  font-weight: 700;
  color: var(--color-text);
  flex-shrink: 0;
}

.reviews-slider__track {
  display: flex;
  gap: 12px;
  margin: 0 -16px;
  padding: 4px 16px 12px;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
}

.reviews-slider__track::-webkit-scrollbar {
  display: none;
}

.reviews-slider__loading {
  display: flex;
  gap: 12px;
  margin: 0 -16px;
  padding: 4px 16px 12px;
  overflow-x: hidden;
}

.review-card {
  display: grid;
  gap: 10px;
  flex: 0 0 86%;
  max-width: 320px;
  padding: 14px;
  border-radius: var(--radius-lg);
  background: var(--color-white);
  box-shadow: var(--shadow-card);
  scroll-snap-align: start;
}

.review-card--skeleton {
  align-content: start;
  min-height: 160px;
}

.review-card__skeleton-line {
  height: 12px;
  border-radius: var(--radius-sm);
}

.review-card__skeleton-line--header {
  height: 36px;
  width: 60%;
}

.review-card__skeleton-line--short {
  width: 50%;
}

.review-card__head {
  display: flex;
  align-items: center;
  gap: 10px;
}

.review-card__avatar {
  display: grid;
  place-items: center;
  width: 36px;
  height: 36px;
  border-radius: var(--radius-full);
  background: var(--color-bg-card);
  color: var(--color-text);
  font-size: 13px;
  font-weight: 700;
  overflow: hidden;
  flex-shrink: 0;
}

.review-card__avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.review-card__avatar-initials {
  line-height: 1;
}

.review-card__meta {
  display: grid;
  gap: 2px;
  min-width: 0;
}

.review-card__name {
  font-size: 14px;
  font-weight: 700;
  color: var(--color-text);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.review-card__date {
  font-size: 11px;
  color: var(--color-text-muted);
}

.review-card__rating {
  display: inline-flex;
  gap: 2px;
  font-size: 14px;
  letter-spacing: 1px;
  line-height: 1;
}

.review-card__star {
  color: var(--color-border);
}

.review-card__star--filled {
  color: var(--color-accent);
}

.review-card__comment {
  margin: 0;
  font-size: 13px;
  line-height: 1.5;
  color: var(--color-text);
  display: -webkit-box;
  -webkit-line-clamp: 6;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.reviews-slider__empty {
  padding: 16px;
  border-radius: var(--radius-lg);
  background: var(--color-white);
  text-align: center;
  color: var(--color-text-muted);
  font-size: 13px;
}
</style>
