<script setup>
const {t} = useI18n()
</script>

<style src="./menu.scss" lang="scss" scoped />
<!-- <i18n src="./lang.yaml" lang="yaml"></i18n> -->

<template>
  <modal-wrapper class="dark">
    <nuxt-link to="/">
      <nuxt-img
        src="/images/logo-full.svg"
        width="190"
        height="60"
        class = "logo-img"
      />
      
    </nuxt-link>

    <the-sidebar-menu :items="useMenu().all" class="menu"></the-sidebar-menu>

    <hr class="light">

    <the-header-button-lang class="lang"></the-header-button-lang>

  </modal-wrapper>
</template>