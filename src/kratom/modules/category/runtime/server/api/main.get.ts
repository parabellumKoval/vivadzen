import { defineEventHandler, getHeader, getQuery } from 'h3'
import { getCategoryMainListFromCache } from '../utils/cache'

function firstString(value: unknown): string | undefined {
  if (Array.isArray(value)) {
    return value.length ? firstString(value[0]) : undefined
  }
  return typeof value === 'string' && value.length ? value : undefined
}

function toBooleanFlag(value: unknown): boolean {
  if (Array.isArray(value)) {
    return value.some((item) => toBooleanFlag(item))
  }
  if (typeof value === 'boolean') return value
  if (typeof value === 'number') return value !== 0
  if (typeof value === 'string') {
    const normalized = value.toLowerCase()
    return ['1', 'true', 'yes', 'y', 'on'].includes(normalized)
  }
  return false
}

export default defineEventHandler(async (event) => {
  const rawQuery = getQuery(event) as Record<string, any>
  const { force: rawForce, locale: rawLocale, region: rawRegion } = rawQuery || {}

  const localeParam = firstString(rawLocale)
  const regionParam = firstString(rawRegion)
  const force = toBooleanFlag(rawForce)

  const headerLocale = getHeader(event, 'accept-language') || undefined
  const headerRegion = getHeader(event, 'x-region') || undefined

  const payload = await getCategoryMainListFromCache({
    locale: localeParam || headerLocale,
    region: regionParam || headerRegion,
    force,
    event
  })

  return payload
})
