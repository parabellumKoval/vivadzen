<script setup lang="ts">
const { t } = useI18n()
const route = useRoute()
const regionPath = useToLocalePath()
const { resetPassword } = useAuth()
const noty = useNoty()

definePageMeta({
  bg: '#f3ece2',
})

if (route.query.error) {
  const statusCode = typeof route.query.error_code === 'string'
    ? Number.parseInt(route.query.error_code, 10) || 400
    : 400

  throw createError({
    statusCode,
    message: typeof route.query.error_description === 'string'
      ? route.query.error_description
      : 'Unable to reset password',
  })
}

const token = computed(() => {
  if (typeof route.query.t === 'string' && route.query.t) {
    return route.query.t
  }

  if (typeof route.query.token === 'string' && route.query.token) {
    return route.query.token
  }

  return ''
})

const email = computed(() => typeof route.query.email === 'string' ? route.query.email : '')

const form = reactive({
  password: '',
  password_confirmation: '',
})

const errors = reactive<Record<string, string | null>>({
  password: null,
  password_confirmation: null,
})

const isLoading = ref(false)

const applyValidationErrors = (payload: Record<string, any> | undefined) => {
  Object.keys(errors).forEach((key) => {
    if (key in errors) errors[key] = null
  })

  Object.entries(payload || {}).forEach(([key, value]) => {
    if (!(key in errors)) {
      return
    }

    if (Array.isArray(value) && value.length) {
      errors[key] = String(value[0])
      return
    }

    if (typeof value === 'string') {
      errors[key] = value
    }
  })
}

const submit = async () => {
  applyValidationErrors(undefined)

  if (!form.password) {
    errors.password = t('error.auth.password.require')
    return
  }

  if (form.password !== form.password_confirmation) {
    errors.password_confirmation = t('error.auth.password.confirmation')
    return
  }

  isLoading.value = true

  try {
    await resetPassword(token.value, email.value, form.password, form.password_confirmation)

    noty.setNoty({
      content: t('auth.reset_success'),
      title: '',
      type: 'success',
    }, 7000)

    await navigateTo({
      path: regionPath('/auth/login'),
      query: email.value ? { email: email.value } : undefined,
    })
  } catch (error: any) {
    const data = error?.data || {}
    applyValidationErrors(data?.errors)

    noty.setNoty({
      content: String(data?.message || t('noty.auth.password.recovery.fail')),
      title: '',
      type: 'error',
    }, 7000)
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="page-base auth-page">
    <div class="container kratom-page-container">
      <div class="auth-shell">
        <section class="auth-hero">
          <div class="auth-eyebrow">{{ t('auth.eyebrow') }}</div>
          <h1 class="auth-title">{{ t('auth.reset_title') }}</h1>
          <p class="auth-text">{{ t('auth.reset_text') }}</p>
        </section>

        <section class="auth-card">
          <div class="auth-card__header">
            <h2 class="auth-card__title">{{ t('auth.reset_title') }}</h2>
            <p class="auth-card__text">{{ email || t('auth.email_label') }}</p>
          </div>

          <form class="auth-form" @submit.prevent="submit">
            <form-text v-model="form.password" :placeholder="t('auth.password_new_label')" :error="errors.password" type="password" />
            <form-text
              v-model="form.password_confirmation"
              :placeholder="t('auth.password_confirm_label')"
              :error="errors.password_confirmation"
              type="password"
            />

            <button type="submit" class="button primary" :class="{ loading: isLoading }">
              {{ t('auth.reset_submit') }}
            </button>
          </form>
        </section>
      </div>
    </div>
  </div>
</template>

<style src="../auth/auth.scss" lang="scss" scoped></style>
<i18n src="../auth/lang.yaml" lang="yaml"></i18n>
