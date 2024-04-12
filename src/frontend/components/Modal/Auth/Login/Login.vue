<script setup>
import {useAuthStore} from '~/store/auth'

const { t } = useI18n()
const isLoading = ref(false)

const auth = ref({
  email: '',
  password: ''
})


const errors = ref({
  email: null,
  password: null
})

// HANDLERS
const loginHandler = () => {
  isLoading.value = true

  useAuthStore().login(auth.value)
    .then(({data, error}) => {
      if(data && data.user) {
        useAuthStore().setUserFromSession(data.user)

        useNoty().setNoty({
          content: t('noty.auth.login.success')
        }, 5000)
        
        useModal().close()
      }

      if(error){
        useNoty().setNoty({
          content: t(`error.auth.${error.message}`),
          type: 'error'
        }, 10000)
      }
    })
    .finally(() => {
      isLoading.value = false
    })
}

const changePasswordHandler = () => {
  useModal().open(resolveComponent('ModalAuthPasswordReset'), null, null, {width: {min: 420, max: 420}})
}

const regHandler = () => {
  useModal().open(resolveComponent('ModalAuthRegister'), null, null, {width: {min: 420, max: 420}})
}
</script>

<style src="./../auth.scss" lang="scss" scoped></style>
<style src="./login.scss" lang="scss" scoped></style>

<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <modal-wrapper :title="t('label.login')" class="modal-wrapper-auth">
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

        <button @click="loginHandler" class="button primary form-button">{{ t('button.enter') }}</button>
      </div>

      <div class="footer">
        <div class="forget">
          <div class="label">{{ t('forget') }}</div>
          <simple-button-text
            :text="t('restore')"
            :callback="changePasswordHandler"
            icon="iconoir:password-error"
          ></simple-button-text>
        </div>
        <div class="registration">
          <div class="label">{{ t('have_not') }}</div>
          <simple-button-text
            :text="t('reg')"
            :callback="regHandler"
            icon="iconoir:lock"
          ></simple-button-text>
        </div>
      </div>

    </div>
  </modal-wrapper>
</template>