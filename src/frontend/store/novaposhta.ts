export const useNovaposhtaStore = defineStore('novaposhtaStore', {
  state: () => ({ 
    settlementsState: [] as Object[],
  }),
  
  getters: {
    settlements: (state) => state.settlementsState,
  },

  actions: {
    async getSettlements(find: string) {
      return await $fetch("/api/np/get-settlements", {
        method: 'POST',
        body: {
          'find': find
        }
      })
    },

    async getWarehouses(city: string, find: string) {
      return await $fetch("/api/np/get-warehouses", {
        method: 'POST',
        body: {
          'city': city,
          'find': find
        }
      })
    },
  },
})