<script setup>

import {useCategoryStore} from '~/store/category'
const {t} = useI18n()
const sub = ref({
  email: null
})
const isActive = ref(false)

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

</script>

<style src="./footer.scss" lang="scss" scoped />

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
        <div class="djini-desc">Интернет-магазин “Джинни” - продукты спортивного питания, витамины и минералы, биологически активные добавки, косметическую продукцию</div>
      </div>

      <div class="phone">
        <div class="footer-label">Контактные телефоны</div>
        <a href="/" class="phone-item">+38 (099) 777-33-45</a>
        <a href="/" class="phone-item">+38 (097) 777-33-45</a>
      </div>

      <div class="social">
        <div class="footer-label">Наши соц.сети</div>
        <div class="social-items">
          <a href="/" class="social-item instagram">
            <IconCSS name="iconoir:instagram" class="social-icon"></IconCSS>
          </a>
          <a href="/" class="social-item facebook">
            <IconCSS name="iconoir:facebook" class="social-icon"></IconCSS>
          </a>
          <a href="/" class="social-item tiktok">
            <IconCSS name="mingcute:tiktok-line" class="social-icon"></IconCSS>
          </a>
        </div>
      </div>

      <div class="address">
        <div class="address-item">
          <IconCSS name="iconoir:map" class="address-icon"></IconCSS>
          <a href="/" class="address-link">Днепропетровская область, Днепр, улица Троицкая, 3А</a>
        </div>
        <div class="address-item">
          <IconCSS name="iconoir:mail" class="address-icon"></IconCSS>
          <a href="/" class="address-link">djini.in.ua@gmail.com</a>
        </div>
      </div>
    </div>

    <div class="nav">
      <div class="sub">
        <div class="sub-title">Подписывайтесь на скидки!</div>
        <div class="footer-label">Рассылаем подписчикам промокоды и бонусы каждую неделю.</div>

        <div class="sub-form">
          <form-text v-model="sub.email" placeholder="Введите ваш email-адрес" class="sub-input"></form-text>
          <button class="button primary sub-btn">Подписаться</button>
        </div>
      </div>

      <hr class="footer-hr nav-hr" />

      <div class="menu menu-1">
        <div class="footer-label">Гид по покупке</div>
        <ul class="menu-ul">
          <li v-for="item in menu.customer" :key="item.id">
            <NuxtLink :to="localePath(item.link)" class="menu-link">{{ item.title }}</NuxtLink>
          </li>
        </ul>
      </div>

      <div class="menu menu-2">
        <div class="footer-label">Общая информация</div>
        <ul class="menu-ul">
          <li v-for="item in menu.info" :key="item.id">
            <NuxtLink :to="localePath(item.link)" class="menu-link">{{ item.title }}</NuxtLink>
          </li>
        </ul>
      </div>

      <div class="vendors">
        
        <div class="payment">
          <div class="footer-label">Мы принимаем</div>
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
          <div class="footer-label">Служби доставки</div>
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
      Djini.com.ua ©2019-2023 / Все права защищены 
    </div>

  </footer>
</template>

<i18n src="./lang.yaml" lang="yaml"></i18n>