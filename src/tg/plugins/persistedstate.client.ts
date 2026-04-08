import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'

export default defineNuxtPlugin((nuxtApp) => {
  ;(nuxtApp as any).$pinia.use(piniaPluginPersistedstate)
})
