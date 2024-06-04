<script setup>
import { useAuthStore } from '~~/store/auth';
const {t} = useI18n()
const props = defineProps({})

// COMPUTEDS
const auth = computed(() => {
  return useAuthStore().auth
})
// METHODS
// HANDLERS
const loginModalHandler = () => {
  useModal().open(resolveComponent('ModalAuthSocial'), null, null, {width: {min: 420, max: 420}})
}

const logoutConfirmHandler = () => {
  useModal().open(resolveComponent('ModalConfirm'), {
    title: t('title'),
    desc: t('desc'),
    yes: {
      title: t('button.logout'),
      callback: logoutHandler
    },
    no: {
      title: t('button.cancel'),
      callback: null
    },
    type: 'default'
  }, null, {
    width: {
      max: 420
    }
  })
}

const logoutHandler = () => {
  useAuthStore().logout().then(() => {
    navigateTo('/checkout')
  })
}

// WATCHERS
</script>

<style src='./user.scss' lang='scss' scoped></style>
<i18n src='./lang.yaml' lang='yaml'></i18n>

<template>
  <div class="user-wrapper">
    <template v-if="auth">
      <div class="auth-wrapper">
        <account-card></account-card>
        <div class="auth-wrapper-line"></div>
        <div class="auth-wrapper-text">
          {{ t('logout') }}
          <simple-button-text :text="t('button.logout_system')" :callback="logoutConfirmHandler"></simple-button-text>
        </div>
      </div>
    </template>
    <template v-else>
      <div class="form-static">
        <div class="label">{{ t('label.auth') }}</div>
        <div class="">
          <simple-button-text
            @click="loginModalHandler"
            :text="t('button.login_or_register')"
            icon="iconoir:user"
            class="promocode-action">
          </simple-button-text>
        </div>
      </div>
    </template>
  </div>
</template>