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
    all: (state) => state.allState.data,
    category: (state) => state.categoryState,
  },

  actions: {
    async getAll(query: string) {
      const url = useRuntimeConfig().public.apiBase + '/categories'

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

    async getOne(slug: string) {
      const url = `${useRuntimeConfig().public.apiBase}/categories/${slug}`

      return await useServerApiFetch(url).then(({data, error}) => {

        console.log('getOne', data)
        
        if(data && data.data) {
          //this.categoryState = data
          return data.data
        }
      })

      // $fetch(runtimeConfig.public.apiBase + '/categories/' + slug)
      //   .then(({ data }) => (this.categoryState = data))
      //   .catch((error) => console.log(error));
    },
  },
})