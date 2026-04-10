type Order = {
  id: number,
  email: string,
  fullname: string,
  photo: string
};

export const useOrderStore = defineStore('orderStore', {
  state: () => ({
    ordersState: {
      data: [] as Order[],
      meta: null
    },
  }),
  
  getters: {
    orders: (state) => state.ordersState.data,
    ordersMeta: (state) => state.ordersState.meta,
  },

  actions: {
    unshiftOrder(order: Order) {
      this.ordersState.data.unshift(order)
    },

    async getOrder(code: string) {
      const { $api } = useNuxtApp()

      return await $api(`/order/${code}`, {
        method: 'GET',
      })
    },

    async getOrders(data = null, refresh = true) {
      const { $api } = useNuxtApp()

      const response = await $api('/order/get', {
        method: 'POST',
        body: data ? JSON.parse(JSON.stringify(data)) : undefined,
      })

      this.ordersState.data = Array.isArray((response as any)?.data) ? (response as any).data : []
      this.ordersState.meta = (response as any)?.meta || null

      return response
    },

  },
})
