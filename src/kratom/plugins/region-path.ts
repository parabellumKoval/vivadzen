const normalize = (value: string | null | undefined) => String(value || '').trim().toLowerCase()

export default defineNuxtPlugin((nuxtApp) => {
  const runtimeConfig = useRuntimeConfig()
  const defaultLocale = normalize(runtimeConfig.public.kratomStore?.defaultLocale || 'cs')

  const regionPath = (path: string, opts: { absolute?: boolean } = {}) => {
    if (/^https?:\/\//i.test(path || '')) {
      return path
    }

    const locale = normalize((tryUseNuxtApp() as any)?.$i18n?.locale?.value || defaultLocale) || defaultLocale
    const baseUrl = runtimeConfig.public?.site?.url || runtimeConfig.public?.siteUrl || ''
    const normalizedPath = String(path || '/').replace(/^\/+/, '')
    const segments = [] as string[]

    if (locale !== defaultLocale) {
      segments.push(locale)
    }

    if (normalizedPath) {
      segments.push(normalizedPath)
    }

    const localized = segments.length ? `/${segments.join('/')}` : '/'
    if (opts.absolute) {
      return `${String(baseUrl).replace(/\/+$/, '')}${localized}`
    }

    return localized
  }

  nuxtApp.provide('regionPath', regionPath)
})
