// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2026-05-01',
  devtools: { enabled: true },
  srcDir: '.',

  dir: {
    app: '.',
    assets: 'assets',
    layouts: 'layouts',
    middleware: 'middleware',
    pages: 'pages',
  },

  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt',
    'pinia-plugin-persistedstate/nuxt',
    '@vueuse/nuxt',
  ],

  app: {
    head: {
      title: 'Vivadzen Admin',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'robots', content: 'noindex, nofollow' },
      ],
    },
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE ?? 'http://localhost:8002/admin-api',
      siteBase: process.env.NUXT_PUBLIC_SITE_BASE ?? 'http://localhost:8002',
    },
  },

  // Лёгкий SPA для админки (SSR не нужен, экономим ресурсы)
  ssr: false,

  imports: {
    dirs: ['stores'],
  },

  typescript: {
    strict: true,
  },
})
