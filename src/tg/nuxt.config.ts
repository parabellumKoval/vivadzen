import path from 'node:path'

const HOST = process.env.HOST_IP || 'localhost'
const IS_PRODUCTION = process.env.NODE_ENV === 'production'
const IS_VERCEL = Boolean(process.env.VERCEL)
const VERCEL_SITE_URL = process.env.VERCEL_URL ? `https://${process.env.VERCEL_URL}` : ''
const HOST_URL = process.env.NUXT_PUBLIC_SITE_URL
  || process.env.SITE_URL
  || VERCEL_SITE_URL
  || (IS_PRODUCTION ? '' : `http://${HOST}:3001`)
const SERVER_URL = process.env.SERVER_URL || (IS_PRODUCTION || IS_VERCEL ? '' : `http://${HOST}:8000`)
const normalizePublicUrl = (value: string) => value.trim().replace(/\/+$/, '')
const isLoopbackUrl = (value: string) => /^https?:\/\/(?:localhost|127\.0\.0\.1|0\.0\.0\.0|\[?::1\]?)(?::\d+)?(?:\/|$)/i.test(value.trim())
const API_SERVER_URL = normalizePublicUrl(
  process.env.NUXT_PUBLIC_API_BASE
  || process.env.API_SERVER_URL
  || (SERVER_URL ? `${SERVER_URL}/api` : '')
)

if (IS_PRODUCTION && isLoopbackUrl(API_SERVER_URL)) {
  throw new Error('TG app production build cannot use a localhost API. Set NUXT_PUBLIC_API_BASE to a public HTTPS backend URL.')
}
const FRONT_DIR = path.resolve(__dirname, '../front')

const REGIONS = {
  global: { name: 'Global', locale: 'en', currency: 'USD' },
  ua: { name: 'Ukraine', locale: 'uk', currency: 'UAH' },
  cz: { name: 'Czechia', locale: 'cs', currency: 'CZK' },
  de: { name: 'Germany', locale: 'de', currency: 'EUR' },
  es: { name: 'Spain', locale: 'es', currency: 'EUR' }
}

const LOCALES_BY_REGION = {
  global: ['en', 'de', 'es', 'ru', 'uk', 'cs'],
  ua: ['uk', 'ru'],
  cz: ['cs', 'en', 'ru', 'uk'],
  de: ['de', 'en', 'ru', 'uk'],
  es: ['es', 'en', 'ru', 'uk']
}

const TG_BASE_CATEGORY_ID = process.env.NUXT_PUBLIC_TG_BASE_CATEGORY_ID
  ? Number.parseInt(process.env.NUXT_PUBLIC_TG_BASE_CATEGORY_ID, 10)
  : 118
const TG_BASE_CATEGORY_SLUG = (process.env.NUXT_PUBLIC_TG_BASE_CATEGORY_SLUG || 'basic').trim()

export default defineNuxtConfig({
  ssr: false,

  srcDir: '',
  devtools: { enabled: false },

  modules: [
    [
      '@pinia/nuxt',
      {
        autoImports: ['defineStore']
      }
    ]
  ],

  css: ['~/assets/tg.css'],

  components: [
    {
      path: '~/components',
      pathPrefix: false
    }
  ],

  alias: {
    '@front': FRONT_DIR
  },

  imports: {
    dirs: ['composables']
  },

  app: {
    head: {
      title: 'Vivadzen',
      meta: [
        { name: 'viewport', content: 'width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover' },
        { name: 'theme-color', content: '#fff7ed' },
        { name: 'robots', content: 'noindex, nofollow, noarchive, nosnippet, noimageindex, notranslate' },
        { name: 'googlebot', content: 'noindex, nofollow, noarchive, nosnippet, noimageindex, notranslate' },
        { name: 'bingbot', content: 'noindex, nofollow, noarchive, nosnippet, noimageindex, notranslate' }
      ]
    }
  },

  routeRules: {
    '/**': {
      headers: {
        'X-Robots-Tag': 'noindex, nofollow, noarchive, nosnippet, noimageindex, notranslate'
      }
    }
  },

  runtimeConfig: {
    public: {
      siteUrl: HOST_URL,
      apiBase: API_SERVER_URL,
      storefrontCode: process.env.NUXT_PUBLIC_STOREFRONT_CODE || 'telegram',
      noimage: '/images/noimage.png',
      tg: {
        fallbackRegion: 'cz',
        catalog: {
          baseCategoryId: Number.isFinite(TG_BASE_CATEGORY_ID) ? TG_BASE_CATEGORY_ID : null,
          baseCategorySlug: TG_BASE_CATEGORY_SLUG || null,
        },
        regionAliases: {
          global: 'zz'
        },
        regions: REGIONS,
        localesByRegion: LOCALES_BY_REGION
      }
    }
  },

  vite: {
    resolve: {
      alias: {
        '@front': FRONT_DIR
      }
    },
    server: {
      fs: {
        allow: [__dirname, FRONT_DIR]
      }
    }
  },

  devServer: {
    port: 3001,
    host: '0.0.0.0'
  },

  typescript: {
    strict: false,
    typeCheck: false
  },

  compatibilityDate: '2025-12-20'
})
