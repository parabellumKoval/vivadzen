<script setup lang="ts">
const { t } = useI18n()
const route = useRoute()
const regionPath = useToLocalePath()
const { register, socialSignIn, init } = useAuth()
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

const form = reactive({
  email: '',
  password: '',
  password_confirmation: '',
  agree: false,
})

const errors = reactive<Record<string, string | null>>({
  email: null,
  password: null,
  password_confirmation: null,
})

const isLoading = ref(false)

const authLinkQuery = computed(() => {
  const query: Record<string, string> = {}

  if (typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/')) {
    query.redirect = route.query.redirect
  }

  if (form.email) {
    query.email = form.email
  }

  return Object.keys(query).length ? query : undefined
})

const benefitItems = computed(() => [
  {
    icon: 'ph:user-circle-plus',
    title: t('auth.benefits.profile_title'),
    text: t('auth.benefits.profile_text'),
  },
  {
    icon: 'ph:map-pin-line',
    title: t('auth.benefits.addresses_title'),
    text: t('auth.benefits.addresses_text'),
  },
  {
    icon: 'ph:clock-counter-clockwise',
    title: t('auth.benefits.orders_title'),
    text: t('auth.benefits.orders_text'),
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

  if (form.password !== form.password_confirmation) {
    errors.password_confirmation = t('error.auth.password.confirmation')
    return
  }

  if (!form.agree) {
    noty.setNoty({
      content: t('noty.auth.register.privacy'),
      title: '',
      type: 'error',
    }, 7000)
    return
  }

  isLoading.value = true

  try {
    const result = await register({
      email: form.email,
      password: form.password,
    })

    if (result?.requiresEmailVerification) {
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
      content: t('noty.auth.register.success'),
      title: '',
      type: 'success',
    }, 7000)

    await navigateTo(redirectTarget.value)
  } catch (error: any) {
    const data = error?.data || {}
    applyValidationErrors(data?.errors)

    noty.setNoty({
      content: String(data?.message || t('noty.auth.register.fail')),
      title: '',
      type: 'error',
    }, 7000)
  } finally {
    isLoading.value = false
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
          <h1 class="auth-title">{{ t('auth.register_title') }}</h1>
          <p class="auth-text">{{ t('auth.register_text') }}</p>

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
            <h2 class="auth-card__title">{{ t('auth.register_title') }}</h2>
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
            <form-text
              v-model="form.password_confirmation"
              :placeholder="t('form.enter.password_confirmation')"
              :error="errors.password_confirmation"
              type="password"
            />

            <label class="auth-checkbox">
              <input v-model="form.agree" type="checkbox">
              <span class="auth-checkbox__control" aria-hidden="true">
                <IconCSS v-if="form.agree" name="ph:check-bold" size="12" />
              </span>
              <span class="auth-checkbox__text">
                {{ t('auth.agree_prefix') }}
                <NuxtLink :to="regionPath('/policy')" class="auth-text-button">{{ t('auth.agree_link') }}</NuxtLink>
              </span>
            </label>

            <button type="submit" class="button primary" :class="{ loading: isLoading }">
              {{ t('button.register') }}
            </button>
          </form>

          <div class="auth-footer">
            <div class="auth-footer__row">
              <span>{{ t('auth.have_account') }}</span>
              <NuxtLink :to="{ path: regionPath('/auth/login'), query: authLinkQuery }" class="auth-text-button">
                {{ t('auth.go_login') }}
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
