import type { SnapCarouselOptions } from '../module'

declare module 'nuxt/schema' {
  interface NuxtConfig {
    snapCarousel?: SnapCarouselOptions
  }
  interface NuxtOptions {
    snapCarousel?: SnapCarouselOptions
  }
}

export {}
