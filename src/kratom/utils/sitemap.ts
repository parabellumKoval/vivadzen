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

type SitemapPayloadItem = {
  slug?: string | null
  lastmod?: string | null
  updated_at?: string | null
  updatedAt?: string | null
}

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

type GenerateSitemapEntriesOptions = {
  locale: string
  storefront: string
  country: string
  catalogEndpoint: string
  categorySlug: string
  productResource: string
  defaultLocale?: string
}

const fetchCatalogProducts = async ({
  catalogEndpoint,
  storefront,
  country,
  locale,
  categorySlug,
  productResource
}: {
  catalogEndpoint: string
  storefront: string
  country: string
  locale: string
  categorySlug: string
  productResource: string
}) => {
  const headers = {
    'X-Storefront': storefront,
    'X-Region': country,
    'Accept-Language': locale,
  }
  const baseQuery = {
    with_products: true,
    category_slug: categorySlug,
    per_page: 200,
    cache: true,
    resource: productResource,
  }
  const fetchPage = (page: number) => $fetch(catalogEndpoint, {
    headers,
    query: {
      ...baseQuery,
      page,
    },
  })

  const firstPage = await fetchPage(1).catch(() => null)
  const firstPageProducts = Array.isArray((firstPage as any)?.products?.data)
    ? (firstPage as any).products.data
    : []
  const lastPage = Number((firstPage as any)?.products?.meta?.last_page || 1)

  if (lastPage <= 1) {
    return firstPageProducts
  }

  const restPages = await Promise.all(
    Array.from({ length: lastPage - 1 }, (_, index) => fetchPage(index + 2).catch(() => null))
  )

  return [
    ...firstPageProducts,
    ...restPages.flatMap((page) => Array.isArray((page as any)?.products?.data) ? (page as any).products.data : [])
  ]
}

export const generateSitemapEntries = async ({
  locale,
  storefront,
  country,
  catalogEndpoint,
  categorySlug,
  productResource,
  defaultLocale = SITEMAP_DEFAULT_LOCALE
}: GenerateSitemapEntriesOptions) => {
  const normalizedLocale = normalizeLocale(locale) || defaultLocale
  const normalizedCountry = String(country || SITEMAP_COUNTRY).trim().toLowerCase()

  try {
    const products = await fetchCatalogProducts({
      catalogEndpoint,
      storefront,
      country: normalizedCountry,
      locale: normalizedLocale,
      categorySlug,
      productResource
    })

    const staticEntries = SITEMAP_STATIC_ROUTES.map((slug) => ({
      loc: buildLocalizedPath(slug, normalizedLocale, defaultLocale)
    }))

    const dynamicEntries = (products as SitemapPayloadItem[])
      .filter((item: SitemapPayloadItem) => normalizeSlug(item?.slug ?? ''))
      .map((item: SitemapPayloadItem) => ({
        loc: buildLocalizedPath(normalizeSlug(item?.slug ?? ''), normalizedLocale, defaultLocale),
        lastmod: item?.lastmod || item?.updated_at || item?.updatedAt || undefined
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
