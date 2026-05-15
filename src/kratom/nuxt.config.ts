import path from 'path'
import { buildSitemapsOptions } from './utils/sitemap'

const HOST = process.env.HOST_IP || 'localhost'
const SITE_URL = process.env.SITE_URL || (process.env.NODE_ENV === 'production' ? `https://${HOST}` : `http://${HOST}:3001`)
const SERVER_URL = process.env.SERVER_URL || `http://${HOST}:8000`
const API_SERVER_URL = process.env.API_SERVER_URL || `${SERVER_URL}/api`
const DOMAIN = process.env.DOMAIN || `${HOST}:8000`
const parseBooleanEnv = (value: string | undefined, defaultValue: boolean) => {
  if (value == null || value.trim() === '') {
    return defaultValue
  }

  const normalized = value.trim().toLowerCase()

  if (['true', '1', 'yes', 'on'].includes(normalized)) {
    return true
  }

  if (['false', '0', 'no', 'off'].includes(normalized)) {
    return false
  }

  return defaultValue
}

const parseIntegerEnv = (value: string | undefined) => {
  if (value == null || value.trim() === '') {
    return null
  }

  const parsed = Number.parseInt(value.trim(), 10)

  return Number.isFinite(parsed) ? parsed : null
}

export default defineNuxtConfig({
  srcDir: process.env.SRC_DIR || '',
  rootDir: process.env.ROOT_DIR || '',
  devtools: { enabled: false },
  logLevel: 'info',
  debug: process.env.NODE_ENV === 'development',

  runtimeConfig: {
    public: {
      site: {
        url: SITE_URL,
      },
      siteUrl: SITE_URL,
      frontendUrl: SITE_URL,
      serverBase: SERVER_URL,
      apiBase: API_SERVER_URL,
      storefrontCode: 'kratom',
      kratomCheckoutAgeVerificationEnabled: parseBooleanEnv(process.env.NUXT_PUBLIC_KRATOM_CHECKOUT_AGE_VERIFICATION_ENABLED, true),
      adultoPublicKey: process.env.NUXT_PUBLIC_ADULTO_PUBLIC_KEY || '',
      adultoWidgetScriptUrl: process.env.NUXT_PUBLIC_ADULTO_WIDGET_SCRIPT_URL || 'https://api.js.m2a.cz/api.js',
      imagesDir: '/server/uploads/images',
      noimage: '/images/noimage.png',
      noimagegray: '/images/noimagegray.png',
      noimageTransparent: '/images/noimage-transparent.png',
      staticImageProvider: process.env.STATIC_IMAGE_PROVIDER,
      appVersion: '1.0.0',
      landingPromoSubscribe: {
        feedbackType: process.env.LANDING_PROMO_FEEDBACK_TYPE || 'landing_kratom_local_sale',
      },
      kratomStore: {
        categorySlug: 'kratom',
        catalogParentCategorySlug: process.env.NUXT_PUBLIC_KRATOM_CATALOG_PARENT_CATEGORY_SLUG || '',
        catalogParentCategoryId: parseIntegerEnv(process.env.NUXT_PUBLIC_KRATOM_CATALOG_PARENT_CATEGORY_ID),
        region: 'cz',
        currency: 'CZK',
        locales: ['cs', 'en', 'ru', 'uk'],
        defaultLocale: 'cs',
        productResources: {
          card: 'kratom_small',
          detail: 'kratom_large',
        },
        badgeAttributes: {
          color: {
            slugs: ['color', 'kratom-color', 'kratom-colour'],
            nameHints: ['color', 'colour', 'barva', 'цвет', 'колір'],
          },
          origin: {
            slugs: ['origin', 'region', 'kratom-origin', 'kratom-region'],
            nameHints: ['origin', 'region', 'puvod', 'původ', 'происх', 'походжен', 'regi'],
          },
        },
        badgeImages: {
          color: {
            red: '/images/kratom-types/red.png',
            green: '/images/kratom-types/green.png',
            white: '/images/kratom-types/white.png',
            gold: '/images/kratom-types/gold.png',
            yellow: '/images/kratom-types/gold.png',
          },
          origin: {
            maengda: '/images/kratom-types/circle3-1.png',
            'maeng-da': '/images/kratom-types/circle3-1.png',
            malay: '/images/kratom-types/circle3-2.png',
            thai: '/images/kratom-types/circle3-3.png',
            borneo: '/images/kratom-types/circle3-4.png',
            sumatra: '/images/kratom-types/circle3-5.png',
          },
        },
      },
    },
  },

  devServer: {
    port: Number(process.env.PORT || 3001),
    host: '0.0.0.0',
  },

  imports: {
    dirs: [
      'composables',
      'composables/campaign',
      'composables/product',
      'composables/form',
      'store',
    ],
  },

  app: {
    head: {
      templateParams: {
        siteName: 'VivaDzen Kratom',
        separator: '-',
      },
      link: [
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=Onest:wght@300;400;500;600;700;900&family=Cormorant+Garamond:wght@500;600;700&display=swap' },
      ],
    },
  },

  css: ['@/assets/scss/main.scss'],

  vite: {
    resolve: {
      alias: {
        lang: path.resolve(__dirname, './lang'),
      },
      preserveSymlinks: false,
    },
    css: {
      preprocessorOptions: {
        scss: {
          additionalData: `
            @use "@/assets/scss/vars" as *;
            @use "@/assets/scss/mixins" as *;
          `,
        },
      },
    },
    server: {
      fs: {
        strict: true,
        allow: [
          process.cwd(),
          __dirname,
          path.resolve(__dirname, '..'),
          path.resolve(__dirname, '../front/node_modules'),
        ],
      },
      watch: {
        ignored: [
          '**/.git/**',
          '**/.nuxt/**',
          '**/dist/**',
          '**/node_modules/**',
          '**/coverage/**',
          '**/.turbo/**',
          '**/.next/**',
          '**/*.log',
        ],
      },
    },
  },

  svgo: {
    defaultImport: 'component',
    svgoConfig: {
      plugins: [
        {
          name: 'preset-default',
          params: {
            overrides: {
              removeViewBox: false,
              cleanupIds: false,
            },
          },
        },
      ],
    },
  },

  modules: [
    '@zadigetvoltaire/nuxt-gtm',
    'nuxt-svgo',
    './modules/auth-bridge',
    './modules/settings',
    './modules/category',
    './modules/wrapperHtml',
    './modules/snap-carousel',
    './modules/packeta',
    'nuxt-anchorscroll',
    [
      'nuxt-icon',
      {
        class: 'icon',
      },
    ],
    [
      'nuxt-delay-hydration',
      {
        mode: 'init',
        debug: process.env.NODE_ENV === 'development',
      },
    ],
    '@nuxtjs/device',
    '@nuxtjs/fontaine',
    [
      '@nuxt/image',
      {
        provider: process.env.IMAGE_PROVIDER || 'ipx',
        quality: 60,
        screens: {
          mobile: 767,
          tablet: 1023,
          desktop: 1919,
          xl: 2540,
        },
        domains: [
          DOMAIN,
          '*.vivadzen.com',
          'api.vivadzen.com',
          'vivadzen.com',
          'localhost:8000',
          '*.googleusercontent.com',
          'lh3.googleusercontent.com',
          'images.prismic.io',
          '*.cdninstagram.com',
          '*.cloudinary.com',
          '*.fbsbx.com',
        ],
        alias: {
          server: SERVER_URL,
        },
        dir: process.env.IMAGE_DIR || 'public',
        vercel: {
          dirname: 'public',
        },
        bunny: {
          // baseURL: 'https://vivadzen.b-cdn.net',
        },
        ipx: {
          domains: [
            DOMAIN,
            '*.vivadzen.com',
            'api.vivadzen.com',
            'vivadzen.com',
            'localhost:8000',
            '*.googleusercontent.com',
            'lh3.googleusercontent.com',
            'images.prismic.io',
            '*.cdninstagram.com',
            '*.fbsbx.com',
          ],
        },
      },
    ],
    [
      '@pinia/nuxt',
      {
        autoImports: ['defineStore'],
      },
    ],
    '@nuxtjs/i18n',
    '@nuxt/content',
    'nuxt-schema-org',
    '@nuxtjs/sitemap',
    '@nuxtjs/robots',
  ],

  experimental: {
    renderJsonPayloads: false,
    appManifest: false,
  },

  packeta: {
    widgetApiKey: process.env.PACKETA_WIDGET_API_KEY,
    language: 'cs',
    defaultCountry: 'CZ',
    carriers: ['packeta'],
  },

  snapCarousel: {
    screens: {
      mobile: 0,
      tablet: 768,
      desktop: 1024,
      ld: 1441,
      xld: 1920,
    },
    defaults: {
      mode: 'page',
      loop: true,
      gap: { mobile: 10, tablet: 10, desktop: 15, ld: 15, xld: 15 },
      showArrows: true,
      showDots: true,
      snapStop: 'normal',
      itemsPerPage: { mobile: 2, tablet: 4, desktop: 5, ld: 6, xld: 7 },
    },
  },

  categoryModule: {
    slugsEndpoint: '/company-category/slugs-simple',
    detailsEndpoint: '/category_cached/:slug',
    listEndpoint: '/category',
    mainListEndpoint: '/category/main',
    enableTtl: false,
    ttlSec: 3600,
    languages: ['cs', 'en', 'ru', 'uk'],
    regions: ['cz'],
    slugsRoutePath: '/api/_categories/slugs',
    categoryRoutePath: '/api/_categories/:slug',
    listRoutePath: '/api/_categories/list',
    mainListRoutePath: '/api/_categories/main',
    refreshMainListRoutePath: '/api/_categories/refresh/main',
    refreshSlugsRoutePath: '/api/_categories/refresh/slugs',
    refreshAllRoutePath: '/api/_categories/refresh/all',
    refreshSingleRoutePath: '/api/_categories/refresh/:slug',
    refreshListRoutePath: '/api/_categories/refresh/list',
  },

  settingsModule: {
    apiUrl: `${API_SERVER_URL}/settings/nested`,
    enableTtl: false,
    ttlSec: 1800,
    refreshRoutePath: '/api/_refresh-settings',
    regions: ['cz'],
    locales: ['cs', 'en', 'ru', 'uk'],
  },

  gtm: {
    id: process.env.GTM,
    defer: true,
    compatibility: true, 
  },

  authBridge: {
    tokenCookieName: 'auth_token',
    endpoints: {
      me: '/auth/me',
      login: '/auth/login',
      logout: '/auth/logout',
      register: '/auth/register',
      forgot: '/auth/password/forgot',
      reset: '/auth/password/reset',
      resendLoggedIn: '/auth/email/verification-notification',
      resendByEmail: '/auth/email/resend',
      changePassword: '/auth/password/change',
      profileUpdate: '/profile/update',
      profileEmailChange: '/auth/email/change',
      socialUrl: '/auth/oauth/:provider/url',
      validateReferralCode: '/auth/referral/validate/:code',
      referrals: '/auth/referral/all',
      wallet: '/profile/wallet/ledger',
    },
    heartbeat: { enabled: true, intervalMs: 300000 },
  },

  i18n: {
    baseUrl: SITE_URL,
    compilation: {
      strictMessage: false,
    },
    defaultLocale: 'cs',
    lazy: true,
    strategy: 'prefix_except_default',
    langDir: '../lang',
    vueI18n: '../i18n.config.ts',
    detectBrowserLanguage: false,
    locales: [
      {
        iso: 'cs-CZ',
        language: 'cs-CZ',
        code: 'cs',
        file: 'cs.yaml',
        name: 'Čeština',
        shortName: 'Čes',
      },
      {
        iso: 'en-US',
        language: 'en-US',
        code: 'en',
        file: 'en.yaml',
        name: 'English',
        shortName: 'Eng',
      },
      {
        iso: 'ru-RU',
        language: 'ru-RU',
        code: 'ru',
        file: 'ru.yaml',
        name: 'Русский',
        shortName: 'Рус',
      },
      {
        iso: 'uk-UA',
        language: 'uk-UA',
        code: 'uk',
        file: 'uk.yaml',
        name: 'Українська',
        shortName: 'Укр',
      },
    ],
    experimental: {
      autoImportTranslationFunctions: true,
    },
    bundle: {
      optimizeTranslationDirective: false,
    },
  },

  schemaOrg: {
    enabled: true,
  },

  robots: {
    autoI18n: false,
    disableNuxtContentIntegration: true,
    disallow: [
      '/_ipx',
      '/.well-known',
      '/api/**',
      '/checkout/**',
      '/en/checkout/**',
      '/ru/checkout/**',
      '/uk/checkout/**',
      '/*?*gclid=*',
      '/*?*fbclid=*',
      '/*?*utm_*',
    ],
  },

  sitemap: {
    enabled: true,
    siteUrl: SITE_URL,
    cacheMaxAgeSeconds: 3600,

    exclude: [
      '/checkout/**',
      '/auth/**',
    ],

    defaults: {
      changefreq: 'daily',
      priority: 1,
      lastmod: new Date().toISOString(),
    },

    sitemaps: buildSitemapsOptions(),
  },

  content: {
    defaultLocale: 'cs',
    locales: ['cs', 'en', 'ru', 'uk'],
    navigation: false,
  },

  nitro: {
    storage: {
      cache: {
        driver: 'memory',
      },
    },
    routeRules: {
      '/api/_backend': { proxy: API_SERVER_URL },
      '/api/_backend/**': { proxy: `${API_SERVER_URL}/**` },
      '/account': { ssr: false, static: false, swr: false, delayHydration: false },
      '/account/**': { ssr: false, static: false, swr: false, delayHydration: false },
      '/cs/account': { ssr: false, static: false, swr: false, delayHydration: false },
      '/cs/account/**': { ssr: false, static: false, swr: false, delayHydration: false },
      '/en/account': { ssr: false, static: false, swr: false, delayHydration: false },
      '/en/account/**': { ssr: false, static: false, swr: false, delayHydration: false },
      '/ru/account': { ssr: false, static: false, swr: false, delayHydration: false },
      '/ru/account/**': { ssr: false, static: false, swr: false, delayHydration: false },
      '/uk/account': { ssr: false, static: false, swr: false, delayHydration: false },
      '/uk/account/**': { ssr: false, static: false, swr: false, delayHydration: false },
      '/checkout': { ssr: false, static: false, swr: false, delayHydration: false },
      '/checkout/**': { ssr: false, static: false, swr: false, delayHydration: false },
      '/cs/checkout': { ssr: false, static: false, swr: false, delayHydration: false },
      '/cs/checkout/**': { ssr: false, static: false, swr: false, delayHydration: false },
      '/en/checkout': { ssr: false, static: false, swr: false, delayHydration: false },
      '/en/checkout/**': { ssr: false, static: false, swr: false, delayHydration: false },
      '/ru/checkout': { ssr: false, static: false, swr: false, delayHydration: false },
      '/ru/checkout/**': { ssr: false, static: false, swr: false, delayHydration: false },
      '/uk/checkout': { ssr: false, static: false, swr: false, delayHydration: false },
      '/uk/checkout/**': { ssr: false, static: false, swr: false, delayHydration: false },
      '/_ipx/**': { headers: { 'Cache-Control': 'max-age=31536000, immutable' } },
      '/assets/**': { headers: { 'Cache-Control': 'max-age=31536000, immutable' } },
      '/images/**': { headers: { 'Cache-Control': 'max-age=31536000, immutable' } },
      '/_nuxt/**': { headers: { 'Cache-Control': 'max-age=31536000, immutable' } },
    },
    compressPublicAssets: {
      gzip: true,
      brotli: true,
    },
    prerender: {
      crawlLinks: false,
      routes: [],
      failOnError: false,
    },
  },

  compatibilityDate: '2025-02-01',
})
