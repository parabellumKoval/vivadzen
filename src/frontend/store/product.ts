import { defineStore } from "pinia";

type ProductSmall = {
  id: number,
  name: string,
  slug: string,
  price: number,
  image: object,
  old_price?: number,
  excerpt: string,
  stimulation?: number,
  relaxation?: number,
  euphoria?: number,
  modifications: object[]
};

type ProductLarge = {
  id: number,
  name: string,
  slug: string,
  price: number,
  images: object[],
  old_price?: number,
  content: string,
  category: object,
  stimulation: number,
  relaxation: number,
  euphoria: number,
  modifications: object[]
};

export const useProductStore = defineStore('productStore', {
  state: () => ({ 
    // allState: {
    //   data: [] as ProductSmall[],
    //   meta: Object
    // },

    // productState: null as ProductLarge | null,

    searchState: {
      data: [] as ProductSmall[],
      meta: Object
    },

    gridData: [] as ProductSmall[]
  }),
  
  getters: {
    // all: (state) => state.allState.data,
    // meta: (state) => state.allState.meta,
    // product: (state) => state.productState,
    found: (state) => state.searchState.data,
    grid: (state) => state.gridData,
  },

  actions: {
    async search(query: string) {
      await this.getAll(query).then(({ data }) => {
        this.searchState.data = data.data
        this.searchState.meta = data.meta
      })
    },

    async getAll(query: Object) {
      const url = useRuntimeConfig().public.apiBase + '/product'
      return await useServerApiFetch(url, query)
    },

    async index(query: Object, refresh: boolean = true) {
      return await this.getAll(query).then(({ data }) => {

        if(!data)
          return

        return {
          products: data.products.data || null,
          filters: data.filters || null,
          meta: data.products.meta || null
        }
      })
    },

    async getMultiple(ids: Number[]) {
      const runtimeConfig = useRuntimeConfig()
      const url = `${runtimeConfig.public.apiBase}/product/ids`;

      return await useServerApiFetch(url, {ids: ids})
        .then(({data, error}) => {

          if(data?.data) {
            return data.data
          }

          if(error)
            throw new Error(error)
        })
    },

    async getRandom(id: Number) {
      const runtimeConfig = useRuntimeConfig()
      const url = `${runtimeConfig.public.apiBase}/products/random?not_id=${id}`;

      return await useApiFetch(url)
        .then(({data, error}) => {

          if(data && data.data) {
            this.gridData = data.data
            return data.data
          }

          if(error)
            throw new Error(error)
        })
    },

    async show(slug: string) {
      const url = `${useRuntimeConfig().public.apiBase}/product/${slug}`;

      return await useServerApiFetch(url).then(({data, error}) => {
        if(data) {
          return data
        }
        
        if(error)
          throw new Error(error)
      })
    },

  },
})