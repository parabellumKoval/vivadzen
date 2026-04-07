export const useLiqpayStore = defineStore('liqpayStore', {
  state: () => ({ 
    dataState: {},
  }),
  
  getters: {
    data: (state) => state.dataState,
  },

  actions: {
    async getFormData(data: Object) {
      const url = `${useRuntimeConfig().public.apiBase}/payment/liqpay/create`

      return await useApiFetch(url, {...data}, 'POST')
        .then(({data, error}) => {
          if(data) {
            return data
          }

          if(error)
            throw error
        })
        .catch((e) => {
          throw e
        })
    },
  },
})
