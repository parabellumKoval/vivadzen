import type { FetchInstance } from 'ofetch'

declare module '#app' {
  interface NuxtApp {
    $api: FetchInstance
    $tg?: any
  }
}

declare module 'vue' {
  interface ComponentCustomProperties {
    $api: FetchInstance
    $tg?: any
  }
}

export {}
