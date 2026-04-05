<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { useDeliveryStore } from '~/store/delivery'
import { useCartStore } from '~/store/cart'

const { t } = useI18n()
const { currency, region } = useRegion()
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
  const isMessengerCod = methodKey === 'messenger_address' && order.value?.payment?.method === 'messenger_cod'

  if (!methodKey || !destinationCountry) {
    return null
  }

  return {
    methodKey,
    destinationCountry,
    weightG: 1000,
    codAmount: isMessengerCod ? (cartStore.totalProducts || 0) : 0,
    codEnabled: isMessengerCod ? 1 : 0,
    meta: {
      subtotal: cartStore.totalProducts || 0,
      promocode_discount: cartStore.promocodeSale || 0,
      bonus_discount: 0,
      personal_discount: 0,
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
    [basePrice, deliveryPrice],
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
          v-if="basePrice"
          :value="basePrice.amount"
          :currency-code="basePrice.currency"
          class="price"
        ></simple-price>
        <span v-else>—</span>
      </div>
    </div>
  </ClientOnly>
</template>
