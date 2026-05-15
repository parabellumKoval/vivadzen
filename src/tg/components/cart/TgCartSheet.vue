<script setup lang="ts">
import { useTgCartStore } from '~/stores/cart'

defineProps<{
  modelValue: boolean
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
}>()

const cart = useTgCartStore()
const { t } = useTgI18n()
const { formatMoney } = useTgProductUtils()
const { pathFor } = useTgRouting()

const close = () => emit('update:modelValue', false)

const checkout = async () => {
  close()
  await navigateTo(pathFor('checkout'))
}
</script>

<template>
  <TgBottomSheet
    :model-value="modelValue"
    :title="`${t('cart')} (${cart.totalQty})`"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <div v-if="cart.items.length" class="cart-sheet">
      <div class="cart-sheet__items">
        <article v-for="item in cart.items" :key="`${item.productId}-${item.variantId}`" class="cart-item">
          <NuxtImg
            :src="item.image"
            :alt="item.name"
            class="cart-item__image"
            width="128"
            height="128"
            densities="1x 2x"
            format="webp"
            quality="72"
            loading="lazy"
            decoding="async"
          />
          <div class="cart-item__body">
            <div class="cart-item__name">{{ item.name }}</div>
            <div v-if="item.variantLabel" class="cart-item__variant">{{ item.variantLabel }}</div>
            <div class="cart-item__footer">
              <TgQtyCounter
                :model-value="item.quantity"
                @update:model-value="cart.updateQty(item.productId, item.variantId, $event)"
              />
              <div class="cart-item__price">
                {{ formatMoney(item.price * item.quantity, item.currency) }}
              </div>
            </div>
          </div>
          <button type="button" class="cart-item__remove" aria-label="Remove" @click="cart.removeItem(item.productId, item.variantId)">
            <TgIcon name="close" :size="16" :stroke="2.4" />
          </button>
        </article>
      </div>

      <TgFreeDeliveryProgress :total="cart.totalPrice" />

      <div class="cart-sheet__summary">
        <span>{{ t('total') }}</span>
        <strong>{{ formatMoney(cart.totalPrice, cart.items[0]?.currency) }}</strong>
      </div>

      <button type="button" class="tg-btn tg-btn--accent" @click="checkout">
        <TgIcon name="lightning" :size="18" :stroke="2.2" />
        {{ t('checkout_now') }}
      </button>
    </div>

    <div v-else class="tg-empty">
      <div>
        <div class="tg-empty__icon"><TgIcon name="bag" :size="32" :stroke="2.2" /></div>
        <p class="tg-empty__title">{{ t('cart_empty') }}</p>
        <p class="tg-empty__text">{{ t('back_to_catalog') }}</p>
      </div>
    </div>
  </TgBottomSheet>
</template>

<style scoped>
.cart-sheet {
  display: grid;
  gap: 16px;
}

.cart-sheet__items {
  display: grid;
  gap: 10px;
}

.cart-item {
  position: relative;
  display: grid;
  grid-template-columns: 64px minmax(0, 1fr) 28px;
  gap: 10px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-md);
  background: var(--color-white);
  padding: 10px;
  box-shadow: var(--shadow-card-sm);
}

.cart-item__image {
  width: 64px;
  height: 64px;
  border-radius: var(--radius-sm);
  background: var(--color-white);
  object-fit: contain;
}

.cart-item__body {
  min-width: 0;
}

.cart-item__name {
  display: -webkit-box;
  overflow: hidden;
  font-size: 13px;
  font-weight: 700;
  line-height: 1.35;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.cart-item__variant {
  margin-top: 3px;
  color: var(--color-text-muted);
  font-size: 12px;
}

.cart-item__footer {
  display: flex;
  margin-top: 8px;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.cart-item__price {
  color: var(--color-accent);
  text-align: right;
  font-size: 13px;
  font-weight: 800;
}

.cart-item__remove {
  display: grid;
  width: 28px;
  height: 28px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-full);
  background: var(--color-white);
  color: var(--color-ink);
  place-items: center;
  line-height: 1;
}

.cart-sheet__summary {
  display: flex;
  border-top: 2px dashed var(--color-ink);
  padding-top: 14px;
  align-items: center;
  justify-content: space-between;
  font-family: var(--font-display);
  font-size: 14px;
  text-transform: uppercase;
}

.cart-sheet__summary strong {
  color: var(--color-accent-dark);
  font-family: var(--font-display);
  font-size: 22px;
}
</style>
