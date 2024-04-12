<script setup>
import {useAuthStore} from '~/store/auth'

const {t} = useI18n()
const props = defineProps({})

definePageMeta({
  crumb: {
    name: 'title.account.settings',
    item: '/'
  },
  tab: 'settings'
});


const isLoading = ref(false)
const isLoadingPassword = ref(false)

const user = ref({
  firstname: null,
  lastname: null,
  phone: null,
  email: null
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
const saveAdressHandler = () => {

}

const saveInfoHandler = () => {
  isLoading.value = true
  useAuthStore()
    .update({data: {
      firstname: user.value.firstname,
      lastname: user.value.lastname,
      phone: user.value.phone
    }})
    .then(({data, error}) => {
      if(data) {
        useNoty().setNoty({
          content: t('noty.update.success'),
          type: 'success'
        })
      }

      if(error) {
        useNoty().setNoty({
          content: t('noty.update.fail'),
          type: 'error'
        })
      }
    })
    .finally(() => {
      isLoading.value = false
    })
}

const showUpdateEmailHandler = () => {
  useModal().open(resolveComponent('ModalAuthEmailNew'), null, null, {width: {max: 420}})
}


const updatePasswordConfirmHandler = () => {
  useModal().open(resolveComponent('ModalConfirm'), {
    title: t('title_change_pass'),
    desc: t('desc_change'),
    yes: {
      title: t('button.yes'),
      callback: updatePasswordHandler
    },
    no: {
      title: t('button.cancel'),
      callback: null
    },
    type: 'good'
  }, null, {
    width: {
      max: 420
    }
  })
}

const updatePasswordHandler = () => {
  useModal().open(resolveComponent('ModalAuthPasswordReset'), user.value.email, null, {width: {max: 420}})

  // isLoadingPassword.value = true
  // useAuthStore().sendPasswordResetLink(user.value.email).then(({data, error}) => {
  //   if(data !== null)
  //     useNoty().setNoty({
  //       content: t('noty.auth.password.recovery.sent', {email: user.value.email})
  //     })
    
  //   if(error) {
  //     useNoty().setNoty({
  //       content: t(`errors.${error.message}`),
  //       type: 'error'
  //     })
  //   }
  // }).finally(() => {
  //   isLoadingPassword.value = false
  // })
}

// WATCHERS
watch(() => useAuthStore().user, (v) => {
  if(v){
    user.value = v
  }
}, {
  immediate: true
})
</script>

<style src='./settings.scss' lang='scss' scoped></style>
<style src='./../account-page.scss' lang='scss' scoped></style>
<i18n src='./lang.yaml' lang='yaml'></i18n>

<template>
  <div>
    <div class="title-secondary">{{ t('title.account.settings') }}</div>
    <div class="settings-box">
      <div class="settings-label">{{ t('personal_data') }}</div>
      <div class="settings-grid">
        <form-text v-model="user.firstname" :placeholder="t('form.firstname')"></form-text>
        <form-text v-model="user.lastname" :placeholder="t('form.lastname')"></form-text>
        <form-text v-model="user.phone" :placeholder="t('form.phone')"></form-text>
        <form-text v-model="user.email" :placeholder="t('form.email')"></form-text>
      </div>
      <button
        @click="saveInfoHandler"
        class="button primary settings-action-btn"
      >{{ t('button.save') }}</button>
    </div>
    <div class="settings-box">
      <div class="settings-label">{{ t('label.delivery') }}</div>
      <div class="settings-grid">
        <form-text v-model="address.street" :placeholder="t('form.delivery.address')"></form-text>
        <form-text v-model="address.city" :placeholder="t('form.delivery.settlement')"></form-text>
        <form-text v-model="address.zip" :placeholder="t('form.delivery.zip')"></form-text>
        <form-text v-model="address.warehouse" :placeholder="t('form.delivery.warehouse')"></form-text>
      </div>
      <button @click="saveAdressHandler" class="button primary settings-action-btn">{{ t('button.save') }}</button>
    </div>
    <div class="settings-box">
      <div class="settings-label">{{ t('security') }}</div>
      <div class="settings-buttons">
        <button @click="showUpdateEmailHandler" class="button secondary">
          <IconCSS name="iconoir:mail" class="inline-icon"></IconCSS>
          <span>{{ t('change_email') }}</span>
        </button>
        <button @click="updatePasswordConfirmHandler" class="button secondary">
          <IconCSS name="iconoir:password-cursor" class="inline-icon"></IconCSS>
          <span>{{ t('change_pass') }}</span>
        </button>
      </div>
    </div>
  </div>
</template>