export type SettingsEntry = Record<string, any>
export type SettingsLocaleBucket = Record<string, SettingsEntry>
export type SettingsDataset = Record<string, SettingsLocaleBucket>

export const DEFAULT_REGION_KEY = '__default__'
export const DEFAULT_LOCALE_KEY = '__default__'

export interface SettingsServerPayload {
  all: SettingsDataset
  region: string | null
  locale: string | null
  current?: SettingsEntry
}

export const normalizeRegionKey = (region?: string | null) => {
  if (!region) return DEFAULT_REGION_KEY
  const normalized = region.toLowerCase()
  return normalized === 'global' ? DEFAULT_REGION_KEY : normalized
}

export const normalizeLocaleKey = (locale?: string | null) => {
  if (!locale) return DEFAULT_LOCALE_KEY
  return locale.toLowerCase()
}

const ensureRegionBucket = (dataset: SettingsDataset, regionKey: string) => {
  if (!dataset[regionKey]) dataset[regionKey] = {}
  return dataset[regionKey]
}

export const setDatasetEntry = (
  dataset: SettingsDataset,
  region: string | null | undefined,
  locale: string | null | undefined,
  value: SettingsEntry
) => {
  const regionKey = normalizeRegionKey(region)
  const localeKey = normalizeLocaleKey(locale)
  ensureRegionBucket(dataset, regionKey)[localeKey] = value
}

const firstEntry = (bucket: SettingsLocaleBucket | undefined): SettingsEntry | null => {
  if (!bucket) return null
  const [first] = Object.values(bucket)
  return first || null
}

export const getDatasetEntry = (
  dataset: SettingsDataset,
  region?: string | null,
  locale?: string | null
): SettingsEntry => {
  const regionKey = normalizeRegionKey(region)
  const localeKey = normalizeLocaleKey(locale)

  const regionBucket = dataset[regionKey]
  if (regionBucket) {
    if (regionBucket[localeKey]) return regionBucket[localeKey]
    if (regionBucket[DEFAULT_LOCALE_KEY]) return regionBucket[DEFAULT_LOCALE_KEY]
    const fallback = firstEntry(regionBucket)
    if (fallback) return fallback
  }

  const defaultBucket = dataset[DEFAULT_REGION_KEY]
  if (defaultBucket) {
    if (defaultBucket[localeKey]) return defaultBucket[localeKey]
    if (defaultBucket[DEFAULT_LOCALE_KEY]) return defaultBucket[DEFAULT_LOCALE_KEY]
    const fallback = firstEntry(defaultBucket)
    if (fallback) return fallback
  }

  for (const bucket of Object.values(dataset)) {
    const candidate = firstEntry(bucket)
    if (candidate) return candidate
  }

  return {}
}

export const countDatasetEntries = (dataset: SettingsDataset) => {
  return Object.values(dataset).reduce((sum, locales) => sum + Object.keys(locales).length, 0)
}
