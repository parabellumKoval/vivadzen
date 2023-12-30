<script setup>
const {t} = useI18n()

const avatar = computed(() => {
  return 'https://daks2k3a4ib2z.cloudfront.net/56e4ca59e9c75b714d1a5771/5773cef3b411727174e0a2e9_JMD_Face.png' || useAuthStore().avatar
})


// HANDLERS
const logoutHandler = () => {

}
</script>

<style src="./account.scss" lang="scss" scoped />
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <modal-wrapper :class="{dark: $device.isMobile}">
    <div class="profile">
      <nuxt-img
        :src="avatar"
        width="190"
        height="60"
        sizes = "mobile:30px tablet:30px desktop:30px"
        format = "avif"
        fit = "cover"
        quality = "100"
        class = "image"
      />
      <div>
        <div class="name">Freesty goop</div>
        <div class="email">freesty1goop@gmail.com</div>
      </div>
    </div>

    <the-sidebar-menu :items="useMenu().account" class="menu"></the-sidebar-menu>

    <hr class="light wide-hr">

    <button @click="logoutHandler" class="logout-btn">
      <div class="icon-wrapper">
        <IconCSS name="ph:sign-out-light" size="20" class="icon"></IconCSS>
      </div>
      <span class="name">{{ t('logout') }}</span>
    </button>

  </modal-wrapper>
</template>