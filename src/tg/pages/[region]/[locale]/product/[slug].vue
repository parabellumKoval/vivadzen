<script setup lang="ts">
import type { TgProduct, TgProductVariant } from '~/types/tg'
import { useTgCartStore } from '~/stores/cart'
import { useTgUiStore } from '~/stores/ui'

const route = useRoute()
const cart = useTgCartStore()
const ui = useTgUiStore()
const { t } = useTgI18n()
const { haptic } = useTelegram()
const { catalogPath } = useTgRouting()
const {
  imagesOf,
  variantsOf,
  priceOf,
  oldPriceOf,
  currencyOf,
  hasSale,
  formatMoney,
  descriptionOf,
  isInStock
} = useTgProductUtils()
const { $api } = useNuxtApp()

const slug = computed(() => String(route.params.slug || ''))
const selectedVariant = ref<TgProductVariant | null>(null)
const expanded = ref(false)

const normalizeProduct = (payload: any): TgProduct | null => {
  return payload?.product || payload?.data?.product || payload?.data || payload || null
}

const { data: product, pending, error } = await useAsyncData(
  () => `tg-product-${slug.value}`,
  async () => normalizeProduct(await $api(`/catalog/${slug.value}`, { method: 'GET' })),
  {
    default: () => null
  }
)

watch(product, (value) => {
  const variants = variantsOf(value)
  selectedVariant.value = variants.find((variant) => isInStock(variant)) || variants[0] || null
}, { immediate: true })

watch(error, async (value) => {
  if (!value) return
  ui.showToast(t('loading_error'), 'error')
  await navigateTo(catalogPath(), { replace: true })
}, { immediate: true })

const variants = computed(() => variantsOf(product.value))
const activePriceSource = computed(() => selectedVariant.value || product.value)
const productQty = computed(() => {
  if (!product.value?.id) return 0
  return cart.getQty(product.value.id, selectedVariant.value?.id || null)
})

const description = computed(() => descriptionOf(product.value))
const descriptionText = computed(() => description.value.replace(/<[^>]+>/g, '').trim())
const shouldCollapse = computed(() => descriptionText.value.length > 280)

const addToCart = () => {
  if (!product.value) return
  cart.addItem(product.value, selectedVariant.value)
  haptic('light')
}
</script>

<template>
  <TgLayout :title="t('product')" :show-back="true" transparent>
    <div v-if="pending && !product" class="product-page product-page--pending">
      <div class="skeleton product-page__image" />
      <div class="tg-page">
        <div class="skeleton product-page__line" />
        <div class="skeleton product-page__line product-page__line--short" />
      </div>
    </div>

    <article v-else-if="product" class="product-page">
      <div class="product-page__gallery">
        <div class="product-page__slides">
          <img
            v-for="src in imagesOf(product)"
            :key="src"
            :src="src"
            :alt="product.name"
            class="product-page__image"
          >
        </div>
      </div>

      <div class="tg-page product-page__content">
        <span v-if="hasSale(activePriceSource)" class="product-page__badge">SALE</span>
        <h1 class="product-page__title">{{ product.name }}</h1>

        <div class="product-page__price">
          <span v-if="oldPriceOf(activePriceSource)" class="product-page__old">
            {{ formatMoney(oldPriceOf(activePriceSource), currencyOf(activePriceSource)) }}
          </span>
          <strong>{{ formatMoney(priceOf(activePriceSource), currencyOf(activePriceSource)) }}</strong>
        </div>

        <TgVariantPicker
          v-if="variants.length"
          v-model="selectedVariant"
          :variants="variants"
          :label="t('choose_variant')"
        />

        <div v-if="description" class="product-page__desc" :class="{ collapsed: shouldCollapse && !expanded }">
          <div v-html="description" />
        </div>

        <button
          v-if="shouldCollapse"
          type="button"
          class="product-page__read"
          @click="expanded = !expanded"
        >
          {{ expanded ? t('hide') : t('read_more') }}
        </button>
      </div>

      <div class="product-page__sticky">
        <TgQtyCounter
          v-if="productQty"
          :model-value="productQty"
          @update:model-value="cart.updateQty(product.id, selectedVariant?.id || null, $event)"
        />
        <button
          v-else
          type="button"
          class="tg-btn tg-btn--accent"
          :disabled="variants.length > 0 && !selectedVariant"
          @click="addToCart"
        >
          {{ t('add_to_cart') }}
        </button>
      </div>
    </article>
  </TgLayout>
</template>

<style scoped>
.product-page {
  display: grid;
  gap: 0;
}

.product-page__gallery {
  background: var(--color-bg-card);
}

.product-page__slides {
  display: grid;
  grid-auto-columns: 100%;
  grid-auto-flow: column;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  scrollbar-width: none;
}

.product-page__slides::-webkit-scrollbar {
  display: none;
}

.product-page__image {
  width: 100%;
  aspect-ratio: 1 / 1;
  object-fit: contain;
  padding: 16px;
  scroll-snap-align: start;
}

.product-page__content {
  display: grid;
  gap: 16px;
}

.product-page__badge {
  width: max-content;
  border-radius: var(--radius-sm);
  background: var(--color-danger);
  color: var(--color-white);
  padding: 3px 7px;
  font-size: 10px;
  font-weight: 700;
}

.product-page__title {
  margin: 0;
  font-size: 18px;
  font-weight: 800;
  line-height: 1.25;
}

.product-page__price {
  display: flex;
  align-items: baseline;
  gap: 8px;
}

.product-page__price strong {
  color: var(--color-accent);
  font-size: 22px;
  font-weight: 800;
}

.product-page__old {
  color: var(--color-text-muted);
  font-size: 13px;
  text-decoration: line-through;
}

.product-page__desc {
  position: relative;
  overflow: hidden;
  color: var(--color-text);
  font-size: 14px;
  line-height: 1.6;
}

.product-page__desc.collapsed {
  max-height: 190px;
}

.product-page__read {
  width: max-content;
  border: 0;
  background: transparent;
  color: var(--color-primary);
  padding: 0;
  font-size: 14px;
  font-weight: 700;
}

.product-page__sticky {
  position: sticky;
  bottom: calc(64px + env(safe-area-inset-bottom));
  display: flex;
  margin: 0 16px 12px;
  justify-content: center;
}

.product-page__sticky .tg-btn {
  box-shadow: 0 8px 24px rgba(255, 132, 0, 0.24);
}

.product-page--pending {
  padding: 16px;
}

.product-page--pending .product-page__image {
  border-radius: var(--radius-lg);
}

.product-page__line {
  height: 18px;
  margin-bottom: 10px;
}

.product-page__line--short {
  width: 50%;
}
</style>
