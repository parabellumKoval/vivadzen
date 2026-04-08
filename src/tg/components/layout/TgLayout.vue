<script setup lang="ts">
withDefaults(defineProps<{
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
</script>

<template>
  <div class="tg-layout">
    <TgTopBar
      :title="title"
      :show-back="showBack"
      :show-lang="showLang"
      :transparent="transparent"
    />
    <main class="tg-main">
      <slot />
    </main>
    <TgBottomNav />
  </div>
</template>

<style scoped>
.tg-layout {
  min-height: var(--tg-viewport-height, 100dvh);
  max-width: 480px;
  margin: 0 auto;
  background: var(--color-bg);
}

.tg-main {
  min-height: var(--tg-viewport-height, 100dvh);
  padding-top: 56px;
  padding-bottom: calc(72px + env(safe-area-inset-bottom));
}
</style>
