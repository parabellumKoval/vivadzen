<script setup lang="ts">
import type { TgCategory } from '~/types/tg'
import { normalizeCategorySlug } from '~/composables/useTgCatalog'

const props = defineProps<{
  categories: TgCategory[]
}>()

const emit = defineEmits<{
  select: [slug: string | null]
}>()

const { t } = useTgI18n()
const { categoryPath } = useTgRouting()

const countOf = (category: TgCategory) => {
  return category.count ?? category.products_count ?? category.productsCount ?? category.meta?.products_count ?? null
}

const select = (slug: string | null) => {
  emit('select', normalizeCategorySlug(slug))
}
</script>

<template>
  <div class="category-bar">
    <NuxtLink
      :to="categoryPath()"
      class="category-bar__pill"
      :prefetch="false"
      @click="select(null)"
    >
      {{ t('all') }}
    </NuxtLink>

    <NuxtLink
      v-for="category in props.categories"
      :key="category.slug || category.id"
      :to="categoryPath(category.slug)"
      class="category-bar__pill"
      :prefetch="false"
      @click="select(category.slug)"
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
  top: var(--tg-chrome-height);
  display: flex;
  height: var(--tg-category-bar-height);
  gap: 8px;
  overflow-x: auto;
  background: var(--color-bg);
  padding: 7px 16px;
  align-items: center;
  scrollbar-width: none;
}

.category-bar::-webkit-scrollbar {
  display: none;
}

.category-bar__pill {
  display: inline-flex;
  flex-shrink: 0;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-full);
  background: var(--color-white);
  color: var(--color-ink);
  padding: 8px 14px;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
  font-family: var(--font-display);
  font-size: 12px;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.category-bar__pill.router-link-exact-active {
  background: var(--color-ink);
  color: var(--color-lime);
  box-shadow: var(--shadow-card-sm);
}

.category-bar__count {
  display: inline-grid;
  min-width: 18px;
  height: 18px;
  border-radius: var(--radius-full);
  background: var(--color-lime);
  color: var(--color-ink);
  padding: 0 5px;
  place-items: center;
  font-size: 10px;
}

.category-bar__pill.router-link-exact-active .category-bar__count {
  background: var(--color-accent);
  color: var(--color-white);
}
</style>
