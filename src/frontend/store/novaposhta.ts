export const useNovaposhtaStore = defineStore('novaposhtaStore', {
  state: () => ({ 
    settlementsState: [] as Object[],
  }),
  
  getters: {
    settlements: (state) => state.settlementsState,
  },

  actions: {
    async getSettlements(find: string, ref: string) {
      return await $fetch("/api/np/get-settlements", {
        method: 'POST',
        body: {
          'find': find,
          'ref': ref
        }
      })
    },

    async getWarehouses(find: string, settlementRef: string) {
      return await $fetch("/api/np/get-warehouses", {
        method: 'POST',
        body: {
          'settlementRef': settlementRef,
          'find': find
        }
      })
    },


    async getStreets(find: string, settlementRef: string) {
      return await $fetch("/api/np/get-streets", {
        method: 'POST',
        body: {
          'settlementRef': settlementRef,
          'find': find
        }
      })
    }

  },
})