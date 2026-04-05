import { ofetch } from 'ofetch'
import { useRuntimeConfig } from '#imports'
import {
  normalizeLocaleKey,
  normalizeRegionKey,
  setDatasetEntry,
  type SettingsDataset,
} from '../../utils/settings-helpers'

type SettingsEntry = Record<string, any>

interface CacheEntry {
  data: SettingsDataset
  fetchedAt: number
}

interface SettingsModuleRuntimeOptions {
  apiUrl: string
  ttlSec?: number
  regions?: string[]
  locales?: string[]
  enableTtl?: boolean
}

interface FetchCombo {
  region?: string
  locale?: string
}

const STORAGE_KEY = 'settings-cache'
const FETCH_CONCURRENCY = 1

function storage() {
  return useStorage<CacheEntry>('cache')
}

const now = () => Date.now()

interface NormalizedSettingsModuleRuntimeOptions extends SettingsModuleRuntimeOptions {
  enableTtl: boolean
}

function getModuleOptions(): NormalizedSettingsModuleRuntimeOptions {
  const config = useRuntimeConfig()
  const raw = (config?.settingsModule || {}) as SettingsModuleRuntimeOptions
  return {
    ...raw,
    enableTtl: raw.enableTtl !== false
  }
}

function resolveStorefrontCode(): string {
  const config = useRuntimeConfig()
  return String(config.public?.storefrontCode || 'kratom').trim()
}

async function readCache(): Promise<CacheEntry | null> {
  return await storage().getItem<CacheEntry>(STORAGE_KEY)
}

async function writeCache(entry: CacheEntry) {
  await storage().setItem(STORAGE_KEY, entry)
}

function normalizeDataset(value: unknown): SettingsDataset {
  return value && typeof value === 'object' ? (value as SettingsDataset) : {}
}

function hasExactVariant(dataset: SettingsDataset, region?: string | null, locale?: string | null) {
  const regionKey = normalizeRegionKey(region)
  const localeKey = normalizeLocaleKey(locale)
  const bucket = dataset[regionKey]

  return Boolean(bucket && Object.prototype.hasOwnProperty.call(bucket, localeKey))
}

const normalizeList = (values?: string[]) => {
  if (!values?.length) return []
  const uniques = new Set<string>()
  for (const value of values) {
    if (!value) continue
    uniques.add(value.toLowerCase())
  }
  return Array.from(uniques)
}

const dedupeCombos = (combos: FetchCombo[]) => {
  const seen = new Set<string>()
  const result: FetchCombo[] = []
  for (const combo of combos) {
    const key = `${combo.region ?? ''}::${combo.locale ?? ''}`
    if (seen.has(key)) continue
    seen.add(key)
    result.push(combo)
  }
  return result
}

const buildCombos = (regions?: string[], locales?: string[]): FetchCombo[] => {
  const regionList = normalizeList(regions)
  const localeList = normalizeList(locales)

  const combos: FetchCombo[] = [
    { region: undefined, locale: undefined },
  ]

  if (localeList.length) {
    for (const locale of localeList) {
      combos.push({ region: undefined, locale })
    }
  }

  if (regionList.length) {
    for (const region of regionList) {
      combos.push({ region, locale: undefined })
      if (localeList.length) {
        for (const locale of localeList) {
          combos.push({ region, locale })
        }
      }
    }
  }

  return dedupeCombos(combos)
}

const fetchCombo = async (apiUrl: string, combo: FetchCombo): Promise<SettingsEntry> => {
  const storefrontCode = resolveStorefrontCode()
  const headers: Record<string, string> = {
    Accept: 'application/json',
    'X-Storefront': storefrontCode,
  }

  if (combo.locale) headers['Accept-Language'] = combo.locale
  if (combo.region) headers['X-Region'] = combo.region

  const query: Record<string, string> = {
    storefront: storefrontCode,
  }

  if (combo.region) {
    query.country = combo.region
  }

  return await ofetch<SettingsEntry>(apiUrl, {
    retry: 0,
    headers,
    query,
  })
}

const runTasks = async (tasks: Array<() => Promise<void>>, limit: number) => {
  const executing = new Set<Promise<void>>()
  for (const task of tasks) {
    const promise = task()
    executing.add(promise)
    promise.finally(() => executing.delete(promise))
    if (executing.size >= limit) {
      await Promise.race(executing)
    }
  }
  await Promise.all(executing)
}

async function fetchRemote(): Promise<CacheEntry | null> {
  const { apiUrl, regions, locales } = getModuleOptions()
  if (!apiUrl) {
    throw new Error('settingsModule.apiUrl is not configured')
  }

  const dataset: SettingsDataset = {}
  const combos = buildCombos(regions, locales)
  const errors: Error[] = []

  await runTasks(
    combos.map((combo) => async () => {
      try {
        const value = await fetchCombo(apiUrl, combo)
        setDatasetEntry(dataset, combo.region, combo.locale, value)
      } catch (err) {
        const error = err instanceof Error ? err : new Error(String(err))
        errors.push(error)
        console.error('[settings-module] fetch failed', { combo, error })
      }
    }),
    FETCH_CONCURRENCY
  )

  if (!Object.keys(dataset).length) {
    if (errors.length) throw errors[0]
    return { data: {}, fetchedAt: now() }
  }

  return { data: dataset, fetchedAt: now() }
}

export async function getSettingsSWR(): Promise<SettingsDataset> {
  const { ttlSec, enableTtl } = getModuleOptions()
  const resolvedTtlSec = typeof ttlSec === 'number' && !Number.isNaN(ttlSec) ? ttlSec : 300
  const ttlMs = resolvedTtlSec > 0 ? resolvedTtlSec * 1000 : 0
  const cached = await readCache()
  if (cached) {
    if (!enableTtl) {
      return cached.data || {}
    }
    const fresh = ttlMs > 0 && (now() - cached.fetchedAt) < ttlMs
    if (fresh) {
      return cached.data || {}
    }
    void (async () => {
      try {
        const freshEntry = await fetchRemote()
        if (freshEntry) await writeCache(freshEntry)
      } catch {}
    })()
    return cached.data || {}
  }

  try {
    const freshEntry = await fetchRemote()
    if (freshEntry) {
      await writeCache(freshEntry)
      return freshEntry.data || {}
    }
  } catch {
    return {}
  }

  return {}
}

export async function ensureSettingsVariant(
  region?: string | null,
  locale?: string | null
): Promise<SettingsDataset> {
  const cached = await readCache()
  const dataset = normalizeDataset(cached?.data)

  if (hasExactVariant(dataset, region, locale)) {
    return dataset
  }

  try {
    const { apiUrl } = getModuleOptions()
    if (!apiUrl) {
      return dataset
    }

    const value = await fetchCombo(apiUrl, {
      region: region || undefined,
      locale: locale || undefined,
    })

    const nextDataset: SettingsDataset = { ...dataset }
    setDatasetEntry(nextDataset, region, locale, value)
    await writeCache({
      data: nextDataset,
      fetchedAt: now(),
    })

    return nextDataset
  } catch {
    return dataset
  }
}

export async function refreshSettingsNow(): Promise<SettingsDataset> {
  const freshEntry = await fetchRemote()
  if (freshEntry) {
    await writeCache(freshEntry)
    return freshEntry.data
  }
  const existing = await readCache()
  return existing?.data ?? {}
}
