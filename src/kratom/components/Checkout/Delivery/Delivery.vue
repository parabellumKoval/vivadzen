<script setup>
import {useCartStore} from '~/store/cart'

const {t} = useI18n()
const props = defineProps({})
const {get} = useSettings()
const {methods} = useDelivery()
const {region, fallbackRegion} = useRegion()

// const methods = ref([
//     {key: 'packeta_warehouse', title: t('packeta_warehouse'), image: '/images/logo/zasilkovna.png'}, 
//     {key: 'packeta_address', title: t('packeta_address'), image: '/images/logo/zasilkovna.png'}, 
//     {key: 'novaposhta_warehouse', title: t('novaposhta_warehouse'), image: '/images/logo/np.png'}, 
//     {key: 'novaposhta_address', title: t('novaposhta_address'), image: '/images/logo/np.png'}, 
//     {key: 'default_pickup', title: t('default_pickup'), image: '/images/company.png'}
//   ])

// COMPUTEDS
const order = computed(() => {
  return useCartStore().order
})

const errors = computed(() => {
  return useCartStore().errors
})

const deliveryMethods = computed(() => {
  return methods.value
})

const isDefaultRegion = computed(() => {
  return region.value === fallbackRegion
})

const deliveryMethod = computed({
  get() {
    return order.value?.delivery?.method
  },
  set(val) {
    // прямое изменение store
    const store = useCartStore()
    if (!store.order.delivery) store.order.delivery = {}
    store.order.delivery.method = val
  }
})

// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='./../checkout.scss' lang='scss' scoped></style>
<!-- <i18n src='./lang.yaml' lang='yaml'></i18n> -->

<template>
  <checkout-delivery-country class="form-row"></checkout-delivery-country>
  <template v-if="isDefaultRegion">
    <div>{{ t('messages.chose_delivery_country') }}</div>
  </template>
  <template v-else>
    <form-tabs
      v-model="order.delivery.method"
      :items="deliveryMethods"
      :error="errors?.delivery?.method"
      class="form-tabs"
    ></form-tabs>
    <transition name="fade-in">
      <!-- Warehouse delivery -->
      <div v-if="order.delivery.method === 'packeta_warehouse'" class="form-grid">
        <checkout-delivery-providers-packeta-warehouse></checkout-delivery-providers-packeta-warehouse>
      </div>
      <!-- Warehouse delivery -->
      <div v-else-if="order.delivery.method === 'packeta_address'" class="form-grid">
        <checkout-delivery-providers-packeta-address></checkout-delivery-providers-packeta-address>
      </div>
      <!-- Warehouse delivery -->
      <div v-else-if="order.delivery.method === 'novaposhta_warehouse'" class="form-grid">
        <checkout-delivery-providers-novaposhta-warehouse></checkout-delivery-providers-novaposhta-warehouse>
      </div>
      <!-- Address delivery -->
      <div v-else-if="order.delivery.method === 'novaposhta_address'" class="form-grid">
        <checkout-delivery-providers-novaposhta-address></checkout-delivery-providers-novaposhta-address>
      </div>
      <!-- Pickup delivery -->
      <div v-else-if="order.delivery.method === 'default_pickup'" class="form-grid">
        <checkout-delivery-providers-default-pickup></checkout-delivery-providers-default-pickup>
      </div>
      <!-- Address delivery -->
      <div v-else-if="order.delivery.method === 'default_address'" class="form-grid">
        <checkout-delivery-providers-default-address></checkout-delivery-providers-default-address>
      </div>
    </transition>
  </template>
</template>
