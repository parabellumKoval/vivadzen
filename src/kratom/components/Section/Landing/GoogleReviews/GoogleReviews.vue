<script setup lang="ts">
import {
  useGoogleReviewsStore,
  type GoogleReview,
  type GoogleReviewsMeta,
} from '~/store/googleReviews'
import GoogleReviewsIntro from './GoogleReviewsIntro.vue'

type DisplayMode = 'carousel' | 'masonry'

type Props = {
  mode?: DisplayMode
  perPage?: number
  paginated?: boolean
  dataKey?: string
  titleTag?: string
}

const props = withDefaults(defineProps<Props>(), {
  mode: 'carousel',
  perPage: 20,
  paginated: false,
  dataKey: 'google-reviews',
  titleTag: 'h2',
})

const { t, locale } = useI18n()
const googleReviewsStore = useGoogleReviewsStore()

const items = ref<GoogleReview[]>([])
const metaState = ref<GoogleReviewsMeta | null>(null)
const isLoadingMore = ref(false)

const isServer = process.server
const isMasonry = computed(() => props.mode === 'masonry')
const commentLimit = computed(() => (isMasonry.value ? 280 : 180))

const requestParams = computed(() => ({
  page: 1,
  per_page: props.perPage,
  rating: 5,
  has_comment: true,
}))

const asyncKey = computed(() => `${props.dataKey}-${props.mode}-${props.perPage}`)

const {
  data: reviewsData,
  pending: reviewsPending,
} = await useAsyncData(
  asyncKey,
  async () => {
    return await googleReviewsStore.fetchReviews(requestParams.value)
  },
  {
    lazy: !isServer,
    server: true,
    watch: [requestParams],
  }
)

watch(
  () => reviewsData.value,
  (value) => {
    items.value = value?.reviews || []
    metaState.value = value?.meta || null
  },
  { immediate: true }
)

const reviews = computed(() => items.value)
const meta = computed(() => metaState.value)
const averageRating = computed(() => meta.value?.avg_rating || 0)
const totalReviews = computed(() => meta.value?.total || 0)
const hasMorePages = computed(() => {
  if (!props.paginated || reviewsPending.value) {
    return false
  }

  const currentPage = Number(meta.value?.current_page || 1)
  const lastPage = Number(meta.value?.last_page || 1)
  return currentPage < lastPage
})

const mergeReviews = (current: GoogleReview[], incoming: GoogleReview[]) => {
  const merged = new Map<string, GoogleReview>()

  for (const review of current) {
    const key = String(review.review_id || review.id)
    merged.set(key, review)
  }

  for (const review of incoming) {
    const key = String(review.review_id || review.id)
    merged.set(key, review)
  }

  return Array.from(merged.values())
}

const loadMore = async () => {
  if (!hasMorePages.value || isLoadingMore.value) {
    return
  }

  isLoadingMore.value = true

  try {
    const nextPage = Number(meta.value?.current_page || 1) + 1
    const response = await googleReviewsStore.fetchReviews({
      ...requestParams.value,
      page: nextPage,
    })

    if (response.reviews?.length) {
      items.value = mergeReviews(items.value, response.reviews)
    }

    if (response.meta) {
      metaState.value = response.meta
    }
  } finally {
    isLoadingMore.value = false
  }
}

const formatDate = (dateString: string) => {
  if (!dateString) return ''

  try {
    return new Intl.DateTimeFormat(locale.value, {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    }).format(new Date(dateString))
  } catch {
    return new Date(dateString).toLocaleDateString()
  }
}

const getStarClass = (rating: number, index: number): string => {
  if (index <= rating) return 'filled'
  return 'empty'
}

const getReviewerInitials = (name: string) => {
  if (!name) return '?'

  const parts = name.trim().split(' ')
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }

  return name.slice(0, 2).toUpperCase()
}

const truncateComment = (comment: string, maxLength: number) => {
  if (!comment) return ''
  if (comment.length <= maxLength) return comment
  return `${comment.slice(0, maxLength).trim()}...`
}
</script>

<style src="./google-reviews.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <section class="google-reviews" :class="{ 'google-reviews--masonry': isMasonry }">
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
          <component :is="titleTag" class="google-reviews__title">
            {{ t('title') }}
          </component>
          <p class="google-reviews__description">
            {{ t('subtitle') }}
          </p>
        </div>
      </div>

      <GoogleReviewsIntro :count="totalReviews" :rating="averageRating" />

      <div v-if="reviewsPending && !reviews.length" class="google-reviews__loading">
        <div class="google-reviews__loading-spinner"></div>
        <p>{{ t('loading') }}</p>
      </div>

      <template v-else-if="reviews.length">
        <div v-if="!isMasonry" class="google-reviews__carousel-wrapper">
          <SnapCarousel
            :items="reviews"
            :items-per-page="{ mobile: 1, tablet: 2, desktop: 3, ld: 3, xld: 3 }"
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
                  {{ truncateComment(review.comment, commentLimit) }}
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

        <div v-else class="google-reviews__masonry">
          <article v-for="review in reviews" :key="review.review_id || review.id" class="review-card">
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
              {{ truncateComment(review.comment, commentLimit) }}
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
                {{ truncateComment(review.reply.comment, 120) }}
              </p>
            </div>
          </article>
        </div>

        <div v-if="isMasonry && props.paginated && hasMorePages" class="google-reviews__load-more">
          <button
            type="button"
            class="google-reviews__load-more-button"
            :disabled="isLoadingMore"
            @click="loadMore"
          >
            {{ isLoadingMore ? t('loading_more') : t('load_more') }}
          </button>
        </div>
      </template>

      <div v-else class="google-reviews__empty">
        <IconCSS name="ph:chat-circle-text" class="google-reviews__empty-icon" />
        <p>{{ t('no_reviews') }}</p>
      </div>
    </div>
  </section>
</template>
