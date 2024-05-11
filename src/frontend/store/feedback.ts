export const useFeedbackStore = defineStore('feedbackStore', {
  state: () => ({}),
  
  getters: {},

  actions: {
    async create(data: object) {
      const runtimeConfig = useRuntimeConfig()
      const url = `${runtimeConfig.public.apiBase}/feedback`

      const fullData = {
        ...data
      }

      return await useApiFetch(url, fullData, 'POST').then((response) => {
        if(response.data) {

        }

        return {data: response.data, error: response.error}
      })
    }
  },
})