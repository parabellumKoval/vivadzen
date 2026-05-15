<script setup lang="ts">
const open = ref(false)
const { t } = useTgI18n()
const { locale, locales, switchLocale } = useTgRouting()

const labelOf = (code: string) => {
  const key = `language_name_${code}`
  const label = t(key)
  return label === key ? code.toUpperCase() : label
}

const selectLocale = async (code: string) => {
  open.value = false
  await switchLocale(code)
}
</script>

<template>
  <button type="button" class="lang-btn" :aria-label="t('change_language')" @click="open = true">
    <TgIcon name="globe" :size="16" :stroke="2.2" />
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
        <span>{{ labelOf(code) }}</span>
        <TgIcon v-if="code === locale" name="check" :size="18" :stroke="2.6" />
      </button>
    </div>
  </TgBottomSheet>
</template>

<style scoped>
.lang-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  width: 52px;
  height: 36px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-full);
  background: var(--color-white);
  color: var(--color-ink);
  padding: 0 8px;
  justify-content: center;
  font-family: var(--font-display);
  font-size: 11px;
  letter-spacing: 0.04em;
  line-height: 1;
}

.lang-list {
  display: grid;
  gap: 8px;
}

.lang-list__item {
  display: flex;
  min-height: 50px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-md);
  background: var(--color-white);
  padding: 0 14px;
  align-items: center;
  justify-content: space-between;
  color: var(--color-ink);
  font-family: var(--font-body);
  font-size: 14px;
  font-weight: 600;
}

.lang-list__item.active {
  background: var(--color-lime);
}
</style>
