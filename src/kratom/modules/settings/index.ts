import { defineNuxtModule, addPlugin, createResolver, addServerHandler, addImports } from '@nuxt/kit'

export interface SettingsModuleOptions {
  /**
   * Absolute URL to Laravel settings endpoint
   * e.g. http://localhost:8000/api/settings
   */
  apiUrl: string,
  /**
   * Enable automatic refresh based on TTL (default true)
   */
  enableTtl?: boolean,
  /**
   * Cache TTL in seconds (default 300 = 5 min)
   */
  ttlSec?: number,
  /**
   * Route for manual cache refresh
   * POST /api/_refresh-settings by default
   */
  refreshRoutePath?: string,
  /**
   * Regions to prefetch settings for
   */
  regions?: string[],
  /**
   * Locales to prefetch settings for (per region)
   */
  locales?: string[]
}

export default defineNuxtModule<SettingsModuleOptions>({
  meta: {
    name: 'settings',
    configKey: 'settingsModule'
  },
  defaults: {
    apiUrl: 'http://localhost:8000/api/settings',
    enableTtl: true,
    ttlSec: 300,
    refreshRoutePath: '/api/_refresh-settings',
    regions: [],
    locales: []
  },
  setup (options, nuxt) {
    const resolver = createResolver(import.meta.url)

    const resolvedOptions = {
      ...options,
      ...(nuxt.options.runtimeConfig.settingsModule || {})
    }
    nuxt.options.runtimeConfig.settingsModule = resolvedOptions
    nuxt.hook('nitro:config', (nitroConfig) => {
      nitroConfig.runtimeConfig = nitroConfig.runtimeConfig || {}
      nitroConfig.runtimeConfig.settingsModule = {
        ...resolvedOptions,
        ...(nitroConfig.runtimeConfig.settingsModule || {})
      }
    })

    // Register server middleware to inject settings onto event.context
    addServerHandler({
      handler: resolver.resolve('./runtime/server/middleware/inject-settings'),
      middleware: true
    })

    // Register API endpoint for manual refresh (POST)
    addServerHandler({
      route: options.refreshRoutePath || '/api/_refresh-settings',
      handler: resolver.resolve('./runtime/server/api/refresh-settings.post')
    })

    // Register plugin to expose $settings / $getSetting
    addPlugin(resolver.resolve('./runtime/plugins/settings'))

    // Auto-import composable useSettings()
    addImports({
      name: 'useSettings',
      from: resolver.resolve('./runtime/composables/useSettings')
    })
  }
})
