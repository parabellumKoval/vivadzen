import path from 'path'

const HOST = process.env.HOST_IP || 'localhost'
const SITE_URL = process.env.SITE_URL || (process.env.NODE_ENV === 'production' ? `https://${HOST}` : `http://${HOST}:3001`)
const SERVER_URL = process.env.SERVER_URL || `http://${HOST}:8000`
const API_SERVER_URL = process.env.API_SERVER_URL || `${SERVER_URL}/api`
const DOMAIN = process.env.DOMAIN || `${HOST}:8000`

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
        region: 'cz',
        currency: 'CZK',
        locales: ['cs', 'en', 'de', 'es'],
        defaultLocale: 'cs',
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
        allow: [process.cwd()],
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

  categoryModule: {
    slugsEndpoint: '/company-category/slugs-simple',
    detailsEndpoint: '/category_cached/:slug',
    listEndpoint: '/category',
    mainListEndpoint: '/category/main',
    enableTtl: false,
    ttlSec: 3600,
    languages: ['cs', 'en', 'de', 'es'],
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
    locales: ['cs', 'en', 'de', 'es'],
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
        iso: 'de-DE',
        language: 'de-DE',
        code: 'de',
        file: 'de.yaml',
        name: 'Deutsch',
        shortName: 'Deu',
      },
      {
        iso: 'es-ES',
        language: 'es-ES',
        code: 'es',
        file: 'es.yaml',
        name: 'Español',
        shortName: 'Esp',
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

  content: {
    defaultLocale: 'cs',
    locales: ['cs', 'en', 'de', 'es'],
    navigation: false,
  },

  nitro: {
    storage: {
      cache: {
        driver: 'memory',
      },
    },
    routeRules: {
      '/checkout': { ssr: false, static: false, swr: false, delayHydration: false },
      '/checkout/**': { ssr: false, static: false, swr: false, delayHydration: false },
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
