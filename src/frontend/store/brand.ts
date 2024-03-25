export const useBrandStore = defineStore('brandStore', {
  state: () => ({}),
  
  getters: {},

  actions: {
    async index(params: Object) {
      const url = useRuntimeConfig().public.apiBase + '/brand'

      return await useServerApiFetch(url, params).then(({data, error}) => {
        if(data.data) {
          return data.data
        }

        if(error)
          throw error
      })
    },

    async show(slug: string) {
      const url = `${useRuntimeConfig().public.apiBase}/brand/${slug}`

      return await useServerApiFetch(url).then(({data, error}) => {
        if(data.data) {
          return data.data
        }

        if(error)
          throw error
      })
    },
  },
})