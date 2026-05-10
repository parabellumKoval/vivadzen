<script setup lang="ts">
const props = withDefaults(defineProps<{
  category?: string | null
}>(), {
  category: null
})

const categorySlug = computed(() => props.category || null)
const { t } = useTgI18n()
const catalog = useTgCatalog(categorySlug)
const {
  categories,
  products,
  meta,
  activeCategory,
  loading,
  hasMore,
  loadMore,
  loadProducts
} = catalog

const initialLoading = ref(true)

const refreshCatalog = async () => {
  initialLoading.value = true
  try {
    await catalog.refresh()
  } finally {
    initialLoading.value = false
  }
}

onMounted(() => {
  void refreshCatalog()
})

watch(categorySlug, async () => {
  if (initialLoading.value) return
  initialLoading.value = true
  try {
    await loadProducts(true)
  } finally {
    initialLoading.value = false
  }
})

const title = computed(() => activeCategory.value?.name || t('catalog'))
const count = computed(() => Number(meta.value?.total || products.value.length || 0))
</script>

<template>
  <TgLayout :title="title" :show-lang="true">
    <TgCategoryBar :categories="categories" :active-category="categorySlug" />

    <section class="tg-page catalog-page">
      <div class="catalog-page__head">
        <h1 class="tg-title">{{ title }}</h1>
        <p class="tg-subtitle">{{ t('products_count', { count }) }}</p>
      </div>

      <div v-if="(loading || initialLoading) && !products.length" class="tg-grid">
        <TgProductCardSkeleton v-for="item in 4" :key="item" />
      </div>

      <div v-else-if="products.length" class="tg-grid">
        <TgProductCard
          v-for="product in products"
          :key="product.id || product.slug"
          :product="product"
        />
      </div>

      <div v-else class="tg-empty">
        <div>
          <div class="tg-empty__icon">□</div>
          <p class="tg-empty__title">{{ t('empty_catalog') }}</p>
        </div>
      </div>

      <button
        v-if="hasMore"
        type="button"
        class="tg-btn catalog-page__more"
        :disabled="loading"
        @click="loadMore"
      >
        {{ t('load_more') }}
      </button>
    </section>

    <TgGoogleReviewsSlider class="catalog-page__reviews" />
  </TgLayout>
</template>

<style scoped>
.catalog-page {
  display: grid;
  gap: 16px;
}

.catalog-page__head {
  display: grid;
  gap: 4px;
}

.catalog-page__more {
  margin-top: 4px;
}

.catalog-page__reviews {
  margin-top: 8px;
  padding-bottom: 16px;
}
</style>
