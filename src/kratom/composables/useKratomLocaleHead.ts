const LANGUAGE_BY_LOCALE: Record<string, string> = {
  cs: 'cs-CZ',
  en: 'en-US',
  ru: 'ru-RU',
  uk: 'uk-UA',
}

const normalizeLocale = (value: string | null | undefined) => String(value || '').trim().toLowerCase()

const stripLocaleSegment = (path: string, locales: string[]) => {
  const cleanPath = String(path || '/').replace(/[?#].*$/, '') || '/'
  const segments = cleanPath.split('/').filter(Boolean)

  if (segments[0] && locales.includes(segments[0])) {
    const next = '/' + segments.slice(1).join('/')
    return next === '/' ? '/' : next.replace(/\/+$/, '') || '/'
  }

  return cleanPath === '/' ? '/' : cleanPath.replace(/\/+$/, '') || '/'
}

const buildLocalePath = (path: string, locale: string, defaultLocale: string) => {
  const normalizedPath = String(path || '/').replace(/^\/+/, '').replace(/\/+$/, '')
  const segments: string[] = []

  if (locale !== defaultLocale) {
    segments.push(locale)
  }

  if (normalizedPath) {
    segments.push(normalizedPath)
  }

  return segments.length ? `/${segments.join('/')}` : '/'
}

export const useKratomLocaleHead = () => {
  const route = useRoute()
  const runtimeConfig = useRuntimeConfig()
  const nuxtApp = tryUseNuxtApp()

  const configuredLocales = ((runtimeConfig.public.kratomStore?.locales || ['cs', 'en', 'ru', 'uk']) as string[])
    .map((code) => normalizeLocale(code))
    .filter(Boolean)

  const defaultLocale = normalizeLocale(runtimeConfig.public.kratomStore?.defaultLocale || 'cs') || 'cs'
  const baseUrl = String(runtimeConfig.public.site?.url || runtimeConfig.public.siteUrl || '').replace(/\/+$/, '')

  const locale = computed(() => {
    return normalizeLocale((nuxtApp as any)?.$i18n?.locale?.value || defaultLocale) || defaultLocale
  })

  const basePath = computed(() => stripLocaleSegment(route.path || '/', configuredLocales))
  const htmlAttrs = computed(() => ({
    lang: LANGUAGE_BY_LOCALE[locale.value] || locale.value,
    dir: 'ltr',
  }))

  const links = computed(() => {
    if (!baseUrl) {
      return []
    }

    const alternate = configuredLocales.map((code) => ({
      rel: 'alternate',
      hreflang: LANGUAGE_BY_LOCALE[code] || code,
      href: `${baseUrl}${buildLocalePath(basePath.value, code, defaultLocale)}`,
    }))

    return [
      {
        rel: 'canonical',
        href: `${baseUrl}${buildLocalePath(basePath.value, locale.value, defaultLocale)}`,
      },
      ...alternate,
      {
        rel: 'alternate',
        hreflang: 'x-default',
        href: `${baseUrl}${buildLocalePath(basePath.value, defaultLocale, defaultLocale)}`,
      },
    ]
  })

  useHead(() => ({
    htmlAttrs: htmlAttrs.value,
    link: links.value,
  }))

  return {
    htmlAttrs,
  }
}
