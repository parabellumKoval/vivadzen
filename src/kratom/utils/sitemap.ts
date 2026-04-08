import { $fetch } from 'ofetch'

export const SITEMAP_LOCALES = ['cs', 'en', 'ru', 'uk'] as const
export const SITEMAP_DEFAULT_LOCALE = 'cs'
export const SITEMAP_COUNTRY = 'cz'

export const SITEMAP_STATIC_ROUTES = [
  '/',
  '/about',
  '/blog',
  '/catalog',
  '/contacts',
  '/policy',
  '/returns',
  '/reviews',
  '/terms'
]

export const normalizeLocale = (value?: string | null) => String(value || '').trim().toLowerCase()
export const normalizeSlug = (value?: string | null) => String(value || '').trim().replace(/^\/+|\/+$/g, '')

export const buildLocalizedPath = (slug: string, locale: string, defaultLocale = SITEMAP_DEFAULT_LOCALE) => {
  const normalizedLocale = normalizeLocale(locale)
  const normalizedDefaultLocale = normalizeLocale(defaultLocale)
  const base = normalizeSlug(slug)
  const segments: string[] = []

  if (normalizedLocale && normalizedLocale !== normalizedDefaultLocale) {
    segments.push(normalizedLocale)
  }

  if (base) {
    segments.push(base)
  }

  return segments.length ? `/${segments.join('/')}` : '/'
}

const isCountryAllowed = (itemRegions: any, targetCountry: string) => {
  const normalizedTarget = String(targetCountry || '').trim().toLowerCase()

  if (!Array.isArray(itemRegions) || !itemRegions.length) {
    return true
  }

  const normalized = new Set(
    itemRegions
      .map((value: any) => String(value || '').trim().toLowerCase())
      .filter(Boolean)
  )

  return normalized.has(normalizedTarget) || normalized.has('global')
}

type GenerateSitemapEntriesOptions = {
  locale: string
  storefront: string
  country: string
  dataEndpoint: string
  defaultLocale?: string
}

export const generateSitemapEntries = async ({
  locale,
  storefront,
  country,
  dataEndpoint,
  defaultLocale = SITEMAP_DEFAULT_LOCALE
}: GenerateSitemapEntriesOptions) => {
  const normalizedLocale = normalizeLocale(locale) || defaultLocale
  const normalizedCountry = String(country || SITEMAP_COUNTRY).trim().toLowerCase()

  try {
    const payload = await $fetch(dataEndpoint, {
      headers: {
        'X-Storefront': storefront,
        'X-Region': normalizedCountry,
        'Accept-Language': normalizedLocale || locale,
      },
      query: {
        storefront,
        country: normalizedCountry,
      },
    }).catch(() => ({ items: [] }))
    const items = Array.isArray((payload as any)?.items) ? (payload as any).items : []

    const staticEntries = SITEMAP_STATIC_ROUTES.map((slug) => ({
      loc: buildLocalizedPath(slug, normalizedLocale, defaultLocale)
    }))

    const dynamicEntries = items
      .filter((item) => isCountryAllowed(item?.available_regions ?? item?.availableRegions ?? [], normalizedCountry))
      .map((item) => ({
        loc: buildLocalizedPath(normalizeSlug(item?.slug ?? ''), normalizedLocale, defaultLocale),
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

export const buildSitemapsOptions = (locales: readonly string[] = SITEMAP_LOCALES) => {
  return locales
    .map((locale) => normalizeLocale(locale))
    .filter(Boolean)
    .reduce<Record<string, any>>((acc, locale) => {
      const fetchPath = `/api/sitemap/${locale}`

      acc[locale] = {
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
