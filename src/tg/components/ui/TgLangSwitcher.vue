<script setup lang="ts">
const open = ref(false)
const { t } = useTgI18n()
const { locale, locales, switchLocale } = useTgRouting()

const labels: Record<string, string> = {
  uk: 'Українська',
  ru: 'Русский',
  cs: 'Čeština',
  en: 'English',
  de: 'Deutsch',
  es: 'Español'
}

const selectLocale = async (code: string) => {
  open.value = false
  await switchLocale(code)
}
</script>

<template>
  <button type="button" class="lang-btn" :aria-label="t('change_language')" @click="open = true">
    {{ locale.toUpperCase() }}
  </button>

  <TgBottomSheet v-model="open" :title="t('change_language')">
    <div class="lang-list">
      <button
        v-for="code in locales"
        :key="code"
        type="button"
        class="lang-list__item"
        :class="{ active: code === locale }"
        @click="selectLocale(code)"
      >
        <span>{{ labels[code] || code }}</span>
        <span v-if="code === locale">✓</span>
      </button>
    </div>
  </TgBottomSheet>
</template>

<style scoped>
.lang-btn {
  width: 44px;
  height: 44px;
  border: 0;
  border-radius: var(--radius-sm);
  background: transparent;
  color: var(--color-primary);
  font-size: 13px;
  font-weight: 700;
}

.lang-list {
  display: grid;
  gap: 8px;
}

.lang-list__item {
  display: flex;
  min-height: 48px;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-md);
  background: var(--color-bg-card);
  padding: 0 14px;
  align-items: center;
  justify-content: space-between;
  color: var(--color-text);
  font-size: 14px;
  font-weight: 600;
}

.lang-list__item.active {
  border-color: var(--color-primary);
  background: rgba(115, 197, 111, 0.12);
  color: var(--color-primary);
}
</style>
