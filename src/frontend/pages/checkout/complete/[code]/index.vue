<script setup>
import {useOrderFaker} from '~/composables/fakers/useOrderFaker.ts'
import {useOrderStore} from '~/store/order';

const {t} = useI18n() 

const breadcrumbs = [
  {
    name: t('title.home'),
    item: useToLocalePath()('/')
  },{
    name: t('title.checkout'),
    item: useToLocalePath()('/checkout')
  },{
    name: t('messages.checkout_complete'),
    item: useToLocalePath()('/checkout/complete')
  }
]

// const order = ref(null)

const order = computed(() => {
  return useOrderFaker()(1)[0]
})

const code = computed(() => {
  return useRoute().params.code
})


const user = computed(() => {
  return order.value?.user || null
})

const delivery = computed(() => {
  return order.value?.delivery || null
})

const payment = computed(() => {
  return order.value?.payment || null
})

const products = computed(() => {
  return order.value?.products || null
})

// useAsyncData('show-order', async() => await useOrderStore().getOrder(code.value))
//   .then(({data, error}) => {
//     order.value = data.value
//   })
</script>

<style src="./complete.scss" lang="scss" scoped />

<template>
  <div class="page-base">
    <div class="container" v-if="order">

      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <h1 class="caption">{{ t('messages.checkout_complete') }}</h1>

      <div v-if="order" class="wrapper">
        <div class="content">
          
          <div class="grid">
            <div class="grid-title full-width">{{ t('common') }}</div>

            <div v-if="order.code" class="cell cell-column">
              <div class="label">{{ t('number') }}</div>
              <div class="value">{{ order.code }}</div>
            </div>

            <div v-if="order.status" class="cell cell-column cell-status">
              <div class="label">{{ t('status') }}</div>
              <div :class="order.status" class="value">{{ $t(`status.${order.status}`) }}</div>
            </div>

            <div v-if="order.created_at" class="cell cell-column">
              <div class="label">{{ t('date') }}</div>
              <div class="value">{{ $d(order.created_at, 'long') }}</div>
            </div>
          </div>


          <div class="grid">
            <div class="column">
              <div class="grid-title">{{ t('user') }}</div>
              <div v-if="user?.firstname" class="cell">
                <div class="label">{{ $t('form.firstname') }}</div>
                <div class="value">{{ user.firstname }}</div>
              </div>
              <div v-if="user?.lastname" class="cell">
                <div class="label">{{ $t('form.lastname') }}</div>
                <div class="value">{{ user.lastname }}</div>
              </div>
              <div v-if="user?.phone" class="cell">
                <div class="label">{{ $t('form.phone') }}</div>
                <div class="value">{{ user.phone }}</div>
              </div>
              <div v-if="user?.email" class="cell">
                <div class="label">{{ $t('form.email') }}</div>
                <div class="value">{{ user.email }}</div>
              </div>
            </div>

            <div class="column">
              <div class="grid-title">{{ t('delivery') }}</div>
              <div v-if="order?.delivery_status" class="cell">
                <div class="label">{{ t('status') }}</div>
                <div :class="order.delivery_status" class="value">{{ $t(`delivery_status.${order.delivery_status}`) }}</div>
              </div>
              <div v-if="delivery?.method" class="cell">
                <div class="label">{{ $t('form.method') }}</div>
                <div class="value">{{ $t(`form.delivery.${delivery.method}`) }}</div>
              </div>
              <div v-if="delivery?.city" class="cell">
                <div class="label">{{ $t('form.city') }}</div>
                <div class="value">{{ delivery.city }}</div>
              </div>
              <div v-if="delivery?.warehouse" class="cell">
                <div class="label">{{ $t('form.warehouse') }}</div>
                <div class="value">{{ delivery.warehouse }}</div>
              </div>
              <div v-if="delivery?.street" class="cell">
                <div class="label">{{ $t('form.street') }}</div>
                <div class="value">{{ delivery.street }}</div>
              </div>
              <div v-if="delivery?.house" class="cell">
                <div class="label">{{ $t('form.house') }}</div>
                <div class="value">{{ delivery.house }}</div>
              </div>
              <div v-if="delivery?.room" class="cell">
                <div class="label">{{ $t('form.room') }}</div>
                <div class="value">{{ delivery.room }}</div>
              </div>
              <div v-if="delivery?.zip" class="cell">
                <div class="label">{{ $t('form.zip') }}</div>
                <div class="value">{{ delivery.zip }}</div>
              </div>
            </div>

            <div class="column">
              <div class="grid-title">{{ t('payment') }}</div>
              <div v-if="order?.pay_status" class="cell">
                <div class="label">{{ t('status') }}</div>
                <div :class="order.pay_status" class="value">{{ $t(`pay_status.${order.pay_status}`) }}</div>
              </div>
              <div v-if="order?.price" class="cell cell-price">
                <div class="label">{{ t('total') }}</div>
                <div class="value">
                  <simple-price :value="order.price"></simple-price>
                </div>
              </div>
              <div v-if="payment?.method" class="cell">
                <div class="label">{{ $t('form.method') }}</div>
                <div class="value">{{ $t(`form.payment.${payment.method}`) }}</div>
              </div>
              <div v-if="order?.is_paid" class="cell cell-status">
                <div class="label">{{ t('status') }}</div>
                <div class="value success">{{ order.is_paid }}</div>
              </div>
            </div>
          </div>

          

          <div v-if="products" class="product-wrapper">
            <div class="grid-title p-title">{{ t('cart') }}</div>
            <div class="product-grid">
              <product-card-checkout-static
                v-for="product in products"
                :key="product.id"
                :item="product"
                class="product-card"
              >
              </product-card-checkout-static>
            </div>
          </div>
        </div>
        
        <aside class="aside">
          <checkout-thanks></checkout-thanks>
        </aside>
      </div>
    </div>
  </div>
</template>

<i18n src="./lang.yaml" lang="yaml"></i18n>