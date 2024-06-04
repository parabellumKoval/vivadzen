<script setup>
import {useCartStore} from '~/store/cart'
import {useAuthStore} from '~/store/auth';

const {t} = useI18n()
const localePath = useLocalePath()

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

const user = computed(() => {
  return useAuthStore().user
})

const auth = computed(() => {
  return useAuthStore().auth
})

// METHODS

// HANDLERS
const goCompleteHandler = () => {
  
  let orderable = {
    orderable_id: null,
    orderable_type: null
  }

  if(auth.value && user.value) {
    orderable.orderable_id = user.value.id
    orderable.orderable_type = 'supabase'
  }

  useCartStore().createOrder(orderable).then((r) => {
    if(r && r.code) {
      navigateTo(localePath('/checkout/complete/' + r.code))
    }
  }).catch((e) => {
    useNoty().setNoty({
      title: t('error.error'),
      content: t('error.check_fields'),
      type: 'error'
    }, 7000)

    emit('scrollToError')
  })
}

const goPayHandler = () => {
  
  let orderable = {
    orderable_id: null,
    orderable_type: null
  }

  if(auth.value && user.value) {
    orderable.orderable_id = user.value.id
    orderable.orderable_type = 'supabase'
  }


  useCartStore().validate(orderable).then((r) => {
    navigateTo(localePath('/checkout/payment'))
  }).catch((e) => {
    useNoty().setNoty({
      title: t('error.error'),
      content: t('error.check_fields'),
      type: 'error'
    }, 7000)

    emit('scrollToError')
  })
}
// WATCHERS
</script>

<style src='./sale.scss' lang='scss' scoped></style>

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