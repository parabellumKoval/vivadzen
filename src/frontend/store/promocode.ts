type Promocode = {
  id: number,
  email: string,
  fullname: string,
  photo: string
};

export const usePromocodeStore = defineStore('promocodeStore', {
  state: () => ({}),
  
  getters: {},

  actions: {
    async show(code: string) {
      const url = `${useRuntimeConfig().public.apiBase}/promocode/${code}/find-and-check`

      return await useServerApiFetch(url)
        .then(({data, error}) => {
          if(data) {
            return data
          }

          if(error) {
            throw error
          }
        })
    },

    async index(query: Object) {
      const params = query? '?' + new URLSearchParams(query).toString(): '';
      const url = `${useRuntimeConfig().public.apiBase}/promocode${params}`

      return await useServerApiFetch(url)
        .then(({data, error}) => {
          if(data) {
            return data
          }

          if(error) {
            throw error
          }
        })
    }

  },
})