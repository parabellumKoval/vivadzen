import { defineStore } from "pinia";


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
  }),
  
  getters: {
  },

  actions: {

    indexLazy(query: Object, options: Object) {
      const url = useRuntimeConfig().public.apiBase + '/product'
      const allOptions = {
        lazy: true,
        ...options
      }

      return useApiFetch(url, query, 'GET', allOptions)
    },

    async catalogFilters(query: Object) {
      const url = useRuntimeConfig().public.apiBase + '/product/catalog'
      return await useApiFetch(url, query, 'GET', {lazy: false}).then(({data}) => {
        return data.value
      })
    },

    async prices(query: Object) {
      const url = useRuntimeConfig().public.apiBase + '/product/prices'
      return await useApiFetch(url, query, 'GET', {lazy: false}).then(({data}) => {
        return data.value
      })
    },

    async catalog(query: Object) {
      const url = useRuntimeConfig().public.apiBase + '/catalog'
      return await useApiFetch(url, query, 'GET', {lazy: false})
    },

    async index(query: Object) {
      const url = useRuntimeConfig().public.apiBase + '/product'
      return await useApiFetch(url, query, 'GET', {lazy: false})
    },

    async getAll(query: Object) {
      const url = useRuntimeConfig().public.apiBase + '/product'
      return await useApiFetch(url, query, 'GET', {lazy: true})
    },

    async lists(query: Object, page: String) {
      const url = useRuntimeConfig().public.apiBase + `/lists/${page}`
      return await useApiFetch(url, query, 'GET', {lazy: false})
    },

    // async index(query: Object) {
    //   return await this.getAll(query).then(({ data }) => {

    //     if(!data)
    //       return

    //     return {
    //       products: data.products.data || null,
    //       filters: data.filters || null,
    //       meta: data.products.meta || null
    //     }
    //   })
    // },

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
            return data.data
          }

          if(error)
            throw new Error(error)
        })
    },

    async show(slug: string) {
      const url = `${useRuntimeConfig().public.apiBase}/catalog/${slug}`;

      return await useApiFetch(url, null, 'GET', {lazy: false})
    },

  },
})