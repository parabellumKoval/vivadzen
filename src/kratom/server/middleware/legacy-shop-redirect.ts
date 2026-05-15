import { defineEventHandler, getRequestURL, sendRedirect, setResponseStatus } from 'h3'
import {
  GENERATED_KRATOM_LIVE_PATHS,
  GENERATED_KRATOM_PRODUCT_SLUGS,
  GENERATED_LEGACY_REGIONS,
  GENERATED_MANUAL_REDIRECTS,
} from '~/config/generatedShopRedirects'

const SHOP_ORIGIN = 'https://shop.vivadzen.com'
const SKIP_PREFIXES = ['/api', '/_nuxt', '/_ipx', '/__sitemap__', '/assets', '/images']
const SKIP_EXACT_PATHS = new Set(['/favicon.ico', '/robots.txt', '/sitemap.xml', '/sitemap_index.xml'])
const LIVE_PATHS = new Set<string>(GENERATED_KRATOM_LIVE_PATHS)
const LIVE_PRODUCT_SLUGS = new Set<string>(GENERATED_KRATOM_PRODUCT_SLUGS)
const KRATOM_LOCALES = new Set(['cs', 'en', 'ru', 'uk'])
const KRATOM_DEFAULT_LOCALE = 'cs'
const UNSUPPORTED_LEGACY_LOCALE_FALLBACK = 'en'
const LEGACY_REGION_DEFAULT_LOCALES = {
  global: 'en',
  ua: 'uk',
  cz: 'cs',
  de: 'de',
  es: 'es',
} as const
const LEGACY_REGION_ALIASES = {
  zz: 'global',
} as const
const LEGACY_ROOT_LOCALE_TARGETS = {
  ru: ['ua', 'ru'],
} as const
const CURRENT_APP_PREFIXES = ['/account', '/auth', '/checkout', '/new-password']
const LEGACY_PRODUCT_SEGMENTS = new Set(['produkt', 'product'])
const LEGACY_CATALOG_SEGMENTS = new Set(['viva-dzen-shop', 'shop-2'])
const LEGACY_PRODUCT_TAXONOMY_SEGMENTS = new Set(['product-category', 'product-tag'])
const LEGACY_CONTENT_TAXONOMY_SEGMENTS = new Set(['category', 'tag'])
const LEGACY_TECHNICAL_SEGMENTS = new Set(['wp-admin', 'wp-content', 'wp-json'])
const LEGACY_KRATOM_CATEGORY_SLUGS = new Set([
  'accessories-kratom',
  'dalsi-kratomove-produkty',
  'kratom',
  'kratom-edible',
  'kratom-choco',
  'kratom-fudge',
  'kratom-gummies',
  'kratom-pills',
  'kratom-powder',
  'kratom-prasek',
  'kratomove-tablety',
  'liquid-kratom',
  'liquid-kratomu',
  'ochuceny-kratom',
  'other-kratom-products',
  'prislusenstvi',
  'prislusenstvi-kratom',
])
const LEGACY_KRATOM_GUIDE_TAG = 'pruvodce-kratomem'
const KRATOM_PRODUCT_TERM_RE = /(?:^|-)(?:kratom|kratomu|kratoma)(?:-|$)/
const KRATOM_PRODUCT_FORM_RE = /(?:^|-)(?:choco|edible|edibles|extrakt|fudge|gummies|jablecny|kapsle|kapslich|liquid|pills|polvo|poroshok|powder|prasek|tablety|visnovy)(?:-|$)|-\d+(?:g|ml)$/

type LegacyPrefix = {
  sourceLength: number
  targetSegments: string[]
  isRootLocale: boolean
}

type LocalLegacyContext = {
  sourceLength: number
  locale: string
}

function normalizePath(value: string) {
  const raw = String(value || '').trim()
  if (!raw || raw === '/') {
    return '/'
  }

  const withLeadingSlash = raw.startsWith('/') ? raw : `/${raw}`
  const normalized = withLeadingSlash.replace(/\/{2,}/g, '/').replace(/\/+$/, '')
  return (normalized || '/').replace(/%[0-9A-F]{2}/g, (match) => match.toLowerCase())
}

function pathSegments(pathname: string) {
  const normalized = normalizePath(pathname)
  return normalized === '/' ? [] : normalized.slice(1).split('/').filter(Boolean)
}

function segmentsToPath(segments: string[]) {
  return segments.length ? `/${segments.join('/')}` : '/'
}

function pathMatchesPrefix(pathname: string, prefix: string) {
  return pathname === prefix || pathname.startsWith(`${prefix}/`)
}

function withoutKratomLocalePrefix(pathname: string) {
  const segments = pathSegments(pathname)
  if (segments.length > 1 && KRATOM_LOCALES.has(segments[0])) {
    return segmentsToPath(segments.slice(1))
  }

  return normalizePath(pathname)
}

function isCurrentAppPath(pathname: string) {
  const normalized = withoutKratomLocalePrefix(pathname)
  return CURRENT_APP_PREFIXES.some((prefix) => pathMatchesPrefix(normalized, prefix))
}

function normalizeLegacyRegion(value: string) {
  const normalized = String(value || '').trim().toLowerCase()
  return LEGACY_REGION_ALIASES[normalized as keyof typeof LEGACY_REGION_ALIASES] || normalized
}

function mapLegacyLocaleToKratomLocale(value: string | null | undefined) {
  const normalized = String(value || '').trim().toLowerCase()

  if (KRATOM_LOCALES.has(normalized)) {
    return normalized
  }

  if (normalized === 'de' || normalized === 'es') {
    return UNSUPPORTED_LEGACY_LOCALE_FALLBACK
  }

  return KRATOM_DEFAULT_LOCALE
}

function parseLegacyPrefix(segments: string[]): LegacyPrefix | null {
  const [rawRegion, maybeLocale] = segments
  const region = normalizeLegacyRegion(rawRegion)
  const locales = GENERATED_LEGACY_REGIONS[region as keyof typeof GENERATED_LEGACY_REGIONS]

  if (locales) {
    if (maybeLocale && locales.includes(maybeLocale as never)) {
      return {
        sourceLength: 2,
        targetSegments: [region, maybeLocale],
        isRootLocale: false,
      }
    }

    return {
      sourceLength: 1,
      targetSegments: [region],
      isRootLocale: false,
    }
  }

  const rootLocaleTarget = LEGACY_ROOT_LOCALE_TARGETS[rawRegion as keyof typeof LEGACY_ROOT_LOCALE_TARGETS]
  if (rootLocaleTarget) {
    return {
      sourceLength: 1,
      targetSegments: [...rootLocaleTarget],
      isRootLocale: true,
    }
  }

  return null
}

function parseLocalLegacyContext(segments: string[]): LocalLegacyContext {
  const prefix = parseLegacyPrefix(segments)

  if (prefix) {
    const sourceRegion = normalizeLegacyRegion(segments[0])
    const explicitLocale = prefix.sourceLength > 1 ? segments[1] : null
    const defaultLocale = prefix.isRootLocale
      ? segments[0]
      : LEGACY_REGION_DEFAULT_LOCALES[sourceRegion as keyof typeof LEGACY_REGION_DEFAULT_LOCALES]

    return {
      sourceLength: prefix.sourceLength,
      locale: mapLegacyLocaleToKratomLocale(explicitLocale || defaultLocale),
    }
  }

  if (segments[0] && KRATOM_LOCALES.has(segments[0])) {
    return {
      sourceLength: 1,
      locale: segments[0],
    }
  }

  return {
    sourceLength: 0,
    locale: UNSUPPORTED_LEGACY_LOCALE_FALLBACK,
  }
}

function buildLocalKratomPath(targetSegments: string[], locale: string) {
  const mappedLocale = mapLegacyLocaleToKratomLocale(locale)
  const localizedSegments = mappedLocale === KRATOM_DEFAULT_LOCALE ? [] : [mappedLocale]
  return segmentsToPath([...localizedSegments, ...targetSegments])
}

function stripTerminalLegacySegments(pathname: string) {
  const segments = pathSegments(pathname)

  while (segments[segments.length - 1] === 'feed') {
    segments.pop()
  }

  if (segments.length >= 2 && segments[segments.length - 2] === 'page' && /^\d+$/.test(segments[segments.length - 1])) {
    segments.splice(segments.length - 2, 2)
  }

  return segmentsToPath(segments)
}

function manualRedirectTarget(pathname: string) {
  const normalized = normalizePath(pathname)
  const direct = GENERATED_MANUAL_REDIRECTS[normalized as keyof typeof GENERATED_MANUAL_REDIRECTS]
  if (direct) {
    return direct
  }

  const stripped = stripTerminalLegacySegments(normalized)
  if (stripped !== normalized) {
    return GENERATED_MANUAL_REDIRECTS[stripped as keyof typeof GENERATED_MANUAL_REDIRECTS] || null
  }

  return null
}

function buildTargetUrl(pathname: string, search: string) {
  const targetUrl = new URL(normalizePath(pathname), `${SHOP_ORIGIN}/`)
  if (search) {
    targetUrl.search = search
  }
  return targetUrl.toString()
}

function sendShopRedirect(event: Parameters<typeof sendRedirect>[0], requestUrl: URL, targetPath: string) {
  const target = buildTargetUrl(targetPath, requestUrl.search)
  const targetUrl = new URL(target)

  if (
    targetUrl.origin === requestUrl.origin &&
    normalizePath(targetUrl.pathname) === normalizePath(requestUrl.pathname) &&
    targetUrl.search === requestUrl.search
  ) {
    return
  }

  return sendRedirect(event, target, 301)
}

function sendLocalRedirect(event: Parameters<typeof sendRedirect>[0], requestUrl: URL, targetPath: string) {
  const target = normalizePath(targetPath)

  if (normalizePath(requestUrl.pathname) === target && !requestUrl.search) {
    return
  }

  return sendRedirect(event, target, 301)
}

function resolveProductRedirectPath(segments: string[]) {
  const prefix = parseLegacyPrefix(segments)
  const productIndex = prefix ? prefix.sourceLength : 0
  if (!LEGACY_PRODUCT_SEGMENTS.has(segments[productIndex])) {
    return null
  }

  const slugSegments = segments.slice(productIndex + 1)
  if (!slugSegments.length) {
    return segmentsToPath([...(prefix?.targetSegments || []), 'catalog'])
  }

  return segmentsToPath([...(prefix?.targetSegments || []), ...slugSegments])
}

function resolveTaxonomyRedirectPath(segments: string[]) {
  const prefix = parseLegacyPrefix(segments)
  const taxonomyIndex = prefix ? prefix.sourceLength : 0
  const segment = segments[taxonomyIndex]

  if (LEGACY_PRODUCT_TAXONOMY_SEGMENTS.has(segment)) {
    return segmentsToPath([...(prefix?.targetSegments || []), 'catalog'])
  }

  if (LEGACY_CONTENT_TAXONOMY_SEGMENTS.has(segment)) {
    return segmentsToPath([...(prefix?.targetSegments || []), 'blog'])
  }

  return null
}

function resolveCatalogRedirectPath(segments: string[]) {
  const prefix = parseLegacyPrefix(segments)
  const catalogIndex = prefix ? prefix.sourceLength : 0
  const segment = segments[catalogIndex]

  if (LEGACY_CATALOG_SEGMENTS.has(segment)) {
    return segmentsToPath([...(prefix?.targetSegments || []), 'catalog'])
  }

  if ((segment === 'pages' || segment === 'index.php') && (prefix?.isRootLocale || prefix?.targetSegments.length)) {
    return segmentsToPath(prefix.targetSegments)
  }

  if (segment === 'pages' || segment === 'index.php') {
    return '/'
  }

  return null
}

function isKratomProductSlug(slug: string) {
  if (LIVE_PRODUCT_SLUGS.has(slug)) {
    return true
  }

  if (LEGACY_KRATOM_CATEGORY_SLUGS.has(slug)) {
    return false
  }

  return KRATOM_PRODUCT_TERM_RE.test(slug) && KRATOM_PRODUCT_FORM_RE.test(slug)
}

function buildLocalKratomProductPath(slug: string, context: LocalLegacyContext) {
  if (context.sourceLength === 0) {
    return segmentsToPath([slug])
  }

  return buildLocalKratomPath([slug], context.locale)
}

function resolveLocalKratomProductRedirectPath(segments: string[]) {
  const context = parseLocalLegacyContext(segments)
  const contentSegments = segments.slice(context.sourceLength)

  if (!contentSegments.length) {
    return null
  }

  const slugSegments = LEGACY_PRODUCT_SEGMENTS.has(contentSegments[0])
    ? contentSegments.slice(1)
    : contentSegments

  if (slugSegments.length !== 1 || !isKratomProductSlug(slugSegments[0])) {
    return null
  }

  return buildLocalKratomProductPath(slugSegments[0], context)
}

function resolveLocalKratomCategoryRedirectPath(segments: string[]) {
  const context = parseLocalLegacyContext(segments)
  const contentSegments = segments.slice(context.sourceLength)

  if (!contentSegments.length) {
    return null
  }

  if (contentSegments[0] === 'product-category' && contentSegments[1] === 'kratom') {
    return buildLocalKratomPath(['catalog'], context.locale)
  }

  if (contentSegments[0] === 'product-tag' && contentSegments.some((segment) => KRATOM_PRODUCT_TERM_RE.test(segment))) {
    return buildLocalKratomPath(['catalog'], context.locale)
  }

  if (contentSegments.length === 1 && LEGACY_KRATOM_CATEGORY_SLUGS.has(contentSegments[0])) {
    return buildLocalKratomPath(['catalog'], context.locale)
  }

  return null
}

function resolveLocalKratomGuideRedirectPath(segments: string[]) {
  const context = parseLocalLegacyContext(segments)
  const contentSegments = segments.slice(context.sourceLength)

  if (!contentSegments.length) {
    return null
  }

  if (
    ['blog', 'category', 'tag'].includes(contentSegments[0]) &&
    contentSegments.includes(LEGACY_KRATOM_GUIDE_TAG)
  ) {
    return buildLocalKratomPath([], context.locale)
  }

  return null
}

function resolveLocalKratomRedirectPath(pathname: string) {
  const stripped = stripTerminalLegacySegments(pathname)
  const segments = pathSegments(stripped)

  if (!segments.length) {
    return null
  }

  return resolveLocalKratomProductRedirectPath(segments)
    || resolveLocalKratomCategoryRedirectPath(segments)
    || resolveLocalKratomGuideRedirectPath(segments)
}

function resolvePatternRedirectPath(pathname: string) {
  const stripped = stripTerminalLegacySegments(pathname)
  const segments = pathSegments(stripped)
  if (!segments.length && normalizePath(pathname) !== '/') {
    return '/'
  }

  if (!segments.length) {
    return null
  }

  if (segments.some((segment) => LEGACY_TECHNICAL_SEGMENTS.has(segment))) {
    return 'gone'
  }

  const productRedirect = resolveProductRedirectPath(segments)
  if (productRedirect) {
    return productRedirect
  }

  const taxonomyRedirect = resolveTaxonomyRedirectPath(segments)
  if (taxonomyRedirect) {
    return taxonomyRedirect
  }

  const catalogRedirect = resolveCatalogRedirectPath(segments)
  if (catalogRedirect) {
    return catalogRedirect
  }

  const prefix = parseLegacyPrefix(segments)
  if (prefix && !prefix.isRootLocale) {
    return segmentsToPath([...prefix.targetSegments, ...segments.slice(prefix.sourceLength)])
  }

  if (prefix?.isRootLocale && ['blog', 'brands', 'reviews'].includes(segments[prefix.sourceLength])) {
    return segmentsToPath([...prefix.targetSegments, ...segments.slice(prefix.sourceLength)])
  }

  if (prefix?.isRootLocale && segments.length === prefix.sourceLength + 1) {
    return segmentsToPath([...prefix.targetSegments, ...segments.slice(prefix.sourceLength)])
  }

  if (['blog', 'brands', 'reviews'].includes(segments[0])) {
    return stripped
  }

  if (segments.length === 1) {
    return stripped
  }

  return null
}

export default defineEventHandler((event) => {
  const requestUrl = getRequestURL(event)
  const pathname = normalizePath(requestUrl.pathname || '/')

  if (SKIP_EXACT_PATHS.has(pathname) || SKIP_PREFIXES.some((prefix) => pathname.startsWith(prefix))) {
    return
  }

  if (isCurrentAppPath(pathname)) {
    return
  }

  if (LIVE_PATHS.has(pathname)) {
    return
  }

  const localKratomTarget = resolveLocalKratomRedirectPath(pathname)
  if (localKratomTarget) {
    return sendLocalRedirect(event, requestUrl, localKratomTarget)
  }

  const manualTarget = manualRedirectTarget(pathname)
  if (manualTarget) {
    return sendShopRedirect(event, requestUrl, manualTarget)
  }

  const patternTarget = resolvePatternRedirectPath(pathname)
  if (patternTarget === 'gone') {
    setResponseStatus(event, 410)
    return 'Gone'
  }

  if (patternTarget) {
    return sendShopRedirect(event, requestUrl, patternTarget)
  }
})
