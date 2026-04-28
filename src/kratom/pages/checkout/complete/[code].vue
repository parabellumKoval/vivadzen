<script setup lang="ts">
const { t } = useI18n()
const route = useRoute()
const { needsAddress, needsPickupLocation, needsWarehouse } = useSavedDeliveryAddresses()

definePageMeta({
  bg: '#f3ece2',
})

const code = computed(() => String(route.params.code || ''))

const { data, error } = await useAsyncData(
  () => `kratom-order-${code.value}`,
  async () => {
    return await useOrderStore().getOrder(code.value)
  },
  { server: true },
)

if (error.value) {
  throw createError({
    statusCode: error.value.statusCode || error.value.status || 404,
    statusMessage: error.value.statusMessage || error.value.message || t('error.order_not_found'),
    fatal: true,
  })
}

const order = computed(() => data.value || null)
const user = computed(() => order.value?.user || null)
const delivery = computed(() => order.value?.delivery || null)
const payment = computed(() => order.value?.payment || null)
const products = computed(() => order.value?.products || [])

const normalizeDeliveryLine = (value: unknown) => {
  return String(value ?? '').replace(/\s+/g, ' ').trim()
}

const uniqueDeliveryLines = (values: Array<unknown>) => {
  const seen = new Set<string>()

  return values
    .map(normalizeDeliveryLine)
    .filter((line) => {
      if (!line || seen.has(line)) {
        return false
      }

      seen.add(line)
      return true
    })
}

const deliveryLines = computed(() => {
  if (!delivery.value) {
    return []
  }

  const streetLine = [delivery.value.street, delivery.value.house, delivery.value.room]
    .filter(Boolean)
    .join(' ')

  if (needsPickupLocation(delivery.value.method)) {
    return uniqueDeliveryLines([delivery.value.warehouse || streetLine])
  }

  if (needsWarehouse(delivery.value.method)) {
    return uniqueDeliveryLines([delivery.value.settlement, delivery.value.warehouse])
  }

  if (needsAddress(delivery.value.method)) {
    return uniqueDeliveryLines([delivery.value.settlement, streetLine, delivery.value.zip])
  }

  return uniqueDeliveryLines([delivery.value.settlement, delivery.value.warehouse, streetLine, delivery.value.zip])
})

const breadcrumbs = computed(() => [
  { name: t('title.home'), item: '/' },
  { name: t('title.checkout'), item: '/checkout' },
  { name: t('title.checkout_complete'), item: `/checkout/complete/${code.value}` },
])

useSeo().setPageSeo('checkout_complete', {
  params: () => ({
    code: code.value,
  }),
  fallbackTitle: () => t('title.checkout_complete'),
  robots: 'noindex, nofollow',
})

useCartStore().$reset()
</script>

<template>
  <div class="page-base kratom-complete-page">
    <div class="container kratom-page-container kratom-complete-shell">
      <the-breadcrumbs :crumbs="breadcrumbs" />
      <h1 class="title-common">{{ t('title.checkout_complete') }}</h1>

      <div v-if="order" class="kratom-complete-layout">
        <section class="kratom-complete-card">
          <div class="kratom-complete-grid">
            <div>
              <p class="kratom-complete-label">{{ t('kratom.complete.order') }}</p>
              <strong>#{{ order.code }}</strong>
            </div>
            <div>
              <p class="kratom-complete-label">{{ t('label.status') }}</p>
              <strong>{{ $t(`status.${order.status}`) }}</strong>
            </div>
            <div>
              <p class="kratom-complete-label">{{ t('kratom.complete.created') }}</p>
              <strong>{{ $d(order.created_at, 'long') }}</strong>
            </div>
            <div>
              <p class="kratom-complete-label">{{ t('label.total') }}</p>
              <strong><simple-price :value="order.price" /></strong>
            </div>
          </div>
        </section>

        <section class="kratom-complete-card">
          <h2 class="title-secondary">{{ t('kratom.complete.customer') }}</h2>
          <div class="kratom-complete-stack">
            <div v-if="user?.first_name || user?.last_name">{{ user?.first_name }} {{ user?.last_name }}</div>
            <div v-if="user?.phone">{{ user.phone }}</div>
            <div v-if="user?.email">{{ user.email }}</div>
          </div>
        </section>

        <section class="kratom-complete-card">
          <h2 class="title-secondary">{{ t('kratom.complete.delivery') }}</h2>
          <div class="kratom-complete-stack">
            <div v-if="delivery?.method">{{ $t(`form.delivery.${delivery.method}`) }}</div>
            <div v-for="(line, index) in deliveryLines" :key="`delivery-line-${index}`">{{ line }}</div>
          </div>
        </section>

        <section class="kratom-complete-card">
          <h2 class="title-secondary">{{ t('kratom.complete.payment') }}</h2>
          <div class="kratom-complete-stack">
            <div v-if="payment?.method">{{ $t(`form.payment.${payment.method}`) }}</div>
            <div v-if="order?.pay_status">{{ $t(`pay_status.${order.pay_status}`) }}</div>
          </div>
        </section>

        <section class="kratom-complete-card">
          <h2 class="title-secondary">{{ t('title.cart') }}</h2>
          <div class="kratom-complete-products">
            <product-card-checkout-static
              v-for="product in products"
              :key="product.id"
              :item="product"
            />
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.kratom-complete-shell {
  padding-top: 24px;
  padding-bottom: 48px;
}

.kratom-complete-layout {
  display: grid;
  gap: 22px;
}

.kratom-complete-card {
  padding: 28px;
  border-radius: 32px;
  background: rgba(255, 250, 244, 0.92);
  border: 1px solid rgba(74, 91, 68, 0.1);
}

.kratom-complete-grid {
  display: grid;
  gap: 18px;

  @include tablet {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}

.kratom-complete-label {
  margin-bottom: 8px;
  color: #8a5a2b;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 12px;
  font-weight: 700;
}

.kratom-complete-stack {
  display: grid;
  gap: 10px;
  line-height: 1.6;
}

.kratom-complete-products {
  display: grid;
  gap: 12px;
}
</style>
