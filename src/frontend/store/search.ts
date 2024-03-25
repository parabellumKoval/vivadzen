import { defineStore } from "pinia";

export const useSearchStore = defineStore('searchStore', {
  persist: true,

  state: () => ({
    history: [] as String[]
  }),
  
  getters: {
    getHistory: (state) => state.history,
  },

  actions: {
    setHistory(search: string) {
      const issetValue = this.history.indexOf(search)
      
      if(issetValue !== -1){
        this.history.splice(issetValue, 1)
      }else if(this.history.length >= 15) {
        this.history.splice(this.history.length - 1, 1)
      }

      this.history.unshift(search)
    },

    async index(params: object) {
      const url = useRuntimeConfig().public.apiBase + '/search'

      return await useServerApiFetch(url, params).then(({data, error}) => {

        if(data) {
          this.setHistory(params.search)
          return data
        }

        if(error) {
          return error
        }
      })
    },

    async livesearch(params: object) {
      const url = useRuntimeConfig().public.apiBase + '/search/livesearch'

      return await useServerApiFetch(url, params).then(({data, error}) => {

        if(data) {
          // this.setHistory(params.search)
          return data
        }

        if(error) {
          return error
        }
      })
    }

  }
})