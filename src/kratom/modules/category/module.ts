import { addImports, addServerHandler, createResolver, defineNuxtModule } from '@nuxt/kit'

export interface CategoryModuleOptions {
  /**
   * Base URL for remote API calls. If omitted, falls back to runtimeConfig.public.apiBase.
   */
  baseUrl?: string
  /**
   * Endpoint used to fetch the list of category slugs.
   */
  slugsEndpoint?: string
  /**
   * Endpoint used to fetch full category details. Supports :slug placeholders.
   */
  detailsEndpoint?: string
  /**
   * Endpoint used to fetch category lists.
   */
  listEndpoint?: string
  /**
   * Endpoint used to fetch categories for the main page.
   */
  mainListEndpoint?: string
  /**
   * When true (default), cached values expire after ttlSec seconds.
   */
  enableTtl?: boolean
  /**
   * TTL duration in seconds. Ignored when enableTtl is false.
   */
  ttlSec?: number
  /**
   * Languages (Accept-Language header values) to prefetch categories for.
   */
  languages?: string[]
  /**
   * Regions (X-Region header values) to prefetch categories for.
   */
  regions?: string[]
  /**
   * Internal API route that returns cached slugs.
   */
  slugsRoutePath?: string
  /**
   * Internal API route that returns cached category details (per locale/region).
   * Should include `:slug` placeholder.
   */
  categoryRoutePath?: string
  /**
   * Internal API route that returns cached category lists.
   */
  listRoutePath?: string
  /**
   * Internal API route that returns cached categories prepared for the main page.
   */
  mainListRoutePath?: string
  /**
   * Internal webhook route that forces slugs cache rebuild.
   */
  refreshSlugsRoutePath?: string
  /**
   * Internal webhook route that rebuilds cache for every category.
   */
  refreshAllRoutePath?: string
  /**
   * Internal webhook route that rebuilds cache only for the provided slug.
   * Should include `:slug` placeholder.
   */
  refreshSingleRoutePath?: string
  /**
   * Internal webhook route that rebuilds cached category lists.
   */
  refreshListRoutePath?: string
  /**
   * Internal webhook route that rebuilds cached categories prepared for the main page.
   */
  refreshMainListRoutePath?: string
}

const DEFAULT_OPTIONS: Required<Omit<CategoryModuleOptions, 'baseUrl'>> & { baseUrl?: string } = {
  baseUrl: undefined,
  slugsEndpoint: '/djini-category/slugs-simple',
  detailsEndpoint: '/category_cached/:slug',
  listEndpoint: '/category',
  mainListEndpoint: '/category/main',
  enableTtl: true,
  ttlSec: 600,
  languages: [],
  regions: [],
  slugsRoutePath: '/api/_categories/slugs',
  categoryRoutePath: '/api/_categories/:slug',
  listRoutePath: '/api/_categories/list',
  mainListRoutePath: '/api/_categories/main',
  refreshSlugsRoutePath: '/api/_categories/refresh/slugs',
  refreshAllRoutePath: '/api/_categories/refresh/all',
  refreshSingleRoutePath: '/api/_categories/refresh/:slug',
  refreshListRoutePath: '/api/_categories/refresh/list',
  refreshMainListRoutePath: '/api/_categories/refresh/main'
}

export default defineNuxtModule<CategoryModuleOptions>({
  meta: {
    name: 'category-module',
    configKey: 'categoryModule'
  },
  defaults: DEFAULT_OPTIONS,
  setup (moduleOptions, nuxt) {
    const resolver = createResolver(import.meta.url)
    const runtimeDir = resolver.resolve('./runtime')

    nuxt.options.build.transpile = nuxt.options.build.transpile || []
    if (!nuxt.options.build.transpile.includes(runtimeDir)) {
      nuxt.options.build.transpile.push(runtimeDir)
    }

    const resolvedOptions = {
      ...DEFAULT_OPTIONS,
      ...moduleOptions,
      ...(nuxt.options.runtimeConfig.categoryModule || {})
    }

    nuxt.options.runtimeConfig.categoryModule = resolvedOptions

    nuxt.options.runtimeConfig.public = nuxt.options.runtimeConfig.public || {}
    nuxt.options.runtimeConfig.public.categoryModule = {
      ...(nuxt.options.runtimeConfig.public.categoryModule || {}),
      slugsRoutePath: resolvedOptions.slugsRoutePath,
      categoryRoutePath: resolvedOptions.categoryRoutePath,
      listRoutePath: resolvedOptions.listRoutePath,
      mainListRoutePath: resolvedOptions.mainListRoutePath,
      refreshMainListRoutePath: resolvedOptions.refreshMainListRoutePath
    }

    nuxt.hook('nitro:config', (nitroConfig) => {
      nitroConfig.runtimeConfig = nitroConfig.runtimeConfig || {}
      nitroConfig.runtimeConfig.categoryModule = {
        ...resolvedOptions,
        ...(nitroConfig.runtimeConfig.categoryModule || {})
      }
      nitroConfig.runtimeConfig.public = nitroConfig.runtimeConfig.public || {}
      nitroConfig.runtimeConfig.public.categoryModule = {
        ...(nitroConfig.runtimeConfig.public.categoryModule || {}),
        slugsRoutePath: resolvedOptions.slugsRoutePath,
        categoryRoutePath: resolvedOptions.categoryRoutePath,
        listRoutePath: resolvedOptions.listRoutePath,
        mainListRoutePath: resolvedOptions.mainListRoutePath,
        refreshMainListRoutePath: resolvedOptions.refreshMainListRoutePath
      }
      nitroConfig.alias = nitroConfig.alias || {}
      nitroConfig.alias['#category-module'] = runtimeDir
    })

    nuxt.options.alias = nuxt.options.alias || {}
    nuxt.options.alias['#category-module'] = runtimeDir

    addServerHandler({
      route: resolvedOptions.slugsRoutePath,
      handler: resolver.resolve('./runtime/server/api/slugs.get')
    })

    addServerHandler({
      route: resolvedOptions.categoryRoutePath,
      handler: resolver.resolve('./runtime/server/api/category.get')
    })

    addServerHandler({
      route: resolvedOptions.listRoutePath,
      handler: resolver.resolve('./runtime/server/api/list.get')
    })

    addServerHandler({
      route: resolvedOptions.mainListRoutePath,
      handler: resolver.resolve('./runtime/server/api/main.get')
    })

    addServerHandler({
      route: resolvedOptions.refreshSlugsRoutePath,
      handler: resolver.resolve('./runtime/server/api/refresh-slugs.post')
    })

    addServerHandler({
      route: resolvedOptions.refreshAllRoutePath,
      handler: resolver.resolve('./runtime/server/api/refresh-all.post')
    })

    addServerHandler({
      route: resolvedOptions.refreshSingleRoutePath,
      handler: resolver.resolve('./runtime/server/api/refresh-single.post')
    })

    addServerHandler({
      route: resolvedOptions.refreshListRoutePath,
      handler: resolver.resolve('./runtime/server/api/refresh-list.post')
    })

    addServerHandler({
      route: resolvedOptions.refreshMainListRoutePath,
      handler: resolver.resolve('./runtime/server/api/refresh-main.post')
    })

    addImports([
      {
        name: 'getCategorySlugs',
        from: resolver.resolve('./runtime/server/utils/cache')
      },
      {
        name: 'getCategoryFromCache',
        from: resolver.resolve('./runtime/server/utils/cache')
      },
      {
        name: 'refreshCategorySlugs',
        from: resolver.resolve('./runtime/server/utils/cache')
      },
      {
        name: 'refreshCategory',
        from: resolver.resolve('./runtime/server/utils/cache')
      },
      {
        name: 'refreshAllCategories',
        from: resolver.resolve('./runtime/server/utils/cache')
      },
      {
        name: 'getCategoryListFromCache',
        from: resolver.resolve('./runtime/server/utils/cache')
      },
      {
        name: 'getCategoryMainListFromCache',
        from: resolver.resolve('./runtime/server/utils/cache')
      },
      {
        name: 'refreshCategoryList',
        from: resolver.resolve('./runtime/server/utils/cache')
      },
      {
        name: 'refreshCategoryMainList',
        from: resolver.resolve('./runtime/server/utils/cache')
      }
    ])
  }
})

export type { CategoryModuleOptions }
