import { defineNuxtModule, addImports, addPlugin, createResolver } from '@nuxt/kit'

export interface AuthBridgeOptions {
  tokenCookieName?: string
  endpoints?: {
    me?: string
    login?: string
    logout?: string
    register?: string
    forgot?: string
    reset?: string
    resendLoggedIn?: string
    resendByEmail?: string
    changePassword?: string
    profileUpdate?: string
    profileEmailChange?: string
    socialUrl?: string
    validateReferralCode?: string
  }
  order?: { orderableType?: string }
  heartbeat?: { enabled?: boolean; intervalMs?: number }
  referrals?: {
    queryParam?: string
    ttlDays?: number
    cookieName?: string
  }
}

export default defineNuxtModule<AuthBridgeOptions>({
  meta: { name: 'auth-bridge', configKey: 'authBridge' },
  defaults: {
    tokenCookieName: 'auth_token',
    endpoints: {
      me: '/api/auth/me',
      login: '/api/auth/login',
      logout: '/api/auth/logout',
      register: '/api/auth/register',
      forgot: '/api/auth/password/forgot',
      reset: '/api/auth/password/reset',
      resendLoggedIn: '/api/auth/email/verification-notification',
      resendByEmail: '/api/auth/email/resend',
      changePassword: '/api/auth/password/change',
      profileUpdate: '/api/profile/update',
      profileEmailChange: '/api/profile/email-change',
      socialUrl: '/api/auth/oauth/:provider/url',
      validateReferralCode: '/api/auth/referral/validate/:code',
    },
    order: { orderableType: 'profile_user' },
    heartbeat: { enabled: true, intervalMs: 5 * 60 * 1000 },
    referrals: {
      queryParam: 'ref',
      ttlDays: 30,
      cookieName: 'referral_code',
    },
  },
  setup(opts, nuxt) {
    const { resolve } = createResolver(import.meta.url)

    // прокинем опции в runtimeConfig
    nuxt.options.runtimeConfig.public.authBridge = opts

    // авто-импорт composable
    addImports({ name: 'useAuth', as: 'useAuth', from: resolve('./runtime/composables/useAuth') })
    addImports({
      name: 'useReferralBridge',
      as: 'useReferralBridge',
      from: resolve('./runtime/composables/useReferralBridge'),
    })

    // плагин, который регистрирует route-middlewares programmatically
    addPlugin({ src: resolve('./runtime/plugins/query-handlers'), mode: 'all' })
    addPlugin({ src: resolve('./runtime/plugins/register-middlewares'), mode: 'all' })

    // heartbeat по фокусу/интервалу (клиент)
    if (opts.heartbeat?.enabled !== false) {
      addPlugin({ src: resolve('./runtime/plugins/heartbeat.client'), mode: 'client' })
    }
  }
})
