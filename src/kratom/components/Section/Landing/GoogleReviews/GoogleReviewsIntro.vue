<script setup lang="ts">
type Props = {
  rating?: number
  count?: number
}

const props = withDefaults(defineProps<Props>(), {
  rating: 0,
  count: 0
})

const { t } = useI18n()

const safeRating = computed(() => (Number.isFinite(props.rating) ? props.rating : 0))
const roundedRating = computed(() => Math.round(safeRating.value))
const displayRating = computed(() => safeRating.value.toFixed(1))
const displayCount = computed(() => (Number.isFinite(props.count) ? props.count : 0))

const getStarClass = (rating: number, index: number): string => {
  if (index <= rating) return 'filled'
  return 'empty'
}
</script>

<style src="./google-reviews-intro.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <section class="google-reviews-intro">
    <div class="google-reviews-intro__inner">
      <div class="google-reviews-intro__media">
        <nuxt-img
          src="/images/landing/drink.png"
          :alt="t('drink_alt')"
          loading="lazy"
          class="google-reviews-intro__image"
        />
      </div>
      <div class="google-reviews-intro__content">
        <h3 class="google-reviews-intro__title">
          {{ t('intro_title') }}
        </h3>
        <p class="google-reviews-intro__subtitle">
          {{ t('intro_subtitle', { count: displayCount }) }}
        </p>

        <div class="google-reviews-intro__rating">
          <div class="google-reviews-intro__rating-source">
            <Icon name="logos:google-icon" class="google-reviews-intro__google-icon" />
            <div class="google-reviews-intro__rating-stars">
              <IconCSS
                v-for="i in 5"
                :key="i"
                name="ph:star-fill"
                class="google-reviews-intro__rating-star"
                :class="getStarClass(roundedRating, i)"
              />
            </div>
            <span class="google-reviews-intro__rating-value">{{ displayRating }}</span>
          </div>
        </div>

        <div class="google-reviews-intro__cta">
          <p class="google-reviews-intro__cta-text">{{ t('cta_text') }}</p>
          <a
            href="https://g.page/r/CYrhuRRktnHVEBE/review"
            target="_blank"
            rel="noopener noreferrer"
            class="google-reviews-intro__cta-button"
          >
            <IconCSS name="logos:google-icon" />
            {{ t('leave_review') }}
          </a>
        </div>
      </div>
    </div>
  </section>
</template>
