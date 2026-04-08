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
  const country = String(runtimeConfig.public?.kratomStore?.region || SITEMAP_COUNTRY).trim().toLowerCase()
  const defaultLocale = normalizeLocale(runtimeConfig.public?.kratomStore?.defaultLocale || SITEMAP_DEFAULT_LOCALE)

  if (!apiBase) {
    throw createError({ statusCode: 500, statusMessage: 'API base URL is not configured' })
  }

  const urls = await generateSitemapEntries({
    locale: localeParam,
    storefront: storefrontCode,
    country,
    defaultLocale,
    dataEndpoint: `${apiBase}/sitemap/full`
  })

  return { urls }
})
