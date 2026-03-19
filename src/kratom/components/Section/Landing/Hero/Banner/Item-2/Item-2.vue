<script setup>
const { t } = useI18n()

const scrollToSection = (id) => {
  if (process.server) return
  const element = document.getElementById(id)
  if (!element) return
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
  element.scrollIntoView({
    behavior: prefersReducedMotion ? 'auto' : 'smooth',
    block: 'start'
  })
  window.history.replaceState(null, '', `#${id}`)
}

const scrollToKratomVariety = () => {
  scrollToSection('colors')
}

const scrollToContacts = () => {
  scrollToSection('contacts')
}
</script>

<style src='./item-2.scss' lang='scss' scoped></style>
<i18n src='./lang.yaml' lang='yaml'></i18n>

<template>
  <section class="hero-banner-item-2">
    <div class="hero-banner-item-2__bg-wrapper">
      <video
        autoplay
        loop
        muted
        playsinline
        class="hero-banner-item-2__bg-video"
      >
        <source src="/video/banner.mp4" type="video/mp4">
      </video>
      <div class="hero-banner-item-2__overlay"></div>
    </div>

    <div class="hero-banner-item-2__content">
      <div class="container">

        <div class="hero-banner-top">
          <div class="hero-banner-item-2__license">
            <nuxt-img
              src="/images/company-white-color.png"
              width="246"
              height="50"
              fit="cover"
              sizes="mobile: 80px tablet: 270px desktop: 270px"
              class="license-logo"
              :alt="t('license_logo_alt')"
            />
            <p class="license-text">{{ t('license_text') }}</p>
          </div>
          <div class="hero-banner-top__line"></div>
          <div class="hero-banner-item-2__rating">
            <div class="rating-stars">
              <span class="star" v-for="i in 5" :key="i">★</span>
            </div>
            <p class="rating-text">{{ t('rating_text') }}</p>
          </div>
        </div>

        <h1 class="hero-banner-item-2__title">
          {{ t('title') }}
        </h1>

        <p class="hero-banner-item-2__description">
          {{ t('description') }}
        </p>

        <div class="hero-banner-item-2__buttons">
          <button type="button" class="button promo" @click="scrollToKratomVariety">
            {{ t('btn_catalog') }}
          </button>
          <button type="button" class="button color-secondary" @click="scrollToContacts">
            {{ t('btn_shop') }}
          </button>
        </div>
      </div>
    </div>

    <div class="more__wrapper">
      <IconCSS name="iconamoon:mouse-thin" class="more__icon" />
    </div>
  </section>
</template>
