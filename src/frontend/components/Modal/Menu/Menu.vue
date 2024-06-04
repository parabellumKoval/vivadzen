<script setup>
import { useAuthStore } from '~~/store/auth';

const localePath = useLocalePath()
const {t} = useI18n()

// COMPUTEDS
const menu = computed(() => {
  return {
    customer: useMenu().customer.value,
    info: useMenu().info.value
  }
})

const user = computed(() => {
  return useAuthStore().user
})

const auth = computed(() => {
  return useAuthStore().auth
})

// METHODS
// HANDLERS
const goToAccount = () => {
  closeHandler()
  navigateTo(localePath('/account/settings'))
}

const closeHandler = () => {
  useModal().close()
}

const loginHandler = () => {
  useModal().open(resolveComponent('ModalAuthSocial'), null, null)
}
// WATCHERS
</script>

<style src='./menu.scss' lang='scss' scoped></style>
<i18n src='./lang.yaml' lang='yaml'></i18n>

<template>
  <modal-wrapper class="m-wrapper">
    <div class="mm">
      
      <div class="mm-header">
        <!-- User -->
        <div class="mm-header-user">
          <template v-if="auth && user" >
            <button @click="goToAccount" class="avatar" type="button" clickable>
              <nuxt-img
                :src="useAuthStore().avatar"
                width="50"
                height="50"
                sizes = "mobile:30px tablet:30px desktop:30px"
                format = "avif"
                fit = "cover"
                quality = "100"
                class = "avatar-image"
              />
            </button>
            <button @click="goToAccount" class="mm-header-btn">{{ user.email }}</button>
          </template>
          <template v-else>
            <div @click="loginHandler" class="avatar-faker">
              <IconCSS name="iconoir:user" size="24"></IconCSS>
            </div>
            <button @click="loginHandler" class="mm-header-btn">{{ t('login_or_register') }}</button>
          </template>
        </div>
        
        <!-- Languages -->
        <div class="langs">
          <span class="langs-label">{{ t('label.lang') }}</span>
          <the-header-languages class="langs-switcher"></the-header-languages>
        </div>

      </div>

      <div class="mm-content">

        <the-header-catalog class="mm-content-catalog"></the-header-catalog>

        <comparison-btn class="comp-btn"></comparison-btn>

        <div class="menu menu-2">
          <div class="menu-label">{{ t('label.common_info') }}</div>
          <ul class="menu-ul">
            <li v-for="item in menu.info" :key="item.id">
              <NuxtLink :to="localePath(item.link)" @click="closeHandler" class="menu-link">{{ item.title }}</NuxtLink>
            </li>
          </ul>
        </div>

        <div class="menu menu-1">
          <div class="menu-label">{{ t('label.guide') }}</div>
          <ul class="menu-ul">
            <li v-for="item in menu.customer" :key="item.id">
              <NuxtLink :to="localePath(item.link)" @click="closeHandler" class="menu-link">{{ item.title }}</NuxtLink>
            </li>
          </ul>
        </div>

      </div>
    </div>
  </modal-wrapper>
</template>