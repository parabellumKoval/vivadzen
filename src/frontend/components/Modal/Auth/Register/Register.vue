<script setup>
import {useAuthStore} from '~/store/auth'

const { t } = useI18n()
const isLoading = ref(false)

const auth = ref({
  email: '',
  password: '',
  password_confirmation: '',
  privacy: false
})

const errors = ref({
  fullname: null,
  email: null,
  password: null,
  password_confirmation: null
})

// HANDLERS
const registerValidateHandler = () => {
  if(!auth.value.email || auth.value.email < 3) {
    useNoty().setNoty({
      content: t('noty.auth.register.fail'),
      type: 'error'
    })

    errors.value.email = t('error.required')
    return
  }

  if(!auth.value.password || auth.value.password.length < 6) {
    useNoty().setNoty({
      content: t('noty.auth.register.fail'),
      type: 'error'
    })

    errors.value.password = t('error.auth.password.require')
    return
  }

  if(auth.value.password !== auth.value.password_confirmation) {
    useNoty().setNoty({
      content: t('noty.auth.register.fail'),
      type: 'error'
    })

    errors.value.password_confirmation = t('error.auth.password.confirmation')
    return
  }

  if(!auth.value.privacy) {
    useNoty().setNoty({
      content: t('noty.auth.register.privacy'),
      type: 'error'
    })

    return
  }

  registerHandler()
}

const registerHandler = () => {
  isLoading.value = true

  useAuthStore().register(auth.value)
    .then(({data, error}) => {
      if(data && data.user) {
        // useAuthStore().setUserFromSession(data.user)

        useNoty().setNoty({
          content: t('noty.auth.email.confirmation.sent')
        }, 10000)
        
      }

      if(error){
        useNoty().setNoty({
          content: t(`error.auth.${error.message}`)
        }, 10000)
      }
    })
    .finally(() => {
      isLoading.value = false
    })
}

const loginHandler = () => {
  useModal().open(resolveComponent('ModalAuthSocial'), null, null, {width: {min: 420, max: 420}})
}
</script>

<style src="./../auth.scss" lang="scss" scoped></style>
<style src="./register.scss" lang="scss" scoped></style>

<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <modal-wrapper :title="t('label.register')" class="modal-wrapper-auth">
    <div class="modal-content-wrapper">
      
      <div class="form">
        <form-text
          v-model="auth.email"
          :error="errors?.email"
          @input="() => errors.email = null"
          :placeholder="t('form.enter.email')"
          required
        ></form-text>
        <form-password
          v-model="auth.password"
          :error="errors?.password"
          @input="() => errors.password = null"
          :placeholder="t('form.enter.password')"
          required
        ></form-password>
        <form-password
          v-model="auth.password_confirmation"
          :error="errors?.password_confirmation"
          @input="() => errors.password_confirmation = null"
          :placeholder="t('form.enter.password_confirmation')"
          required
        ></form-password>

        <div class="agree-block">
          <form-checkbox v-model="auth.privacy" :is-boolean="true">
            <span class="privacy-policy">
              {{ $t('messages.i_agreed_with') }}&nbsp;
              <NuxtLink :to="localePath('/privacy')" clickable class="a-link" target="_blade">{{ $t('messages.agree_privacy_policy') }}</NuxtLink>
            </span>
          </form-checkbox>
        </div>

        <button @click="registerValidateHandler" class="button primary form-button">{{ t('button.register') }}</button>
      </div>

      <div class="footer">
        <div class="registration">
          <div class="label">{{ t('account') }}</div>
          <simple-button-text
            :text="t('button.enter')"
            :callback="loginHandler"
            icon="iconoir:lock"
          ></simple-button-text>
        </div>
      </div>

    </div>
  </modal-wrapper>
</template>