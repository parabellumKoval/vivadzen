<script setup lang="ts">
import type { TgCategory } from '~/types/tg'

const props = defineProps<{
  categories: TgCategory[]
  activeCategory?: string | null
}>()

const { t } = useTgI18n()
const { categoryPath } = useTgRouting()

const countOf = (category: TgCategory) => {
  return category.count ?? category.products_count ?? category.productsCount ?? category.meta?.products_count ?? null
}
</script>

<template>
  <div class="category-bar">
    <NuxtLink
      :to="categoryPath()"
      class="category-bar__pill"
      :class="{ active: !activeCategory }"
      :prefetch="false"
    >
      {{ t('all') }}
    </NuxtLink>

    <NuxtLink
      v-for="category in props.categories"
      :key="category.slug || category.id"
      :to="categoryPath(category.slug)"
      class="category-bar__pill"
      :class="{ active: category.slug === activeCategory }"
      :prefetch="false"
    >
      {{ category.name }}
      <span v-if="countOf(category) !== null" class="category-bar__count">{{ countOf(category) }}</span>
    </NuxtLink>
  </div>
</template>

<style scoped>
.category-bar {
  position: sticky;
  z-index: 50;
  top: 56px;
  display: flex;
  gap: 8px;
  overflow-x: auto;
  background: var(--color-bg);
  padding: 12px 16px;
  scrollbar-width: none;
}

.category-bar::-webkit-scrollbar {
  display: none;
}

.category-bar__pill {
  display: inline-flex;
  flex-shrink: 0;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-full);
  background: var(--color-white);
  color: var(--color-text-muted);
  padding: 7px 14px;
  align-items: center;
  gap: 5px;
  white-space: nowrap;
  font-size: 13px;
  font-weight: 600;
}

.category-bar__pill.active {
  border-color: var(--color-primary);
  background: var(--color-primary);
  color: var(--color-white);
}

.category-bar__count {
  opacity: 0.8;
  font-size: 11px;
  font-weight: 400;
}
</style>
