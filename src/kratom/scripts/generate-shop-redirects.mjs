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

function parseLegacyPath(pathname) {
  const normalized = normalizePath(pathname)
  const segments = normalized === '/' ? [] : normalized.slice(1).split('/').filter(Boolean)

  if (!segments.length) {
    return null
  }

  const [rawRegion, maybeLocale, ...rest] = segments
  const region = normalizeRegion(rawRegion)

  if (!LEGACY_REGIONS[region]) {
    return null
  }

  const locales = LEGACY_REGIONS[region]
  if (maybeLocale && locales.includes(maybeLocale)) {
    return {
      region,
      locale: maybeLocale,
      remainder: rest.length ? `/${rest.join('/')}` : '/',
      hasExplicitLocale: true,
    }
  }

  return {
    region,
    locale: locales[0],
    remainder: maybeLocale ? `/${[maybeLocale, ...rest].join('/')}` : '/',
    hasExplicitLocale: false,
  }
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

function buildRegionOnlyRedirects(mainItems, liveCurrentPaths) {
  const redirects = new Map()

  const append = (fullPath) => {
    const parsed = parseLegacyPath(fullPath)
    if (!parsed || parsed.hasExplicitLocale) {
      return
    }

    redirects.set(normalizePath(fullPath), normalizePath(fullPath))
  }

  for (const region of Object.keys(LEGACY_REGIONS)) {
    for (const staticPath of LEGACY_STATIC_PATHS) {
      const fullPath = staticPath === '/' ? `/${region}` : `/${region}${staticPath}`
      append(fullPath)
    }
  }

  for (const item of mainItems) {
    const slug = normalizeSlug(item?.slug)
    if (!slug) {
      continue
    }

    const regions = Array.isArray(item?.available_regions) && item.available_regions.length
      ? item.available_regions
      : ['global']

    for (const region of regions) {
      const normalizedRegion = normalizeRegion(region)
      if (!LEGACY_REGIONS[normalizedRegion]) {
        continue
      }

      append(`/${normalizedRegion}/${slug}`)
    }
  }

  return redirects
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
  const [mainItems, kratomItems, manualRedirects] = await Promise.all([
    fetchSitemapItems('main'),
    fetchSitemapItems('kratom'),
    readManualRedirects(),
  ])

  const liveCurrentPaths = Array.from(buildLiveCurrentPaths(kratomItems)).sort()
  const regionOnlyRedirects = Array.from(buildRegionOnlyRedirects(mainItems, new Set(liveCurrentPaths)).entries())
    .sort(([left], [right]) => left.localeCompare(right))
  const manualRedirectMap = manualRedirects
    .filter(({ source }) => !new Set(liveCurrentPaths).has(source))
    .sort((left, right) => left.source.localeCompare(right.source))
    .map(({ source, destination }) => [source, destination])

  const file = `// Generated by scripts/generate-shop-redirects.mjs on ${new Date().toISOString()}
// Live kratom routes: ${liveCurrentPaths.length}
// Region-only redirects: ${regionOnlyRedirects.length}
// Manual CSV redirects: ${manualRedirectMap.length}

${serializeStringArray('GENERATED_KRATOM_LIVE_PATHS', liveCurrentPaths)}

${serializeObject('GENERATED_REGION_ONLY_REDIRECTS', regionOnlyRedirects)}

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
