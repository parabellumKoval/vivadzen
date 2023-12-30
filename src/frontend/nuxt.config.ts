import dynamicRoutes from './helpers/dynamicRoutes'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  devtools: { enabled: false },
  // Debug
  debug: false,
  appConfig: {},
  runtimeConfig: {
    public: {
      siteUrl: process.env.SITE_URL || 'https://abu.com.ua',
      frontendUrl: process.env.SITE_URL,
      novaposhtaKey: process.env.NOVAPOSHTA_KEY,
      apiBase: process.env.API_SERVER_URL
    }
  },
  app: {
    pageTransition: { name: 'page-tr', mode: 'out-in' },
    layoutTransition: { name: 'layout-tr', mode: 'out-in' },
    head: {
      script: [
        // {
        //   type: 'text/partytown',
        //   innerHTML: `(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        //   new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        //   j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        //   'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        //   })(window,document,'script','dataLayer','${process.env.GTM}');`
        // }
      ],

      noscript: [
        // {
        //   tagPosition: 'bodyOpen',
        //   innerHTML: `<iframe src="https://www.googletagmanager.com/ns.html?id=${process.env.GTM}" height="0" width="0" style="display:none;visibility:hidden"></iframe>`
        // }
      ]
    },
  },

  css: [
    '@/assets/scss/main.scss'
  ],

  modules: [
    [
      '@nuxtjs/supabase',
      {
        redirect: '/'
      }
    ],
    [
      '@nuxtjs/prismic',
      {
        endpoint: 'https://all-be-ukraine.cdn.prismic.io/api/v2',
        preview: false,
        toolbar: false
      }
    ],
    [
      'nuxt-icon',
      {
        class: 'icon'
      }
    ],
    // [
    //   '@nuxtjs/partytown',
    //   {
    //     debug: process.env.NODE_ENV === 'development',
    //     forward: ['dataLayer.push']
    //   }
    // ],
    [
      'nuxt-simple-sitemap',
      {
        enabled: true,
        exclude: [
            '/account/**'
        ],
        defaults: {
          changefreq: 'daily',
          priority: 1,
          lastmod: new Date().toISOString(),
        },
        urls: dynamicRoutes
      }
    ],
    'nuxt-schema-org',
    [
      '@nuxtjs/device',
      {
        refreshOnResize: true
      }
    ],
    // '@nuxtjs/fontaine',
    [
      '@nuxtjs/google-fonts',
      {
        families: {
          Rubik: {
            wght: [300, 400, 500, 700]
          },
        },
        display: 'swap',
        preload: true
      }
    ],
    [
      '@nuxt/image',
      {
        provider: process.env.IMAGE_PROVIDER || "ipx",
        
        screens: {
          mobile: 767,
          tablet: 1023,
          desktop: 1919,
        },
        
        domains: [
          process.env.DOMAIN,
          '*.googleusercontent.com',
          'lh3.googleusercontent.com',
          'images.prismic.io'
        ],
        
        alias: {
          server: process.env.SERVER_URL
        },
        
        dir: process.env.IMAGE_DIR || "public",
        
        vercel: {
          dirname: 'public'
        },

        ipx: {
          domains: [
            process.env.DOMAIN,
            '*.googleusercontent.com',
            'lh3.googleusercontent.com',
            'images.prismic.io'
          ]
        }
      }
    ],
    [
      '@pinia/nuxt',
      {
        autoImports: [
          'defineStore',
        ],
      },
    ],
    '@pinia-plugin-persistedstate/nuxt',
    '@nuxtjs/i18n',
    // [
    //   '@nuxtjs/i18n',
    //   {
    //     baseUrl: 'https://po.ua',
    //     defaultLocale: 'uk',
    //     lazy: true,
    //     langDir: './lang',
    //     locales: [
    //       {
    //         iso: 'uk-UA',
    //         code: 'uk',
    //         file: 'uk.yaml',
    //         name: 'Українська',
    //         shortName: 'UA',
    //       },
    //       {
    //         iso: 'ru-RU',
    //         code: 'ru',
    //         file: 'ru.yaml',
    //         name: 'Русский',
    //         shortName: 'RU',
    //       }
    //     ],
    //     // experimental: {
    //     //   jsTsFormatResource: true,
    //     // },
    //     precompile: {
    //       strictMessage: false
    //     },
    //     vueI18n: './i18n.config.ts'
    //   }
    // ],
    [
      '@nuxt/content', 
      {
        defaultLocale: 'ru',
        locales: ['uk','ru']
      }
    ],
    // '@vueuse/nuxt',
  ],

  experimental: {
    renderJsonPayloads: false,
  },

  // routeRules: {
  //   '/shop/**': { isr: false},
  //   //'/**': { isr: 60 * 30},
  //   '/**': { isr: false},
  //   // pages
  //   '/o-nas': { prerender: true, headers: { 'Cache-Control': 'max-age=31536000, immutable' } },
  //   '/certification': { prerender: true, headers: { 'Cache-Control': 'max-age=31536000, immutable' } },
  //   '/contacts': { prerender: true, headers: { 'Cache-Control': 'max-age=31536000, immutable' } },
  //   '/delivery': { prerender: true, headers: { 'Cache-Control': 'max-age=31536000, immutable' } },
  //   '/guarantees': { prerender: true, headers: { 'Cache-Control': 'max-age=31536000, immutable' } },
  //   '/payment': { prerender: true, headers: { 'Cache-Control': 'max-age=31536000, immutable' } },
  //   '/policy': { prerender: true, headers: { 'Cache-Control': 'max-age=31536000, immutable' } },
  //   '/science': { prerender: true, headers: { 'Cache-Control': 'max-age=31536000, immutable' } },
  //   '/terms': { prerender: true, headers: { 'Cache-Control': 'max-age=31536000, immutable' } },
  //   // api
  //   '/api/**': { swr: false, prerender: false },
  //   // assets
  //   '/assets/**': { headers: { 'Cache-Control': 'max-age=31536000, immutable' } },
  //   '/images/**': { headers: { 'Cache-Control': 'max-age=31536000, immutable' } },
  //   '/_nuxt/**': { headers: { 'Cache-Control': 'max-age=31536000, immutable' } },
  //   '/**/*.js': { headers: { 'Cache-Control': 'max-age=31536000, immutable' } },
  //   '/**/*.css': { headers: { 'Cache-Control': 'max-age=31536000, immutable' } },
  //   '/**/*.json': { headers: { 'Cache-Control': 'max-age=31536000, immutable' } },
  //   '/**/*.html': { headers: { 'Cache-Control': 'max-age=31536000, immutable' } },
  //   '/**/*.xml': { headers: { 'Cache-Control': 'max-age=31536000, immutable' } },
  //   '/**/*.svg': { headers: { 'Cache-Control': 'max-age=31536000, immutable' } },
  // },
  i18n: {
    baseUrl: 'https://po.ua',
    defaultLocale: 'uk',
    lazy: true,
    langDir: './lang',
    locales: [
      {
        iso: 'uk-UA',
        code: 'uk',
        file: 'uk.yaml',
        name: 'Українська',
        shortName: 'Укр',
      },
      {
        iso: 'ru-RU',
        code: 'ru',
        file: 'ru.yaml',
        name: 'Русский',
        shortName: 'Рус',
      }
    ]
  },
  nitro: {
    compressPublicAssets: { 
      gzip: true, 
      brotli: true 
    },
    minify: true,
  },
})
