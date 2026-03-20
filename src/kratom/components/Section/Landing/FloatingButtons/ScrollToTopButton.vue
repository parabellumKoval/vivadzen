<script setup>
const { t } = useI18n()

const isVisible = ref(false)
let visibilityRafId = 0

const updateVisibility = () => {
  isVisible.value = (window.scrollY || document.documentElement.scrollTop || 0) > 340
}

const scheduleVisibilityUpdate = () => {
  if (visibilityRafId) {
    return
  }

  visibilityRafId = window.requestAnimationFrame(() => {
    visibilityRafId = 0
    updateVisibility()
  })
}

const scrollToTop = () => {
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches

  window.scrollTo({
    top: 0,
    behavior: prefersReducedMotion ? 'auto' : 'smooth'
  })
}

onMounted(() => {
  updateVisibility()
  window.addEventListener('scroll', scheduleVisibilityUpdate, { passive: true })
  window.addEventListener('resize', scheduleVisibilityUpdate)
})

onBeforeUnmount(() => {
  window.removeEventListener('scroll', scheduleVisibilityUpdate)
  window.removeEventListener('resize', scheduleVisibilityUpdate)

  if (visibilityRafId) {
    window.cancelAnimationFrame(visibilityRafId)
  }
})
</script>

<i18n lang="yaml">
ru:
  aria_label: "Прокрутить страницу вверх"

uk:
  aria_label: "Прокрутити сторінку вгору"

en:
  aria_label: "Scroll page to top"

cs:
  aria_label: "Posunout stránku nahoru"
</i18n>

<template>
  <button
    class="scroll-top-button"
    :class="{ 'scroll-top-button--visible': isVisible }"
    :aria-label="t('aria_label')"
    :tabindex="isVisible ? 0 : -1"
    type="button"
    @click="scrollToTop"
  >
    <IconCSS name="ph:arrow-up-bold" class="scroll-top-button__icon" />
  </button>
</template>

<style scoped lang="scss">
.scroll-top-button {
  width: clamp(48px, 4.8vw, 58px);
  height: clamp(48px, 4.8vw, 58px);
  border: 0;
  border-radius: 50%;
  cursor: pointer;
  opacity: 0;
  pointer-events: none;
  transform: translateY(10px) scale(0.92);
  color: #ffffff;
  backdrop-filter: blur(3px);
  background: rgba(0,0,0,0.2);
  // background: radial-gradient(circle at 28% 22%, #ffc891 0%, #ff8b2c 52%, #e45710 100%);
  // box-shadow:
  //   0 14px 26px rgba(228, 87, 16, 0.32),
  //   0 3px 8px rgba(0, 0, 0, 0.26);
  transition:
    opacity 0.26s ease,
    transform 0.26s ease,
    box-shadow 0.26s ease;
}

.scroll-top-button--visible {
  opacity: 1;
  pointer-events: auto;
  transform: translateY(0) scale(1);
}

.scroll-top-button:hover {
  // box-shadow:
  //   0 20px 32px rgba(228, 87, 16, 0.4),
  //   0 5px 10px rgba(0, 0, 0, 0.28);
  transform: translateY(-2px) scale(1.03);
}

.scroll-top-button__icon {
  font-size: clamp(20px, 2vw, 24px);
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.24));
}

@media (max-width: 767px) {
  .scroll-top-button:hover {
    transform: translateY(0) scale(1);
  }
}
</style>
