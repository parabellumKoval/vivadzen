import { readFile, writeFile } from 'node:fs/promises'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const projectRoot = path.resolve(__dirname, '..')
const repoRoot = path.resolve(projectRoot, '..', '..')

const API_BASE = (process.env.REDIRECTS_API_BASE || 'https://api.vivadzen.com/api').replace(/\/+$/, '')
const OUTPUT_FILE = path.join(projectRoot, 'config', 'generatedShopRedirects.ts')
const FRONT_REDIRECTS_CSV = path.join(repoRoot, 'src', 'front', 'redirects.csv')

const LEGACY_REGIONS = {
  global: ['en', 'de', 'es', 'ru', 'uk', 'cs'],
  ua: ['uk', 'ru'],
  cz: ['cs', 'en', 'ru', 'uk'],
  de: ['de', 'en', 'ru', 'uk'],
  es: ['es', 'en', 'ru', 'uk'],
}

const LEGACY_REGION_ALIASES = {
  zz: 'global',
}

const KRATOM_LOCALES = ['cs', 'en', 'ru', 'uk']
const KRATOM_DEFAULT_LOCALE = 'cs'
const KRATOM_REGION = 'cz'
const KRATOM_CATEGORY_SLUG = 'kratom'
const KRATOM_CATALOG_PER_PAGE = 48
const KRATOM_STATIC_PATHS = [
  '/',
  '/about',
  '/blog',
  '/catalog',
  '/contacts',
  '/delivery',
  '/payment',
  '/policy',
  '/returns',
  '/reviews',
  '/terms',
  '/checkout',
  '/checkout/payment',
]

const LIVE_KRATOM_ITEM_TYPES = new Set(['article', 'product'])

function normalizePath(value) {
  const raw = String(value || '').trim()
  if (!raw || raw === '/') {
    return '/'
  }

  const withoutQuery = raw.split('?')[0] || '/'
  const withLeadingSlash = withoutQuery.startsWith('/') ? withoutQuery : `/${withoutQuery}`
  const normalized = withLeadingSlash.replace(/\/{2,}/g, '/').replace(/\/+$/, '')
  return normalized || '/'
}

function normalizeSlug(value) {
  return String(value || '').trim().replace(/^\/+|\/+$/g, '')
}

function normalizeRegion(value) {
  const normalized = String(value || '').trim().toLowerCase()
  return LEGACY_REGION_ALIASES[normalized] || normalized
}

function buildKratomLocalizedPath(slug, locale) {
  const normalizedSlug = normalizeSlug(slug)
  const normalizedLocale = String(locale || '').trim().toLowerCase()
  const segments = []

  if (normalizedLocale && normalizedLocale !== KRATOM_DEFAULT_LOCALE) {
    segments.push(normalizedLocale)
  }

  if (normalizedSlug) {
    segments.push(normalizedSlug)
  }

  return segments.length ? `/${segments.join('/')}` : '/'
}

function isAvailableInRegion(item, region) {
  const targetRegion = normalizeRegion(region)
  const available = Array.isArray(item?.available_regions)
    ? item.available_regions
    : Array.isArray(item?.availableRegions)
      ? item.availableRegions
      : []

  if (!available.length) {
    return true
  }

  const normalized = new Set(available.map((entry) => normalizeRegion(entry)).filter(Boolean))
  return normalized.has(targetRegion) || normalized.has('global')
}

function addPathsToSet(set, pathFactory, slug) {
  for (const locale of KRATOM_LOCALES) {
    set.add(pathFactory(slug, locale))
  }
}

function parseCsvLine(line) {
  const firstComma = line.indexOf(',')
  const secondComma = line.indexOf(',', firstComma + 1)

  if (firstComma === -1 || secondComma === -1) {
    return null
  }

  return {
    source: line.slice(0, firstComma).trim(),
    destination: line.slice(firstComma + 1, secondComma).trim(),
  }
}

async function readManualRedirects() {
  const raw = await readFile(FRONT_REDIRECTS_CSV, 'utf8')
  const lines = raw.split(/\r?\n/).slice(1)
  const redirects = []

  for (const line of lines) {
    const trimmed = line.trim()
    if (!trimmed) {
      continue
    }

    const parsed = parseCsvLine(trimmed)
    if (!parsed) {
      continue
    }

    if (!parsed.source || !parsed.destination || parsed.source.includes('?')) {
      continue
    }

    redirects.push({
      source: normalizePath(parsed.source),
      destination: normalizePath(parsed.destination),
    })
  }

  return redirects
}

async function fetchSitemapItems(storefront) {
  const fileOverride = {
    main: process.env.REDIRECTS_MAIN_SITEMAP_FILE,
    kratom: process.env.REDIRECTS_KRATOM_SITEMAP_FILE,
  }[storefront]

  if (fileOverride) {
    const payload = JSON.parse(await readFile(fileOverride, 'utf8'))
    return Array.isArray(payload?.items) ? payload.items : []
  }

  const url = `${API_BASE}/sitemap/full?storefront=${encodeURIComponent(storefront)}&country=cz`
  const response = await fetch(url, {
    headers: {
      'X-Storefront': storefront,
      'X-Region': 'cz',
      'Accept-Language': 'cs',
    },
  })

  if (!response.ok) {
    throw new Error(`Failed to fetch ${storefront} sitemap payload: ${response.status} ${response.statusText}`)
  }

  const payload = await response.json()
  return Array.isArray(payload?.items) ? payload.items : []
}

function extractCatalogProducts(payload) {
  if (Array.isArray(payload)) {
    return payload
  }

  if (Array.isArray(payload?.products?.data)) {
    return payload.products.data
  }

  return []
}

async function fetchKratomCatalogProducts() {
  const fileOverride = process.env.REDIRECTS_KRATOM_CATALOG_FILE

  if (fileOverride) {
    const payload = JSON.parse(await readFile(fileOverride, 'utf8'))
    return extractCatalogProducts(payload)
  }

  const products = []
  let page = 1
  let lastPage = 1

  do {
    const params = new URLSearchParams({
      with_products: 'true',
      category_slug: KRATOM_CATEGORY_SLUG,
      per_page: String(KRATOM_CATALOG_PER_PAGE),
      page: String(page),
      resource: 'kratom_small',
    })
    const response = await fetch(`${API_BASE}/catalog?${params.toString()}`, {
      headers: {
        'X-Storefront': 'kratom',
        'X-Region': KRATOM_REGION,
        'Accept-Language': KRATOM_DEFAULT_LOCALE,
      },
    })

    if (!response.ok) {
      throw new Error(`Failed to fetch kratom catalog products: ${response.status} ${response.statusText}`)
    }

    const payload = await response.json()
    products.push(...extractCatalogProducts(payload))
    lastPage = Math.max(1, Number(payload?.products?.meta?.last_page || 1))
    page += 1
  } while (page <= lastPage)

  return products
}

function buildLiveCurrentPaths(kratomItems) {
  const livePaths = new Set()

  for (const staticPath of KRATOM_STATIC_PATHS) {
    addPathsToSet(livePaths, buildKratomLocalizedPath, staticPath)
  }

  for (const item of kratomItems) {
    if (!LIVE_KRATOM_ITEM_TYPES.has(String(item?.type || ''))) {
      continue
    }

    if (!isAvailableInRegion(item, KRATOM_REGION)) {
      continue
    }

    const slug = normalizeSlug(item?.slug)
    if (!slug) {
      continue
    }

    addPathsToSet(livePaths, buildKratomLocalizedPath, slug)
  }

  return livePaths
}

function addCatalogProductSlug(slugs, product) {
  const slug = normalizeSlug(product?.slug)
  if (slug) {
    slugs.add(slug)
  }

  if (!Array.isArray(product?.modifications)) {
    return
  }

  for (const modification of product.modifications) {
    const modificationSlug = normalizeSlug(modification?.slug)
    if (modificationSlug) {
      slugs.add(modificationSlug)
    }
  }
}

function buildLiveKratomProductSlugs(catalogProducts) {
  const slugs = new Set()

  for (const product of catalogProducts) {
    addCatalogProductSlug(slugs, product)
  }

  return slugs
}

function serializeStringArray(name, values) {
  const body = values.map((value) => `  ${JSON.stringify(value)}`).join(',\n')
  return `export const ${name} = [\n${body}\n] as const\n`
}

function serializeObject(name, entries) {
  const body = entries
    .map(([key, value]) => `  ${JSON.stringify(key)}: ${JSON.stringify(value)}`)
    .join(',\n')

  return `export const ${name} = {\n${body}\n} as const\n`
}

async function main() {
  const [kratomItems, catalogProducts, manualRedirects] = await Promise.all([
    fetchSitemapItems('kratom'),
    fetchKratomCatalogProducts(),
    readManualRedirects(),
  ])

  const liveProductSlugs = Array.from(buildLiveKratomProductSlugs(catalogProducts)).sort()
  const liveCurrentPathSet = buildLiveCurrentPaths(kratomItems)
  for (const slug of liveProductSlugs) {
    addPathsToSet(liveCurrentPathSet, buildKratomLocalizedPath, slug)
  }

  const liveCurrentPaths = Array.from(liveCurrentPathSet).sort()
  const manualRedirectMap = manualRedirects
    .filter(({ source }) => !liveCurrentPathSet.has(source))
    .sort((left, right) => left.source.localeCompare(right.source))
    .map(({ source, destination }) => [source, destination])

  const file = `// Generated by scripts/generate-shop-redirects.mjs on ${new Date().toISOString()}
// Live kratom routes: ${liveCurrentPaths.length}
// Live kratom products: ${liveProductSlugs.length}
// Manual CSV redirects: ${manualRedirectMap.length}
// Region/path pattern redirects are handled in server/middleware/legacy-shop-redirect.ts.

${serializeStringArray('GENERATED_KRATOM_LIVE_PATHS', liveCurrentPaths)}

${serializeStringArray('GENERATED_KRATOM_PRODUCT_SLUGS', liveProductSlugs)}

${serializeObject('GENERATED_MANUAL_REDIRECTS', manualRedirectMap)}

export const GENERATED_LEGACY_REGIONS = ${JSON.stringify(LEGACY_REGIONS)} as const
`

  await writeFile(OUTPUT_FILE, file, 'utf8')
  console.log(`Wrote redirect manifest to ${OUTPUT_FILE}`)
}

main().catch((error) => {
  console.error(error)
  process.exitCode = 1
})
