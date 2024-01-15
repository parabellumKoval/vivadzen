<script setup>
const { t } = useI18n()
</script>

<style src="./header.scss" lang="scss" scoped />

<template>
  <header class="header">

    <!-- MENU -->
    <the-header-menu class="menu"></the-header-menu>

    <!-- LOGO -->
    <NuxtLink :to="localePath('/')" class="logo" clickable>
      <nuxt-img
        src="/images/djini.png"
        width="55"
        height="51"
        sizes = "mobile:50px tablet:120px desktop:120px"
        format = "avif"
        fit="inside"
        quality = "100"
        class = "logo-img"
      />
    </NuxtLink>

    <!-- CATALOG -->
    <the-header-catalog v-if="!$device.isMobile" class="catalog"></the-header-catalog>

    <!-- SEARCH -->
    <the-header-search class="search"></the-header-search>

    <!-- PHONE -->
   <the-header-contacts v-if="!$device.isMobile" class="contacts"></the-header-contacts>

    <!-- LANGUAGE -->
    <the-header-languages class="languages"></the-header-languages>

    <!-- SEARCH BTN -->
    <button class="header-btn search-btn" type="button" clickable>
      <IconCSS name="iconoir:search" size="30px" class="icon"></IconCSS>
      <span class="hint">{{ t('search') }}</span>
    </button> 

    <!-- USER -->
    <the-header-profile class="profile"></the-header-profile>

    <!-- CART -->
    <the-header-cart class="cart"></the-header-cart>

  </header>
</template>

<!-- <i18n src="./lang.yaml" lang="yaml"></i18n> -->