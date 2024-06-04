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
const clearError = (key) => {

}

const scrollHandler = (item) => {
  nextTick(() => {
    scrollToAnchor(item)
  });
}

const setUserData = () => {
  order.value.user.firstname = !order.value.user.firstname? (user.value?.firstname || null): order.value.user.firstname
  order.value.user.lastname = !order.value.user.lastname? (user.value?.lastname || null): order.value.user.lastname
  order.value.user.phone = !order.value.user.phone? (user.value?.phone || null): order.value.user.phone
  order.value.user.email = !order.value.user.email? (user.value?.email || null): order.value.user.email
}

// HANDLERS
const openCatalogHandler = () => {
  useModal().open(resolveComponent('ModalCatalog'), null)
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
  console.log('update city', v)
  order.value.delivery.warehouse = null
  order.value.delivery.street = null
})

watch(() => order.value.delivery.method, (v) => {
  errors.value.delivery = {}
})

watch(() => authType.value, (v) => {
  errors.value.user = {}
})

watch(() => user.value, (v) => {
  if(v) {
    setUserData()
    // useCartStore().setUser(user.value)
  }
}, {
  deep: true,
  immediate: true
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
      
            <checkout-user class="checkout-user"></checkout-user>

            <div class="form-grid">
              <form-text
                v-if="!auth"
                v-model="order.user.firstname"
                :error="errors?.user?.firstname"
                @input="() => errors?.user?.firstname && (errors.user.firstname = null)"
                :placeholder="t('form.firstname')"
              ></form-text>
              <form-text
                v-if="!auth"
                v-model="order.user.lastname"
                :error="errors?.user?.lastname"
                @input="() => errors?.user?.lastname && (errors.user.lastname = null)"
                :placeholder="t('form.lastname')"
              ></form-text>
              <form-text
                v-model="order.user.phone"
                :error="errors?.user?.phone"
                @input="() => errors?.user?.phone && (errors.user.phone = null)"
                :placeholder="t('form.phone')"
              ></form-text>
              <form-text
                v-model="order.user.email"
                :error="errors?.user?.email"
                @input="() => errors?.user?.email && (errors.user.email = null)"
                :placeholder="t('form.email')"
              ></form-text>
            </div>

          </div>

          <!-- DELIVERY -->
          <div :class="{error: deliveryErrors}" id="delivery-box" class="checkout-box">
            <div class="title-secondary">{{ t('title.delivery') }}</div>
            <form-tabs
              v-model="order.delivery.method"
              :items="[
                {key: 'warehouse', title: t('delivery.warehouse'), image: '/images/logo/np.png'}, 
                {key: 'address', title: t('delivery.address'), image: '/images/logo/np.png'}, 
                {key: 'pickup', title: t('delivery.pickup'), image: '/images/logo/djini.png'}
              ]"
              class="form-tabs"
            ></form-tabs>
            <transition name="fade-in">
              <!-- Warehouse delivery -->
              <div v-if="order.delivery.method === 'warehouse'" class="form-grid">
                <form-novaposhta-settlement
                  v-model="order.delivery"
                  :error="errors?.delivery?.settlement"
                  @input="() => errors?.delivery?.settlement && (errors.delivery.settlement = null)"
                ></form-novaposhta-settlement>

                <form-novaposhta-warehouse
                  v-model="order.delivery"
                  :error="errors?.delivery?.warehouse"
                  @input="() => errors?.delivery?.warehouse && (errors.delivery.warehouse = null)"
                ></form-novaposhta-warehouse>
              </div>
              <!-- Address delivery -->
              <div v-else-if="order.delivery.method === 'address'" class="form-grid">
                <form-novaposhta-settlement
                  v-model="order.delivery"
                  :error="errors?.delivery?.settlement"
                  @input="() => errors?.delivery?.settlement && (errors.delivery.settlement = null)"
                ></form-novaposhta-settlement>
                <form-novaposhta-street
                  v-model="order.delivery"
                  :error="errors?.delivery?.street"
                  @input="() => errors?.delivery?.street && (errors.delivery.street = null)"
                ></form-novaposhta-street>
                <form-text
                  v-model="order.delivery.house"
                  :error="errors?.delivery?.house"
                  @input="() => errors?.delivery?.house && (errors.delivery.house = null)"
                  :placeholder="t('form.delivery.house')"
                ></form-text>
                <form-text
                  v-model="order.delivery.room"
                  :error="errors?.delivery?.room"
                  @input="() => errors?.delivery?.room && (errors.delivery.room = null)"
                  :placeholder="t('form.delivery.room')"
                ></form-text>
                <form-text
                  v-model="order.delivery.zip"
                  :error="errors?.delivery?.zip"
                  @input="() => errors?.delivery?.zip && (errors.delivery.zip = null)"
                  :placeholder="t('form.delivery.zip')"
                ></form-text>
              </div>
              <!-- Pickup delivery -->
              <div v-else-if="order.delivery.method === 'pickup'" class="form-grid">
                <div class="form-static">
                  <div class="label">{{ t('label.our_address') }}</div>
                  <div>{{ t('meta.address') }}</div>
                </div>
              </div>
            </transition>

            <div class="form-row">
              <form-textarea
                v-model="order.comment"
                :error="errors?.comment"
                @input="() => errors?.comment && (errors.comment = null)"
                :placeholder="t('form.comment')"
              ></form-textarea>
            </div>

          </div>

          <!-- PAYMENT -->
          <div :class="{error: paymentErrors}" id="payment-box" class="checkout-box">
            <div class="title-secondary">{{ t('title.payment') }}</div>
            <form-tabs
              v-model="order.payment.method"
              :items="[
                {key: 'online', title: t('pay.online'), image: '/images/logo/liqpay.png'}, 
                {key: 'cash', title: t('pay.cash'), image: '/images/logo/np.png'}
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

            <checkout-contacts class="contacts-box"></checkout-contacts>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>