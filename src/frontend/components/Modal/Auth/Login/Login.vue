<script setup>
const { t } = useI18n()

const auth = ref({
  login: '',
  pass: '',
})

const regHandler = () => {
  useModal().open(resolveComponent('ModalAuthRegister'), null, null, {width: {min: 420, max: 420}})
}
</script>

<style src="./../auth.scss" lang="scss" scoped></style>
<style src="./login.scss" lang="scss" scoped></style>

<template>
  <modal-wrapper :title="t('label.login')" class="modal-wrapper-auth">
    <div class="modal-content-wrapper">
      
      <div class="form">
        <form-text v-model="auth.login" placeholder="Введите email"></form-text>
        <form-password v-model="auth.pass" placeholder="Введите пароль"></form-password>
        <button class="button primary form-button">{{ t('button.enter') }}</button>
      </div>

      <div class="footer">
        <div class="forget">
          <div class="label">Забыли пароль?</div>
          <simple-button-text text="Восстановить" icon="iconoir:password-error" :callback="regHandler"></simple-button-text>
        </div>
        <div class="registration">
          <div class="label">У вас нет аккаунта?</div>
          <simple-button-text text="Регистрация" icon="iconoir:lock" :callback="regHandler"></simple-button-text>
        </div>
      </div>

    </div>
  </modal-wrapper>
</template>