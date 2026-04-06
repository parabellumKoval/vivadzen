import { readFile, writeFile } from 'node:fs/promises'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const projectRoot = path.resolve(__dirname, '..')
const repoRoot = path.resolve(projectRoot, '..', '..')

const API_BASE = (process.env.REDIRECTS_API_BASE || 'https://api.vivadzen.com/api').replace(/\/+$/, '')
const SHOP_ORIGIN = 'https://shop.vivadzen.com'
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

const LEGACY_STATIC_PATHS = [
  '/',
  '/about',
  '/affiliate',
  '/blog',
  '/brands',
  '/catalog',
  '/certificates',
  '/comparison',
  '/contacts',
  '/delivery',
  '/faq',
  '/guarantees',
  '/payment',
  '/policy',
  '/remove-userdata',
  '/returns',
  '/reviews',
  '/reviews/products',
  '/reviews/shop',
  '/search',
  '/terms',
  '/vivapoints',
]

const KRATOM_LOCALES = ['cs', 'en', 'ru', 'uk']
const KRATOM_DEFAULT_LOCALE = 'cs'
const KRATOM_REGION = 'cz'
const KRATOM_STATIC_PATHS = [
  '/',
  '/about',
  '/blog',
  '/catalog',
  '/contacts',
  '/policy',
  '/returns',
  '/reviews',
  '/terms',
  '/checkout',
  '/checkout/payment',
]

const LIVE_KRATOM_ITEM_TYPES = new Set(['article', 'product', 'category'])

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

function buildLegacyLocalizedPath(slug, region, locale) {
  const normalizedRegion = normalizeRegion(region)
  const normalizedLocale = String(locale || '').trim().toLowerCase()
  const normalizedSlug = normalizeSlug(slug)
  const defaultLocale = LEGACY_REGIONS[normalizedRegion]?.[0] || 'en'
  const segments = []

  if (normalizedRegion && normalizedRegion !== 'global') {
    segments.push(normalizedRegion)
    if (normalizedLocale && normalizedLocale !== defaultLocale) {
      segments.push(normalizedLocale)
    }
  } else if (normalizedLocale && normalizedLocale !== defaultLocale) {
    segments.push('global', normalizedLocale)
  }

  if (normalizedSlug) {
    segments.push(normalizedSlug)
  }

  return segments.length ? `/${segments.join('/')}` : '/'
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

function buildPathVariants(pathname) {
  const normalized = normalizePath(pathname)
  return normalized === '/' ? ['/'] : [normalized, `${normalized}/`]
}

function buildAbsoluteShopUrl(pathname) {
  return new URL(normalizePath(pathname), `${SHOP_ORIGIN}/`).toString()
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

function addLegacyPathsToSet(set, slug, regions) {
  const normalizedSlug = normalizeSlug(slug)
  if (!normalizedSlug && slug !== '/') {
    return
  }

  for (const region of regions) {
    const normalizedRegion = normalizeRegion(region)
    const locales = LEGACY_REGIONS[normalizedRegion]
    if (!locales) {
      continue
    }

    for (const locale of locales) {
      set.add(buildLegacyLocalizedPath(normalizedSlug, normalizedRegion, locale))
    }
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

    redirects.push(parsed)
  }

  return redirects
}

async function fetchSitemapItems(storefront) {
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

function buildLegacyGeneratedRedirects(mainItems, liveCurrentPaths) {
  const legacyPaths = new Set()

  for (const staticPath of LEGACY_STATIC_PATHS) {
    addLegacyPathsToSet(legacyPaths, staticPath, Object.keys(LEGACY_REGIONS))
  }

  for (const item of mainItems) {
    const slug = normalizeSlug(item?.slug)
    if (!slug) {
      continue
    }

    const regions = Array.isArray(item?.available_regions) && item.available_regions.length
      ? item.available_regions
      : ['global']

    addLegacyPathsToSet(legacyPaths, slug, regions)
  }

  const generated = new Map()

  for (const legacyPath of legacyPaths) {
    const normalized = normalizePath(legacyPath)
    if (liveCurrentPaths.has(normalized)) {
      continue
    }

    generated.set(normalized, normalized)
  }

  return generated
}

function addRedirectRule(routeRules, sourcePath, destinationPath) {
  const normalizedSource = normalizePath(sourcePath)
  const target = destinationPath.startsWith('http://') || destinationPath.startsWith('https://')
    ? destinationPath
    : buildAbsoluteShopUrl(destinationPath)

  for (const variant of buildPathVariants(normalizedSource)) {
    routeRules[variant] = {
      redirect: {
        to: target,
        statusCode: 301,
      },
    }
  }
}

function serializeRouteRules(routeRules, metadata) {
  const orderedEntries = Object.entries(routeRules).sort(([left], [right]) => left.localeCompare(right))
  const body = orderedEntries
    .map(([source, rule]) => `  ${JSON.stringify(source)}: ${JSON.stringify(rule)}`)
    .join(',\n')

  return `// Generated by scripts/generate-shop-redirects.mjs on ${metadata.generatedAt}
// Legacy routes: ${metadata.legacyCount}
// Live kratom routes excluded: ${metadata.liveCount}
// Manual CSV redirects applied: ${metadata.manualCount}

export const GENERATED_SHOP_REDIRECT_ROUTE_RULES = {
${body}
} as const
`
}

async function main() {
  const [mainItems, kratomItems, manualRedirects] = await Promise.all([
    fetchSitemapItems('main'),
    fetchSitemapItems('kratom'),
    readManualRedirects(),
  ])

  const liveCurrentPaths = buildLiveCurrentPaths(kratomItems)
  const generatedRedirects = buildLegacyGeneratedRedirects(mainItems, liveCurrentPaths)
  const routeRules = {}

  for (const [source, destination] of generatedRedirects.entries()) {
    addRedirectRule(routeRules, source, destination)
  }

  for (const { source, destination } of manualRedirects) {
    const normalizedSource = normalizePath(source)
    if (liveCurrentPaths.has(normalizedSource)) {
      continue
    }

    addRedirectRule(routeRules, normalizedSource, destination)
  }

  const output = serializeRouteRules(routeRules, {
    generatedAt: new Date().toISOString(),
    legacyCount: generatedRedirects.size,
    liveCount: liveCurrentPaths.size,
    manualCount: manualRedirects.length,
  })

  await writeFile(OUTPUT_FILE, output, 'utf8')

  console.log(`Wrote ${Object.keys(routeRules).length} redirect route rules to ${OUTPUT_FILE}`)
}

main().catch((error) => {
  console.error(error)
  process.exitCode = 1
})
