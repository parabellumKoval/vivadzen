import { defineNuxtPlugin, addRouteMiddleware, navigateTo } from '#app'
import { nextTick } from 'vue'

type MaybeArray<T> = T | T[] | undefined

function takeFirst(value: MaybeArray<string>): string | null {
  if (Array.isArray(value)) return value[0] ?? null
  if (typeof value === 'string') return value
  return null
}

export default defineNuxtPlugin((nuxtApp) => {
  const runtime = useRuntimeConfig()
  const authConfig = runtime.public?.authBridge || {}
  const endpoints = authConfig.endpoints || {}
  const referralDefaults = authConfig.referrals || {}

  const getSetting = <T = any>(key: string, fallback: T): T => {
    const getter = (nuxtApp as any)?.$getSetting
    if (typeof getter === 'function') {
      const resolved = getter(key, fallback)
      return (typeof resolved === 'undefined' || resolved === null) ? fallback : resolved
    }
    return fallback
  }

  const resolveReferralParam = () => {
    const fallback = typeof referralDefaults.queryParam === 'string' && referralDefaults.queryParam.trim()
      ? referralDefaults.queryParam.trim()
      : 'ref'
    const candidate = getSetting<string>('profile.referrals.url_param', fallback)
    if (typeof candidate === 'string' && candidate.trim()) {
      return candidate.trim()
    }
    return fallback
  }

  const resolveReferralTtl = () => {
    const fallback = typeof referralDefaults.ttlDays === 'number' && Number.isFinite(referralDefaults.ttlDays)
      ? referralDefaults.ttlDays
      : 30
    const candidate = Number(getSetting('profile.referrals.link_ttl_days', fallback))
    if (Number.isFinite(candidate) && candidate > 0) {
      return candidate
    }
    return fallback
  }

  const normalizeBool = (value: any): boolean => {
    if (typeof value === 'boolean') return value
    if (typeof value === 'string') {
      if (value.toLowerCase() === 'true') return true
      if (value.toLowerCase() === 'false') return false
    }
    if (value && typeof value === 'object') {
      if ('valid' in value) return normalizeBool((value as any).valid)
      if ('data' in value) return normalizeBool((value as any).data)
    }
    return Boolean(value)
  }

  const callValidateReferral = async (code: string) => {
    const template = endpoints.validateReferralCode
    if (!template) return false
    const url = template.replace(':code', encodeURIComponent(code))
    try {
      const fetcher = (nuxtApp as any)?.$api
      if (typeof fetcher === 'function') {
        const response = await fetcher(url, { method: 'GET' })
        return normalizeBool(response)
      }
    } catch {
      // ignore and fallback to raw fetch
    }
    try {
      const base = (runtime.public as any)?.apiBase || ''
      const response = await $fetch(url.startsWith('http') ? url : base + url, {
        method: 'GET',
        credentials: 'include',
      })
      return normalizeBool(response)
    } catch {
      return false
    }
  }

  addRouteMiddleware(
    'auth-bridge-query-handlers',
    async (to) => {
      const queryParamToken = 'token'
      const nextQuery: Record<string, any> = { ...to.query }
      let shouldRedirect = false

      const tokenValue = takeFirst(to.query?.[queryParamToken])

      if (tokenValue) {
        const { finishWithToken } = useAuth()
        await finishWithToken(tokenValue)
        delete nextQuery[queryParamToken]
        shouldRedirect = true
      }

      const referralParam = resolveReferralParam()
      const referralValueRaw = takeFirst((to.query as Record<string, MaybeArray<string>>)[referralParam])

      if (referralValueRaw) {
        const referralCode = referralValueRaw.trim()
        if (referralCode) {
          const referral = useReferralBridge()
          const hasActive = referral.isActive.value && !referral.isExpired()

          if (!hasActive) {
            const isValid = await callValidateReferral(referralCode)
            if (isValid) {
              const ttlDays = resolveReferralTtl()
              referral.setReferral(referralCode, ttlDays)
            }
          }
        }
        
        delete nextQuery[referralParam]
        shouldRedirect = true
      }

      const verifiedValueRaw = takeFirst((to.query as Record<string, MaybeArray<string>>).verified)
      const isVerificationSuccess = verifiedValueRaw === '1'

      if (isVerificationSuccess) {
        const auth = useAuth()

        try {
          await auth.me(true)
        } catch {
          // ignore errors while trying to refresh the session
        }

        if (process.client) {
          const i18n = (nuxtApp as any)?.$i18n
          const translated =
            typeof i18n?.t === 'function'
              ? i18n.t('noty.auth.email.changed.success')
              : 'Email successfully confirmed'

          useNoty().setNoty(
            { content: String(translated), type: 'success' },
          )
        }

        const verificationParams = ['verified', 'id', 'hash', 'expires', 'signature']
        for (const key of verificationParams) {
          if (key in nextQuery) {
            delete nextQuery[key]
          }
        }

        shouldRedirect = true
      }

      if (shouldRedirect) {
        await nextTick()
        const router = nuxtApp.$router
        const cleanedEntries = Object.entries(nextQuery).filter(([, value]) => value != null && value !== '')
        const cleanedQuery = cleanedEntries.length ? Object.fromEntries(cleanedEntries) : undefined
        
        return router.replace({
          path: to.path,
          query: cleanedQuery,
          hash: to.hash
        })
      }
    },
    { global: true },
  )
})
