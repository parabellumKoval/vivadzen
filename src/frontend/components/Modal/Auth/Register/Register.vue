<script setup>
const { t } = useI18n()

const auth = ref({
  email: '',
  pass: '',
  pass_conf: ''
})

const regHandler = () => {
  useModal().open(resolveComponent('ModalAuthRegister'), null, null, {width: {min: 420, max: 420}})
}
</script>

<style src="./../auth.scss" lang="scss" scoped></style>
<style src="./register.scss" lang="scss" scoped></style>

<template>
  <modal-wrapper :title="t('label.register')" class="modal-wrapper-auth">
    <div class="modal-content-wrapper">
      
      <div class="form">
        <form-text v-model="auth.email" placeholder="Введите email"></form-text>
        <form-password v-model="auth.pass" placeholder="Введите пароль"></form-password>
        <form-password v-model="auth.pass_conf" placeholder="Введите пароль"></form-password>
        <button class="button primary form-button">{{ t('button.register') }}</button>
      </div>

      <div class="footer">
        <div class="registration">
          <div class="label">Есть аккаунт?</div>
          <simple-button-text text="Вход" icon="iconoir:lock" :callback="regHandler"></simple-button-text>
        </div>
      </div>

    </div>
  </modal-wrapper>
</template>