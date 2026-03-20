export const useArticleStore = defineStore('articleStore', {
  state: () => ({}),
  
  getters: {},

  actions: {
    async indexLazy(params: Object) {
      const url = useRuntimeConfig().public.apiBase + '/articles'
      return await useApiFetch(url, params, 'GET', {lazy: true})
    },

    async index(params: Object) {
      const url = useRuntimeConfig().public.apiBase + '/articles'

      return await useApiFetch(url, params).then(({data, error}) => {
        if(data.value) {
          return data.value
        }

        if(error.value)
          throw error.value
      })
    },

    async show(slug: string) {
      const url = `${useRuntimeConfig().public.apiBase}/articles/${slug}`

      return await useApiFetch(url).then(({data, error}) => {
        if(data.value) {
          return data.value
        }

        if(error.value)
          throw error.value
      })
    },
  },
})