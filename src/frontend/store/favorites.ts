import { defineStore } from "pinia";

export const useFavoritesStore = defineStore('favoritesStore', {
  state: () => ({ 
    idsState: [],
  }),

  getters: {
    ids: (state) => state.idsState,
  },


  actions: {

    async sync(query: object) {
      const url = useRuntimeConfig().public.apiBase + '/favorites/sync'
      return await useServerApiFetch(url, query).then(({ data, error }) => {
        if(error) {
          throw error
        }

        if(data) {
          if(data.type === 'remove') {
            const issetIndex = this.idsState.indexOf(data.id)

            if (issetIndex !== -1) {
              this.idsState.splice(issetIndex, 1);
            }
            return false
          }else {
            this.idsState.unshift(data.id)

            return true
          }
        }
      })
    },

    async getIds(query: string) {
      const url = useRuntimeConfig().public.apiBase + '/favorites/ids'
      return await useServerApiFetch(url, query).then(({ data }) => {
        if(!data)
          return
        
        this.idsState = data
      })
    },

    async index(query: string) {
      const url = useRuntimeConfig().public.apiBase + '/favorites'
      return await useServerApiFetch(url, query)
    },

    async getAll(query: string, refresh: boolean = true) {
      return await this.index(query).then(({ data }) => {
        if(!data)
          return

        return {
          data: data.data,
          meta: data.meta
        }
      })
    }
  }

})