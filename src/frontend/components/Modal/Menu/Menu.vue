<script setup>
const {t} = useI18n()
const props = defineProps({})

// COMPUTEDS
const menu = computed(() => {
  return {
    customer: useMenu().customer.value,
    info: useMenu().info.value
  }
})
// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='./menu.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <modal-wrapper>
    <div class="mm">
      <div class="mm-header">
        <div class="mm-header-user">
          <div class="avatar-faker">
            <IconCSS name="iconoir:user" size="24"></IconCSS>
          </div>
          <div class="mm-header-user-btns">
            <button class="mm-header-btn">{{ t('label.login') }}</button>
            <div class="mm-header-delimiter"></div>
            <button class="mm-header-btn">{{ t('label.label') }}</button>
          </div>
        </div>
        <div class="langs">
          <span class="langs-label">{{ t('label.lang') }}</span>
          <the-header-languages class="langs-switcher"></the-header-languages>
        </div>
      </div>
      <div class="mm-content">
        <the-header-catalog class="mm-content-catalog"></the-header-catalog>

        <div class="menu menu-1">
          <div class="menu-label">{{ t('label.guide') }}</div>
          <ul class="menu-ul">
            <li v-for="item in menu.customer" :key="item.id">
              <NuxtLink :to="localePath(item.link)" class="menu-link">{{ item.title }}</NuxtLink>
            </li>
          </ul>
        </div>

        <div class="menu menu-2">
          <div class="menu-label">{{ t('label.common_info') }}</div>
          <ul class="menu-ul">
            <li v-for="item in menu.info" :key="item.id">
              <NuxtLink :to="localePath(item.link)" class="menu-link">{{ item.title }}</NuxtLink>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </modal-wrapper>
</template>