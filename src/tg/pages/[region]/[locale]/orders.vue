<script setup lang="ts">
import type { TgOrder } from '~/types/tg'

const { t } = useTgI18n()
const { catalogPath } = useTgRouting()
const { formatMoney } = useTgProductUtils()
const { orders, loading, loadOrders } = useTgOrders()

const selectedOrder = ref<TgOrder | null>(null)
const orderSheetOpen = computed({
  get: () => Boolean(selectedOrder.value),
  set: (value: boolean) => {
    if (!value) selectedOrder.value = null
  }
})
const orderProducts = computed<any[]>(() => {
  return Array.isArray(selectedOrder.value?.products) ? selectedOrder.value.products : []
})

await loadOrders()

const statusLabel = (status?: string) => status || t('status_new')
const dateLabel = (value?: string) => {
  if (!value) return ''
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return new Intl.DateTimeFormat(undefined, { day: '2-digit', month: 'short', year: 'numeric' }).format(date)
}

const orderTotal = (order: TgOrder) => {
  return order.price ?? order.total ?? order.amount ?? 0
}
</script>

<template>
  <TgLayout :title="t('order_history')" :show-back="true" :show-lang="true">
    <section class="tg-page orders-page">
      <div v-if="loading" class="orders-page__list">
        <div v-for="item in 3" :key="item" class="skeleton orders-page__skeleton" />
      </div>

      <div v-else-if="orders.length" class="orders-page__list">
        <article v-for="order in orders" :key="order.id || order.code" class="order-card">
          <div class="order-card__head">
            <strong>#{{ order.code || order.id }}</strong>
            <span>{{ dateLabel(order.created_at) }}</span>
          </div>
          <div class="order-card__status">
            <span>{{ statusLabel(order.status) }}</span>
          </div>
          <div class="order-card__footer">
            <span>{{ Array.isArray(order.products) ? order.products.length : '' }}</span>
            <strong>{{ formatMoney(orderTotal(order), order.currency) }}</strong>
          </div>
          <button type="button" class="order-card__more" @click="selectedOrder = order">
            {{ t('details') }}
            <TgIcon name="chevron-right" :size="14" :stroke="2.4" />
          </button>
        </article>
      </div>

      <div v-else class="tg-empty">
        <div>
          <div class="tg-empty__icon"><TgIcon name="package" :size="32" :stroke="2.2" /></div>
          <p class="tg-empty__title">{{ t('no_orders') }}</p>
          <NuxtLink :to="catalogPath()" class="tg-btn orders-page__btn">{{ t('back_to_catalog') }}</NuxtLink>
        </div>
      </div>
    </section>

    <TgBottomSheet v-model="orderSheetOpen" :title="selectedOrder ? `#${selectedOrder.code || selectedOrder.id}` : ''">
      <div v-if="selectedOrder" class="order-details">
        <div>
          <span>{{ t('status') }}</span>
          <strong>{{ statusLabel(selectedOrder.status) }}</strong>
        </div>
        <div>
          <span>{{ t('total') }}</span>
          <strong>{{ formatMoney(orderTotal(selectedOrder), selectedOrder.currency) }}</strong>
        </div>
        <div v-if="dateLabel(selectedOrder.created_at)">
          <span>{{ dateLabel(selectedOrder.created_at) }}</span>
        </div>
        <ul v-if="orderProducts.length" class="order-details__products">
          <li v-for="(product, index) in orderProducts" :key="product?.id || index">
            <span>{{ product?.name || product?.title || `#${index + 1}` }}</span>
            <strong v-if="product?.price">{{ formatMoney(product.price, selectedOrder.currency) }}</strong>
          </li>
        </ul>
      </div>
    </TgBottomSheet>
  </TgLayout>
</template>

<style scoped>
.orders-page__list {
  display: grid;
  gap: 12px;
}

.orders-page__skeleton {
  height: 116px;
}

.order-card {
  display: grid;
  gap: 8px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-lg);
  background: var(--color-white);
  padding: 14px;
  box-shadow: var(--shadow-card-sm);
}

.order-card__head,
.order-card__footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.order-card__head span,
.order-card__footer span {
  color: var(--color-text-muted);
  font-size: 12px;
}

.order-card__status span {
  display: inline-flex;
  border-radius: var(--radius-full);
  background: rgba(255, 132, 0, 0.12);
  color: var(--color-accent);
  padding: 4px 9px;
  font-size: 12px;
  font-weight: 700;
}

.order-card__footer strong {
  color: var(--color-accent);
}

.order-card__more {
  display: inline-flex;
  width: max-content;
  justify-self: end;
  align-items: center;
  gap: 4px;
  border: 0;
  background: transparent;
  color: var(--color-primary-dark);
  padding: 0;
  font-family: var(--font-display);
  font-size: 12px;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.orders-page__btn {
  display: inline-flex;
  width: auto;
  margin-top: 14px;
}

.order-details {
  display: grid;
  gap: 12px;
}

.order-details div {
  display: flex;
  justify-content: space-between;
}

.order-details__products {
  display: grid;
  margin: 0;
  padding: 0;
  gap: 8px;
  list-style: none;
}

.order-details__products li {
  display: flex;
  border-radius: var(--radius-md);
  background: var(--color-bg-card);
  padding: 10px;
  justify-content: space-between;
  gap: 12px;
  font-size: 13px;
}
</style>
