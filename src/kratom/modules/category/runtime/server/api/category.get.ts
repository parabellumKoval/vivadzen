import { defineEventHandler, getHeader, getQuery, createError } from 'h3'
import { getCategoryFromCache } from '../utils/cache'

export default defineEventHandler(async (event) => {
  const slug = event.context.params?.slug
  if (!slug) {
    throw createError({ statusCode: 400, statusMessage: 'Category slug is required' })
  }

  const query = getQuery(event)
  const localeParam = typeof query.locale === 'string' ? query.locale : undefined
  const regionParam = typeof query.region === 'string' ? query.region : undefined
  const forceParam =
    typeof query.force === 'string'
      ? ['true', '1', 'yes'].includes(query.force.toLowerCase())
      : false

  const acceptLanguageHeader = getHeader(event, 'accept-language') || undefined
  const headerLocale = acceptLanguageHeader?.split(',')[0]?.trim()
  const xRegion = getHeader(event, 'x-region') || undefined

  const payload = await getCategoryFromCache(slug, {
    locale: localeParam || headerLocale || acceptLanguageHeader,
    region: regionParam || xRegion,
    force: forceParam,
    event
  })

  if (payload == null) {
    throw createError({ statusCode: 404, statusMessage: 'Category not found' })
  }

  return payload
})
