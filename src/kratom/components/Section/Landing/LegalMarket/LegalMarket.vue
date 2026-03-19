<script setup>
const { t } = useI18n()
const { openLicenseModal } = useDocumentsModal()

const STAMP_RADIUS = 82
const stampText = computed(() => String(t('stamp_text') || ''))
const stampPathD = computed(
  () => `M 150,150 m -${STAMP_RADIUS},0 a ${STAMP_RADIUS},${STAMP_RADIUS} 0 1,1 ${STAMP_RADIUS * 2},0 a ${STAMP_RADIUS},${STAMP_RADIUS} 0 1,1 -${STAMP_RADIUS * 2},0`
)
const stampTextLength = computed(() => Math.round(2 * Math.PI * STAMP_RADIUS))
const stampFontSize = computed(() => {
  const length = stampText.value.length

  if (length >= 90) return '8px'
  if (length >= 75) return '9px'
  return '11px'
})
</script>

<style src="./legal-market.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>
<i18n src="./../lang.yaml" lang="yaml"></i18n>

<template>
  <section class="legal-wrapper">
    <div class="legal-flag">
      <div class="legal-flag__top"></div>
      <div class="legal-flag__bottom"></div>
      <div class="legal-flag__triangle"></div>
    </div>

    <div class="container">
      <div class="legal-inner">
        <div class="legal-left">
          <h2 class="legal-left__title">
            {{ t('title') }} <br> — {{ t('title_suffix') }} <span class="orange">{{ t('title_highlight') }}</span>
          </h2>
          <p class="legal-left__description">
            {{ t('description') }}
          </p>
          <button class="button secondary bold uppercase legal-left__button" type="button" @click="openLicenseModal">
            <IconCSS name="ix:eye-magnifying-glass" class="inline-icon" />
            <span>{{ t('license_button') }}</span>
          </button>
        </div>

        <div class="legal-right">
          <div class="legal-right__item legal-right__item--1">
            <div class="legal-right__img-wrapper">
              <nuxt-img
                src="/images/landing/logo.png"
                sizes="desktop: 400px"
                class="legal-right__img"
                :alt="t('right.item1.image_alt')"
              />
            </div>
            <h4 class="legal-right__title">{{ t('right.item1.title') }}</h4>
            <p class="legal-right__description">{{ t('right.item1.description') }}</p>
          </div>
          <div class="legal-right__item legal-right__item--2">
            <div class="legal-right__img-wrapper">
              <nuxt-img
                src="/images/landing/botl.png"
                sizes="desktop: 400px"
                class="legal-right__img"
                :alt="t('right.item2.image_alt')"
              />
              <svg class="legal-right__stamp" viewBox="0 0 300 300">
                <defs>
                  <path id="circlePath2" :d="stampPathD" />
                </defs>
                <text>
                  <textPath
                    class="legal-right__stamp-text"
                    href="#circlePath2"
                    startOffset="0%"
                    :textLength="stampTextLength"
                    lengthAdjust="spacingAndGlyphs"
                    :style="{ '--stamp-font-size': stampFontSize }"
                  >
                    {{ stampText }}
                  </textPath>
                </text>
              </svg>
            </div>
            <h4 class="legal-right__title">{{ t('right.item2.title') }}</h4>
            <p class="legal-right__description">{{ t('right.item2.description') }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
