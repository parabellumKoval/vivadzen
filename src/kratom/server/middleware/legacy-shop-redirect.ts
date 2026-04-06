import { defineEventHandler, getRequestURL, sendRedirect } from 'h3'
import { GENERATED_SHOP_REDIRECT_ROUTE_RULES } from '~/config/generatedShopRedirects'

const SKIP_PREFIXES = ['/api', '/_nuxt', '/_ipx', '/assets', '/images']
const SKIP_EXACT_PATHS = new Set(['/favicon.ico', '/robots.txt', '/sitemap.xml'])

export default defineEventHandler((event) => {
  const requestUrl = getRequestURL(event)
  const pathname = requestUrl.pathname || '/'

  if (SKIP_EXACT_PATHS.has(pathname) || SKIP_PREFIXES.some((prefix) => pathname.startsWith(prefix))) {
    return
  }

  const rule = GENERATED_SHOP_REDIRECT_ROUTE_RULES[pathname as keyof typeof GENERATED_SHOP_REDIRECT_ROUTE_RULES]

  if (!rule?.redirect?.to) {
    return
  }

  const targetUrl = new URL(rule.redirect.to)

  if (requestUrl.search) {
    targetUrl.search = requestUrl.search
  }

  return sendRedirect(event, targetUrl.toString(), rule.redirect.statusCode)
})
