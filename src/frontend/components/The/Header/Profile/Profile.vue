<script setup>
import { useAuthStore } from '~~/store/auth';

const { t } = useI18n()

// COMPUTED
const user = computed(() => {
  return useAuthStore().user
})

const auth = computed(() => {
  return useAuthStore().auth
})


const showAuthHandler = () => {
  if(auth.value && user.value) {
    navigateTo(useLocalePath()('/account/settings'))
  } else {
    useModal().open(resolveComponent('ModalAuthSocial'), null, null, {width: {
      min: 420, max: 420
    }})
  }
}
</script>

<style src="./profile.scss" lang="scss" scoped />

<template>
  <div>
    <button v-if="auth && user" @click="showAuthHandler" class="header-btn profile-btn" type="button" clickable>
      <nuxt-img
        :src="useAuthStore().avatar"
        width="30"
        height="30"
        sizes = "mobile:30px tablet:30px desktop:30px"
        format = "avif"
        fit = "cover"
        quality = "100"
        class = "icon-image"
      />
      <span class="hint">{{ t('profile') }}</span>
    </button>

    <button v-if="!auth" @click="showAuthHandler" class="header-btn profile-btn" type="button" clickable>
      <IconCSS name="iconoir:user" size="30px" class="icon"></IconCSS>
      <span class="hint">{{ t('profile') }}</span>
    </button>
  </div>
</template>

<i18n lang="yaml">
uk:
  profile: "Профіль"
ru:
  profile: "Профиль"
</i18n>