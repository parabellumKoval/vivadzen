import { watch } from 'vue'
import { onNuxtReady } from '#app'
import { useCategoryStore } from '~/store/category'

const normalizeString = (value?: string | null) => {
  if (typeof value !== 'string') {
    return null
  }
  const trimmed = value.trim().toLowerCase()
  return trimmed.length ? trimmed : null
}

export default defineNuxtPlugin(() => {
  if (process.server) {
    return
  }

  const locale = useNuxtApp().$i18n.locale
  const regionAlias = useRegion().regionAlias
  const categoryStore = useCategoryStore()
  const refetchCategories = async () => {
    try {
      await Promise.all([
        categoryStore.listCached(null, true, false),
        categoryStore.listMainCached(false),
      ])
    } catch (error) {
      console.error('[category-refetch] Failed to refetch categories', error)
    }
  }

  let scheduled: ReturnType<typeof setTimeout> | null = null
  const scheduleRefetch = () => {
    if (scheduled) {
      clearTimeout(scheduled)
    }
    scheduled = setTimeout(() => {
      scheduled = null
      refetchCategories()
    }, 0)
  }

  onNuxtReady(() => {
    let lastRegion = normalizeString(regionAlias?.value)
    let lastLocale = normalizeString(locale?.value)

    watch(
      [regionAlias, locale],
      () => {
        const nextRegion = normalizeString(regionAlias?.value)
        const nextLocale = normalizeString(locale?.value)

        if (nextRegion === lastRegion && nextLocale === lastLocale) {
          return
        }

        lastRegion = nextRegion
        lastLocale = nextLocale
        scheduleRefetch()
      },
      { immediate: false },
    )
  })
})
