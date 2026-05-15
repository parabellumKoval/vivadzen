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
  productNameOf,
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
const productName = computed(() => productNameOf(product.value))
const productQty = computed(() => {
  if (!product.value?.id) return 0
  return cart.getQty(product.value.id, selectedVariant.value?.id || null)
})

const description = computed(() => descriptionOf(product.value))
const descriptionText = computed(() => description.value.replace(/<[^>]+>/g, '').trim())
const shouldCollapse = computed(() => descriptionText.value.length > 140)

const productInStock = computed(() => isInStock(activePriceSource.value))

const addToCart = () => {
  if (!product.value || !productInStock.value) return
  cart.addItem(product.value, selectedVariant.value)
  haptic('light')
}
</script>

<template>
  <TgLayout :title="t('product')" :show-back="true" :show-lang="true" transparent>
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
          <NuxtImg
            v-for="(src, index) in imagesOf(product)"
            :key="src"
            :src="src"
            :alt="productName"
            class="product-page__image"
            width="640"
            height="640"
            sizes="100vw sm:480px"
            densities="1x 2x"
            format="webp"
            quality="82"
            :loading="index === 0 ? 'eager' : 'lazy'"
            :fetchpriority="index === 0 ? 'high' : 'auto'"
            decoding="async"
          />
        </div>
      </div>

      <div class="tg-page product-page__content">
        <div class="product-page__badges">
          <span v-if="hasSale(activePriceSource)" class="tg-pill tg-pill--accent">{{ t('sale') }}</span>
          <span class="tg-pill" :class="productInStock ? 'tg-pill--lime' : 'tg-pill--ink'">
            <TgIcon :name="productInStock ? 'check' : 'close'" :size="12" :stroke="3" />
            {{ productInStock ? t('in_stock') : t('out_of_stock') }}
          </span>
        </div>

        <h1 class="product-page__title">{{ productName }}</h1>

        <div class="product-page__price-card">
          <div class="product-page__price">
            <strong :class="{ 'product-page__price-sale': hasSale(activePriceSource) }">
              {{ formatMoney(priceOf(activePriceSource), currencyOf(activePriceSource)) }}
            </strong>
            <span v-if="oldPriceOf(activePriceSource) > priceOf(activePriceSource)" class="product-page__old">
              {{ formatMoney(oldPriceOf(activePriceSource), currencyOf(activePriceSource)) }}
            </span>
          </div>
          <span v-if="hasSale(activePriceSource)" class="product-page__save">
            -{{ Math.round((1 - priceOf(activePriceSource) / oldPriceOf(activePriceSource)) * 100) }}%
          </span>
        </div>

        <TgVariantPicker
          v-if="variants.length"
          v-model="selectedVariant"
          :variants="variants"
          :label="t('choose_variant')"
        />

        <TgProductDelivery />

        <div v-if="description" class="product-page__desc-wrap">
          <div
            class="product-page__desc"
            :class="{ collapsed: shouldCollapse && !expanded }"
          >
            <div v-html="description" />
            <div v-if="shouldCollapse && !expanded" class="product-page__desc-fade" />
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
      </div>

      <TgGoogleReviewsSlider class="product-page__reviews" />

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
          :disabled="!productInStock || (variants.length > 0 && !selectedVariant)"
          @click="addToCart"
        >
          {{ productInStock ? t('add_to_cart') : t('out_of_stock') }}
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
  display: block;
  width: 100%;
  height: min(72vw, 340px);
  object-fit: contain;
  padding: 16px;
  scroll-snap-align: start;
}

.product-page__content {
  display: grid;
  gap: 18px;
}

.product-page__badges {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.product-page__badges .tg-pill {
  gap: 4px;
}

.product-page__title {
  margin: 0;
  font-family: var(--font-display);
  font-size: 24px;
  font-weight: 900;
  letter-spacing: -0.01em;
  line-height: 1.05;
  text-transform: uppercase;
}

.product-page__price-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-md);
  background: var(--color-white);
  padding: 12px 14px;
  box-shadow: var(--shadow-card);
}

.product-page__price {
  display: flex;
  align-items: baseline;
  gap: 10px;
  min-width: 0;
}

.product-page__price strong {
  font-family: var(--font-display);
  font-size: 28px;
  letter-spacing: -0.02em;
  color: var(--color-ink);
}

.product-page__price-sale {
  color: var(--color-accent) !important;
}

.product-page__old {
  color: var(--color-text-muted);
  font-size: 14px;
  font-weight: 600;
  text-decoration: line-through;
}

.product-page__save {
  display: inline-flex;
  align-items: center;
  border-radius: var(--radius-full);
  background: var(--color-accent);
  color: var(--color-white);
  padding: 5px 10px;
  font-family: var(--font-display);
  font-size: 13px;
  letter-spacing: 0.02em;
}

.product-page__desc-wrap {
  display: grid;
  gap: 8px;
}

.product-page__desc {
  position: relative;
  overflow: hidden;
  color: var(--color-text);
  font-size: 14px;
  line-height: 1.6;
}

.product-page__desc.collapsed {
  max-height: 110px;
}

.product-page__desc-fade {
  position: absolute;
  right: 0;
  bottom: 0;
  left: 0;
  height: 70px;
  pointer-events: none;
  background: linear-gradient(180deg, rgba(255, 245, 225, 0) 0%, rgba(255, 245, 225, 0.85) 60%, var(--color-bg) 100%);
}

.product-page__read {
  width: max-content;
  border: 0;
  background: transparent;
  color: var(--color-primary-dark);
  padding: 0;
  font-family: var(--font-display);
  font-size: 12px;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.product-page__reviews {
  margin-top: 8px;
  padding-bottom: 16px;
}

.product-page__sticky {
  position: sticky;
  bottom: calc(76px + env(safe-area-inset-bottom));
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
