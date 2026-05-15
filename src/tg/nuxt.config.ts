import fs from 'node:fs'
import path from 'node:path'

const initialEnvKeys = new Set(Object.keys(process.env))

const parseEnvValue = (value: string) => {
  const trimmed = value.trim()
  const quote = trimmed[0]

  if ((quote === '"' || quote === "'") && trimmed.endsWith(quote)) {
    return trimmed.slice(1, -1)
  }

  return trimmed
}

const loadEnvFile = (filePath: string, overrideLoadedEnv = false) => {
  if (!fs.existsSync(filePath)) return

  const content = fs.readFileSync(filePath, 'utf8')
  for (const line of content.split(/\r?\n/)) {
    const match = line.match(/^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)\s*$/)
    if (!match) continue

    const [, key, rawValue] = match
    if (initialEnvKeys.has(key)) continue
    if (!overrideLoadedEnv && process.env[key] !== undefined) continue

    process.env[key] = parseEnvValue(rawValue)
  }
}

loadEnvFile(path.resolve(__dirname, '../../.env'))
loadEnvFile(path.resolve(__dirname, '.env'), true)

const HOST = process.env.HOST_IP || 'localhost'
const FONT_STYLESHEET_URL = 'https://fonts.googleapis.com/css2?family=Archivo+Black&family=Space+Grotesk:wght@400;500;600;700&display=swap'
const IS_PRODUCTION = process.env.NODE_ENV === 'production'
const IS_VERCEL = Boolean(process.env.VERCEL)
const IS_DEPLOYMENT_BUILD = IS_PRODUCTION && (IS_VERCEL || Boolean(process.env.CI))
const VERCEL_SITE_URL = process.env.VERCEL_URL ? `https://${process.env.VERCEL_URL}` : ''
const HOST_URL = process.env.NUXT_PUBLIC_SITE_URL
  || process.env.SITE_URL
  || process.env.FRONTEND_URL
  || VERCEL_SITE_URL
  || (IS_PRODUCTION ? '' : `http://${HOST}:3001`)
const SERVER_URL = process.env.SERVER_URL
  || process.env.BACKEND_URL
  || (IS_PRODUCTION || IS_VERCEL ? '' : `http://${HOST}:8000`)
const normalizePublicUrl = (value: string) => value.trim().replace(/\/+$/, '')
const hostnameFromUrl = (value: string) => {
  try {
    return value ? new URL(value).hostname : ''
  } catch {
    return ''
  }
}
const isLoopbackUrl = (value: string) => /^https?:\/\/(?:localhost|127\.0\.0\.1|0\.0\.0\.0|\[?::1\]?)(?::\d+)?(?:\/|$)/i.test(value.trim())
const API_SERVER_URL = normalizePublicUrl(
  process.env.NUXT_PUBLIC_API_BASE
  || process.env.API_SERVER_URL
  || (SERVER_URL ? `${SERVER_URL}/api` : '')
)

if (IS_DEPLOYMENT_BUILD && isLoopbackUrl(API_SERVER_URL)) {
  throw new Error('TG app production build cannot use a localhost API. Set NUXT_PUBLIC_API_BASE to a public HTTPS backend URL.')
}
const IMAGE_DOMAINS = Array.from(new Set([
  hostnameFromUrl(API_SERVER_URL),
  hostnameFromUrl(SERVER_URL),
  hostnameFromUrl(HOST_URL),
  hostnameFromUrl(process.env.NUXT_PUBLIC_IMAGE_CDN || '')
].filter(Boolean)))
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
const parseJsonEnv = (value: string | undefined, fallback: Record<string, unknown>) => {
  if (!value) return fallback

  try {
    const parsed = JSON.parse(value)
    return parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : fallback
  } catch {
    return fallback
  }
}

const TG_HOME_VIBES_DEFAULTS: Record<string, { tag?: string, icon?: string, bg?: string }> = {
  muhomory: { tag: 'AMANITA', icon: 'mushroom', bg: 'magenta' },
  kanabis: { tag: 'GREEN', icon: 'leaf', bg: 'primary' },
  'kava-kava': { tag: 'CHILL', icon: 'heart', bg: 'yellow' },
  kanna: { tag: 'MOOD', icon: 'sparkles', bg: 'lime' },
  akuamma: { tag: 'RELIEF', icon: 'shield', bg: 'white' },
  kaapi: { tag: 'VINE', icon: 'globe', bg: 'accent' },
  'gavayskaya-roza': { tag: 'ROSE', icon: 'flame', bg: 'magenta' },
  koridalis: { tag: 'CALM', icon: 'circle', bg: 'primary' }
}

const parseHomeVibes = (raw: string | undefined) => {
  if (!raw) return TG_HOME_VIBES_DEFAULTS
  try {
    const parsed = JSON.parse(raw)
    if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
      return parsed as Record<string, { tag?: string, icon?: string, bg?: string }>
    }
  } catch (err) {
    console.warn('[tg] Failed to parse NUXT_PUBLIC_TG_HOME_VIBES, using defaults:', err)
  }
  return TG_HOME_VIBES_DEFAULTS
}

const TG_HOME_VIBES = parseHomeVibes(process.env.NUXT_PUBLIC_TG_HOME_VIBES)

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
    ],
    '@nuxt/image'
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
        { name: 'theme-color', content: '#fff5e1' },
        { name: 'robots', content: 'noindex, nofollow, noarchive, nosnippet, noimageindex, notranslate' },
        { name: 'googlebot', content: 'noindex, nofollow, noarchive, nosnippet, noimageindex, notranslate' },
        { name: 'bingbot', content: 'noindex, nofollow, noarchive, nosnippet, noimageindex, notranslate' }
      ],
      link: [
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { rel: 'stylesheet', href: FONT_STYLESHEET_URL }
      ]
    }
  },

  image: {
    provider: 'ipx',
    domains: IMAGE_DOMAINS,
    quality: 82,
    format: ['webp']
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
      packeta: {
        apiKey: process.env.PACKETA_WIDGET_API_KEY || process.env.NUXT_PUBLIC_PACKETA_API_KEY || '',
        language: process.env.NUXT_PUBLIC_PACKETA_LANGUAGE || 'en',
        defaultCountry: process.env.NUXT_PUBLIC_PACKETA_COUNTRY || 'CZ',
        carriers: ['packeta']
      },
      tg: {
        fallbackRegion: 'cz',
        catalog: {
          baseCategoryId: Number.isFinite(TG_BASE_CATEGORY_ID) ? TG_BASE_CATEGORY_ID : null,
          baseCategorySlug: TG_BASE_CATEGORY_SLUG || null,
        },
        homeVibes: TG_HOME_VIBES,
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
