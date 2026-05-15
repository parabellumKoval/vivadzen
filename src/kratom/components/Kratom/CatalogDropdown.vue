<script setup lang="ts">
type CategoryRecord = {
  id: number | string
  name: string
  slug: string
}

const props = defineProps<{
  enabled: boolean
}>()

const emit = defineEmits<{
  navigate: []
}>()

const { t, locale } = useI18n()
const regionPath = useToLocalePath()
const regionStore = useRegion()
const { loadCatalogCategories } = useKratomCatalogCategories()

const categoriesCache = useState<Record<string, CategoryRecord[]>>(
  'kratom-catalog-dropdown-categories',
  () => ({}),
)
const loadedKeys = useState<Record<string, boolean>>(
  'kratom-catalog-dropdown-categories-loaded',
  () => ({}),
)
const pending = ref(false)

const storageKey = computed(() => {
  const localeKey = String(locale.value || 'default').toLowerCase()
  const regionKey = String(regionStore.regionAlias.value || 'default').toLowerCase()
  return `${localeKey}:${regionKey}`
})

const categories = computed(() => categoriesCache.value[storageKey.value] || [])

const menuItems = computed(() => [
  {
    key: 'all',
    label: t('kratom.catalog.all_kratom'),
    to: regionPath('/catalog'),
  },
  ...categories.value.map((category) => ({
    key: category.id ?? category.slug,
    label: category.name,
    to: regionPath(`/catalog/${category.slug}`),
  })),
])

const loadCategories = async () => {
  if (loadedKeys.value[storageKey.value] || pending.value) {
    return
  }

  pending.value = true

  try {
    const payload = await loadCatalogCategories()
    categoriesCache.value[storageKey.value] = payload.categories
    loadedKeys.value[storageKey.value] = true
  } catch (error) {
    console.error('Failed to load catalog dropdown categories', error)
  } finally {
    pending.value = false
  }
}

watch(
  () => [props.enabled, locale.value, regionStore.regionAlias.value],
  ([isEnabled]) => {
    if (isEnabled) {
      loadCategories()
    }
  },
  { immediate: true },
)

const handleNavigate = () => {
  emit('navigate')
}
</script>

<template>
  <div class="catalog-dropdown">
    <div class="catalog-dropdown__panel">
      <div v-if="pending" class="catalog-dropdown__list catalog-dropdown__list--loading" aria-hidden="true">
        <div v-for="index in 6" :key="index" class="catalog-dropdown__skeleton"></div>
      </div>

      <!--
      <div v-else-if="products.length" class="catalog-dropdown__list">
        <NuxtLink
          v-for="product in products"
          :key="product.id ?? product.slug"
          :to="regionPath(`/${product.slug}`)"
          class="catalog-dropdown__item"
          @click="handleNavigate"
        >
          <span class="catalog-dropdown__thumb">
            <nuxt-img
              :src="getImage(product)"
              :alt="product.name"
              :title="product.name"
              width="96"
              height="96"
              sizes="96px"
              format="webp"
              quality="70"
              fit="contain"
            />
          </span>

          <span class="catalog-dropdown__content">
            <span class="catalog-dropdown__name">{{ product.name }}</span>
            <span v-if="getProductPrice(product) !== null" class="catalog-dropdown__price">
              <span class="catalog-dropdown__price-prefix">{{ t('from') }}</span>
              <simple-price :value="getProductPrice(product)" :currency-code="getCurrencyCode(product)" />
            </span>
          </span>
        </NuxtLink>
      </div>
      -->

      <div v-else-if="menuItems.length" class="catalog-dropdown__list">
        <NuxtLink
          v-for="item in menuItems"
          :key="item.key"
          :to="item.to"
          class="catalog-dropdown__item"
          @click="handleNavigate"
        >
          <span class="catalog-dropdown__name">{{ item.label }}</span>
          <IconCSS name="ph:caret-right-bold" class="catalog-dropdown__icon" />
        </NuxtLink>
      </div>

      <div v-else class="catalog-dropdown__empty">
        {{ t('messages.no_catalog_results') }}
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.catalog-dropdown {
  position: absolute;
  top: calc(100% - 2px);
  left: 0;
  width: min(460px, 72vw);
  padding-top: 12px;
  opacity: 0;
  visibility: hidden;
  pointer-events: none;
  transform: translateY(8px);
  transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s ease;
  z-index: 30;

  &::before {
    content: '';
    position: absolute;
    top: 1px;
    left: 38px;
    width: 24px;
    height: 14px;
    background: rgba(250, 246, 236, 0.98);
    clip-path: polygon(50% 0, 0 100%, 100% 100%);
    filter: drop-shadow(0 -1px 0 rgba(74, 91, 68, 0.14));
  }
}

.catalog-dropdown__panel {
  position: relative;
  border-radius: 28px;
  background: rgba(250, 246, 236, 0.98);
  border: 1px solid rgba(74, 91, 68, 0.14);
  box-shadow: 0 28px 80px rgba(31, 43, 29, 0.18);
  backdrop-filter: blur(22px);
  overflow: hidden;
}

.catalog-dropdown__list {
  max-height: min(68vh, 640px);
  overflow-y: auto;
  display: grid;
}

.catalog-dropdown__item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  padding: 16px 18px;
  text-decoration: none;
  color: inherit;
  transition: background 0.18s ease;
  border-bottom: 1px solid $color-border;

  &:hover,
  &:focus-visible {
    background: rgba(255, 255, 255, 0.75);
    outline: none;
  }
}

.catalog-dropdown__name {
  color: #1f2b1d;
  font-size: 15px;
  line-height: 1.35;
  font-weight: 600;
}

.catalog-dropdown__icon {
  color: #8a5a2b;
  font-size: 18px;
  flex-shrink: 0;
}

.catalog-dropdown__list--loading {
  padding: 14px;
}

.catalog-dropdown__skeleton {
  height: 56px;
  border-radius: 20px;
  background: linear-gradient(120deg, rgba(225, 215, 202, 0.8), rgba(247, 240, 232, 1), rgba(225, 215, 202, 0.8));
  background-size: 200% 100%;
  animation: catalog-dropdown-pulse 1.4s linear infinite;
}

.catalog-dropdown__empty {
  padding: 24px 20px;
  color: #5f6458;
  text-align: center;
}

@keyframes catalog-dropdown-pulse {
  0% {
    background-position: 200% 0;
  }

  100% {
    background-position: -200% 0;
  }
}
</style>
