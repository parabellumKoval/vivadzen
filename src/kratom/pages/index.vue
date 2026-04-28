<script setup lang="ts">
const { t } = useI18n()
const pageRoot = ref<HTMLElement | null>(null)
const isRevealReady = ref(false)
let revealObservers: IntersectionObserver[] = []

definePageMeta({
  layout: 'landing',
  footerFlushTop: true,
})

const marqueeItems = computed(() => [t('marquee.items.0'), t('marquee.items.1')])

const assembleSection = (section: Element, immediate = false) => {
  if (section.classList.contains('page-reveal--assembled')) return
  const markAssembled = () => section.classList.add('page-reveal--assembled')
  if (immediate) {
    markAssembled()
    return
  }
  window.setTimeout(markAssembled, 180)
}

const activateSection = (section: Element, immediate = false) => {
  if (section.classList.contains('page-reveal--active')) return
  section.classList.add('page-reveal--active')
  if (immediate) {
    assembleSection(section, true)
    return
  }
  requestAnimationFrame(() => requestAnimationFrame(() => assembleSection(section)))
}

onMounted(async () => {
  await nextTick()
  const sections = Array.from(pageRoot.value?.querySelectorAll('.page-reveal-section') ?? [])
  if (!sections.length) return

  const isMobileViewport = window.matchMedia('(max-width: 767px)').matches
  const initialViewportThreshold = window.innerHeight * (isMobileViewport ? 1.35 : 0.9)
  sections.forEach((section) => {
    if (section.getBoundingClientRect().top <= initialViewportThreshold) {
      activateSection(section, true)
    }
  })

  isRevealReady.value = true

  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    sections.forEach((section) => activateSection(section, true))
    return
  }

  const registerObserver = (targets: Element[], options: IntersectionObserverInit) => {
    const pendingTargets = targets.filter((section) => !section.classList.contains('page-reveal--active'))
    if (!pendingTargets.length) return

    const observer = new IntersectionObserver((entries, currentObserver) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return
        activateSection(entry.target)
        currentObserver.unobserve(entry.target)
      })
    }, options)

    pendingTargets.forEach((section) => observer.observe(section))
    revealObservers.push(observer)
  }

  registerObserver(sections, isMobileViewport
    ? {
        threshold: 0.04,
        rootMargin: '0px 0px 32% 0px',
      }
    : {
        threshold: 0.12,
        rootMargin: '0px 0px 8% 0px',
      })
})

onBeforeUnmount(() => {
  revealObservers.forEach((observer) => observer.disconnect())
  revealObservers = []
})

useSeo().setPageSeo('home', {
  fallbackTitle: () => t('title.czech-republic-kratom'),
})
</script>

<i18n src="./_landing/lang.yaml" lang="yaml"></i18n>

<template>
  <div ref="pageRoot" class="czech-kratom-page" :class="{ 'page-reveal-ready': isRevealReady }">
    <section-landing-hero-header />

    <div id="home" class="page-reveal-section">
      <section-landing-hero-banner />
    </div>
    <div id="ticker" class="page-reveal-section">
      <section-landing-marquee-ticker :items="marqueeItems" />
    </div>
    <div id="store" class="page-reveal-section">
      <section-landing-product-grid />
    </div>
    <div id="legal" class="page-reveal-section">
      <section-landing-legal-market />
    </div>
    <div id="timeline" class="page-reveal-section">
      <section-landing-timeline-section />
    </div>
    <!-- <div id="promo" class="page-reveal-section">
      <section-landing-promo-subscribe />
    </div> -->
    <div id="kratom" class="page-reveal-section">
      <section-landing-kratom />
    </div>
    <div id="colors" class="page-reveal-section">
      <section-landing-kratom-variety />
    </div>
    <div id="origins" class="page-reveal-section">
      <section-landing-kratom-variety-origins />
    </div>
    <div id="sample" class="page-reveal-section">
      <section-landing-sample-set />
    </div>
    <div id="bonus" class="page-reveal-section">
      <section-landing-referral-bonus />
    </div>

    <!-- <div id="store" class="page-reveal-section">
      <section-landing-online-store />
    </div> -->
    <div id="reviews" class="page-reveal-section">
      <section-landing-google-reviews />
    </div>
    <div id="contacts" class="page-reveal-section">
      <section-landing-contacts />
      <section-landing-partner-stores-promo />
    </div>
    <div id="welcome" class="page-reveal-section">
      <section-landing-welcome />
    </div>
  </div>
</template>

<style scoped lang="scss">
.czech-kratom-page.page-reveal-ready {
  .page-reveal-section {
    opacity: 0;
    filter: blur(8px);
    transform: translate3d(0, 24px, 0) scale(0.992);
    transition:
      opacity 0.48s cubic-bezier(0.22, 0.61, 0.36, 1),
      filter 0.48s cubic-bezier(0.22, 0.61, 0.36, 1),
      transform 0.48s cubic-bezier(0.22, 0.61, 0.36, 1);
    will-change: opacity, filter, transform;
  }

  .page-reveal-section.page-reveal--active {
    opacity: 1;
    filter: blur(0);
    transform: translate3d(0, 0, 0) scale(1);
  }

  :deep(.page-reveal-section > * > *) {
    --reveal-x: 0px;
    --reveal-y: 30px;
    --reveal-scale: 0.87;
    --reveal-rotate: 0deg;
    opacity: 0;
    filter: blur(12px);
    translate: var(--reveal-x) var(--reveal-y);
    scale: var(--reveal-scale);
    rotate: var(--reveal-rotate);
    transition:
      opacity 0.72s cubic-bezier(0.2, 0.9, 0.32, 1),
      filter 0.72s cubic-bezier(0.2, 0.9, 0.32, 1),
      translate 0.82s cubic-bezier(0.2, 0.9, 0.32, 1),
      scale 0.82s cubic-bezier(0.2, 0.9, 0.32, 1),
      rotate 0.82s cubic-bezier(0.2, 0.9, 0.32, 1);
  }

  :deep(.page-reveal-section.page-reveal--assembled > * > *) {
    opacity: 1;
    filter: blur(0);
    translate: 0 0;
    scale: 1;
    rotate: 0deg;
  }
}
</style>
