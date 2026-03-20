<script setup>
import WrapperHtml from '@/modules/wrapperHtml/components/WrapperHtml.vue'
const { t } = useI18n()

const activeIndex = ref(0)
const activeHandler = (index) => {
  activeIndex.value = index
}

const ribbonRef = ref(null)
const isHoverable = ref(true)
const isReady = ref(false)
let hoverMedia
let hoverMediaListener
let mediaLoadHandler
const isOverflowing = ref(false)
const overlapPx = ref(0)
let resizeObserver

const measureOverlap = () => {
  const ribbon = ribbonRef.value
  if (!ribbon) return

  const styles = getComputedStyle(ribbon)
  const overflowRoot = ribbon.parentElement || ribbon
  const rootStyles = getComputedStyle(overflowRoot)
  const parseGap = (value) => {
    const parsed = parseFloat(value)
    return Number.isFinite(parsed) ? parsed : null
  }
  let gap = parseGap(styles.columnGap) ?? parseGap(styles.gap) ?? parseGap(styles.rowGap)
  if (!Number.isFinite(gap)) {
    gap = 0
  }
  const paddingX =
    (parseFloat(rootStyles.paddingLeft) || 0) + (parseFloat(rootStyles.paddingRight) || 0)
  const cards = Array.from(ribbon.querySelectorAll('.origin-card'))
  const overlapSlots = Math.max(cards.length - 1, 0)

  if (!cards.length || overlapSlots === 0) {
    isOverflowing.value = false
    overlapPx.value = 0
    return
  }

  if (gap === 0 && cards.length > 1 && styles.flexDirection.includes('row')) {
    const firstRect = cards[0].getBoundingClientRect()
    const secondRect = cards[1].getBoundingClientRect()
    gap = Math.max(secondRect.left - firstRect.right, 0)
  }

  const totalWidth =
    cards.reduce((sum, el) => sum + el.offsetWidth, 0) + gap * overlapSlots
  const availableWidth = Math.max(overflowRoot.clientWidth - paddingX, 0)

  if (totalWidth > availableWidth) {
    overlapPx.value = Math.ceil((totalWidth - availableWidth) / overlapSlots)
    isOverflowing.value = true
  } else {
    overlapPx.value = 0
    isOverflowing.value = false
  }
}

const scheduleMeasure = () => {
  requestAnimationFrame(measureOverlap)
}

onMounted(async () => {
  await nextTick()
  scheduleMeasure()
  const ribbon = ribbonRef.value
  if (!ribbon) return
  hoverMedia = window.matchMedia('(hover: hover) and (pointer: fine)')
  isHoverable.value = hoverMedia.matches
  hoverMediaListener = (event) => {
    isHoverable.value = event.matches
  }
  if (hoverMedia.addEventListener) {
    hoverMedia.addEventListener('change', hoverMediaListener)
  } else {
    hoverMedia.addListener(hoverMediaListener)
  }
  resizeObserver = new ResizeObserver(() => {
    scheduleMeasure()
  })
  resizeObserver.observe(ribbon)
  mediaLoadHandler = () => {
    scheduleMeasure()
  }
  ribbon.querySelectorAll('img, video').forEach((el) => {
    el.addEventListener('load', mediaLoadHandler, { once: true })
    el.addEventListener('loadedmetadata', mediaLoadHandler, { once: true })
  })
  window.addEventListener('load', mediaLoadHandler, { once: true })
  requestAnimationFrame(() => {
    scheduleMeasure()
    isReady.value = true
  })
})

onBeforeUnmount(() => {
  if (resizeObserver) {
    resizeObserver.disconnect()
  }
  if (hoverMedia && hoverMediaListener) {
    if (hoverMedia.removeEventListener) {
      hoverMedia.removeEventListener('change', hoverMediaListener)
    } else {
      hoverMedia.removeListener(hoverMediaListener)
    }
  }
  if (mediaLoadHandler) {
    window.removeEventListener('load', mediaLoadHandler)
  }
})

const hoverHandler = (index) => {
  if (!isHoverable.value) return
  activeHandler(index)
}


const originConfigs = [
  {
    key: 'maengda',
    video: '/video/regions/maengda.mp4',
    img: '/images/landing/regions/maengda.png',
    bgColor: '#fdf9d0',
    color: '#e39c0f'
  },
  {
    key: 'malay',
    video: '/video/regions/Malay.mp4',
    img: '/images/landing/regions/malay2.png',
    bgColor: '#c6dfca',
    color: '#5a9a91'
  },
  {
    key: 'thai',
    video: '/video/regions/Thai.mp4',
    img: '/images/landing/regions/thai.png',
    bgColor: '#f4dfd7',
    color: '#a95e74'
  },
  {
    key: 'borneo',
    video: '/video/regions/Borneo3.mp4',
    img: '/images/landing/regions/borneo2.png',
    bgColor: '#bde6eb',
    color: '#0f5880'
  },
  {
    key: 'sumatra',
    video: '/video/regions/Sumatra.mp4',
    img: '/images/landing/regions/sumatra2.png',
    bgColor: '#fcd0b3',
    color: '#d34b1e'
  }
]

const specLabels = computed(() => ({
  speed: t('spec_labels.speed'),
  duration: t('spec_labels.duration'),
  character: t('spec_labels.character')
}))

const origins = computed(() =>
  originConfigs.map((origin) => ({
    ...origin,
    name: t(`origins.${origin.key}.name`),
    subtitle: t(`origins.${origin.key}.subtitle`),
    specs: {
      speed: t(`origins.${origin.key}.specs.speed`),
      duration: t(`origins.${origin.key}.specs.duration`),
      character: t(`origins.${origin.key}.specs.character`)
    }
  }))
)
</script>

<style src="./origins.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <section class="kratom-regions-section" :class="{ 'is-ready': isReady }">
    <div class="origins-ribbon">
      <div class="header-card">
        <h3 class="header-card__title">
          <WrapperHtml :string="t('header.title')" :wrappers="['<span class=\'orange\'>']" />
        </h3>
        <!-- <nuxt-img
          src="/images/landing/product.png"
          :alt="t('header.image_alt')"
          width="506"
          height="776"
          quality="90"
          fit="cover"
          sizes="mobile: 100vw tablet: 550px desktop: 550px"
          class="header-card__img"
        /> -->
      </div>
      <div class="origins-ribbon__content">
        <div
          class="origins-ribbon__cards"
          ref="ribbonRef"
          :class="{ 'is-overflowing': isOverflowing }"
          :style="{ '--overlap': `${overlapPx}px` }"
        >
          <div
            v-for="(origin, index) in origins"
            :key="origin.key"
            :class="{ active: index === activeIndex }"
            :style="{ zIndex: index === activeIndex ? origins.length + 10 : index + 1, '--bg-color': origin.bgColor, '--color': origin.color }"
            @mouseover="hoverHandler(index)"
            @click="activeHandler(index)"
            class="origin-card"
          >
            <div class="origin-card__title">
              <span class="big">{{ origin.name }}</span>
              <span class="same"> {{ t('kratom_word') }}</span>
            </div>
            <div class="origin-card__content">
              <div class="origin-card__media">
                <transition name="media-switch" mode="out-in">
                  <video
                    v-if="index === activeIndex"
                    :key="`video-${origin.key}`"
                    :src="origin.video"
                    autoplay
                    loop
                    muted
                    playsinline
                    preload="metadata"
                  ></video>
                  <nuxt-img
                    v-else
                    :key="`image-${origin.key}`"
                    :src="origin.img"
                    :alt="origin.name"
                    class="origin-card__image"
                  />
                </transition>
              </div>
              <div class="origin-card__specs">
                <p class="origin-card__subtitle">{{ origin.subtitle }}</p>
                <div v-for="(value, key) in origin.specs" :key="key" class="spec-pill">
                  <span class="spec-pill__label">{{ specLabels[key] }}</span>
                  <span class="spec-pill__value">{{ value }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="bottom__wrapper">
          <div class="bottom__text">
            <div class="bottom__text-title">{{ t('bottom.title') }}</div>
            <div class="bottom__text-desc">{{ t('bottom.description') }}</div>
          </div>
          <section-landing-kratom-variety-support class="support-box" />
        </div>
      </div>
    </div>
  </section>
</template>
