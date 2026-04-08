const normalizeParam = (value: unknown) => {
  if (Array.isArray(value)) return String(value[0] || '').trim().toLowerCase()
  return String(value || '').trim().toLowerCase()
}

export const useTgRouting = () => {
  const route = useRoute()
  const config = useRuntimeConfig()
  const tgConfig = config.public.tg as any
  const fallbackRegion = tgConfig?.fallbackRegion || 'cz'

  const region = computed(() => normalizeParam(route.params.region) || fallbackRegion)
  const localesByRegion = computed<Record<string, string[]>>(() => tgConfig?.localesByRegion || {})
  const regionConfig = computed<Record<string, any>>(() => tgConfig?.regions || {})

  const getLocalesForRegion = (regionCode: string) => {
    const normalized = normalizeParam(regionCode) || fallbackRegion
    return localesByRegion.value[normalized] || ['en']
  }

  const getDefaultLocaleFor = (regionCode: string) => {
    const normalized = normalizeParam(regionCode) || fallbackRegion
    return regionConfig.value[normalized]?.locale || getLocalesForRegion(normalized)[0] || 'en'
  }

  const locale = computed(() => {
    const requested = normalizeParam(route.params.locale)
    const allowed = getLocalesForRegion(region.value)
    if (requested && allowed.includes(requested)) return requested
    return getDefaultLocaleFor(region.value)
  })

  const regionAlias = computed(() => {
    return tgConfig?.regionAliases?.[region.value] || region.value
  })

  const currency = computed(() => regionConfig.value[region.value]?.currency || 'USD')

  const basePath = computed(() => `/${region.value}/${locale.value}`)

  const pathFor = (path = '') => {
    const cleanPath = String(path || '').replace(/^\/+/, '')
    return cleanPath ? `${basePath.value}/${cleanPath}` : basePath.value
  }

  const categoryPath = (slug?: string | null) => {
    return slug ? pathFor(slug) : pathFor()
  }

  const productPath = (slug?: string | null) => {
    return slug ? pathFor(`product/${slug}`) : pathFor()
  }

  const switchLocale = async (nextLocale: string) => {
    const allowed = getLocalesForRegion(region.value)
    if (!allowed.includes(nextLocale)) return

    const segments = route.path.split('/').filter(Boolean)
    if (!segments.length) {
      await navigateTo(`/${region.value}/${nextLocale}`)
      return
    }

    segments[0] = region.value
    segments[1] = nextLocale
    await navigateTo(`/${segments.join('/')}`)
  }

  return {
    region,
    regionAlias,
    locale,
    currency,
    locales: computed(() => getLocalesForRegion(region.value)),
    fallbackRegion,
    getLocalesForRegion,
    getDefaultLocaleFor,
    basePath,
    pathFor,
    categoryPath,
    productPath,
    switchLocale
  }
}
