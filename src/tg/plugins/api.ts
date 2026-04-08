const normalizeParam = (value: unknown) => {
  if (Array.isArray(value)) return String(value[0] || '').trim().toLowerCase()
  return String(value || '').trim().toLowerCase()
}

export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig()
  const storefrontCode = String(config.public.storefrontCode || 'main').trim()

  const api = $fetch.create({
    baseURL: config.public.apiBase,
    credentials: 'include',
    onRequest({ options }) {
      const route = (nuxtApp as any).$router?.currentRoute?.value
      const tgConfig = config.public.tg as any
      const region = normalizeParam(route?.params?.region) || tgConfig?.fallbackRegion || 'cz'
      const locale = normalizeParam(route?.params?.locale) || tgConfig?.regions?.[region]?.locale || 'cs'
      const regionAlias = tgConfig?.regionAliases?.[region] || region
      const headers = new Headers(options.headers || {})

      const baseHeaders: Record<string, string> = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-Storefront': storefrontCode,
        'Accept-Language': locale,
        'X-Region': regionAlias
      }

      const telegram = process.client ? (window as any).Telegram?.WebApp : null
      if (telegram?.initData) {
        baseHeaders['X-Telegram-Init-Data'] = telegram.initData
      }

      for (const [key, value] of Object.entries(baseHeaders)) {
        if (value && !headers.has(key)) {
          headers.set(key, value)
        }
      }

      options.headers = headers
    }
  })

  return {
    provide: {
      api
    }
  }
})
