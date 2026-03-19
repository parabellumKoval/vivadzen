import { computed, watch } from 'vue'
import {
  getDatasetEntry,
  normalizeLocaleKey,
  normalizeRegionKey,
  type SettingsDataset,
  type SettingsServerPayload,
} from '../utils/settings-helpers'

interface SettingsVariantResponse {
  ok: boolean
  region: string | null
  locale: string | null
  data: Record<string, any>
}

export default defineNuxtPlugin((nuxtApp) => {
  const dataset = useState<SettingsDataset>('settings-dataset', () => ({}))
  const meta = useState<{ region: string | null; locale: string | null }>('settings-meta', () => ({
    region: null,
    locale: null,
  }))

  if (process.server) {
    const ev = useRequestEvent()
    const payload = ev?.context?.settings as SettingsServerPayload | undefined
    if (payload) {
      dataset.value = payload.all || {}
      meta.value.region = payload.region
      meta.value.locale = payload.locale
    }
  }

  const i18n = (nuxtApp as any).$i18n
  const regionStore = useRegion()

  const currentRegion = computed(() => {
    return regionStore?.region?.value ?? meta.value.region ?? 'global'
  })

  const currentLocale = computed(() => {
    return i18n?.locale?.value ?? meta.value.locale ?? null
  })

  const currentSettings = computed(() => {
    return getDatasetEntry(dataset.value, currentRegion.value, currentLocale.value)
  })

  if (regionStore?.region) {
    watch(regionStore.region, (value) => {
      meta.value.region = value ?? meta.value.region
    }, { immediate: !process.server })
  }

  if (i18n?.locale) {
    watch(i18n.locale, (value: string | null | undefined) => {
      meta.value.locale = value ?? meta.value.locale
    }, { immediate: !process.server })
  }

  const variantKey = (region: string | null, locale: string | null) => `${region ?? ''}::${locale ?? ''}`

  const hasExactVariant = (region: string | null, locale: string | null) => {
    const regionKey = normalizeRegionKey(region)
    const localeKey = normalizeLocaleKey(locale)
    const bucket = dataset.value[regionKey]
    return Boolean(bucket && Object.prototype.hasOwnProperty.call(bucket, localeKey))
  }

  const applyVariant = (region: string | null, locale: string | null, payload: Record<string, any> | null | undefined) => {
    const regionKey = normalizeRegionKey(region)
    const localeKey = normalizeLocaleKey(locale)
    const next: SettingsDataset = { ...dataset.value }
    const bucket = { ...(next[regionKey] || {}) }
    bucket[localeKey] = payload ?? {}
    next[regionKey] = bucket
    dataset.value = next
  }

  const pendingFetches = new Map<string, Promise<void>>()

  const fetchVariant = async (region: string | null, locale: string | null) => {
    if (process.server) return
    const key = variantKey(region, locale)
    if (pendingFetches.has(key)) {
      return pendingFetches.get(key)
    }

    const query: Record<string, string> = {}
    if (region) query.region = region
    if (locale) query.locale = locale

    const promise = $fetch<SettingsVariantResponse>('/api/_settings', {
      method: 'GET',
      query,
      retry: 0,
    })
      .then((response) => {
        if (!response || response.ok === false) {
          return
        }
        const resolvedRegion = typeof response.region === 'string' ? response.region : region
        const resolvedLocale = typeof response.locale === 'string' ? response.locale : locale
        applyVariant(resolvedRegion ?? null, resolvedLocale ?? null, response.data)
      })
      .catch((error) => {
        console.error('[settings-module] Failed to refetch settings variant', error)
      })
      .finally(() => {
        pendingFetches.delete(key)
      })

    pendingFetches.set(key, promise)
    return promise
  }

  if (process.client) {
    let lastKey: string | null = null

    watch(
      () => [currentRegion.value, currentLocale.value],
      ([regionValue, localeValue]) => {
        const region = typeof regionValue === 'string' && regionValue.trim().length ? regionValue : null
        const locale = typeof localeValue === 'string' && localeValue.trim().length ? localeValue : null
        const key = variantKey(region, locale)
        const missingEntry = !hasExactVariant(region, locale)

        if (!missingEntry) {
          lastKey = key
          return
        }

        if (key === lastKey) {
          return
        }

        lastKey = key
        void fetchVariant(region, locale)
      },
      { immediate: true }
    )
  }

  const getSetting = (key: string, def?: any) => {
    const root: any = currentSettings.value || {}
    if (Object.prototype.hasOwnProperty.call(root, key)) {
      return root[key]
    }
    const parts = key.split('.')
    let cur: any = root
    for (const part of parts) {
      if (cur && Object.prototype.hasOwnProperty.call(cur, part)) {
        cur = cur[part]
      } else {
        return def
      }
    }
    return cur ?? def
  }

  return {
    provide: {
      settings: currentSettings,
      settingsAll: computed(() => dataset.value),
      getSetting,
    }
  }
})
