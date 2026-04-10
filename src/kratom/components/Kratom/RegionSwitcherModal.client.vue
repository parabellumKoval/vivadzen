<script setup lang="ts">
const isOpen = ref(false)
const storageKey = 'kratom-region-switcher-modal-dismissed-v1'
const desktopMediaQuery = '(min-width: 1024px)'

let timer: ReturnType<typeof window.setTimeout> | null = null

const close = () => {
  isOpen.value = false
  sessionStorage.setItem(storageKey, '1')
}

onMounted(() => {
  if (sessionStorage.getItem(storageKey) || !window.matchMedia(desktopMediaQuery).matches) {
    return
  }

  timer = window.setTimeout(() => {
    isOpen.value = true
  }, 10000)
})

onUnmounted(() => {
  if (timer !== null) {
    window.clearTimeout(timer)
  }
})
</script>

<template>
  <Transition name="region-switcher-modal">
    <aside
      v-if="isOpen"
      class="region-switcher-modal"
      role="dialog"
      :aria-label="$t('kratom.region_switcher.title')"
    >
      <button
        type="button"
        class="region-switcher-modal__close"
        :aria-label="$t('kratom.region_switcher.dismiss')"
        @click="close"
      >
        <IconCSS name="ph:x" size="18" />
      </button>

      <KratomRegionSwitcher variant="modal" />
    </aside>
  </Transition>
</template>

<style scoped lang="scss">
.region-switcher-modal {
  position: fixed;
  right: 14px;
  bottom: 14px;
  z-index: 320;
  width: min(420px, calc(100vw - 28px));

  @include desktop {
    right: 24px;
    bottom: 24px;
  }
}

.region-switcher-modal__close {
  position: absolute;
  top: 14px;
  right: 14px;
  z-index: 2;
  width: 34px;
  height: 34px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 0;
  border-radius: 999px;
  background: rgba(23, 32, 25, 0.08);
  color: #172019;
  cursor: pointer;
}

.region-switcher-modal-enter-active,
.region-switcher-modal-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.region-switcher-modal-enter-from,
.region-switcher-modal-leave-to {
  opacity: 0;
  transform: translateY(14px);
}
</style>
