<script setup>
import { useFeedbackStore } from '~~/store/feedback';
import {useCategoryStore} from '~/store/category'

const {t} = useI18n()

const sub = ref({
  type: 'subscription',
  email: null
})
const errors = ref(null)

const menu = computed(() => {
  return {
    customer: useMenu().customer.value,
    info: useMenu().info.value
  }
})

const categories = computed(() => {
  return useCategoryStore().all
})

const socials = ref([
  ...useSocial().all
])

const payments = ref([
  ...usePaymentVendor().main
])

const deliveries = ref([
  ...useDeliveryVendor().all
])

// METHODS
const resetSub = () => {
  sub.value.email = null
}

const resetErrors = () => {
  errors.value = null
}

// HANDLERS
const subHandler = () => {

  useFeedbackStore().create(sub.value).then(({data, error}) => {

    if(data) {
      useNoty().setNoty({
        content: t('noty.subscription.sent'),
        type: 'success'
      }, 5000)

      resetSub()
      resetErrors()
    }

    if(error) {
      throw error
    }

  }).catch((e) => {

    useNoty().setNoty({
      title: t('noty.subscription.fail'),
      content: t('error.check_fields'),
      type: 'error'
    }, 5000)

    errors.value = e
  })
}
</script>

<style src="./footer.scss" lang="scss" scoped />
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <footer class="footer">
    <div class="company">
      <div class="djini">
        <nuxt-img
          src = "/images/djini.png"
          width="55"
          height="51"
          sizes = "mobile:100vw tablet:75px desktop:75px"
          format = "webp"
          quality = "60"
          loading = "lazy"
          fit="outside"
          class="djini-logo"
        />
        <div class="djini-desc">{{ t('meta.djini_desc') }}</div>
      </div>

      <div class="phone">
        <div class="footer-label">{{ t('label.contact_phones') }}</div>
        <a :href="useContacts().phone" class="phone-item">{{ useContacts().phone }}</a>
        <a :href="useContacts().phone2" class="phone-item">{{ useContacts().phone2 }}</a>
      </div>

      <div class="social">
        <div class="footer-label">{{ t('label.our_socials') }}</div>
        <div class="social-items">
          <a
            v-for="network in useSocial().networks"
            :key="network.key"
            :href="network.link"
            :class="network.key + '-bg'"
            class="social-item"
          >
            <IconCSS :name="network.icon" class="social-icon"></IconCSS>
          </a>
        </div>
      </div>

      <div class="address">
        <div class="address-item">
          <IconCSS name="iconoir:map" class="address-icon"></IconCSS>
          <a href="/" class="address-link">{{ t('meta.address') }}</a>
        </div>
        <div class="address-item">
          <IconCSS name="iconoir:mail" class="address-icon"></IconCSS>
          <a :href="'mailto:' + useContacts().email" class="address-link">{{ useContacts().email }}</a>
        </div>
      </div>
    </div>

    <div class="nav">
      <div class="sub">
        <div class="sub-title">{{ t('label.sub_title') }}</div>
        <div class="footer-label">{{ t('label.sub_desc') }}</div>

        <div class="sub-form">
          <form-text
            v-model="sub.email"
            :error="errors?.email"
            @input="() => errors.email = null"
            :placeholder="t('form.enter.email')"
            class="sub-input"
          ></form-text>
          <button @click="subHandler" class="button primary sub-btn">{{ t('button.sub') }}</button>
        </div>
      </div>

      <hr class="footer-hr nav-hr" />

      <div class="menu menu-1">
        <div class="footer-label">{{ t('label.guide') }}</div>
        <ul class="menu-ul">
          <li v-for="item in menu.customer" :key="item.id">
            <NuxtLink :to="localePath(item.link)" class="menu-link">{{ item.title }}</NuxtLink>
          </li>
        </ul>
      </div>

      <div class="menu menu-2">
        <div class="footer-label">{{ t('label.common_info') }}</div>
        <ul class="menu-ul">
          <li v-for="item in menu.info" :key="item.id">
            <NuxtLink :to="localePath(item.link)" class="menu-link">{{ item.title }}</NuxtLink>
          </li>
        </ul>
      </div>

      <div class="vendors">
        
        <div class="payment">
          <div class="footer-label">{{ t('we_receive') }}</div>
          <div class="payment-inner">
            <div v-for="payment in payments" :key="payment.id" class="payment-item">
              <nuxt-img
                :src = "payment.logo"
                width="50"
                height="50"
                sizes = "mobile:100vw tablet:75px desktop:75px"
                format = "webp"
                quality = "60"
                loading = "lazy"
                fit="outside"
                class="payment-logo"
              />
            </div>
          </div>
        </div>

        <div class="delivery">
          <div class="footer-label">{{ t('delivery') }}</div>
          <div class="delivery-inner">
            <div v-for="delivery in deliveries" :key="delivery.id" class="delivery-item">
              <nuxt-img
                :src = "delivery.logo"
                width="50"
                height="50"
                sizes = "mobile:100vw tablet:75px desktop:75px"
                format = "webp"
                quality = "60"
                loading = "lazy"
                fit="outside"
                class="delivery-logo"
              />
            </div>
          </div>
        </div>

      </div>

    </div>

    <hr class="footer-hr" />

    <div class="last-section">
      djini.com.ua ©2019 - {{ new Date().getFullYear() }} / {{ t('rights') }}
    </div>

  </footer>
</template>