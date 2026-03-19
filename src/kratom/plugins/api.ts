import {useReferralBridge} from '~/modules/auth-bridge/runtime/composables/useReferralBridge'
import {useRegion} from '~/composables/useRegion'

let logoutInProgress = false

export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig()
  const token = useCookie<string | null>('auth_token', {
    sameSite: 'lax',
    secure: process.env.NODE_ENV === 'production',
    path: '/',
  })
  const referral = useReferralBridge()

  const i18n = (nuxtApp as any).$i18n;
  const regionStore = useRegion();

  const locale = i18n?.locale;
  const region = regionStore.regionAlias;  


  const $api = $fetch.create({
    baseURL: config.public.apiBase,
    credentials: 'include',
    onRequest({ options }) {
      // Заголовки из конкретного вызова + то, что уже поставил ofetch
      const h = new Headers(options.headers || {})

      const base: Record<string, string> = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(locale?.value ? { 'Accept-Language': locale.value } : {}),
        ...(region?.value ? { 'X-Region': region.value } : {}),
      }

      if (token.value) base.Authorization = `Bearer ${token.value}`

      const xsrf = useCookie('XSRF-TOKEN').value
      if (xsrf) base['X-XSRF-TOKEN'] = xsrf

      const referralCode = referral.code.value
      if (referralCode) base['X-Referral-Code'] = referralCode

      for (const [k, v] of Object.entries(base)) {
        if (!h.has(k)) h.set(k, v)
      }

      options.headers = h
    },
    onResponseError({ response }) {
      if (response.status === 401 && token.value && !logoutInProgress) {
        const { logout } = useAuth()
        logoutInProgress = true
        logout() // централизованный выход
          .finally(() => {
            logoutInProgress = false
          })
      }
    },
  })

  return { provide: { api: $api } }
})
