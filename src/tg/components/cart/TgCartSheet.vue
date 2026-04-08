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
          <img :src="item.image" :alt="item.name" class="cart-item__image">
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
          <button type="button" class="cart-item__remove" @click="cart.removeItem(item.productId, item.variantId)">×</button>
        </article>
      </div>

      <div class="cart-sheet__summary">
        <span>{{ t('total') }}</span>
        <strong>{{ formatMoney(cart.totalPrice, cart.items[0]?.currency) }}</strong>
      </div>

      <button type="button" class="tg-btn tg-btn--accent" @click="checkout">
        {{ t('checkout_now') }}
      </button>
    </div>

    <div v-else class="tg-empty">
      <div>
        <div class="tg-empty__icon">□</div>
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
  border-radius: var(--radius-md);
  background: var(--color-bg-card);
  padding: 10px;
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
  width: 28px;
  height: 28px;
  border: 0;
  border-radius: var(--radius-full);
  background: rgba(229, 62, 62, 0.12);
  color: var(--color-danger);
  font-size: 20px;
  line-height: 1;
}

.cart-sheet__summary {
  display: flex;
  border-top: 1px solid var(--color-border);
  padding-top: 14px;
  align-items: center;
  justify-content: space-between;
  font-size: 15px;
}

.cart-sheet__summary strong {
  color: var(--color-accent);
  font-size: 18px;
}
</style>
