<script setup lang="ts">
const props = withDefaults(defineProps<{
  title?: string
  showBack?: boolean
  showLang?: boolean
  transparent?: boolean
}>(), {
  title: '',
  showBack: false,
  showLang: false,
  transparent: false
})

const router = useRouter()
const { pathFor } = useTgRouting()
const { webApp } = useTelegram()

const goBack = () => {
  if (process.client && window.history.length > 1) {
    router.back()
    return
  }

  navigateTo(pathFor())
}

onMounted(() => {
  const backButton = webApp.value?.BackButton
  if (!backButton) return

  if (props.showBack) {
    backButton.show()
    backButton.onClick(goBack)
  } else {
    backButton.hide()
  }
})

watch(() => props.showBack, (value) => {
  const backButton = webApp.value?.BackButton
  if (!backButton) return

  if (value) {
    backButton.show()
    backButton.onClick(goBack)
  } else {
    backButton.hide()
  }
})

onBeforeUnmount(() => {
  const backButton = webApp.value?.BackButton
  if (!backButton) return

  try {
    backButton.offClick(goBack)
    backButton.hide()
  } catch {
    backButton.hide()
  }
})
</script>

<template>
  <header class="tg-topbar" :class="{ 'tg-topbar--transparent': transparent }">
    <button
      v-if="showBack"
      type="button"
      class="tg-topbar__btn"
      aria-label="Back"
      @click="goBack"
    >
      <TgIcon name="arrow-left" :size="22" :stroke="2.4" />
    </button>
    <div v-else class="tg-topbar__spacer" />

    <div class="tg-topbar__title">
      <slot name="title">{{ title || 'Vivadzen' }}</slot>
    </div>

    <TgLangSwitcher v-if="showLang" />
    <div v-else class="tg-topbar__spacer" />
  </header>
</template>

<style scoped>
.tg-topbar {
  position: fixed;
  z-index: 100;
  top: var(--tg-supheader-height);
  right: 0;
  left: 0;
  display: grid;
  grid-template-columns: 52px minmax(0, 1fr) 52px;
  align-items: center;
  width: 100%;
  max-width: 480px;
  height: var(--tg-topbar-height);
  margin: 0 auto;
  border-bottom: 2px solid var(--color-ink);
  background: var(--color-bg);
  padding: 0 6px;
}

.tg-topbar--transparent {
  background: rgba(255, 245, 225, 0.92);
  backdrop-filter: blur(12px);
}

.tg-topbar__btn,
.tg-topbar__spacer {
  width: 44px;
  height: 44px;
}

.tg-topbar__btn {
  display: grid;
  place-items: center;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-full);
  background: var(--color-white);
  color: var(--color-ink);
  line-height: 1;
}

.tg-topbar__title {
  overflow: hidden;
  text-align: center;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-family: var(--font-display);
  font-size: 18px;
  letter-spacing: -0.01em;
  text-transform: uppercase;
}
</style>
