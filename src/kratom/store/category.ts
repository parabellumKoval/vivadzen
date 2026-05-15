type Category = {
  id: number,
  name: string,
  slug: string,
  children: object[]
};

const resolveLocaleHeader = (
  locale: unknown,
  fallbackLocale: unknown = null,
  forcedLocale: unknown = null,
) => {
  let localeValue = (locale as { value?: unknown } | null)?.value ?? locale

  if (typeof forcedLocale === 'string' && forcedLocale.length) {
    localeValue = forcedLocale
  } else if (typeof fallbackLocale === 'string' && fallbackLocale.length) {
    localeValue = fallbackLocale
  }

  return typeof localeValue === 'string' && localeValue.length ? localeValue : null
}

const resolveStorefrontHeader = (storefrontCode: unknown) => {
  return String(storefrontCode || 'kratom').trim()
}

const buildCategoryHeaders = ({
  localeValue,
  regionAlias,
  storefrontCode,
}: {
  localeValue: string | null
  regionAlias: unknown
  storefrontCode: unknown
}) => {
  const headers: Record<string, string> = {
    Accept: 'application/json',
    'X-Storefront': resolveStorefrontHeader(storefrontCode),
  }

  if (typeof localeValue === 'string' && localeValue.length) {
    headers['Accept-Language'] = localeValue
  }

  const regionValue = (regionAlias as { value?: unknown } | null)?.value ?? regionAlias
  if (typeof regionValue === 'string' && regionValue.length) {
    headers['X-Region'] = regionValue
  }

  return headers
}

export const useCategoryStore = defineStore('categoryStore', {
  state: () => ({ 
    allState: {
      data: [] as Category[],
      meta: null as Record<string, any> | null
    },
    mainState: {
      data: [] as Category[],
      meta: null as Record<string, any> | null
    },
    categoryState: null as Category | null,
  }),
  
  getters: {
    list: (state) => state.allState.data,
    mainList: (state) => state.mainState.data,
    category: (state) => state.categoryState,
  },

  actions: {
    async listCached(query: Record<string, any> | null = null, state = true, force = false) {
      const runtimeConfig = useRuntimeConfig()
      const routePath =
        runtimeConfig.public?.categoryModule?.listRoutePath || '/api/_categories/list'

      const nuxtApp = useNuxtApp()
      const regionStore = useRegion()
      const event = process.server ? useRequestEvent() : null
      const fallbackLocale =
        typeof regionStore?.getDefaultLocaleFor === 'function'
          ? regionStore.getDefaultLocaleFor(regionStore.region.value)
          : null
      const forcedLocale =
        typeof event?.context?.forcedLocale === 'string' ? event.context.forcedLocale : null
      const localeValue = resolveLocaleHeader(nuxtApp.$i18n?.locale, fallbackLocale, forcedLocale)
      const regionAlias = regionStore.regionAlias.value

      const headers = buildCategoryHeaders({
        localeValue,
        regionAlias,
        storefrontCode: runtimeConfig.public.storefrontCode,
      })

      const queryPayload = query ? JSON.parse(JSON.stringify(query)) : undefined
      let params: Record<string, any> | undefined = queryPayload
      if (force) {
        params = { ...(params || {}), force: '1' }
      }

      try {
        const data = await $fetch(routePath, {
          headers,
          query: params
        })

        if (state && (!params || Object.keys(params).length === (force ? 1 : 0))) {
          this.allState.data = data?.data ?? data ?? []
          this.allState.meta = data?.meta ?? null
        }

        return data
      } catch (error) {
        throw error
      }
    },

    async listMainCached(force = false) {
      const runtimeConfig = useRuntimeConfig()
      const routePath =
        runtimeConfig.public?.categoryModule?.mainListRoutePath || '/api/_categories/main'

      const nuxtApp = useNuxtApp()
      const regionStore = useRegion()
      const event = process.server ? useRequestEvent() : null
      const fallbackLocale =
        typeof regionStore?.getDefaultLocaleFor === 'function'
          ? regionStore.getDefaultLocaleFor(regionStore.region.value)
          : null
      const forcedLocale =
        typeof event?.context?.forcedLocale === 'string' ? event.context.forcedLocale : null
      const localeValue = resolveLocaleHeader(nuxtApp.$i18n?.locale, fallbackLocale, forcedLocale)
      const regionAlias = regionStore.regionAlias.value

      const headers = buildCategoryHeaders({
        localeValue,
        regionAlias,
        storefrontCode: runtimeConfig.public.storefrontCode,
      })

      const params = force ? { force: '1' } : undefined

      try {
        const data = await $fetch(routePath, {
          headers,
          query: params
        })

        this.mainState.data = data?.data ?? data ?? []
        this.mainState.meta = data?.meta ?? null

        return data
      } catch (error) {
        throw error
      }
    },

    async show(slug: string) {
      const url = `${useRuntimeConfig().public.apiBase}/category/${slug}`

      return await useApiFetch(url).then(({data, error}) => {
        
        if(data && data.data) {
          return data.data
        }
      })
    },


    async showCached(slug: string) {
      const runtimeConfig = useRuntimeConfig()
      const template =
        runtimeConfig.public?.categoryModule?.categoryRoutePath || '/api/_categories/:slug'
      const targetPath = template.includes(':slug')
        ? template.replace(':slug', encodeURIComponent(slug))
        : `${template}/${encodeURIComponent(slug)}`

      const nuxtApp = useNuxtApp()
      const regionStore = useRegion()
      const event = process.server ? useRequestEvent() : null
      const fallbackLocale =
        typeof regionStore?.getDefaultLocaleFor === 'function'
          ? regionStore.getDefaultLocaleFor(regionStore.region.value)
          : null
      const forcedLocale =
        typeof event?.context?.forcedLocale === 'string' ? event.context.forcedLocale : null
      const localeValue = resolveLocaleHeader(nuxtApp.$i18n?.locale, fallbackLocale, forcedLocale)
      const regionAlias = regionStore.regionAlias.value

      const headers = buildCategoryHeaders({
        localeValue,
        regionAlias,
        storefrontCode: runtimeConfig.public.storefrontCode,
      })

      try {
        const data = await $fetch(targetPath, { headers })
        return { data, error: null }
      } catch (error) {
        return { data: null, error }
      }
    }

  },
})
