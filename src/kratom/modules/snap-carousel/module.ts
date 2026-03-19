import { defineNuxtModule, createResolver, addComponent, addPlugin, addImportsDir } from '@nuxt/kit'

export type ScreenMap = Record<string, number> // { xs:0, sm:640, md:768, ... } — min-width в px

export interface SnapCarouselDefaults {
  mode?: 'page' | 'item'
  loop?: boolean
  /** gap может быть числом/строкой ИЛИ responsive-объектом по брейкпоинтам */
  gap?: number | string | Record<string, number | string>
  showArrows?: boolean
  showDots?: boolean
  snapStop?: 'normal' | 'always'
  itemsPerPage?: Record<string, number> // { xs:1, sm:2, md:3, lg:4 }
}

export interface SnapCarouselOptions {
  screens?: ScreenMap
  defaults?: SnapCarouselDefaults
}

const DEFAULT_SCREENS: ScreenMap = {
  xs: 0,
  sm: 640,
  md: 768,
  lg: 1024,
  xl: 1280,
  '2xl': 1536
}

const DEFAULTS: Required<Omit<SnapCarouselDefaults, 'gap'>> & { gap: SnapCarouselDefaults['gap'] } = {
  mode: 'page',
  loop: true,
  gap: 16,
  showArrows: true,
  showDots: true,
  snapStop: 'normal',
  itemsPerPage: { xs: 1, sm: 2, md: 3, lg: 4, xl: 4, '2xl': 5 }
}

function merge<T extends object>(base: T, extra?: Partial<T>): T {
  return Object.assign({}, base, extra || {})
}

export default defineNuxtModule<SnapCarouselOptions>({
  meta: {
    name: 'snap-carousel',
    configKey: 'snapCarousel'
  },
  defaults: {
    screens: DEFAULT_SCREENS,
    defaults: DEFAULTS
  },
  setup (options, nuxt) {
    const resolver = createResolver(import.meta.url)

    const pub = (nuxt.options.runtimeConfig.public as any)
    pub.snapCarousel = {
      screens: merge(DEFAULT_SCREENS, options.screens),
      defaults: {
        ...DEFAULTS,
        ...options.defaults,
        itemsPerPage: merge(DEFAULTS.itemsPerPage, options.defaults?.itemsPerPage),
        gap: options.defaults?.gap ?? DEFAULTS.gap
      }
    }

    nuxt.options.css = nuxt.options.css || []
    // nuxt.options.css.push(resolver.resolve('runtime/styles.css'))

    addPlugin(resolver.resolve('runtime/plugin'))
    addImportsDir(resolver.resolve('runtime/composables'))

    addComponent({ name: 'SnapCarousel', filePath: resolver.resolve('runtime/components/SnapCarousel.vue') })
    addComponent({ name: 'SnapSlide', filePath: resolver.resolve('runtime/components/SnapSlide.vue') })

    nuxt.hook('prepare:types', ({ references }) => {
      references.push({ path: resolver.resolve('runtime/types.d.ts') })
    })
  }
})
