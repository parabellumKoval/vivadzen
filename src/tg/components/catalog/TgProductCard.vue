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
  priceOf,
  oldPriceOf,
  currencyOf,
  hasSale,
  formatMoney
} = useTgProductUtils()

const variantSheetOpen = ref(false)

const productId = computed(() => props.product.id || '')
const variants = computed(() => variantsOf(props.product))
const hasVariants = computed(() => variants.value.length > 0)
const qty = computed(() => cart.getQty(productId.value))

const addToCart = () => {
  if (hasVariants.value) {
    variantSheetOpen.value = true
    return
  }

  cart.addItem(props.product)
  haptic('light')
}
</script>

<template>
  <article class="product-card">
    <NuxtLink :to="productPath(product.slug)" class="product-card__image-wrap" :prefetch="false">
      <span v-if="hasSale(product)" class="product-card__badge">SALE</span>
      <img :src="imageOf(product)" :alt="product.name" class="product-card__image">
    </NuxtLink>

    <div class="product-card__body">
      <NuxtLink :to="productPath(product.slug)" class="product-card__name" :prefetch="false">
        {{ product.name }}
      </NuxtLink>

      <div class="product-card__price-row">
        <span v-if="oldPriceOf(product)" class="product-card__price-old">
          {{ formatMoney(oldPriceOf(product), currencyOf(product)) }}
        </span>
        <span class="product-card__price">
          {{ formatMoney(priceOf(product), currencyOf(product)) }}
        </span>
      </div>

      <TgQtyCounter
        v-if="!hasVariants && qty"
        class="product-card__counter"
        :model-value="qty"
        @update:model-value="cart.updateQty(productId, null, $event)"
      />

      <button v-else type="button" class="product-card__btn" @click="addToCart">
        + {{ t('add') }}
      </button>
    </div>

    <TgVariantSheet v-model="variantSheetOpen" :product="product" />
  </article>
</template>

<style scoped>
.product-card {
  display: flex;
  overflow: hidden;
  border-radius: var(--radius-lg);
  background: #fafafa;
  box-shadow: var(--shadow-card);
  flex-direction: column;
}

.product-card__image-wrap {
  position: relative;
  display: block;
  aspect-ratio: 1 / 1;
  /* background: var(--color-bg-card); */
  background: #fff;
  border-bottom: 1px solid #eee;
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
  border-radius: var(--radius-sm);
  background: var(--color-danger);
  color: var(--color-white);
  padding: 3px 7px;
  font-size: 10px;
  font-weight: 700;
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
  color: var(--color-text);
  font-size: 13px;
  font-weight: 500;
  line-height: 1.4;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.product-card__price-row {
  display: flex;
  margin-top: 6px;
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
  color: var(--color-accent);
  font-size: 15px;
  font-weight: 700;
}

.product-card__btn {
  width: 100%;
  min-height: 34px;
  margin-top: 10px;
  border: 0;
  border-radius: var(--radius-sm);
  background: var(--color-primary);
  color: var(--color-white);
  font-size: 13px;
  font-weight: 600;
}

.product-card__counter {
  width: 100%;
  margin-top: 10px;
}
</style>
