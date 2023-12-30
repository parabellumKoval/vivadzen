export const useLiqpayStore = defineStore('liqpayStore', {
  state: () => ({ 
    dataState: {},
  }),
  
  getters: {
    data: (state) => state.dataState,
  },

  actions: {
    async getFormData(data: Object) {
      console.log('getFormData', data)
      const url = `${useRuntimeConfig().public.apiBase}/liqpay/form`

      return await useApiFetch(url, {...data}, 'POST')
        .then(({data, error}) => {
          if(data) {
            console.log('r', data)
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