export const useNovaposhtaStore = defineStore('novaposhtaStore', {
  state: () => ({ 
    settlementsState: [] as Object[],
  }),
  
  getters: {
    settlements: (state) => state.settlementsState,
  },

  actions: {
    async getSettlements(find: string, ref: string, options: { popular?: boolean } = {}) {
      const url = useRuntimeConfig().public.apiBase + '/np/settlements'
      const params: Record<string, string | boolean> = {}

      if (find) params.q = find
      if (ref) params.ref = ref
      if (options.popular) params.popular = true

      const { data } = await useServerApiFetch(url, params)
      return data
    },

    async getWarehouses(find: string, settlementRef: string) {
      const url = useRuntimeConfig().public.apiBase + '/np/warehouses'
      const params: Record<string, string> = {}

      if (find) params.q = find
      if (settlementRef) params.settlementRef = settlementRef

      const { data } = await useServerApiFetch(url, params)
      return data
    },


    async getStreets(find: string, settlementRef: string) {
      const url = useRuntimeConfig().public.apiBase + '/np/streets'
      const params: Record<string, string> = {}

      if (find) params.q = find
      if (settlementRef) params.settlementRef = settlementRef

      const { data } = await useServerApiFetch(url, params)
      return data
    }

  },
})
