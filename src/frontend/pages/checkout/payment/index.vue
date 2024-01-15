<script setup>
import {Vue3Lottie} from 'vue3-lottie'

import {useAuthStore} from '~/store/auth'
import {useCartStore} from '~/store/cart'
import {useLiqpayStore} from '~/store/liqpay'

const {t} = useI18n()

const breadcrumbs = [
  {
    name: t('crumbs.home'),
    item: useToLocalePath()('/')
  },{
    name: t('crumbs.checkout'),
    item: useToLocalePath()('/checkout')
  },{
    name: t('crumbs.checkout_payment'),
    item: useToLocalePath()('/checkout/payment')
  }
]

const formElement = ref(null)
const isLoading = ref(false)

// useHead({
//   script: [ {src: 'https://static.liqpay.ua/elements/customElements.js', type: 'text/javascript'} ]
// })

const order = ref({
  action: "pay",
  amount: 0,
  currency: "UAH",
  description: null,
  order: null
})

const form = ref({
  action: null,
  data: null,
  signature: null,
})

// COMPUTED
const orderable = computed(() => {
  return useAuthStore().orderable  
})


// WATCH
watch(formElement, (v) => {
  if(v) {
    v.submit()
  }
})

// METHODS
const getLiqpayForm = async() => {
  isLoading.value = true
  await useLiqpayStore().getFormData(order.value)
    .then((res) => {
      if(res) {
        form.value = res
      }
    })
    .finally(() => {
      isLoading.value = false
    })
}


// HANDLERS
const submitHandler = (v) => {
  isLoading.value = true
  useCartStore().createOrder(orderable.value).then((res) => {
    if(res) {
      order.value.order = res.code
      order.value.amount = res.price
      order.value.description = `Оплата заказа №${res.code} / Сумма: ${res.price} / Дата: ${res.created_at}`

      getLiqpayForm()
    }
  }).catch((err) => {
    useNoty().setNoty(t('noty.order_fail'))
  }).finally(() => {
    isLoading.value = false
  })
}

</script>

<style src="./payment.scss" lang="scss" scoped />

<template>
  <div class="container">

    <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

    <h1 class="caption">{{ t('crumbs.checkout_payment') }}</h1>

    <form
      v-if="form.action && form.data && form.signature"
      :action="form.action"
      ref="formElement"
      method="POST"
      class="liq-form"
      accept-charset="utf-8"
    >
      <input :value="form.data" type="hidden" name="data"/>
      <input :value="form.signature" type="hidden" name="signature"/>
    </form>

    <!-- <div v-if="liqData && liqSignature">
      <lp-provider :lp-data="liqData" :lp-signature="liqSignature">
            <lp-privat24-pay mode="black"></lp-privat24-pay>
          <lp-qr></lp-qr>
      </lp-provider>
    </div> -->

    <div class="wrapper">
      <div class="section">
        <NuxtLink :to="localePath('/checkout')" class="back-btn link" type="button">
          <IconCSS name="fluent:chevron-left-48-filled" size="30px" class="icon"></IconCSS>
          <span>{{ t('back_to') }}</span>
        </NuxtLink>
      </div>

      <div class="section section-content">
        <div class="title full-width">{{ t('pay_for_order') }}</div>

        <div v-if="!noLottie" class="lottie-wrapper full-width">
          <Vue3Lottie
            animationLink="/lottie/pay-abu.json"
            width="100%"
            :height="180"
            class="lottie"
          />
        </div>
        
        <div class="meta full-width">
          <div class="status">{{ t('status') }}: <span class="value">{{ t('wait_payment') }}</span></div>
          <div class="total">{{ t('to_pay') }}: <simple-price :value="useCartStore().total" class="price"></simple-price></div>
        </div>

        <div class="payment full-width ">
          <div class="payment-title">{{ t('pay_liqpay') }}:</div>
          <button
            @click="submitHandler"
            :class="{loading: isLoading}"
            class="button button-liqpay"
            type="submit"
          >
            <nuxt-img
              src = "/images/logo/liqpay-white.png"
              alt = "Pay with liqpay"
              title = "Pay with liqpay"
              width = "75"
              height = "15"
              sizes = "mobile:100px tablet:100px desktop:100px"
              format = "webp"
              fit = "inside"
              quality = "100"
              loading = "lazy"
              class = "logo"
            />
          </button>
        </div>

        <!-- <div class="payment">
          <div class="payment-title">{{ t('pay_qr') }}:</div>
            <nuxt-img
              src = "/images/qr.png"
              alt = "Pay with qr-code"
              title = "Pay with qr-code"
              width = "170"
              height = "170"
              sizes = "mobile:200px tablet:200px desktop:200px"
              format = "webp"
              fit = "inside"
              quality = "100"
              loading = "lazy"
              class = "qr"
            />
        </div> -->

        <div class="line full-width"></div>
        <div class="footer full-width">{{ t('redirect') }}</div>
      </div>

      <section-support  class="section section-support"></section-support>
    </div>
  </div>
</template>

<i18n src="./lang.yaml" lang="yaml"></i18n>