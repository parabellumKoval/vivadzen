<script setup lang="ts">
const { t } = useI18n()
const route = useRoute()
const regionPath = useToLocalePath()
const { login, socialSignIn, forgotPassword, init } = useAuth()
const noty = useNoty()

definePageMeta({
  bg: '#f3ece2',
  middleware: 'auth-bridge-guest',
})

await init()

const redirectTarget = computed(() => {
  if (typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/')) {
    return route.query.redirect
  }

  return regionPath('/account/orders')
})

const authLinkQuery = computed(() => {
  const query: Record<string, string> = {}

  if (typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/')) {
    query.redirect = route.query.redirect
  }

  const email = form.email || (typeof route.query.email === 'string' ? route.query.email : '')
  if (email) {
    query.email = email
  }

  return Object.keys(query).length ? query : undefined
})

const form = reactive({
  email: typeof route.query.email === 'string' ? route.query.email : '',
  password: '',
})

const recovery = reactive({
  email: typeof route.query.email === 'string' ? route.query.email : '',
})

const errors = reactive<Record<string, string | null>>({
  email: null,
  password: null,
  recovery: null,
})

const isLoading = ref(false)
const isRecoveryLoading = ref(false)
const showRecovery = ref(false)

const benefitItems = computed(() => [
  {
    icon: 'ph:clock-counter-clockwise',
    title: t('auth.benefits.orders_title'),
    text: t('auth.benefits.orders_text'),
  },
  {
    icon: 'ph:map-pin-line',
    title: t('auth.benefits.addresses_title'),
    text: t('auth.benefits.addresses_text'),
  },
  {
    icon: 'ph:shield-check',
    title: t('auth.benefits.profile_title'),
    text: t('auth.benefits.profile_text'),
  },
])

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

const socialRedirect = () => {
  if (typeof window === 'undefined') {
    return undefined
  }

  const target = redirectTarget.value || regionPath('/account/orders')
  return `${window.location.origin}${target}`
}

const submit = async () => {
  applyValidationErrors(undefined)

  if (!form.email) {
    errors.email = t('error.required')
    return
  }

  if (!form.password) {
    errors.password = t('error.auth.password.require')
    return
  }

  isLoading.value = true

  try {
    await login(form.email, form.password)
    noty.setNoty({ content: t('noty.auth.login.success'), title: '', type: 'success' }, 5000)
    await navigateTo(redirectTarget.value)
  } catch (error: any) {
    const data = error?.data || {}
    applyValidationErrors(data?.errors)

    if (data?.code === 'email_unverified') {
      await navigateTo({
        path: regionPath('/auth/verify-email'),
        query: {
          email: form.email,
          redirect: redirectTarget.value,
        },
      })
      return
    }

    noty.setNoty({
      content: String(data?.message || t('noty.auth.login.failed')),
      title: '',
      type: 'error',
    }, 7000)
  } finally {
    isLoading.value = false
  }
}

const recoverPassword = async () => {
  errors.recovery = null

  if (!recovery.email) {
    errors.recovery = t('error.required')
    return
  }

  isRecoveryLoading.value = true

  try {
    await forgotPassword(recovery.email)
    noty.setNoty({
      content: t('auth.reset_sent'),
      title: '',
      type: 'success',
    }, 7000)
  } catch (error: any) {
    errors.recovery = String(error?.data?.message || t('noty.auth.password.recovery.fail'))
    noty.setNoty({
      content: errors.recovery,
      title: '',
      type: 'error',
    }, 7000)
  } finally {
    isRecoveryLoading.value = false
  }
}

const signInWith = async (provider: 'google' | 'facebook') => {
  await socialSignIn(provider, socialRedirect() ? { redirect_uri: socialRedirect() } : undefined)
}
</script>

<template>
  <div class="page-base auth-page">
    <div class="container kratom-page-container">
      <div class="auth-shell">
        <section class="auth-hero">
          <div class="auth-eyebrow">{{ t('auth.eyebrow') }}</div>
          <h1 class="auth-title">{{ t('auth.login_title') }}</h1>
          <p class="auth-text">{{ t('auth.login_text') }}</p>

          <div v-if="redirectTarget.includes('/checkout')" class="auth-note">
            <strong>{{ t('title.checkout') }}.</strong>
            {{ t('auth.redirect_after_login') }}
          </div>

          <div class="auth-benefits">
            <article v-for="item in benefitItems" :key="item.title" class="auth-benefit">
              <div class="auth-benefit__icon">
                <IconCSS :name="item.icon" size="22" />
              </div>
              <div>
                <div class="auth-benefit__title">{{ item.title }}</div>
                <div class="auth-benefit__text">{{ item.text }}</div>
              </div>
            </article>
          </div>
        </section>

        <section class="auth-card">
          <div class="auth-card__header">
            <h2 class="auth-card__title">{{ t('auth.login_title') }}</h2>
            <p class="auth-card__text">{{ t('auth.guest_text') }}</p>
          </div>

          <div class="auth-social">
            <button type="button" class="button auth-social__button auth-social__button--google" @click="signInWith('google')">
              <IconCSS name="iconoir:google" size="20" />
              <span>{{ t('auth.enter_with') }} Google</span>
            </button>
            <button type="button" class="button auth-social__button auth-social__button--facebook" @click="signInWith('facebook')">
              <IconCSS name="iconoir:facebook" size="20" />
              <span>{{ t('auth.enter_with') }} Facebook</span>
            </button>
          </div>

          <div class="auth-separator">
            <span>Email</span>
          </div>

          <form class="auth-form" @submit.prevent="submit">
            <form-text v-model="form.email" :placeholder="t('form.enter.email')" :error="errors.email" />
            <form-text v-model="form.password" :placeholder="t('form.enter.password')" :error="errors.password" type="password" />

            <div class="auth-actions">
              <button type="submit" class="button primary" :class="{ loading: isLoading }">
                {{ t('button.enter') }}
              </button>
              <button type="button" class="auth-link-button" @click="showRecovery = !showRecovery">
                {{ t('auth.forgot_toggle') }}
              </button>
            </div>
          </form>

          <div v-if="showRecovery" class="auth-state">
            <div>
              <strong>{{ t('auth.forgot_toggle') }}</strong>
              <div>{{ t('auth.forgot_text') }}</div>
            </div>

            <form-text v-model="recovery.email" :placeholder="t('form.enter.email')" :error="errors.recovery" />

            <button type="button" class="button secondary" :class="{ loading: isRecoveryLoading }" @click="recoverPassword">
              {{ t('auth.recovery_submit') }}
            </button>
          </div>

          <div class="auth-footer">
            <div class="auth-footer__row">
              <span>{{ t('auth.remember_text') }}</span>
              <NuxtLink :to="{ path: regionPath('/auth/register'), query: authLinkQuery }" class="auth-text-button">
                {{ t('auth.go_register') }}
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
