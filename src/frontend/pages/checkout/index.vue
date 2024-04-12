<script setup>
import {useCartStore} from '~/store/cart'
import { useAuthStore } from '~~/store/auth';

definePageMeta({
  bg: '#eee'
});

const { scrollToAnchor } = useAnchorScroll({
  toAnchor: {
    scrollOptions: {
      behavior: 'smooth',
      offsetTop: -90,
    }
  },
})

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

const errorHtml = ref(null)
const authType = ref('new')

// COMPUTEDS
const products = computed(() => {
  return useCartStore().cart
})

const order = computed(() => {
  return useCartStore().order
})

const errors = computed(() => {
  return useCartStore().errors
})

const userErrors = computed(() => {
  return errors.value.user && Object.keys(errors.value.user).length? true: false
})

const deliveryErrors = computed(() => {
  return errors.value.delivery && Object.keys(errors.value.delivery).length? true: false
})

const paymentErrors = computed(() => {
  return errors.value.payment && Object.keys(errors.value.payment).length? true: false
})

const total = computed(() => {
  return useCartStore().total
})

const user = computed(() => {
  return useAuthStore().user
})

const auth = computed(() => {
  return useAuthStore().auth
})

// METHODS
const scrollHandler = (item) => {
  nextTick(() => {
    scrollToAnchor(item)
  });
}

const setUserData = () => {
  order.value.user.phone = !order.value.user.phone? (user.value?.phone || null): order.value.user.phone
  order.value.user.email = !order.value.user.email? (user.value?.email || null): order.value.user.email
}

// HANDLERS
const openCatalogHandler = () => {
  useModal().open(resolveComponent('ModalCatalog'), null)
}
const contactsHandler = () => {
  useModal().open(resolveComponent('ModalContacts'), null)
}

const scrollToErrorHandler = () => {
  if(errors.value.user && Object.keys(errors.value.user).length) {
    scrollHandler('customer-box')
    return
  }
  
  if(errors.value.delivery && Object.keys(errors.value.delivery).length) {
    scrollHandler('delivery-box')
    return
  }

  if(errors.value.payment && Object.keys(errors.value.payment).length) {
    scrollHandler('payment-box')
    return
  }
}

// WATCH
watch(() => order.value.delivery.city, (v) => {
  order.value.delivery.warehouse = null
  order.value.delivery.street = null
})

watch(() => order.value.delivery.method, (v) => {
  errors.value.delivery = {}
})

watch(() => authType.value, (v) => {
  errors.value.user = {}
})

// Reset errors
useCartStore().clearErrors()
setUserData()
</script>

<style src="./checkout.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <div class="container">
    <div class="page-base">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <div class="title">
        <span class="title-common">{{ t('title.checkout') }}</span>
      </div>

      <div class="content">
        <div class="content-main">

          <!-- PRODUCTS -->
          <div class="checkout-box">
            <div class="title-secondary">{{ t('title.cart') }}</div>
            <template v-if="products?.length">
              <product-card-checkout
                v-for="product in products"
                :key="product.id"
                :item="product"
                class="checkout-product"
              ></product-card-checkout>
            </template>
            <template v-else>
              <div>
                {{ t('no_products') }} 
                <button @click="openCatalogHandler" class="text-link"><span>{{ t('catalog') }}</span></button> 
                {{ t('select') }}
              </div>
            </template>
          </div>

          <!-- CUSTOMER -->
          <div :class="{error: userErrors}" id="customer-box" class="checkout-box">
            <div class="title-secondary">{{ t('label.customer') }}</div>
            <!-- <form-tabs
              v-model="authType"
              :items="[{key: 'new', title: 'Я новый покупатель'}, {key: 'old', title: 'Я постоянный клиент'}]"
              class="form-tabs"
            ></form-tabs> -->
            
            <checkout-user class="checkout-user"></checkout-user>

            <div class="form-grid">
              <form-text
                v-if="!auth"
                v-model="order.user.firstname"
                :error="errors?.user?.firstname"
                @input="() => errors.user.firstname = null"
                :placeholder="t('form.firstname')"
              ></form-text>
              <form-text
                v-if="!auth"
                v-model="order.user.lastname"
                :error="errors?.user?.lastname"
                @input="() => errors.user.lastname = null"
                :placeholder="t('form.lastname')"
              ></form-text>
              <form-text
                v-model="order.user.phone"
                :error="errors?.user?.phone"
                @input="() => errors.user.phone = null"
                :placeholder="t('form.phone')"
              ></form-text>
              <form-text
                v-model="order.user.email"
                :error="errors?.user?.email"
                @input="() => errors.user.email = null"
                :placeholder="t('form.email')"
              ></form-text>
            </div>

          </div>

          <!-- DELIVERY -->
          <div :class="{error: deliveryErrors}" id="delivery-box" class="checkout-box">
            <div class="title-secondary">{{ t('label.delivery') }}</div>
            <form-tabs
              v-model="order.delivery.method"
              :items="[
                {key: 'warehouse', title: 'Отделение Новой Почты', image: '/images/logo/np.png'}, 
                {key: 'address', title: 'Курьером Новой Почты', image: '/images/logo/np.png'}, 
                {key: 'pickup', title: 'Самовывоз', image: '/images/logo/djini.png'}
              ]"
              class="form-tabs"
            ></form-tabs>
            <transition name="fade-in">
              <div v-if="order.delivery.method === 'warehouse'" class="form-grid">
                <form-novaposhta-settlement
                  v-model="order.delivery.city"
                  :error="errors?.delivery?.city"
                  @input="() => errors.delivery.city = null"
                ></form-novaposhta-settlement>
                <form-novaposhta-warehouse
                  v-model="order.delivery.warehouse"
                  :error="errors?.delivery?.warehouse"
                  @input="() => errors.delivery.warehouse = null"
                ></form-novaposhta-warehouse>
              </div>
              <div v-else-if="order.delivery.method === 'address'" class="form-grid">
                <form-novaposhta-settlement
                  v-model="order.delivery.city"
                  :error="errors?.delivery?.city"
                  @input="() => errors.delivery.city = null"
                ></form-novaposhta-settlement>
                <form-novaposhta-street
                  v-model="order.delivery.street"
                  :error="errors?.delivery?.street"
                  @input="() => errors.delivery.street = null"
                ></form-novaposhta-street>
                <form-text
                  v-model="order.delivery.house"
                  :error="errors?.delivery?.house"
                  @input="() => errors.delivery.house = null"
                  :placeholder="t('form.delivery.house')"
                ></form-text>
                <form-text
                  v-model="order.delivery.room"
                  :error="errors?.delivery?.room"
                  @input="() => errors.delivery.room = null"
                  :placeholder="t('form.delivery.room')"
                ></form-text>
                <form-text
                  v-model="order.delivery.zip"
                  :error="errors?.delivery?.zip"
                  @input="() => errors.delivery.zip = null"
                  :placeholder="t('form.delivery.zip')"
                ></form-text>
              </div>
              <div v-else-if="order.delivery.method === 'pickup'" class="form-grid">
                <div class="form-static">
                  <div class="label">{{ t('label.our_address') }}</div>
                  <div>{{ t('meta.address') }}</div>
                </div>
              </div>
            </transition>
          </div>

          <!-- PAYMENT -->
          <div :class="{error: paymentErrors}" id="payment-box" class="checkout-box">
            <div class="title-secondary">{{ t('label.payment') }}</div>
            <form-tabs
              v-model="order.payment.method"
              :items="[
                {key: 'online', title: 'Оплатить онлайн', image: '/images/logo/liqpay.png'}, 
                {key: 'cash', title: 'Оплата при получении заказа', image: '/images/logo/np.png'}
              ]"
              class="form-tabs"
            ></form-tabs>
          </div>

        </div>
        <div class="content-sale">
          <div class="content-sale-sticky">
            <div class="checkout-box">
              <div class="title-secondary">{{ t('label.total') }}</div>
              <checkout-sale @scrollToError="scrollToErrorHandler"></checkout-sale>
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