<script setup>
const {t} = useI18n()
const props = defineProps({})
const user = ref({
  firstname: 'Ivan',
  lastname: '',
  phone: '',
  email: ''
})

const address = ref({
  street: '',
  warehouse: '',
  city: '',
  zip: ''
})
// COMPUTEDS
// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='./settings.scss' lang='scss' scoped></style>
<style src='./../account-page.scss' lang='scss' scoped></style>
<i18n src='./lang.yaml' lang='yaml'></i18n>

<template>
  <div>
    <div class="title-secondary">{{ t('title.settings') }}</div>
    <div class="settings-box">
      <div class="settings-label">{{ t('personal_data') }}</div>
      <div class="settings-grid">
        <form-text v-model="user.firstname" :placeholder="t('form.firstname')"></form-text>
        <form-text v-model="user.lastname" :placeholder="t('form.lastname')"></form-text>
        <form-text v-model="user.phone" :placeholder="t('form.phone')"></form-text>
        <form-text v-model="user.email" :placeholder="t('form.email')"></form-text>
      </div>
    </div>
    <div class="settings-box">
      <div class="settings-label">{{ t('label.delivery') }}</div>
      <div class="settings-grid">
        <form-text v-model="address.street" :placeholder="t('form.address')"></form-text>
        <form-text v-model="address.city" :placeholder="t('form.settlement')"></form-text>
        <form-text v-model="address.zip" :placeholder="t('form.zip')"></form-text>
        <form-text v-model="address.warehouse" :placeholder="t('form.warehouse')"></form-text>
      </div>
    </div>
    <div class="settings-box">
      <div class="settings-label">{{ t('security') }}</div>
      <div class="settings-buttons">
        <button class="button secondary">
          <IconCSS name="iconoir:mail" class="inline-icon"></IconCSS>
          <span>{{ t('change_email') }}</span>
        </button>
        <button class="button secondary">
          <IconCSS name="iconoir:password-cursor" class="inline-icon"></IconCSS>
          <span>{{ t('change_pass') }}</span>
        </button>
      </div>
    </div>
  </div>
</template>