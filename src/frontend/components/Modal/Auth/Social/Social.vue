<script setup>
const { t } = useI18n()

const regHandler = () => {
  useModal().open(resolveComponent('ModalAuthRegister'), null, null, {width: {min: 420, max: 420}})
}

const emailLoginHandler = () => {
  useModal().open(resolveComponent('ModalAuthLogin'), null, null, {width: {min: 420, max: 420}})
}
</script>

<style src="./../auth.scss" lang="scss" scoped></style>
<style src="./social.scss" lang="scss" scoped></style>

<template>
  <modal-wrapper :title="t('label.auth_enter')" class="modal-wrapper-auth">
    <div class="modal-content-wrapper">
      <div class="buttons">
        <button class="button google-bg">
          <IconCSS name="iconoir:google" class="icon"></IconCSS>
          <span class="text">Войти с помощью Google</span>
        </button>

        <button class="button facebook-bg">
          <IconCSS name="iconoir:facebook" class="icon"></IconCSS>
          <span class="text">Войти с помощью Facebook</span>
        </button>

        <button @click="emailLoginHandler" class="button email-btn">
          <IconCSS name="iconoir:mail-opened" class="icon"></IconCSS>
          <span class="text">Войти с помощью Email</span>
        </button>
      </div>
      <div class="footer">
        <div class="registration">
          <div class="label">У вас нет аккаунта?</div>
          <simple-button-text text="Регистрация" icon="iconoir:lock" :callback="regHandler"></simple-button-text>
        </div>
      </div>
    </div>
  </modal-wrapper>
</template>