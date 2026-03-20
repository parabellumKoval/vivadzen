import { defineEventHandler, getRouterParam, createError } from 'h3'
import { useRuntimeConfig } from '#imports'
import {
  REGIONS_MODULE_OPTIONS,
  generateSitemapEntries,
  invertAliases,
  normalizeRegion,
  normalizeLocale
} from '~/utils/sitemap'

export default defineEventHandler(async (event) => {
  const regionParam = normalizeRegion(getRouterParam(event, 'region'))
  const localeParam = normalizeLocale(getRouterParam(event, 'locale'))

  if (!regionParam || !localeParam) {
    throw createError({ statusCode: 400, statusMessage: 'Invalid sitemap context' })
  }

  const runtimeConfig = useRuntimeConfig()
  const apiBase = runtimeConfig.public?.apiBase || runtimeConfig.apiBase

  if (!apiBase) {
    throw createError({ statusCode: 500, statusMessage: 'API base URL is not configured' })
  }

  const aliasToCanonical = invertAliases(REGIONS_MODULE_OPTIONS.regionAliases || {})
  const fallbackRegion = REGIONS_MODULE_OPTIONS.fallbackRegion || 'global'

  const urls = await generateSitemapEntries({
    region: regionParam,
    locale: localeParam,
    fallbackRegion,
    localesByRegion: REGIONS_MODULE_OPTIONS.localesByRegion || {},
    aliasToCanonical,
    dataEndpoint: `${apiBase}/sitemap/full`
  })

  return { urls }
})
