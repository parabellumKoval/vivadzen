<script setup>
import {useCartStore} from '~/store/cart'
const {t} = useI18n()
const props = defineProps({})
const {methods} = usePayment()
const {region, fallbackRegion} = useRegion()

// COMPUTEDS
const order = computed(() => {
  return useCartStore().order
})

const errors = computed(() => {
  return useCartStore().errors
})

const paymentMethods = computed(() => {
  // [
  //     {key: 'online', title: t('payments.online'), image: '/images/logo/GpayApplepay.png'}, 
  //     {key: 'cash', title: t('payments.cash'), image: '/images/logo/np.png'}
  //   ]
  return methods.value
})

const isDefaultRegion = computed(() => {
  return region.value === fallbackRegion
})

const paymentMethod = computed({
  get() {
    return order.value?.payment?.method
  },
  set(val) {
    const store = useCartStore()
    if (!store.order.payment) store.order.payment = {}
    store.order.payment.method = val
  }
})
// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='./../checkout.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <template v-if="!order.delivery.method">
    <div>{{ t('messages.chose_delivery') }}</div>
  </template>
  <template v-if="isDefaultRegion">
    <div>{{ t('messages.chose_delivery_country') }}</div>
  </template>
  <template v-else>
    <form-tabs
      v-model="paymentMethod"
      :items="paymentMethods"
      :error="errors?.payment?.method"
      class="form-tabs"
    ></form-tabs>
    <transition name="fade-in">
      <!-- Warehouse delivery -->
      <div v-if="paymentMethod === 'zasilkovna_cod'" class="form-grid">
        <checkout-payment-providers-zasilkovna-cod></checkout-payment-providers-zasilkovna-cod>
      </div>
      <!-- Warehouse delivery -->
      <div v-else-if="paymentMethod === 'novaposhta_cod'" class="form-grid">
        <checkout-payment-providers-novaposhta-cod></checkout-payment-providers-novaposhta-cod>
      </div>
      <!-- Warehouse delivery -->
      <div v-else-if="paymentMethod === 'default_cash'" class="form-grid">
        <checkout-payment-providers-default-cash></checkout-payment-providers-default-cash>
      </div>
      <!-- Address delivery -->
      <div v-else-if="paymentMethod === 'liqpay_online'" class="form-grid">
        <checkout-payment-providers-liqpay-online></checkout-payment-providers-liqpay-online>
      </div>
      <!-- Pickup delivery -->
      <div v-else-if="paymentMethod === 'card_online'" class="form-grid">
        <checkout-payment-providers-card-online></checkout-payment-providers-card-online>
      </div>
      <!-- Address delivery -->
      <div v-else-if="paymentMethod === 'bank_transfer'" class="form-grid">
        <checkout-payment-providers-bank-transfer></checkout-payment-providers-bank-transfer>
      </div>
    </transition>
  </template>
</template>
