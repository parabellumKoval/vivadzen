const REGIONS_META = {
  global: { name: 'Global', locale: 'cs', currency: 'CZK', flagClass: 'emojione:globe-showing-europe-africa' },
  cz: { name: 'Czech Republic', locale: 'cs', currency: 'CZK', flagClass: 'emojione:flag-for-czechia' },
} as const

const LOCALES = ['cs', 'en', 'ru', 'uk'] as const
const DEFAULT_LOCALE = 'cs'
const FIXED_REGION = 'cz'
const FALLBACK_REGION = 'global'

const normalize = (value: string | null | undefined) => String(value || '').trim().toLowerCase()
const resolveLocale = () => {
  const nuxtApp = tryUseNuxtApp()
  return normalize((nuxtApp as any)?.$i18n?.locale?.value || DEFAULT_LOCALE) || DEFAULT_LOCALE
}

const stripLocalePrefix = (path: string, locales: readonly string[]) => {
  const segments = path.split('/').filter(Boolean)
  if (segments.length && locales.includes(segments[0] as typeof LOCALES[number])) {
    return '/' + segments.slice(1).join('/')
  }
  return path || '/'
}

export const useRegion = () => {
  const region = useState<string>('kratom-region', () => FIXED_REGION)

  const currency = computed(() => REGIONS_META.cz.currency)
  const regionAlias = computed(() => FIXED_REGION)
  const regions = computed(() => [])
  const locales = computed(() => [...LOCALES])
  const localesByRegion = computed(() => ({
    [FIXED_REGION]: [...LOCALES],
    [FALLBACK_REGION]: [...LOCALES],
  }))
  const regionLocales = computed(() => [...LOCALES])
  const localeByRegion = computed(() => ({
    cs: FIXED_REGION,
    en: FIXED_REGION,
    ru: FIXED_REGION,
    uk: FIXED_REGION,
  }))
  const currencyByRegion = computed(() => ({
    cs: 'CZK',
    en: 'CZK',
    ru: 'CZK',
    uk: 'CZK',
  }))
  const regionsMeta = computed(() => REGIONS_META)
  const regionsMetaArray = computed(() => Object.entries(REGIONS_META).map(([code, meta]) => ({ code, ...meta })))

  const getLocalesForRegion = () => [...LOCALES]
  const getDefaultLocaleFor = () => DEFAULT_LOCALE

  const currentUrl = (_nextRegion: string | null, nextLocale: string | null = null) => {
    const activeLocale = normalize(nextLocale || resolveLocale()) || DEFAULT_LOCALE

    if (process.server) {
      return activeLocale === DEFAULT_LOCALE ? '/' : `/${activeLocale}`
    }

    const pathname = stripLocalePrefix(window.location.pathname || '/', LOCALES)
    const suffix = `${window.location.search || ''}${window.location.hash || ''}`
    const normalizedPath = pathname === '/' ? '' : pathname.replace(/^\/+/, '')
    const segments = [] as string[]

    if (activeLocale !== DEFAULT_LOCALE) {
      segments.push(activeLocale)
    }

    if (normalizedPath) {
      segments.push(normalizedPath)
    }

    const localized = segments.length ? `/${segments.join('/')}` : '/'
    return `${localized}${suffix}`
  }

  const setRegion = () => {
    region.value = FIXED_REGION
  }

  return {
    currentUrl,
    setRegion,
    region,
    regionAlias,
    currency,
    regions,
    locales,
    localesByRegion,
    regionLocales,
    getLocalesForRegion,
    getDefaultLocaleFor,
    localeByRegion,
    fallbackRegion: FALLBACK_REGION,
    fallbackCurrency: 'CZK',
    currencyByRegion,
    regionAliases: { cz: 'cz' },
    regionsMeta: REGIONS_META,
    regionsMetaArray: regionsMetaArray.value,
  }
}
