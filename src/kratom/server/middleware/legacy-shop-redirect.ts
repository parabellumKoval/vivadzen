import { defineEventHandler, getRequestURL, sendRedirect } from 'h3'
import {
  GENERATED_KRATOM_LIVE_PATHS,
  GENERATED_LEGACY_REGIONS,
  GENERATED_MANUAL_REDIRECTS,
  GENERATED_REGION_ONLY_REDIRECTS,
} from '~/config/generatedShopRedirects'

const SHOP_ORIGIN = 'https://shop.vivadzen.com'
const SKIP_PREFIXES = ['/api', '/_nuxt', '/_ipx', '/assets', '/images']
const SKIP_EXACT_PATHS = new Set(['/favicon.ico', '/robots.txt', '/sitemap.xml'])
const LIVE_PATHS = new Set<string>(GENERATED_KRATOM_LIVE_PATHS)

function normalizePath(value: string) {
  const raw = String(value || '').trim()
  if (!raw || raw === '/') {
    return '/'
  }

  const withLeadingSlash = raw.startsWith('/') ? raw : `/${raw}`
  const normalized = withLeadingSlash.replace(/\/{2,}/g, '/').replace(/\/+$/, '')
  return normalized || '/'
}

function buildTargetUrl(pathname: string, search: string) {
  const targetUrl = new URL(normalizePath(pathname), `${SHOP_ORIGIN}/`)
  if (search) {
    targetUrl.search = search
  }
  return targetUrl.toString()
}

function parseLegacyPath(pathname: string) {
  const normalized = normalizePath(pathname)
  if (normalized === '/') {
    return null
  }

  const segments = normalized.slice(1).split('/').filter(Boolean)
  if (!segments.length) {
    return null
  }

  const [region, maybeLocale, ...rest] = segments
  const locales = GENERATED_LEGACY_REGIONS[region as keyof typeof GENERATED_LEGACY_REGIONS]
  if (!locales) {
    return null
  }

  if (maybeLocale && locales.includes(maybeLocale as never)) {
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

export default defineEventHandler((event) => {
  const requestUrl = getRequestURL(event)
  const pathname = normalizePath(requestUrl.pathname || '/')

  if (SKIP_EXACT_PATHS.has(pathname) || SKIP_PREFIXES.some((prefix) => pathname.startsWith(prefix))) {
    return
  }

  if (LIVE_PATHS.has(pathname)) {
    return
  }

  const manualTarget = GENERATED_MANUAL_REDIRECTS[pathname as keyof typeof GENERATED_MANUAL_REDIRECTS]
  if (manualTarget) {
    return sendRedirect(event, buildTargetUrl(manualTarget, requestUrl.search), 301)
  }

  const parsed = parseLegacyPath(pathname)
  if (!parsed) {
    return
  }

  if (parsed.hasExplicitLocale) {
    return sendRedirect(event, buildTargetUrl(pathname, requestUrl.search), 301)
  }

  const regionOnlyTarget = GENERATED_REGION_ONLY_REDIRECTS[pathname as keyof typeof GENERATED_REGION_ONLY_REDIRECTS]
  if (!regionOnlyTarget) {
    return
  }

  return sendRedirect(event, buildTargetUrl(regionOnlyTarget, requestUrl.search), 301)
})
