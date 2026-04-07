export const usePaymentStore = defineStore('paymentStore', {
  actions: {
    async createPayment(provider: string, data: Object) {
      const url = `${useRuntimeConfig().public.apiBase}/payment/${provider}/create`

      return await useApiFetch(url, { ...data }, 'POST')
        .then(({ data, error }) => {
          if (data) {
            return data
          }

          if (error) {
            throw error
          }
        })
        .catch((e) => {
          throw e
        })
    },
  },
})
