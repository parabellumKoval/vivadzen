const REGIONS_META = {
  global: { name: 'Global', locale: 'cs', currency: 'CZK', flagClass: 'emojione:globe-showing-europe-africa' },
  cz: { name: 'Czech Republic', locale: 'cs', currency: 'CZK', flagClass: 'emojione:flag-for-czechia' },
  ua: { name: 'Ukraine', locale: 'uk', currency: 'UAH', flagClass: 'emojione:flag-for-ukraine' },
  de: { name: 'Germany', locale: 'en', currency: 'EUR', flagClass: 'emojione:flag-for-germany' },
  es: { name: 'Spain', locale: 'en', currency: 'EUR', flagClass: 'emojione:flag-for-spain' },
  at: { name: 'Austria', locale: 'en', currency: 'EUR', flagClass: 'emojione:flag-for-austria' },
  be: { name: 'Belgium', locale: 'en', currency: 'EUR', flagClass: 'emojione:flag-for-belgium' },
  bg: { name: 'Bulgaria', locale: 'en', currency: 'BGN', flagClass: 'emojione:flag-for-bulgaria' },
  ch: { name: 'Switzerland', locale: 'en', currency: 'CHF', flagClass: 'emojione:flag-for-switzerland' },
  cy: { name: 'Cyprus', locale: 'en', currency: 'EUR', flagClass: 'emojione:flag-for-cyprus' },
  dk: { name: 'Denmark', locale: 'en', currency: 'DKK', flagClass: 'emojione:flag-for-denmark' },
  ee: { name: 'Estonia', locale: 'en', currency: 'EUR', flagClass: 'emojione:flag-for-estonia' },
  fi: { name: 'Finland', locale: 'en', currency: 'EUR', flagClass: 'emojione:flag-for-finland' },
  fr: { name: 'France', locale: 'en', currency: 'EUR', flagClass: 'emojione:flag-for-france' },
  gb: { name: 'United Kingdom', locale: 'en', currency: 'GBP', flagClass: 'emojione:flag-for-united-kingdom' },
  gr: { name: 'Greece', locale: 'en', currency: 'EUR', flagClass: 'emojione:flag-for-greece' },
  hr: { name: 'Croatia', locale: 'en', currency: 'EUR', flagClass: 'emojione:flag-for-croatia' },
  hu: { name: 'Hungary', locale: 'en', currency: 'HUF', flagClass: 'emojione:flag-for-hungary' },
  ie: { name: 'Ireland', locale: 'en', currency: 'EUR', flagClass: 'emojione:flag-for-ireland' },
  it: { name: 'Italy', locale: 'en', currency: 'EUR', flagClass: 'emojione:flag-for-italy' },
  lt: { name: 'Lithuania', locale: 'en', currency: 'EUR', flagClass: 'emojione:flag-for-lithuania' },
  lu: { name: 'Luxembourg', locale: 'en', currency: 'EUR', flagClass: 'emojione:flag-for-luxembourg' },
  lv: { name: 'Latvia', locale: 'en', currency: 'EUR', flagClass: 'emojione:flag-for-latvia' },
  mt: { name: 'Malta', locale: 'en', currency: 'EUR', flagClass: 'emojione:flag-for-malta' },
  nl: { name: 'Netherlands', locale: 'en', currency: 'EUR', flagClass: 'emojione:flag-for-netherlands' },
  no: { name: 'Norway', locale: 'en', currency: 'NOK', flagClass: 'emojione:flag-for-norway' },
  pl: { name: 'Poland', locale: 'en', currency: 'PLN', flagClass: 'emojione:flag-for-poland' },
  pt: { name: 'Portugal', locale: 'en', currency: 'EUR', flagClass: 'emojione:flag-for-portugal' },
  ro: { name: 'Romania', locale: 'en', currency: 'RON', flagClass: 'emojione:flag-for-romania' },
  se: { name: 'Sweden', locale: 'en', currency: 'SEK', flagClass: 'emojione:flag-for-sweden' },
  si: { name: 'Slovenia', locale: 'en', currency: 'EUR', flagClass: 'emojione:flag-for-slovenia' },
  sk: { name: 'Slovakia', locale: 'en', currency: 'EUR', flagClass: 'emojione:flag-for-slovakia' },
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
