import { ensureSettingsVariant } from '../utils/settings-cache'
import {
  getDatasetEntry,
  type SettingsServerPayload,
} from '../../utils/settings-helpers'

const DEFAULT_LOCALE_BY_REGION: Record<string, string> = {
  ua: 'uk',
  cz: 'cs',
  de: 'de',
  es: 'es',
  global: 'en',
}

export default defineEventHandler(async (event) => {
  try {
    const regionRaw = (event.context.region as string | undefined) || 'global'
    const region = regionRaw.toLowerCase()
    const forcedLocale = (event.context.forcedLocale as string | undefined) || null
    const locale = forcedLocale?.toLowerCase() || DEFAULT_LOCALE_BY_REGION[region] || null
    const rawDataset = await ensureSettingsVariant(region, locale)
    const all = rawDataset && typeof rawDataset === 'object' ? rawDataset : {}

    const payload: SettingsServerPayload = {
      all,
      region,
      locale,
    }

    payload.current = getDatasetEntry(all, region, locale)
    ;(event.context as any).settings = payload
  } catch (e) {
    const fallback: SettingsServerPayload = {
      all: {},
      region: 'global',
      locale: null,
    }
    fallback.current = {}
    ;(event.context as any).settings = fallback
  }
})
