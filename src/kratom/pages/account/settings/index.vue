<script setup lang="ts">
const { t } = useI18n()
const { user: authUser, updateProfile, requestEmailChange, changePassword, init } = useAuth()
const noty = useNoty()

await init()

definePageMeta({
  crumb: {
    name: 'title.account.settings',
    item: '/account/settings',
  },
  tab: 'settings',
})

const userForm = ref({
  first_name: '',
  last_name: '',
  phone: '',
  email: '',
})

const emailForm = ref({
  email: '',
  password: '',
})

const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const isSavingProfile = ref(false)
const isChangingEmail = ref(false)
const isChangingPassword = ref(false)

const hydrateFromAuth = (value: Record<string, any> | null) => {
  userForm.value = {
    first_name: value?.first_name || '',
    last_name: value?.last_name || '',
    phone: value?.phone || '',
    email: value?.email || '',
  }
}

watch(
  authUser,
  (value) => {
    hydrateFromAuth(value)
  },
  { immediate: true, deep: true },
)

const saveProfile = async () => {
  isSavingProfile.value = true
  try {
    await updateProfile({
      first_name: userForm.value.first_name,
      last_name: userForm.value.last_name,
      phone: userForm.value.phone,
    })

    noty.setNoty({ content: t('noty.update.success'), type: 'success' })
  } catch (error) {
    noty.setNoty({ content: t('noty.update.fail'), type: 'error' }, 7000)
  } finally {
    isSavingProfile.value = false
  }
}

const submitEmailChange = async () => {
  isChangingEmail.value = true
  try {
    await requestEmailChange({
      email: emailForm.value.email,
      password: emailForm.value.password,
    })

    emailForm.value.password = ''
    noty.setNoty({
      content: t('noty.auth.email.sent', { email: emailForm.value.email }),
      type: 'success',
    }, 7000)
  } catch (error: any) {
    noty.setNoty({
      content: String(error?.data?.message || t('noty.update.fail')),
      type: 'error',
    }, 7000)
  } finally {
    isChangingEmail.value = false
  }
}

const submitPasswordChange = async () => {
  isChangingPassword.value = true
  try {
    await changePassword({
      current_password: passwordForm.value.current_password,
      password: passwordForm.value.password,
      password_confirmation: passwordForm.value.password_confirmation,
    })

    passwordForm.value = {
      current_password: '',
      password: '',
      password_confirmation: '',
    }

    noty.setNoty({
      content: t('noty.auth.password.changed.success'),
      type: 'success',
    }, 7000)
  } catch (error: any) {
    noty.setNoty({
      content: String(error?.data?.message || t('noty.update.fail')),
      type: 'error',
    }, 7000)
  } finally {
    isChangingPassword.value = false
  }
}
</script>

<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <div class="account-settings">
    <section class="account-settings__section">
      <div class="account-settings__section-header">
        <div>
          <div class="account-settings__eyebrow">{{ t('personal_data') }}</div>
          <h2 class="account-settings__title">{{ t('title.account.settings') }}</h2>
        </div>
      </div>

      <div class="account-settings__grid">
        <form-text v-model="userForm.first_name" :placeholder="t('form.firstname')" />
        <form-text v-model="userForm.last_name" :placeholder="t('form.lastname')" />
        <form-phone-region v-model="userForm.phone" :placeholder="t('form.phone')" />
        <form-text v-model="userForm.email" :placeholder="t('form.email')" readonly />
      </div>

      <button type="button" class="button primary" :class="{ loading: isSavingProfile }" @click="saveProfile">
        {{ t('button.save') }}
      </button>
    </section>

    <section class="account-settings__section">
      <div class="account-settings__section-header">
        <div>
          <div class="account-settings__eyebrow">{{ t('security') }}</div>
          <h3 class="account-settings__subtitle">{{ t('security') }}</h3>
        </div>
      </div>

      <div class="account-settings__security">
        <div class="account-settings__security-card">
          <div class="account-settings__security-title">{{ t('change_email') }}</div>
          <div class="account-settings__grid">
            <form-text v-model="emailForm.email" :placeholder="t('form.email')" />
            <form-text v-model="emailForm.password" :placeholder="t('form.password')" type="password" />
          </div>
          <button type="button" class="button secondary" :class="{ loading: isChangingEmail }" @click="submitEmailChange">
            {{ t('button.send') }}
          </button>
        </div>

        <div class="account-settings__security-card">
          <div class="account-settings__security-title">{{ t('change_pass') }}</div>
          <div class="account-settings__grid">
            <form-text v-model="passwordForm.current_password" :placeholder="t('current_password')" type="password" />
            <form-text v-model="passwordForm.password" :placeholder="t('form.password')" type="password" />
            <form-text v-model="passwordForm.password_confirmation" :placeholder="t('form.password_confirmation')" type="password" />
          </div>
          <button type="button" class="button secondary" :class="{ loading: isChangingPassword }" @click="submitPasswordChange">
            {{ t('button.save') }}
          </button>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped lang="scss">
.account-settings {
  display: grid;
  gap: 18px;
}

.account-settings__section {
  display: grid;
  gap: 18px;
}

.account-settings__section-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.account-settings__eyebrow {
  margin-bottom: 8px;
  color: #8a5a2b;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.account-settings__title,
.account-settings__subtitle {
  color: #1f2b1d;
  font-size: 28px;
  line-height: 1.05;
}

.account-settings__subtitle {
  font-size: 24px;
}

.account-settings__grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 14px;

  @include desktop {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

.account-settings__security {
  display: grid;
  gap: 16px;
}

.account-settings__security-card {
  display: grid;
  gap: 16px;
  padding: 20px;
  border-radius: 24px;
  background: rgba(255, 255, 255, 0.68);
  border: 1px solid rgba(74, 91, 68, 0.12);
}

.account-settings__security-title {
  color: #1f2b1d;
  font-size: 18px;
  font-weight: 700;
}
</style>
