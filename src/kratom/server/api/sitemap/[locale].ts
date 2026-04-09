import { defineEventHandler, getRouterParam, createError } from 'h3'
import { useRuntimeConfig } from '#imports'
import {
  SITEMAP_COUNTRY,
  SITEMAP_DEFAULT_LOCALE,
  SITEMAP_LOCALES,
  generateSitemapEntries,
  normalizeLocale
} from '~/utils/sitemap'

export default defineEventHandler(async (event) => {
  const localeParam = normalizeLocale(getRouterParam(event, 'locale'))

  if (!localeParam || !SITEMAP_LOCALES.includes(localeParam as typeof SITEMAP_LOCALES[number])) {
    throw createError({ statusCode: 400, statusMessage: 'Invalid sitemap locale' })
  }

  const runtimeConfig = useRuntimeConfig()
  const apiBase = runtimeConfig.public?.apiBase || runtimeConfig.apiBase
  const storefrontCode = String(runtimeConfig.public?.storefrontCode || 'kratom').trim()
  const kratomStore = runtimeConfig.public?.kratomStore || {}
  const country = String(kratomStore.region || SITEMAP_COUNTRY).trim().toLowerCase()
  const defaultLocale = normalizeLocale(kratomStore.defaultLocale || SITEMAP_DEFAULT_LOCALE)
  const categorySlug = String(kratomStore.categorySlug || 'kratom').trim()
  const productResource = String(kratomStore.productResources?.card || 'kratom_small').trim()

  if (!apiBase) {
    throw createError({ statusCode: 500, statusMessage: 'API base URL is not configured' })
  }

  const urls = await generateSitemapEntries({
    locale: localeParam,
    storefront: storefrontCode,
    country,
    defaultLocale,
    categorySlug,
    productResource,
    catalogEndpoint: `${apiBase}/catalog`,
    articlesEndpoint: `${apiBase}/articles`
  })

  return { urls }
})
