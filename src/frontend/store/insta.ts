import { defineStore } from "pinia";

export const useInstaStore = defineStore('instaStore', {
  state: () => ({ 
    idsState: [],
  }),

  getters: {
    ids: (state) => state.idsState,
  },


  actions: {

    async posts(data: Object = {}) {

      const accessToken = useRuntimeConfig().public.instagramToken
      const apiVersion = 19.0
      const igUserId = 'me'
      const per_page = data?.per_page || 10
    
      const url = `https://graph.instagram.com/${igUserId}/media`

      return await useFetch(url, {
        query: { 
          fields: 'permalink,media_url',
          limit: per_page,
          access_token: accessToken
        },
        key: 'instagram-posts',
        lazy: true
      }).then(({data, error}) => {
        // console.log('insta results', data.value.data)

        if(data.value?.data) {
          return data.value.data
        }

        if(error.value) {
          throw error.value
        }
      })

      // return await $fetch("/api/insta/get-posts", {
      //   method: 'GET'
      // }).then((r) => {
      //   console.log('r', r)
      // })
    },

  }

})