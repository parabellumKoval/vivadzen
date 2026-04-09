<script setup lang="ts">
const { t } = useI18n()
const orderStore = useOrderStore()

definePageMeta({
  crumb: {
    name: 'title.account.orders',
    item: '/account/orders',
  },
  tab: 'orders',
})

const isLoading = ref(false)
const isInitLoading = ref(true)
const orders = ref<any[]>([])
const meta = ref<Record<string, any> | null>(null)

const nextPageAvailable = computed(() => {
  return Boolean(meta.value && meta.value.current_page < meta.value.last_page)
})

const loadOrders = async (page = 1) => {
  const response = await orderStore.getOrders({ page })
  orders.value = page === 1 ? (response?.data || []) : orders.value.concat(response?.data || [])
  meta.value = response?.meta || null
}

const loadMore = async () => {
  if (!nextPageAvailable.value || isLoading.value || !meta.value) {
    return
  }

  isLoading.value = true
  try {
    await loadOrders(Number(meta.value.current_page || 1) + 1)
  } finally {
    isLoading.value = false
  }
}

await useAsyncData('kratom-account-orders', async () => {
  await loadOrders(1)
  return true
}).finally(() => {
  isInitLoading.value = false
})
</script>

<template>
  <div class="account-orders">
    <header class="account-orders__header">
      <div>
        <div class="account-orders__eyebrow">{{ t('title.account.orders') }}</div>
        <h2 class="account-orders__title">{{ t('title.account.orders') }}</h2>
      </div>
    </header>

    <div v-if="isInitLoading" class="account-orders__loading">...</div>
    <div v-else-if="orders.length" class="account-orders__list">
      <account-order-card v-for="order in orders" :key="order.id" :order="order" />
    </div>
    <div v-else class="account-orders__empty">{{ t('messages.no_results') }}</div>

    <div v-if="nextPageAvailable || isLoading" class="account-orders__footer">
      <button type="button" class="button secondary" :class="{ loading: isLoading }" @click="loadMore">
        {{ t('button.load_more') }}
      </button>
    </div>
  </div>
</template>

<style scoped lang="scss">
.account-orders {
  display: grid;
  gap: 18px;
}

.account-orders__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.account-orders__eyebrow {
  margin-bottom: 8px;
  color: #8a5a2b;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.account-orders__title {
  color: #1f2b1d;
  font-size: 28px;
  line-height: 1.05;
}

.account-orders__list {
  display: grid;
  gap: 18px;
}

.account-orders__loading,
.account-orders__empty {
  padding: 32px;
  border-radius: 24px;
  background: rgba(255, 255, 255, 0.68);
  color: #667160;
  text-align: center;
}

.account-orders__footer {
  display: flex;
  justify-content: center;
}
</style>
