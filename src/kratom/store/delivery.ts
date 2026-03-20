export const useDeliveryStore = defineStore('deliveryStore', {
  state: () => ({
    quoteState: null as unknown,
    quoteLoading: false,
    quoteErrorState: null as unknown,
  }),
  
  getters: {
    deliveryPrice: (state) => state.quoteState,
    isQuoteLoading: (state) => state.quoteLoading,
    deliveryError: (state) => state.quoteErrorState,
  },

  actions: {
    resetQuote() {
      this.quoteState = null
      this.quoteErrorState = null
    },

    async quote(payload?: Record<string, any> | null) {
      if (!payload) {
        this.resetQuote()
        return null
      }

      this.quoteLoading = true
      this.quoteErrorState = null

      try {
        const runtimeConfig = useRuntimeConfig()
        const url = `${runtimeConfig.public.apiBase}/shipping/quote`

        const { data, error } = await useApiFetch(url, payload, 'POST', { lazy: false, server: false })

        if (error?.value) {
          throw error.value
        }

        const value = data?.value ?? null
        this.quoteState = value
        return value
      } catch (err) {
        this.quoteState = null
        this.quoteErrorState = err
        throw err
      } finally {
        this.quoteLoading = false
      }
    },
  },
})
