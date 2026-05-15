<script setup lang="ts">
import { roundDownShippingAmount } from '~/utils/shipping-pricing'

type MethodRecord = Record<string, any>
type QuoteRecord = {
  currency?: string
  amount?: number
  breakdown?: Record<string, any>
} | null

const props = withDefaults(defineProps<{
  subtotal?: number
}>(), {
  subtotal: 0,
})

const { t } = useI18n()
const regionPath = useToLocalePath()
const runtimeConfig = useRuntimeConfig()
const { methods: deliveryMethods } = useDelivery()
const { methodsInfo: paymentMethods } = usePayment()
const { region } = useRegion()

const deliveryQuotes = ref<Record<string, QuoteRecord>>({})
const paymentQuotes = ref<Record<string, QuoteRecord>>({})

const calculableDeliveryKeys = new Set([
  'packeta_warehouse',
  'packeta_address',
  'novaposhta_warehouse',
  'novaposhta_address',
  'messenger_address',
])

const destinationCountry = computed(() => String(region.value || '').trim().toUpperCase())

const normalizeSubtotal = computed(() => {
  const value = Number(props.subtotal || 0)
  return Number.isFinite(value) ? Math.max(0, Number(value.toFixed(2))) : 0
})

const deliveryItems = computed(() => {
  return deliveryMethods.value.map((method: MethodRecord) => ({
    ...method,
    key: method.key,
    title: method.title || method.label || t(`delivery.${method.key}`),
    image: method.image || method.logo,
  }))
})

const paymentItems = computed(() => {
  return paymentMethods.value.map((method: MethodRecord) => ({
    ...method,
    key: method.key,
    title: method.title || method.label || t(`payments.${method.key}.title`),
    image: method.image || method.logo,
  }))
})

const isCodPaymentMethod = (methodKey: string) => methodKey.endsWith('_cod')

const buildQuotePayload = (methodKey: string, codEnabled = false) => ({
  methodKey,
  destinationCountry: destinationCountry.value,
  weightG: 1000,
  codEnabled: codEnabled ? 1 : 0,
  codAmount: codEnabled ? normalizeSubtotal.value : 0,
  meta: {
    subtotal: normalizeSubtotal.value,
    promocode_discount: 0,
    bonus_discount: 0,
    personal_discount: 0,
    cod_payment_type: 'cash',
  },
})

const fetchQuote = async (methodKey: string, codEnabled = false): Promise<QuoteRecord> => {
  if (!destinationCountry.value || !calculableDeliveryKeys.has(methodKey)) {
    return null
  }

  try {
    return await $fetch<QuoteRecord>(`${runtimeConfig.public.apiBase}/shipping/quote`, {
      method: 'POST',
      body: buildQuotePayload(methodKey, codEnabled),
    })
  } catch (error) {
    console.error('[kratom-product-delivery-info] Failed to load quote', methodKey, error)
    return null
  }
}

const resolvePaymentDeliveryKey = (method: MethodRecord) => {
  if (!Array.isArray(method?.payments)) {
    return null
  }

  return method.payments.find((deliveryKey: string) => {
    return deliveryItems.value.some((item) => item.key === deliveryKey && calculableDeliveryKeys.has(item.key))
  }) || null
}

const refreshQuotes = async () => {
  const nextDeliveryQuotes = {} as Record<string, QuoteRecord>
  const nextPaymentQuotes = {} as Record<string, QuoteRecord>

  await Promise.all(deliveryItems.value.map(async (item) => {
    nextDeliveryQuotes[item.key] = await fetchQuote(item.key)
  }))

  await Promise.all(paymentItems.value.map(async (item) => {
    if (!isCodPaymentMethod(item.key)) {
      nextPaymentQuotes[item.key] = null
      return
    }

    const deliveryKey = resolvePaymentDeliveryKey(item)
    nextPaymentQuotes[item.key] = deliveryKey ? await fetchQuote(deliveryKey, true) : null
  }))

  deliveryQuotes.value = nextDeliveryQuotes
  paymentQuotes.value = nextPaymentQuotes
}

const deliveryQuoteKey = computed(() => JSON.stringify({
  country: destinationCountry.value,
  subtotal: normalizeSubtotal.value,
  delivery: deliveryItems.value.map((item) => item.key),
  payment: paymentItems.value.map((item) => item.key),
}))

if (import.meta.client) {
  watch(deliveryQuoteKey, () => {
    refreshQuotes()
  }, { immediate: true })
}

const isValidQuote = (quote: QuoteRecord) => {
  const amount = Number(quote?.amount)
  return Boolean(quote?.currency && quote.currency !== 'XXX' && Number.isFinite(amount))
}

const deliveryMeta = (item: MethodRecord) => {
  const quote = deliveryQuotes.value[item.key]

  if (isValidQuote(quote)) {
    return {
      kind: 'price' as const,
      amount: roundDownShippingAmount(Number(quote?.amount)),
      currency: String(quote?.currency || ''),
    }
  }

  if (item?.isPriceObject && item?.price?.amount !== undefined) {
    return {
      kind: 'price' as const,
      amount: roundDownShippingAmount(Number(item.price.amount)),
      currency: String(item.price.currency || ''),
    }
  }

  if (item?.price) {
    return {
      kind: 'text' as const,
      text: String(item.price),
    }
  }

  return {
    kind: 'text' as const,
    text: t('kratom.product.calculated_at_checkout'),
  }
}

const paymentMeta = (item: MethodRecord) => {
  if (!isCodPaymentMethod(item.key)) {
    return {
      kind: 'text' as const,
      text: t('kratom.product.no_fees'),
    }
  }

  const quote = paymentQuotes.value[item.key]
  const codAmount = Number(quote?.breakdown?.cod_gross)
  const currency = String(quote?.currency || '')

  if (currency && currency !== 'XXX' && Number.isFinite(codAmount) && codAmount > 0) {
    return {
      kind: 'price' as const,
      amount: roundDownShippingAmount(codAmount),
      currency,
    }
  }

  return {
    kind: 'text' as const,
    text: t('kratom.product.calculated_at_checkout'),
  }
}
</script>

<template>
  <section
    v-if="deliveryItems.length || paymentItems.length"
    class="product-delivery-info"
    :aria-label="t('kratom.product.shipping_payment')"
  >
    <p class="product-delivery-info__eyebrow">{{ t('kratom.product.shipping_payment') }}</p>

    <div v-if="deliveryItems.length" class="product-delivery-info__section">
      <div class="product-delivery-info__title">{{ t('kratom.product.delivery_methods_title') }}</div>
      <p class="product-delivery-info__note">{{ t('kratom.product.delivery_methods_note') }}</p>
      <div class="product-delivery-info__list">
        <div v-for="item in deliveryItems" :key="item.key" class="product-delivery-info__item">
          <img
            v-if="item.image"
            :src="item.image"
            :alt="item.title"
            class="product-delivery-info__logo"
          >
          <span class="product-delivery-info__name">{{ item.title }}</span>
          <span class="product-delivery-info__meta">
            <template v-if="deliveryMeta(item).kind === 'price'">
              <simple-price
                :value="deliveryMeta(item).amount"
                :currency-code="deliveryMeta(item).currency"
                class="product-delivery-info__meta-price"
              />
            </template>
            <template v-else>
              {{ deliveryMeta(item).text }}
            </template>
          </span>
        </div>
      </div>
      <NuxtLink :to="regionPath('/delivery')" class="product-delivery-info__link">
        {{ t('delivery.more') }}
      </NuxtLink>
    </div>

    <div v-if="paymentItems.length" class="product-delivery-info__section">
      <div class="product-delivery-info__title">{{ t('kratom.product.payment_methods_title') }}</div>
      <p class="product-delivery-info__note">{{ t('kratom.product.payment_methods_note') }}</p>
      <div class="product-delivery-info__list">
        <div v-for="item in paymentItems" :key="item.key" class="product-delivery-info__item">
          <img
            v-if="item.image"
            :src="item.image"
            :alt="item.title"
            class="product-delivery-info__logo"
          >
          <span class="product-delivery-info__name">{{ item.title }}</span>
          <span class="product-delivery-info__meta">
            <template v-if="paymentMeta(item).kind === 'price'">
              <span class="product-delivery-info__meta-label">{{ t('kratom.product.cod_fee') }}</span>
              <simple-price
                :value="paymentMeta(item).amount"
                :currency-code="paymentMeta(item).currency"
                class="product-delivery-info__meta-price"
              />
            </template>
            <template v-else>
              {{ paymentMeta(item).text }}
            </template>
          </span>
        </div>
      </div>
      <NuxtLink :to="regionPath('/payment')" class="product-delivery-info__link">
        {{ t('payments.more') }}
      </NuxtLink>
    </div>
  </section>
</template>

<style scoped lang="scss">
.product-delivery-info {
  display: grid;
  gap: 30px;
  padding: 32px 36px;
  border-radius: 32px;
  border: 1px solid rgba(74, 91, 68, 0.08);
  background:
    radial-gradient(circle at top right, rgba(255, 212, 124, 0.18), transparent 34%),
    linear-gradient(160deg, rgba(255, 249, 239, 0.98), rgba(246, 239, 229, 0.94));
}

.product-delivery-info__eyebrow {
  margin: 0;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: #f28d1a;
}

.product-delivery-info__section {
  display: grid;
  gap: 5px;
}

.product-delivery-info__title {
  font-size: 15px;
  font-weight: 700;
  color: #203019;
}

.product-delivery-info__list {
  display: grid;
  gap: 5px;
}

.product-delivery-info__item {
  display: flex;
  align-items: center;
  gap: 12px;
  min-height: 40px;
  padding: 10px 12px;
  border-radius: 9px;
  font-weight: 600;
  background: rgba(255, 255, 255, 0.72);
  border: 1px solid $color-border;
  overflow: hidden;
}

.product-delivery-info__logo {
  display: block;
  width: 160px;
  height: 42px;
  object-fit: contain;
  object-position: left center;
  flex-shrink: 0;
  background: #f8f8f8;
  padding: 10px 15px;
  margin: -10px 0 -10px -12px;
  border-right: 1px solid #eee;
}

.product-delivery-info__name {
  color: #293626;
  line-height: 1.4;
}

.product-delivery-info__meta {
  margin-left: auto;
  display: inline-flex;
  align-items: center;
  justify-content: flex-end;
  gap: 6px;
  color: #7e8679;
  font-size: 12px;
  line-height: 1.45;
  text-align: right;
  flex-shrink: 0;
}

.product-delivery-info__meta-label {
  color: #8c9388;
}

:deep(.product-delivery-info__meta-price .value) {
  font-size: 12px;
  font-weight: 700;
  color: #70796a;
}

.product-delivery-info__note {
  margin: 0;
  color: #687162;
  font-size: 13px;
  line-height: 1.6;
}

.product-delivery-info__link {
  margin-top: 10px;
  justify-self: start;
  color: #111;
  font-size: 14px;
  font-weight: 400;
  line-height: 1.4;
  text-decoration: underline;
  text-decoration-thickness: 1px;
  text-underline-offset: 0.18em;
  transition: color .2s ease;

  &:hover {
    color: #111;
  }
}

@include mobile {
  .product-delivery-info {
    padding: 32px 36px;
    border-radius: 28px;
  }

  .product-delivery-info__item {
    align-items: flex-start;
    flex-wrap: wrap;
  }

  .product-delivery-info__logo {
    width: 104px;
  }

  .product-delivery-info__meta {
    width: 100%;
    margin-left: 0;
    justify-content: flex-start;
    text-align: left;
  }
}
</style>
