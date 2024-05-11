type Category = {
  id: number,
  name: string,
  slug: string,
  children: object[]
};

export const useCategoryStore = defineStore('categoryStore', {
  state: () => ({ 
    allState: {
      data: [] as Category[],
      meta: Object
    },
    categoryState: null as Category | null,
  }),
  
  getters: {
    list: (state) => state.allState.data,
    category: (state) => state.categoryState,
  },

  actions: {
    async index(query: string) {
      const url = useRuntimeConfig().public.apiBase + '/category'

      return await useServerApiFetch(url, query).then(({data, error}) => {

        if(data) {
          this.allState.data = data.data
          this.allState.meta = data.meta

          return this.allState
        }

        if(error)
          throw error
      })
    },

    async show(slug: string) {
      const url = `${useRuntimeConfig().public.apiBase}/category/${slug}`

      return await useServerApiFetch(url).then(({data, error}) => {
        
        if(data && data.data) {
          return data.data
        }
      })
    },
  },
})