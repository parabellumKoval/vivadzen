<script setup lang="ts">
import { onMounted, onBeforeUnmount, ref, computed, nextTick } from 'vue'
import { useCarouselConfig, resolveItemsPerPage, resolveGap } from '../composables/useCarouselConfig'

type Mode = 'page' | 'item'
type GapValue = number | string | Record<string, number | string>

const props = defineProps<{
  items?: any[]
  itemsPerPage?: Record<string, number>
  mode?: Mode
  loop?: boolean
  gap?: GapValue
  showArrows?: boolean
  showDots?: boolean
  snapStop?: 'normal' | 'always'
  ariaLabel?: string
  /** в page-режиме: начальная страница; в item-режиме: начальный индекс */
  initialPage?: number
  wheelToScroll?: boolean
}>()

const emit = defineEmits<{
  (e: 'pageChange', page: number): void
  (e: 'indexChange', index: number): void
  (e: 'init', payload: { pages: number; items: number; itemsPerPage: number }): void
}>()

const cfg = useCarouselConfig()
const viewport = ref<HTMLElement | null>(null)
const ready = ref(false)

const hasItemsArray = computed(() => Array.isArray(props.items))
const mode = computed<Mode>(() => props.mode || cfg.defaults.mode)
const loop = computed<boolean>(() => props.loop ?? cfg.defaults.loop)
const showArrows = computed<boolean>(() => props.showArrows ?? cfg.defaults.showArrows)
const showDots = computed<boolean>(() => props.showDots ?? cfg.defaults.showDots)
const snapStop = computed(() => props.snapStop ?? cfg.defaults.snapStop)
const wheelToScroll = computed(() => props.wheelToScroll ?? true)

const containerWidth = ref(0)
const itemsCount = ref(0)
const itemsPerPageNow = ref(1)
const pageCount = computed(() => Math.max(1, Math.ceil(itemsCount.value / Math.max(1, itemsPerPageNow.value))))
const activePage = ref(0)
const activeIndex = ref(0)

let ro: ResizeObserver | null = null
let mo: MutationObserver | null = null
let raf = 0
let measureRaf = 0
let measureQueued = false

/** утилиты */
const clamp = (v: number, min: number, max: number) => Math.min(Math.max(v, min), max)

/** список слайдов */
function slides (): HTMLElement[] {
  const el = viewport.value
  if (!el) return []
  return Array.from(el.querySelectorAll<HTMLElement>('[data-snap-slide]'))
}

/** Точные позиции: rect относительно viewport + scrollLeft
 * Это устойчиво к gap, zoom, subpixel и рефлоу шрифтов/изображений.
 */
function positions (): number[] {
  const el = viewport.value
  if (!el) return []
  const base = el.getBoundingClientRect().left
  const left = el.scrollLeft
  return slides().map(s => {
    const r = s.getBoundingClientRect()
    // позиция слайда внутри прокручиваемой области в абсолютных координатах scrollLeft
    return Math.round((r.left - base) + left)
  })
}

/** стартовые индексы страниц: 0, ipp, 2*ipp, ... */
function pageStartIndices (): number[] {
  const list: number[] = []
  const ipp = Math.max(1, itemsPerPageNow.value)
  for (let i = 0; i < itemsCount.value; i += ipp) list.push(i)
  return list
}
function pageStartPositions (): number[] {
  const pos = positions()
  const max = maxScrollLeft()
  return pageStartIndices().map(i => Math.min(pos[i] ?? 0, max))
}

function maxScrollLeft(): number {
  const el = viewport.value
  if (!el) return 0
  return Math.max(0, Math.round(el.scrollWidth - el.clientWidth))
}

/** ===== стабильное измерение макета =====
 * ждём два RAF после nextTick — браузер гарантированно применит layout и gap
 */
async function measureStable () {
  const el = viewport.value
  if (!el) return
  await nextTick()
  await new Promise(r => requestAnimationFrame(() => r(null)))
  await new Promise(r => requestAnimationFrame(() => r(null)))

  containerWidth.value = el.clientWidth
  itemsCount.value = slides().length
  itemsPerPageNow.value = Math.max(
    1,
    resolveItemsPerPage(containerWidth.value, cfg.screens, props.itemsPerPage || cfg.defaults.itemsPerPage)
  )

  const gapCss = resolveGap(containerWidth.value, cfg.screens, props.gap ?? cfg.defaults.gap)
  el.style.setProperty('--items-per-page', String(itemsPerPageNow.value))
  el.style.setProperty('--gap', gapCss)
  el.style.setProperty('--snap-stop', snapStop.value)

  // корректируем активные значения
  activeIndex.value = clamp(activeIndex.value, 0, Math.max(0, itemsCount.value - 1))
  activePage.value = clamp(activePage.value, 0, Math.max(0, pageCount.value - 1))
}

/** мягкий дебаунс измерений, чтобы не мешать скроллу */
function queueMeasure () {
  if (measureQueued) return
  measureQueued = true
  cancelAnimationFrame(measureRaf)
  measureRaf = requestAnimationFrame(async () => {
    measureQueued = false
    await measureStable()
  })
}

/** навигация */
function goToScrollLeft (left: number) {
  const el = viewport.value
  if (!el) return
  el.scrollTo({ left: clamp(left, 0, maxScrollLeft()), behavior: 'smooth' })
}
function goToIndex (idx: number) {
  const el = viewport.value
  if (!el) return
  const pos = positions()
  const clamped = clamp(idx, 0, Math.max(0, itemsCount.value - 1))
  const target = Math.min(pos[clamped] ?? 0, maxScrollLeft())
  goToScrollLeft(target)
}
function goToPage (p: number) {
  const clamped = clamp(p, 0, Math.max(0, pageCount.value - 1))
  const idx = clamped * Math.max(1, itemsPerPageNow.value)
  goToIndex(idx)
}

/** ближайшая страница к текущему scrollLeft */
function detectActivePage (): number {
  const el = viewport.value
  if (!el) return 0
  const x = Math.round(el.scrollLeft)
  const starts = pageStartPositions()
  let nearest = 0, best = Infinity
  for (let i = 0; i < starts.length; i++) {
    const d = Math.abs(starts[i] - x)
    if (d < best) { best = d; nearest = i }
  }
  return nearest
}
/** индекс элемента по текущему scrollLeft (левый видимый) */
function detectActiveIndex (): number {
  const el = viewport.value
  if (!el) return 0
  const x = Math.round(el.scrollLeft) + 1
  const pos = positions()
  let i = 0
  for (let k = 0; k < pos.length; k++) {
    if (pos[k] <= x) i = k
    else break
  }
  return i
}

function onScroll () {
  cancelAnimationFrame(raf)
  raf = requestAnimationFrame(() => {
    const idx = detectActiveIndex()
    if (idx !== activeIndex.value) {
      activeIndex.value = idx
      emit('indexChange', activeIndex.value)
    }
    const pg = detectActivePage()
    if (pg !== activePage.value) {
      activePage.value = pg
      emit('pageChange', activePage.value)
    }
  })
}

function atEnd (): boolean {
  const el = viewport.value
  if (!el) return false
  return Math.abs(Math.round(el.scrollLeft) - maxScrollLeft()) <= 2
}
function atStart (): boolean {
  const el = viewport.value
  if (!el) return true
  return Math.round(el.scrollLeft) <= 2
}

function nextItem () {
  const nextIdx = (activeIndex.value + 1) % Math.max(1, itemsCount.value)
  goToIndex(nextIdx)
}
function prevItem () {
  const prevIdx = (activeIndex.value - 1 + Math.max(1, itemsCount.value)) % Math.max(1, itemsCount.value)
  goToIndex(prevIdx)
}
function nextPageFn () {
  const p = (activePage.value + 1) % Math.max(1, pageCount.value)
  goToPage(p)
}
function prevPageFn () {
  const p = (activePage.value - 1 + Math.max(1, pageCount.value)) % Math.max(1, pageCount.value)
  goToPage(p)
}

function onArrowNext () {
  if (mode.value === 'item') {
    if (loop.value && atEnd()) { goToIndex(0); return }
    nextItem()
  } else {
    if (loop.value && activePage.value === pageCount.value - 1) { goToPage(0); return }
    nextPageFn()
  }
}
function onArrowPrev () {
  if (mode.value === 'item') {
    if (loop.value && atStart()) { goToIndex(itemsCount.value - 1); return }
    prevItem()
  } else {
    if (loop.value && activePage.value === 0) { goToPage(pageCount.value - 1); return }
    prevPageFn()
  }
}

function onWheel (e: WheelEvent) {
  if (!wheelToScroll.value) return
  const el = viewport.value
  if (!el) return
  if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
    e.preventDefault()
    el.scrollLeft += e.deltaY
  }
}

/** ===== dots =====
 * item: switches = max(1, N - ipp + 1), точки 0..switches-1
 * page: как обычно
 */
const switchesCount = computed(() => {
  const N = itemsCount.value
  const ipp = Math.max(1, itemsPerPageNow.value)
  return Math.max(1, N - ipp + 1)
})
const dotsCount = computed(() => mode.value === 'item' ? switchesCount.value : pageCount.value)
const activeDot = computed(() => mode.value === 'item'
  ? Math.min(activeIndex.value, Math.max(0, switchesCount.value - 1))
  : activePage.value
)
function onDotClick(i: number) {
  if (mode.value === 'item') {
    const maxLeft = Math.max(0, itemsCount.value - itemsPerPageNow.value)
    goToIndex(clamp(i, 0, maxLeft))
  } else {
    goToPage(i)
  }
}

/** слушатель загрузки картинок — меряем после их загрузки */
function bindImagesLoad () {
  const el = viewport.value
  if (!el) return
  const imgs = Array.from(el.querySelectorAll('img')) as HTMLImageElement[]
  for (const img of imgs) {
    if (!img.complete) img.addEventListener('load', queueMeasure, { once: true })
  }
}

onMounted(async () => {
  ready.value = false
  await measureStable()   // первая стабильная мерка
  bindImagesLoad()
  ready.value = true

  // RO по изменениям размеров вьюпорта
  if ('ResizeObserver' in window) {
    ro = new ResizeObserver(() => queueMeasure())
    if (viewport.value) ro.observe(viewport.value)
  } else {
    window.addEventListener('resize', queueMeasure)
  }

  // MO по изменению числа слайдов
  if ('MutationObserver' in window && viewport.value) {
    mo = new MutationObserver(() => queueMeasure())
    mo.observe(viewport.value, { childList: true })
  }

  // начальная позиция
  const init = props.initialPage ?? 0
  if (mode.value === 'page') goToPage(init)
  else goToIndex(init)

  emit('init', { pages: pageCount.value, items: itemsCount.value, itemsPerPage: itemsPerPageNow.value })
})

onBeforeUnmount(() => {
  if (ro && viewport.value) ro.unobserve(viewport.value)
  if (ro) ro.disconnect()
  if (mo) mo.disconnect()
  window.removeEventListener('resize', queueMeasure)
  cancelAnimationFrame(raf)
  cancelAnimationFrame(measureRaf)
})
</script>


<style src="./snap-carousel.scss" lang="scss" scoped></style>

<template>
  <div class="snap-carousel">
    <div
      class="snap-carousel__viewport"
      ref="viewport"
      role="region"
      :aria-roledescription="'carousel'"
      :aria-label="ariaLabel || 'Carousel'"
      :data-ready="ready ? '1' : '0'"
      @scroll="onScroll"
      @wheel.passive="onWheel"
      tabindex="0"
    >
      <slot v-if="!hasItemsArray" />
      <template v-else>
        <SnapSlide v-for="(item, idx) in items" :key="idx">
          <slot name="item" :item="item" :index="idx" />
        </SnapSlide>
      </template>
    </div>

    <div v-if="dotsCount > 1" class="snap-carousel__controls">
      <button
        v-if="showArrows"
        class="snap-carousel__arrow snap-carousel__arrow--prev"
        type="button"
        aria-label="Previous"
        @click="onArrowPrev"
      >
        <slot name="prev"><span aria-hidden="true">‹</span></slot>
      </button>
      <div v-if="showDots" class="snap-carousel__dots" role="tablist">
        <slot name="dots" :count="dotsCount" :active="activeDot" :goTo="onDotClick">
          <button
            v-for="i in dotsCount"
            :key="i-1"
            class="snap-carousel__dot"
            :class="{ 'is-active': (i-1) === activeDot }"
            type="button"
            :aria-label="`Go to ${mode === 'item' ? 'position' : 'page'} ${i}`"
            @click="onDotClick(i-1)"
          />
        </slot>
      </div>
      <button
        v-if="showArrows"
        class="snap-carousel__arrow snap-carousel__arrow--next"
        type="button"
        aria-label="Next"
        @click="onArrowNext"
      >
        <slot name="next"><span aria-hidden="true">›</span></slot>
      </button>
    </div>
  </div>
</template>
