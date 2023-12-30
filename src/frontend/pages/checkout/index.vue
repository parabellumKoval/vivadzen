<script setup>
import {useProductFaker} from '~/composables/fakers/useProductFaker.ts'

definePageMeta({
  bg: '#eee'
});

const {t} = useI18n()
const breadcrumbs = [
  {
    name: t('title.home'),
    item: '/'
  },{
    name: t('title.catalog'),
    item: '/catalog'
  }
]

const user = ref({
  time: 'new'
})

const cart = ref({
    products: useProductFaker()(3),
    customer: {
      firstname: '',
      lastname: '',
      phone: '',
      email: null
    },
    delivery: {
      type: 'werehouse',
      settlement: null,
      warehouse: null
    },
    payment: {
      type: 'online',
    }
})

// COMPUTEDS
const total = computed(() => {
  return cart.value.products.reduce((carry, item) => {
    return carry + item.price * item.amount
  }, 0)
})

// HANDLERS
const contactsHandler = () => {
  useModal().open(resolveComponent('ModalContacts'), null)
}
</script>

<style src="./checkout.scss" lang="scss" scoped></style>

<template>
  <div class="container">
    <div class="page-base">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <div class="title">
        <span class="title-common">{{ t('title.checkout') }}</span>
      </div>

      <div class="content">
        <div class="content-main">
          <div class="checkout-box">
            <div class="title-secondary">{{ t('title.cart') }}</div>
            <product-card-checkout
              v-for="product in cart.products"
              :key="product.id"
              :item="product"
              class="checkout-product"
              ></product-card-checkout>
          </div>
          <div class="checkout-box">
            <div class="title-secondary">{{ t('label.customer') }}</div>
            <form-tabs
              v-model="user.time"
              :items="[{key: 'new', title: 'Я новый покупатель'}, {key: 'old', title: 'Я постоянный клиент'}]"
              class="form-tabs"
            ></form-tabs>
            <div class="form-grid">
              <form-text v-model="cart.customer.firstname" :placeholder="t('form.firstname')"></form-text>
              <form-text v-model="cart.customer.lastname" :placeholder="t('form.lastname')"></form-text>
              <form-text v-model="cart.customer.phone" :placeholder="t('form.phone')"></form-text>
              <form-text v-model="cart.customer.email" :placeholder="t('form.email')"></form-text>
            </div>
          </div>
          <div class="checkout-box">
            <div class="title-secondary">{{ t('label.delivery') }}</div>
            <form-tabs
              v-model="cart.delivery.type"
              :items="[
                {key: 'werehouse', title: 'Отделение Новой Почты', image: '/images/logo/np.png'}, 
                {key: 'courier', title: 'Курьером Новой Почты', image: '/images/logo/np.png'}, 
                {key: 'pickup', title: 'Самовывоз', image: '/images/logo/djini.png'}
              ]"
              class="form-tabs"
            ></form-tabs>
            <div class="form-grid">
              <form-novaposhta-settlement v-model="cart.delivery.settlement"></form-novaposhta-settlement>
              <form-novaposhta-warehouse v-model="cart.delivery.warehouse"></form-novaposhta-warehouse>
              <!-- <form-select v-model="cart.settlement" :values="" :placeholder="t('form.settlement')"></form-select>
              <form-select v-model="cart.warehouse" :values="" :placeholder="t('form.warehouse')"></form-select> -->
            </div>
          </div>
          <div class="checkout-box">
            <div class="title-secondary">{{ t('label.payment') }}</div>
            <form-tabs
              v-model="cart.payment.type"
              :items="[
                {key: 'online', title: 'Оплатить онлайн', image: '/images/logo/liqpay.png'}, 
                {key: 'post', title: 'Оплата при получении заказа', image: '/images/logo/np.png'}
              ]"
              class="form-tabs"
            ></form-tabs>
          </div>
        </div>
        <div class="content-sale">
          <div class="content-sale-sticky">
            <div class="checkout-box">
              <div class="title-secondary">{{ t('label.total') }}</div>
              <checkout-sale :cart="cart"></checkout-sale>
            </div>

            <div class="contacts-box">
              <div class="title-secondary">{{ t('messages.have_q') }}</div>
              <div class="contacts-desc">Мы готовы оперативно помочь 👩‍💻</div>
              <div class="contacts">
                <button class="button contacts-button">
                  <IconCSS name="iconoir:phone" class="inline-icon"></IconCSS>
                  <span>+38 (099) 777-33-45</span>
                </button>
                <button @click="contactsHandler" class="button lowcase contacts-button-secondary">
                  <span>Еще</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>