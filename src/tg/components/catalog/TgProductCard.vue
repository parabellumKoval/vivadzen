<script setup lang="ts">
import type { TgProduct } from '~/types/tg'
import { useTgCartStore } from '~/stores/cart'

const props = defineProps<{
  product: TgProduct
}>()

const cart = useTgCartStore()
const { t } = useTgI18n()
const { haptic } = useTelegram()
const { productPath } = useTgRouting()
const {
  imageOf,
  variantsOf,
  productNameOf,
  priceOf,
  oldPriceOf,
  currencyOf,
  hasSale,
  isInStock,
  formatMoney
} = useTgProductUtils()

const variantSheetOpen = ref(false)

const productId = computed(() => props.product.id || '')
const variants = computed(() => variantsOf(props.product))
const hasVariants = computed(() => variants.value.length > 0)
const productName = computed(() => productNameOf(props.product))
const productAvailable = computed(() => isInStock(props.product))
const productHasSale = computed(() => hasSale(props.product))
const qty = computed(() => cart.getQty(productId.value))

const addToCart = () => {
  if (!productAvailable.value) return

  if (hasVariants.value) {
    variantSheetOpen.value = true
    return
  }

  cart.addItem(props.product)
  haptic('light')
}
</script>

<template>
  <article class="product-card" :class="{ 'product-card--unavailable': !productAvailable }">
    <NuxtLink :to="productPath(product.slug)" class="product-card__image-wrap" :prefetch="false">
      <span v-if="productHasSale" class="product-card__badge">{{ t('sale') }}</span>
      <span v-if="!productAvailable" class="product-card__stock">{{ t('out_of_stock') }}</span>
      <NuxtImg
        :src="imageOf(product)"
        :alt="productName"
        class="product-card__image"
        width="320"
        height="320"
        sizes="160px sm:180px md:220px"
        densities="1x 2x"
        format="webp"
        quality="78"
        loading="lazy"
        decoding="async"
      />
    </NuxtLink>

    <div class="product-card__body">
      <NuxtLink :to="productPath(product.slug)" class="product-card__name" :prefetch="false">
        {{ productName }}
      </NuxtLink>

      <div class="product-card__price-row">
        <span v-if="oldPriceOf(product)" class="product-card__price-old">
          {{ formatMoney(oldPriceOf(product), currencyOf(product)) }}
        </span>
        <span class="product-card__price" :class="{ 'product-card__price--sale': productHasSale }">
          {{ formatMoney(priceOf(product), currencyOf(product)) }}
        </span>
      </div>

      <TgQtyCounter
        v-if="productAvailable && !hasVariants && qty"
        class="product-card__counter"
        :model-value="qty"
        @update:model-value="cart.updateQty(productId, null, $event)"
      />

      <button
        v-else
        type="button"
        class="product-card__btn"
        :disabled="!productAvailable"
        @click="addToCart"
      >
        <TgIcon name="plus" :size="14" :stroke="2.6" />
        {{ productAvailable ? t('add') : t('out_of_stock') }}
      </button>
    </div>

    <TgVariantSheet v-model="variantSheetOpen" :product="product" />
  </article>
</template>

<style scoped>
.product-card {
  display: flex;
  overflow: hidden;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-lg);
  background: var(--color-white);
  box-shadow: var(--shadow-card-sm);
  flex-direction: column;
  transition: transform 0.1s ease, box-shadow 0.1s ease;
}

.product-card:hover {
  transform: translate(-1px, -1px);
  box-shadow: var(--shadow-card);
}

.product-card--unavailable {
  opacity: 0.48;
}

.product-card--unavailable:hover {
  transform: none;
  box-shadow: var(--shadow-card-sm);
}

.product-card__image-wrap {
  position: relative;
  display: block;
  aspect-ratio: 1 / 1;
  background: var(--color-bg-input);
  border-bottom: 2px solid var(--color-ink);
}

.product-card__image {
  width: 100%;
  height: 100%;
  object-fit: contain;
  padding: 8px;
}

.product-card__badge {
  position: absolute;
  top: 8px;
  left: 8px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-full);
  background: var(--color-magenta);
  color: var(--color-white);
  padding: 3px 9px;
  font-family: var(--font-display);
  font-size: 10px;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  transform: rotate(-6deg);
}

.product-card__stock {
  position: absolute;
  right: 8px;
  bottom: 8px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-full);
  background: var(--color-white);
  color: var(--color-ink);
  padding: 3px 9px;
  font-family: var(--font-display);
  font-size: 10px;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.product-card__body {
  display: flex;
  flex: 1;
  padding: 10px 10px 12px;
  flex-direction: column;
}

.product-card__name {
  display: -webkit-box;
  overflow: hidden;
  flex: 1;
  color: var(--color-ink);
  font-size: 13px;
  font-weight: 600;
  line-height: 1.35;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.product-card__price-row {
  display: flex;
  margin-top: 8px;
  align-items: baseline;
  gap: 6px;
  flex-wrap: wrap;
}

.product-card__price-old {
  color: var(--color-text-muted);
  font-size: 11px;
  text-decoration: line-through;
}

.product-card__price {
  color: var(--color-ink);
  font-family: var(--font-display);
  font-size: 18px;
  letter-spacing: -0.01em;
}

.product-card__price--sale {
  color: var(--color-accent);
}

.product-card__btn {
  display: inline-flex;
  width: 100%;
  min-height: 36px;
  margin-top: 10px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-full);
  background: var(--color-lime);
  color: var(--color-ink);
  align-items: center;
  justify-content: center;
  gap: 6px;
  font-family: var(--font-display);
  font-size: 12px;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.product-card__btn:hover {
  background: var(--color-primary);
  color: var(--color-white);
}

.product-card__btn:disabled {
  cursor: not-allowed;
  background: var(--color-white);
  color: var(--color-text-muted);
}

.product-card__counter {
  width: 100%;
  margin-top: 10px;
}
</style>
