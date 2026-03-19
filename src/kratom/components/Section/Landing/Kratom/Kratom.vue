<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import KratomFlowerCream from './KratomFlowerCream.svg'
import WrapperHtml from '@/modules/wrapperHtml/components/WrapperHtml.vue'

const { t } = useI18n()

const tiltX = ref(0)
const tiltY = ref(0)

const handleMouseMove = (e) => {
  const windowWidth = window.innerWidth
  const windowHeight = window.innerHeight

  const mouseX = (e.clientX / windowWidth - 0.5) * 2
  const mouseY = (e.clientY / windowHeight - 0.5) * 2

  tiltX.value = mouseY * -10
  tiltY.value = mouseX * 15
}

onMounted(() => {
  window.addEventListener('mousemove', handleMouseMove)
})

onUnmounted(() => {
  window.removeEventListener('mousemove', handleMouseMove)
})

const scrollToColors = () => {
  if (process.server) return
  const element = document.getElementById('colors')
  if (!element) return
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
  element.scrollIntoView({
    behavior: prefersReducedMotion ? 'auto' : 'smooth',
    block: 'start'
  })
  window.history.replaceState(null, '', '#colors')
}

const benefits = computed(() => [
  { title: t('benefits.items.focus'), icon: 'ph:lightbulb', color: 'green' },
  { title: t('benefits.items.energy'), icon: 'ph:lightning', color: 'orange' },
  { title: t('benefits.items.gut'), icon: 'ph:heart', color: 'red' },
  { title: t('benefits.items.immune'), icon: 'ph:shield-check', color: 'blue' },
  { title: t('benefits.items.stress'), icon: 'ph:sparkle', color: 'violet' }
])
</script>

<style src="./kratom.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <section class="kratom-section">
    <div class="container">
      <h2 class="kratom-hero__title">
        <WrapperHtml :string="t('hero.title')" :wrappers="['<span class=\'orange\'>']" />
      </h2>
      <div class="kratom-hero">
        <div class="kratom-hero__product">
          <KratomFlowerCream class="kratom-section__bg-flower kratom-section__bg-flower--1" />
          <KratomFlowerCream class="kratom-section__bg-flower kratom-section__bg-flower--2" />

          <div class="kratom-hero__pack-wrapper">
            <nuxt-img
              src="/images/landing/product.png"
              :alt="t('hero.pack_alt')"
              width="506"
              height="776"
              quality="90"
              fit="cover"
              sizes="mobile: 100vw tablet: 550px desktop: 550px"
              class="kratom-hero__pack-img"
              :style="{
                '--tilt-x': `${tiltX}deg`,
                '--tilt-y': `${tiltY}deg`
              }"
            />
            <nuxt-img src="/images/landing/kratom-2-leaves-2.png" class="kratom-hero__deco-leaf kratom-hero__deco-leaf--1" alt="" />
            <nuxt-img src="/images/landing/kratom-2-leaves.png" class="kratom-hero__deco-leaf kratom-hero__deco-leaf--2" alt="" />
            <nuxt-img src="/images/landing/kratom-3-leaves.png" class="kratom-hero__deco-leaf kratom-hero__deco-leaf--3" alt="" />
          </div>
        </div>

        <div class="kratom-hero__visual">
          <div class="kratom-cup">
            <div class="kratom-cup__image-container">
              <nuxt-img
                src="/images/landing/kratom-cap.png"
                :alt="t('hero.cup_alt')"
                width="722"
                height="725"
                quality="70"
                fit="cover"
                sizes="mobile: 100vw tablet: 750px desktop: 750px"
                class="kratom-cup__img"
              />

              <nuxt-img src="/images/landing/kratom-leaf-2.png" class="kratom-cup__leaf-2" alt="" />
              <nuxt-img src="/images/landing/kratom-leaf-1.png" class="kratom-cup__leaf-1" alt="" />
              <nuxt-img src="/images/landing/seed.png" width="1536" height="1024" class="kratom-cup__seeds" alt="" />
            </div>

            <div class="kratom-callout kratom-callout--top-left">
              <div class="kratom-callout__content">
                <strong>{{ t('callouts.organic.title') }}</strong>
                <span>{{ t('callouts.organic.text') }}</span>
              </div>
              <svg class="kratom-callout__arrow" viewBox="0 0 80 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M2 2 Q 40 2, 70 32" stroke-width="3" stroke-linecap="round" fill="none" />
                <path d="M62 34 L70 32 L69 22" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none" />
              </svg>
            </div>

            <div class="kratom-callout kratom-callout--top-right">
              <div class="kratom-callout__content">
                <strong>{{ t('callouts.alkaloid.title') }}</strong>
                <span>{{ t('callouts.alkaloid.text') }}</span>
              </div>
              <svg class="kratom-callout__arrow" viewBox="0 0 80 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M78 2 Q 40 2, 10 32" stroke-width="3" stroke-linecap="round" fill="none" />
                <path d="M18 34 L10 32 L11 22" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none" />
              </svg>
            </div>

            <div class="kratom-callout kratom-callout--bottom-left">
              <div class="kratom-callout__content">
                <strong>{{ t('callouts.nano.title') }}</strong>
                <span>{{ t('callouts.nano.text') }}</span>
              </div>
              <svg class="kratom-callout__arrow" viewBox="0 0 80 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M2 38 Q 40 38, 70 8" stroke-width="3" stroke-linecap="round" fill="none" />
                <path d="M62 6 L70 8 L69 18" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none" />
              </svg>
            </div>

            <div class="kratom-callout kratom-callout--bottom-right">
              <div class="kratom-callout__content">
                <strong>{{ t('callouts.lab.title') }}</strong>
                <span>{{ t('callouts.lab.text') }}</span>
              </div>
              <svg class="kratom-callout__arrow" viewBox="0 0 80 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M78 38 Q 40 38, 10 8" stroke-width="3" stroke-linecap="round" fill="none" />
                <path d="M18 6 L10 8 L11 18" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <div class="kratom-benefits">
        <h3 class="kratom-benefits__title">{{ t('benefits.title') }}</h3>
        <div class="kratom-benefits__grid">
          <div
            v-for="benefit in benefits"
            :key="benefit.title"
            class="kratom-benefit-card"
            :class="`kratom-benefit-card--${benefit.color}`"
          >
            <div class="kratom-benefit-card__icon-box">
              <IconCSS :name="benefit.icon" class="kratom-benefit-card__icon" />
            </div>
            <span class="kratom-benefit-card__label">{{ benefit.title }}</span>
          </div>
        </div>

        <div class="kratom-benefits__cta">
          <button class="button alternate uppercase bold kratom-btn" type="button" @click="scrollToColors">
            {{ t('benefits.cta') }}
          </button>
        </div>
      </div>
    </div>
  </section>
</template>
