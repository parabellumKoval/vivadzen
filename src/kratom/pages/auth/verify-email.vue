<script setup lang="ts">
const { t } = useI18n()
const route = useRoute()
const regionPath = useToLocalePath()
const {
  user,
  isAuthenticated,
  resendVerificationToLoggedIn,
  resendVerificationByEmail,
  init,
} = useAuth()
const noty = useNoty()

definePageMeta({
  bg: '#f3ece2',
})

await init()

const email = computed(() => {
  if (typeof route.query.email === 'string' && route.query.email) {
    return route.query.email
  }

  return user.value?.email || ''
})

const resendEmail = ref(email.value)
const isLoading = ref(false)

const loginQuery = computed(() => {
  const query: Record<string, string> = {}

  if (typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/')) {
    query.redirect = route.query.redirect
  }

  const nextEmail = resendEmail.value || email.value
  if (nextEmail) {
    query.email = nextEmail
  }

  return Object.keys(query).length ? query : undefined
})

watch(email, (value) => {
  if (!resendEmail.value) {
    resendEmail.value = value || ''
  }
}, { immediate: true })

const resend = async () => {
  if (!isAuthenticated.value && !resendEmail.value) {
    noty.setNoty({
      content: t('error.required'),
      title: '',
      type: 'error',
    }, 5000)
    return
  }

  isLoading.value = true

  try {
    if (isAuthenticated.value && user.value?.email) {
      await resendVerificationToLoggedIn()
    } else {
      await resendVerificationByEmail(resendEmail.value)
    }

    noty.setNoty({
      content: t('auth.verification_sent'),
      title: '',
      type: 'success',
    }, 7000)
  } catch (error: any) {
    noty.setNoty({
      content: String(error?.data?.message || t('auth.verification_sent')),
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
          <h1 class="auth-title">{{ t('auth.verify_title') }}</h1>
          <p class="auth-text">{{ t('auth.verify_text') }}</p>

          <div class="auth-note">
            <strong>{{ email || t('auth.email_label') }}</strong>
            <div>{{ t('auth.verify_resend_hint') }}</div>
          </div>
        </section>

        <section class="auth-card">
          <div class="auth-card__header">
            <h2 class="auth-card__title">{{ t('auth.verify_title') }}</h2>
            <p class="auth-card__text">{{ t('auth.verify_resend_hint') }}</p>
          </div>

          <div class="auth-state">
            <div>
              <strong>{{ t('auth.email_label') }}</strong>
              <div>{{ email || '—' }}</div>
            </div>

            <form-text
              v-if="!isAuthenticated"
              v-model="resendEmail"
              :placeholder="t('form.enter.email')"
            />

            <button type="button" class="button primary" :class="{ loading: isLoading }" @click="resend">
              {{ isAuthenticated ? t('auth.verify_logged_in') : t('auth.verify_guest') }}
            </button>
          </div>

          <div class="auth-footer">
            <div class="auth-footer__row">
              <NuxtLink :to="{ path: regionPath('/auth/login'), query: loginQuery }" class="auth-text-button">
                {{ t('auth.back_to_login') }}
              </NuxtLink>
              <NuxtLink :to="regionPath('/')" class="auth-text-button">
                {{ t('auth.back_to_home') }}
              </NuxtLink>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<style src="./auth.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>
