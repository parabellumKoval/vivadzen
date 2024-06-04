export const useCartStore = defineStore('cartStore', {
  persist: true,

  state: () => ({
    orderState: {
      delivery: {
        method: 'warehouse',
        settlement: null,
        settlementRef: null,
        region: null,
        area: null,
        street: null,
        streetRef: null,
        type: null,
        house: null,
        room: null,
        zip: null,
        warehouse: null,
        warehouseRef: null,
        comment: null
      },
      payment: {
        method: 'online'
      },
      user: {
        phone: null,
        email: null,
        firstname: null,
        lastname: null,
      },
      comment: null,
      promocode: null
    },
    
    data: [] as Product[],

    errorsState: {},

    flashOrder: null,

    promocodeData: null
  }),

  getters: {
    promocode: (state) => state.promocodeData,
    promocodeSale: (state) => {
      if(!state.promocodeData)
        return 0
    
      if(state.promocodeData.type === 'value') {
        return state.promocodeData.value
      }else if(state.promocodeData.type === 'percent') {
        return useCartStore().totalProducts * state.promocodeData.value / 100
      }
    },
    cart: (state) => state.data,
    totalProducts: (state) => {
      const v = state.data.reduce((carry, item) => {
        return carry + item.price * item.amount
      }, 0)
    
      return Number(v.toFixed(2))
    },
    total: (state) => {
      const v = useCartStore().totalProducts - useCartStore().promocodeSale
      return v
    },
    order: (state) => state.orderState,
    errors: (state) => state.errorsState,
    filled: (state) => {
      return (key: string) => {
        if(key === 'user') {
          return state.orderState.user.firstname && state.orderState.user.phone && state.orderState.user.email
        }

        if(key === 'delivery') {
          if(state.orderState.delivery.method === 'warehouse')
            return state.orderState.delivery.city && state.orderState.delivery.warehouse
          else if (state.orderState.delivery.method === 'address')
            return state.orderState.delivery.city && state.orderState.delivery.address && state.orderState.delivery.zip
          else if(state.orderState.delivery.method === 'pickup')
            return true
          else
            return false
        }

        if(key === 'payment') {
          return state.orderState.payment.method
        }
      }
    },
    flash: (state) => state.flashOrder
  },

  actions: {
    removeCode() {
      this.orderState.promocode = null
    },

    setPromocode(data) {
      this.promocodeData = data
    },

    add(data: Product) {
      const product: Product = this.toProductType(data)
      const issetProduct = this.data.find((item) => item.id === product.id)

      if(!issetProduct)
        this.data.push(product)
      else
        issetProduct.amount += product.amount
        
      return Promise.resolve(true)
    },
    
    remove(id: number) {
      const index = this.data.findIndex(item => (item.id === id))
      this.data.splice(index, 1)
      return Promise.resolve(true)
    },

    clearCart() {
      this.data = []
    },

    clearErrors() {
      this.errorsState = {}
    },

    toProductType(data: Product) {
      const {id, name, slug, price, oldPrice, amount, image} = data
      return {id, name, slug, price, oldPrice, amount, image} as Product
    },

    serializeCart() {
      let serialized = {}

      for(const index in this.data){
        const item = this.data[index]
        serialized[item.id] = item.amount
      }

      return serialized
    },

    useBonuses(value: number) {
      this.orderState.bonusesUsed = value
    },

    setUser(user) {
      // const {firstname, lastname, email, phone} = user
      // this.orderState.user = {firstname, lastname, email, phone}
    },

    async copyOrder(id: number) {
      const runtimeConfig = useRuntimeConfig()
      const url = `${runtimeConfig.public.apiBase}/orders/copy`

      return await useApiFetch(url, {id: id}, 'POST')
        .then(({data, error}) => {
          if(data) {
            return data
          }

          if(error) {
            throw error
          }
        })
    },

    async index(data: Object) {
      const url = `${useRuntimeConfig().public.apiBase}/order/all`

      const query = {
        ...data
      }

      return await useApiFetch(url, query, 'GET')
        .then(({data, error}) => {

          if(data) {
            return data
          }

          if(error) {
            throw error
          }
        })
    },

    normalizeDeliveryWarehouse() {
      this.orderState.delivery.street = null
      this.orderState.delivery.streetRef = null
      this.orderState.delivery.house = null
      this.orderState.delivery.room = null
      this.orderState.delivery.zip = null
    },

    normalizeDeliveryAddress() {
      this.orderState.delivery.warehouse = null
      this.orderState.delivery.warehouseRef = null
    },

    normalizeDeliveryPickup() {
      this.orderState.delivery.settlement = null
      this.orderState.delivery.settlementRef = null
      this.orderState.delivery.region = null
      this.orderState.delivery.area = null
      this.orderState.delivery.street = null
      this.orderState.delivery.streetRef = null
      this.orderState.delivery.type = null
      this.orderState.delivery.house = null
      this.orderState.delivery.room = null
      this.orderState.delivery.zip = null
      this.orderState.delivery.warehouse = null
      this.orderState.delivery.warehouseRef = null
    },

    normalizedOrderState() {
      if(this.orderState.delivery.method === 'warehouse') {
        this.normalizeDeliveryWarehouse()
      }else if(this.orderState.delivery.method === 'address') {
        this.normalizeDeliveryAddress()
      }else  if(this.orderState.delivery.method === 'pickup') {
        this.normalizeDeliveryPickup()
      }
    },


    async validate(orderable: Object) {
      const url = `${useRuntimeConfig().public.apiBase}/order/validate`

      delete this.orderState.delivery.comment

      const dataPost = {
        ...orderable,
        ...this.orderState,
        products: this.serializeCart(),
        provider: 'data'
      }
      
      return await useApiFetch(url, dataPost, 'POST')
        .then(({data, error}) => {
          if(data) {
            return data
          }

          if(error) {
            this.errorsState = error?.options
            throw error
          }

        })
    },

    async createOrder(orderable: Object) {
      const url = `${useRuntimeConfig().public.apiBase}/order`

      // delete this.orderState.delivery.city
      // delete this.orderState.delivery.ref
      // delete this.orderState.delivery.name
      // delete this.orderState.delivery.comment

      // Normalize delivery at first
      this.normalizedOrderState()

      const dataPost = {
        ...orderable,
        ...this.orderState,
        products: this.serializeCart(),
        provider: 'data'
      }
      
      return await useApiFetch(url, dataPost, 'POST')
        .then(({data, error}) => {
          
          if(data) {
            this.flashOrder = data
            this.$reset()
            return data
          }

          if(error) {
            this.errorsState = error?.options
            throw error
          }

        })
    }
  },
})