<script setup>
import {useAuthStore} from '~/store/auth'

const { t } = useI18n()
const isLoading = ref(false)

const user = ref({
  password: '',
  password_confirmation: '',
})

const errors = ref({
  password: null,
  password_confirmation: null
})


// METHODS
const validate = () => {
  if(user.value.password !== user.value.password_confirmation) {
    useNoty().setNoty({
      content: t('error.auth.password.confirmation'),
      type: 'error'
    }, 7000)
    
    return false
  }

  return true
}

// HANDLERS
const saveHandler = () => {
  if(!validate())
    return;

  isLoading.value = true

  useAuthStore().update({
    password: user.value.password
  }).then(({data, error}) => {
    
    console.log(data, error)

    if(data.user){
      useNoty().setNoty({
        content: t('noty.auth.password.changed.success'),
        type: 'success'
      })
      
      useModal().close()
      navigateTo('/')
    }

    if(error){
      useNoty().setNoty({
        content: t(`errors.${error.message}`),
        type: 'error'
      }, 7000)
    }

  }).finally(() => {
    isLoading.value = false
  })
}

</script>


<style src="./../../auth.scss" lang="scss" scoped />
<style src="./new.scss" lang="scss" scoped />

<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
<modal-wrapper :title="t('title')">
  <div class="modal-content-wrapper">
    <div class="popup__body">
      <form-password
        v-model="user.password"
        :placeholder="$t('form.enter.password')"
        :error="errors?.password"
        required
        class="form-component"
      >
      </form-password>

      <form-password
        v-model="user.password_confirmation"
        :placeholder="$t('form.enter.password_confirmation')"
        :error="errors?.password_confirmation"
        required
        class="form-component"
      >
      </form-password>
    </div>

    <div class="footer">
      <button
        @click="saveHandler"
        :class="{loading: isLoading}"
        clickable
        class="button primary btn"
      >
        <span class="text">{{ $t('button.save') }}</span>
      </button>
    </div>
  </div>
</modal-wrapper>
</template>