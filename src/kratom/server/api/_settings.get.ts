import { defineEventHandler, getQuery } from 'h3'
import { getSettingsSWR } from '~/modules/settings/runtime/server/utils/settings-cache'
import { getDatasetEntry } from '~/modules/settings/runtime/utils/settings-helpers'

const pickQueryString = (value: unknown): string | null => {
  if (typeof value === 'string') {
    return value
  }
  if (Array.isArray(value) && typeof value[0] === 'string') {
    return value[0]
  }
  return null
}

const normalize = (value: string | null | undefined): string | null => {
  if (!value) return null
  const trimmed = value.trim().toLowerCase()
  return trimmed.length ? trimmed : null
}

export default defineEventHandler(async (event) => {
  const query = getQuery(event)
  const requestedRegion = normalize(pickQueryString((query as any)?.region))
  const requestedLocale = normalize(pickQueryString((query as any)?.locale))

  const fallbackRegion = typeof event.context.region === 'string'
    ? event.context.region
    : null
  const fallbackLocale = typeof (event.context as any).forcedLocale === 'string'
    ? (event.context as any).forcedLocale
    : null

  const region = requestedRegion ?? fallbackRegion
  const locale = requestedLocale ?? fallbackLocale

  const dataset = await getSettingsSWR()
  const current = getDatasetEntry(dataset, region, locale)

  return {
    ok: true,
    region,
    locale,
    data: current,
  }
})
