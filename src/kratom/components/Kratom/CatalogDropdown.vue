<script setup lang="ts">
import { resolveKratomProductImageSrc } from '~/utils/productImage'

type ProductRecord = Record<string, any>

const props = defineProps<{
  enabled: boolean
}>()
const emit = defineEmits<{
  navigate: []
}>()

const { t, locale } = useI18n()
const regionPath = useToLocalePath()
const runtimeConfig = useRuntimeConfig()
const categorySlug = runtimeConfig.public.kratomStore?.categorySlug || 'kratom'
const perPage = 48

const productsCache = useState<Record<string, ProductRecord[]>>('kratom-catalog-dropdown-products', () => ({}))
const loadedLocales = useState<Record<string, boolean>>('kratom-catalog-dropdown-loaded', () => ({}))
const pending = ref(false)

const products = computed(() => productsCache.value[locale.value] || [])

const fetchCatalogPage = async (page: number) => {
  const response = await useProductStore().catalog({
    with_products: true,
    category_slug: categorySlug,
    per_page: perPage,
    page,
    cache: true,
  })

  if (response.error.value) {
    throw response.error.value
  }

  return response.data.value
}

const getProductPrice = (product: ProductRecord | null) => {
  const prices = [
    product?.price,
    ...(Array.isArray(product?.modifications) ? product.modifications.map((modification: ProductRecord | null) => modification?.price) : []),
  ]
    .map((value) => Number(value))
    .filter((value) => Number.isFinite(value))

  return prices.length ? Math.min(...prices) : null
}

const getCurrencyCode = (product: ProductRecord | null) => {
  if (!product) {
    return null
  }

  if (product.currency) {
    return product.currency
  }

  if (!Array.isArray(product.modifications)) {
    return null
  }

  const modificationWithCurrency = product.modifications.find((modification: ProductRecord | null) => modification?.currency)
  return modificationWithCurrency?.currency || null
}

const getImage = (product: ProductRecord | null) => {
  return resolveKratomProductImageSrc(product)
}

const loadProducts = async () => {
  const localeCode = locale.value

  if (loadedLocales.value[localeCode] || pending.value) {
    return
  }

  pending.value = true

  try {
    const firstPage = await fetchCatalogPage(1)
    const firstPageProducts = firstPage?.products?.data || []
    const lastPage = Number(firstPage?.products?.meta?.last_page || 1)

    const restPages = lastPage > 1
      ? await Promise.all(Array.from({ length: lastPage - 1 }, (_, index) => fetchCatalogPage(index + 2)))
      : []

    const allProducts = [
      ...firstPageProducts,
      ...restPages.flatMap((pageData) => pageData?.products?.data || []),
    ]

    const seen = new Set<string | number>()

    productsCache.value[localeCode] = allProducts.filter((product: ProductRecord | null) => {
      const key = product?.id ?? product?.slug
      if (!key || seen.has(key)) {
        return false
      }

      seen.add(key)
      return true
    })
    loadedLocales.value[localeCode] = true
  } catch (error) {
    console.error('Failed to load catalog dropdown products', error)
  } finally {
    pending.value = false
  }
}

watch(
  () => [props.enabled, locale.value],
  ([isEnabled]) => {
    if (isEnabled) {
      loadProducts()
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

      <div v-else class="catalog-dropdown__empty">
        {{ t('messages.no_catalog_results') }}
      </div>

      <div class="catalog-dropdown__footer">
        <NuxtLink :to="regionPath('/catalog')" class="catalog-dropdown__all-link" @click="handleNavigate">
          {{ t('button.view_all') }}
        </NuxtLink>
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

.catalog-dropdown__all-link {
  color: #8a5a2b;
  text-decoration: none;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.catalog-dropdown__list {
  max-height: min(68vh, 640px);
  overflow-y: auto;
  // padding: 10px;
  display: grid;
}

.catalog-dropdown__footer {
  padding: 16px 20px 18px;
  display: flex;
  justify-content: center;
  border-top: 1px solid rgba(74, 91, 68, 0.1);
  background: rgba(250, 246, 236, 0.98);
}

.catalog-dropdown__item {
  display: grid;
  grid-template-columns: 64px minmax(0, 1fr);
  align-items: center;
  gap: 14px;
  padding: 10px 12px;
  text-decoration: none;
  color: inherit;
  transition: background 0.18s ease, transform 0.18s ease;
  border-bottom: 1px solid $color-border;

  &:hover,
  &:focus-visible {
    background: rgba(255, 255, 255, 0.75);
    // transform: translateX(4px);
    outline: none;
  }
}

.catalog-dropdown__thumb {
  width: 64px;
  height: 64px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  background: rgba(255, 255, 255, 0.82);

  :deep(img) {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }
}

.catalog-dropdown__content {
  min-width: 0;
  display: grid;
  gap: 6px;
}

.catalog-dropdown__name {
  color: #1f2b1d;
  font-size: 15px;
  line-height: 1.35;
  font-weight: 600;
}

.catalog-dropdown__price {
  display: inline-flex;
  align-items: baseline;
  gap: 6px;
  color: #8a5a2b;
  font-size: 13px;
  font-weight: 600;

  :deep(.price) {
    display: inline-flex;
  }

  :deep(.value) {
    font-size: 15px;
    font-weight: 700;
    color: #8a5a2b;
  }
}

.catalog-dropdown__price-prefix {
  color: #6d7563;
  text-transform: lowercase;
}

.catalog-dropdown__list--loading {
  padding: 14px;
}

.catalog-dropdown__skeleton {
  height: 84px;
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
