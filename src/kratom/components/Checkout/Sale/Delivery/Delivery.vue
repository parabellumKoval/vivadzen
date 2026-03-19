<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { useDeliveryStore } from '~/store/delivery'
import { useCartStore } from '~/store/cart'

const { t } = useI18n()
const { currency, region } = useRegion()
const converter = useConverter()
const { get } = useSettings()

const cartStore = useCartStore()
const deliveryStore = useDeliveryStore()

const { order } = storeToRefs(cartStore)
const { deliveryPrice } = storeToRefs(deliveryStore)
const isDeliveryCostEnabled = computed(() => !!get('shipping.add_to_order_enabled', false))

const deliveryPayload = computed(() => {
  if (!isDeliveryCostEnabled.value) {
    return null
  }

  const methodKey = order.value?.delivery?.method
  const destinationCountry = region.value

  if (!methodKey || !destinationCountry) {
    return null
  }

  return {
    methodKey,
    destinationCountry,
    weightG: 1000,
    codAmount: 0,
    codEnabled: 0,
    meta: {
      subtotal: cartStore.totalProducts || 0,
      promocode_discount: cartStore.promocodeSale || 0,
      bonus_discount: converter.convertPointsSync(
        order.value.bonus,
        currency.value
      ) || 0,
      personal_discount: cartStore.personalSale || 0,
    }
  }
})

const lastPayloadKey = ref<string | null>(null)

const basePrice = computed(() => {
  if (!isDeliveryCostEnabled.value) return null

  const quote = deliveryPrice.value
  if (!quote) return null

  if (typeof quote === 'number') {
    const baseCurrency = currency.value
    if (!baseCurrency) return null

    return {
      amount: quote,
      currency: baseCurrency,
    }
  }

  const price = (quote as any)?.price ?? quote
  if (!price || typeof price !== 'object') return null

  const amount = Number((price as any).amount)
  const baseCurrency = (price as any).currency || currency.value

  if (!Number.isFinite(amount) || !baseCurrency) {
    return null
  }

  return {
    amount,
    currency: baseCurrency,
  }
})

const convertedPrice = computed(() => {
  const base = basePrice.value
  if (!base) return null

  const targetCurrency = currency.value
  if (!targetCurrency || base.currency === targetCurrency) {
    return base
  }

  const convertedAmount = converter.convertFiatToFiatSync(
    base.amount,
    base.currency,
    targetCurrency
  )

  if (convertedAmount == null || !Number.isFinite(convertedAmount)) {
    return base
  }

  return {
    amount: convertedAmount,
    currency: targetCurrency,
  }
})

if (process.client) {
  watch(
    deliveryPayload,
    async (payload) => {
      if (!payload) {
        lastPayloadKey.value = null
        // deliveryStore.resetQuote()
        cartStore.setDeliveryPricing()
        return
      }

      const payloadKey = JSON.stringify(payload)
      if (lastPayloadKey.value === payloadKey && deliveryPrice.value) {
        return
      }

      lastPayloadKey.value = payloadKey

      try {
        await deliveryStore.quote(payload)
      } catch (err) {
        console.error('Failed to fetch delivery price', err)
        lastPayloadKey.value = null
        cartStore.setDeliveryPricing()
      }
    },
    { immediate: true }
  )

  watch(
    [convertedPrice, deliveryPrice],
    ([price, quote]) => {
      if (!price) {
        cartStore.setDeliveryPricing({ price: null, quote })
        return
      }

      cartStore.setDeliveryPricing({
        price: {
          amount: price.amount,
          currency: price.currency,
        },
        quote,
      })
    },
    { immediate: true }
  )
}
</script>

<style src='./delivery.scss' lang='scss' scoped></style>

<template>
  <ClientOnly>
  <div v-if="isDeliveryCostEnabled" class="sale-item">
    <div class="sale-label">{{ t('messages.delivery_price') }}</div>
    <div class="sale-value">
      <simple-price
        v-if="convertedPrice"
        :value="convertedPrice.amount"
        :currency-code="convertedPrice.currency"
        class="price"
      ></simple-price>
      <span v-else>—</span>
    </div>
  </div>
</ClientOnly>
</template>
