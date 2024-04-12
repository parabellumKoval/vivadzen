<script setup>
import { useAuthStore } from '~~/store/auth';
const {t} = useI18n()
const props = defineProps({})

// COMPUTEDS
const user = computed(() => {
  return useAuthStore().user
})

const auth = computed(() => {
  return useAuthStore().auth
})

const photo = computed(() => {
  return useAuthStore().avatar
})

const name = computed(() => {
  let fullname = ((user.value.firstname || '') + ' ' + (user.value.lastname || '')).trim()
  return fullname || user.value.fullname
})
// METHODS

// HANDLERS
// WATCHERS
</script>

<style src='./card.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="profile">
    <nuxt-img
      :src = "photo"
      width="70"
      height="70"
      sizes = "mobile:100vw tablet:70px desktop:70px"
      format = "webp"
      quality = "60"
      loading = "lazy"
      fit="outside"
      class="profile-image"
    >
    </nuxt-img>
    <div class="profile-data">
      <div class="profile-name">{{ name }}</div>
      <div class="profile-email">{{ user.email }}</div>
    </div>
    
    <slot name="footer" />
  </div>
</template>