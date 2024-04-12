<script setup>
import {useCartStore} from '~/store/cart'
import {usePromocodeStore} from '~/store/promocode'

const {t} = useI18n()
const props = defineProps({
  cart: {
    type: Object
  }
})

const emit = defineEmits(['scrollToError'])


// COMPUTEDS
const productsLength = computed(() => {
  return useCartStore().cart?.length || 0
})

const productsTotal = computed(() => {
  return useCartStore().totalProducts
})

const total = computed(() => {
  return useCartStore().total
})

const order = computed(() => {
  return useCartStore().order
})

const promocodeSale = computed(() => {
  return useCartStore().promocodeSale
})

// METHODS

// HANDLERS
const goCompleteHandler = () => {
  useCartStore().createOrder(null).then((r) => {
    console.log('r', r)
  }).catch((e) => {
    useNoty().setNoty({
      title: t('error.error'),
      content: t('error.check_fields'),
      type: 'error'
    }, 7000)

    emit('scrollToError')
    console.log('e', e)
  })
}

const goPayHandler = () => {

}
// WATCHERS
</script>

<style src='./sale.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="sale">
    <div class="sale-list">
      <div class="sale-item">
        <div class="sale-label">{{ productsLength }} {{ t('messages.products_total') }}</div>
        <div class="sale-value">{{ $n(productsTotal, 'currency') }}</div>
      </div>
      <div class="sale-item">
        <div class="sale-label">{{ t('messages.delivery_price') }}</div>
        <div class="sale-value">{{ t('messages.delivery_vendor_price') }}</div>
      </div>
      <div v-if="promocodeSale" class="sale-item">
        <div class="sale-label">{{ t('label.promocode') }}</div>
        <div class="sale-value">-{{ $n(promocodeSale, 'currency') }}</div>
      </div>
      
      <checkout-promocode></checkout-promocode>

      <div class="sale-footer">
        <div class="sale-item">
          <div class="sale-label">{{ t('messages.to_pay') }}</div>
          <div class="sale-value large">{{ $n(total, 'currency') }}</div>
        </div>
      </div>

      <transition name="fade-in">
        <button
          v-if="order.payment.method === 'online'"
          @click="goPayHandler"
          class="button primary sale-button"
        >
          <span>{{ t('button.pay') }}</span>
        </button>
        <button
          v-else
          @click="goCompleteHandler"
          class="button primary sale-button"
        >
          <span>{{ t('button.create_order') }}</span>
        </button>
      </transition>
    </div>
  </div>
</template>