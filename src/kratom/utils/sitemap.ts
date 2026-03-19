import { $fetch } from 'ofetch'

type RegionsOptions = {
  regions: Record<string, { name: string; locale: string; currency: string; flagClass?: string }>
  regionAliases?: Record<string, string>
  localesByRegion: Record<string, string[]>
  fallbackRegion: string
  fallbackCurrency?: string
}

export const REGIONS_MODULE_OPTIONS: RegionsOptions = {
  regions: {
    global: { name: 'regions.global', locale: 'en', currency: 'USD', flagClass: 'emojione:globe-showing-europe-africa' },
    ua: { name: 'regions.ua', locale: 'uk', currency: 'UAH', flagClass: 'emojione:flag-for-ukraine' },
    cz: { name: 'regions.cz', locale: 'cs', currency: 'CZK', flagClass: 'emojione:flag-for-czechia' },
    de: { name: 'regions.de', locale: 'de', currency: 'EUR', flagClass: 'emojione:flag-for-germany' },
    es: { name: 'regions.es', locale: 'es', currency: 'EUR', flagClass: 'emojione:flag-for-spain' }
  },
  regionAliases: {
    global: 'zz'
  },
  localesByRegion: {
    global: ['en', 'de', 'es', 'ru', 'uk','cs'],
    ua: ['uk', 'ru'],
    cz: ['cs', 'en', 'ru', 'uk'],
    de: ['de', 'en', 'ru', 'uk'],
    es: ['es', 'en', 'ru', 'uk']
  },
  fallbackRegion: 'global',
  fallbackCurrency: 'USD'
}

export const SITEMAP_STATIC_ROUTES = [
  '/',
  '/about',
  '/affiliate',
  '/blog',
  '/brands',
  '/catalog',
  '/certificates',
  '/contacts',
  '/delivery',
  '/faq',
  '/guarantees',
  '/payment',
  '/policy',
  '/returns',
  '/terms',
  '/vivapoints',
  '/search',
  '/comparison',
  '/remove-userdata'
]

export const normalizeRegion = (value?: string | null) => String(value || '').trim().toLowerCase()
export const normalizeLocale = (value?: string | null) => String(value || '').trim().toLowerCase()
export const normalizeSlug = (value?: string | null) => String(value || '').trim().replace(/^\/+|\/+$/g, '')

export const invertAliases = (aliases: Record<string, string> = {}) => {
  const map: Record<string, string> = {}
  Object.entries(aliases || {}).forEach(([canonical, alias]) => {
    const normalizedAlias = normalizeRegion(alias)
    const normalizedCanonical = normalizeRegion(canonical)
    if (normalizedAlias && normalizedCanonical) {
      map[normalizedAlias] = normalizedCanonical
    }
  })
  return map
}

const getDefaultLocaleForRegion = (region: string, localesByRegion: Record<string, string[]>) => {
  const list = localesByRegion[region] || []
  return list.length ? normalizeLocale(list[0]) : 'en'
}

export const buildLocalizedPath = (
  slug: string,
  region: string,
  locale: string,
  fallbackRegion: string,
  localesByRegion: Record<string, string[]>
) => {
  const normalizedRegion = normalizeRegion(region)
  const normalizedFallback = normalizeRegion(fallbackRegion)
  const normalizedLocale = normalizeLocale(locale)
  const defaultLocale = getDefaultLocaleForRegion(normalizedRegion, localesByRegion)
  const base = normalizeSlug(slug)

  const segments: string[] = []

  if (normalizedRegion && normalizedRegion !== normalizedFallback) {
    segments.push(normalizedRegion)
    if (normalizedLocale && normalizedLocale !== defaultLocale) {
      segments.push(normalizedLocale)
    }
  } else if (normalizedRegion && normalizedLocale && normalizedLocale !== defaultLocale) {
    segments.push(normalizedRegion, normalizedLocale)
  }

  if (base) {
    segments.push(base)
  }

  return segments.length ? `/${segments.join('/')}` : '/'
}

const isRegionAllowed = (
  itemRegions: any,
  targetRegion: string,
  aliasToCanonical: Record<string, string>
) => {
  const normalizedTarget = normalizeRegion(targetRegion)
  if (!Array.isArray(itemRegions) || !itemRegions.length) {
    return true
  }

  const normalized = new Set(
    itemRegions
      .map((value: any) => normalizeRegion(value))
      .filter(Boolean)
  )

  if (normalized.has(normalizedTarget) || normalized.has('global')) {
    return true
  }

  const canonical = aliasToCanonical[normalizedTarget]
  if (canonical && normalized.has(canonical)) {
    return true
  }

  for (const [alias, canonicalRegion] of Object.entries(aliasToCanonical)) {
    if (canonicalRegion === normalizedTarget && normalized.has(alias)) {
      return true
    }
  }

  return false
}

type GenerateSitemapEntriesOptions = {
  region: string
  locale: string
  fallbackRegion: string
  localesByRegion: Record<string, string[]>
  aliasToCanonical: Record<string, string>
  dataEndpoint: string
}

export const generateSitemapEntries = async ({
  region,
  locale,
  fallbackRegion,
  localesByRegion,
  aliasToCanonical,
  dataEndpoint
}: GenerateSitemapEntriesOptions) => {
  const normalizedRegion = normalizeRegion(region) || normalizeRegion(fallbackRegion)
  const normalizedLocale = normalizeLocale(locale) || getDefaultLocaleForRegion(normalizedRegion, localesByRegion)

  try {
    const payload = await $fetch(dataEndpoint).catch(() => ({ items: [] }))
    const items = Array.isArray((payload as any)?.items) ? (payload as any).items : []

    const staticEntries = SITEMAP_STATIC_ROUTES.map((slug) => ({
      loc: buildLocalizedPath(slug, normalizedRegion, normalizedLocale, fallbackRegion, localesByRegion)
    }))

    const dynamicEntries = items
      .filter((item) => isRegionAllowed(item?.available_regions ?? item?.availableRegions ?? [], normalizedRegion, aliasToCanonical))
      .map((item) => ({
        loc: buildLocalizedPath(
          normalizeSlug(item?.slug ?? ''),
          normalizedRegion,
          normalizedLocale,
          fallbackRegion,
          localesByRegion
        ),
        lastmod: item?.lastmod || undefined
      }))

    const seen = new Set<string>()
    const merged: Array<{ loc: string; lastmod?: string }> = []

    for (const entry of [...staticEntries, ...dynamicEntries]) {
      const loc = entry.loc || '/'
      if (seen.has(loc)) continue
      seen.add(loc)
      merged.push(entry)
    }

    return Array.isArray(merged) ? merged : []
  } catch {
    return []
  }
}

export const buildSitemapsOptions = (regionsOptions: RegionsOptions) => {
  const localesByRegion = regionsOptions.localesByRegion || {}
  const internalEndpoint = (region: string, locale: string) => `/api/sitemap/${region}/${locale}`

  const pairs = Object.entries(localesByRegion)
    .flatMap(([region, locales]) => (locales || []).map((locale) => ({
      region: normalizeRegion(region),
      locale: normalizeLocale(locale)
    })))
    .filter((pair) => pair.region && pair.locale)

  return pairs.reduce<Record<string, any>>((acc, { region, locale }) => {
    const sitemapName = `${region}-${locale}`

    const fetchPath = internalEndpoint(region, locale)

    acc[sitemapName] = {
      defaults: {
        changefreq: 'daily',
        priority: 1,
        lastmod: new Date().toISOString()
      },
      sources: [fetchPath]
    }

    return acc
  }, {})
}
