export const useCartStore = defineStore('cartStore', {
  persist: true,

  state: () => ({
    orderState: {
      delivery: {
        method: 'warehouse',
        warehouse: null,
        city: null,
        address: null,
        zip: null,
        comment: null
      },
      payment: {
        method: null
      },
      user: {
        phone: null,
        email: null,
        firstname: null,
        lastname: null,
      }
    },
    
    data: [] as Product[],

    errorsState: {},

    flashOrder: null
  }),

  getters: {
    cart: (state) => state.data,
    total: (state) => {
      const total = state.data.reduce((carry, item) => {
        return carry + item.price * item.amount
      }, 0)
    
      return Number(total.toFixed(2))
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
      const {id, name, slug, price, amount, image} = data
      return {id, name, slug, price, amount, image} as Product
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
      const {firstname, lastname, email, phone} = user
      this.orderState.user = {firstname, lastname, email, phone}
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
      const url = `${useRuntimeConfig().public.apiBase}/orders/all`

      const query = {
        ...data,
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

    async validate(provider: String = 'data') {
      const url = `${useRuntimeConfig().public.apiBase}/orders/validate`

      const dataPost = {
        ...this.orderState,
        products: this.serializeCart(),
        provider: provider
      }
      
      return await useApiFetch(url, dataPost, 'POST')
        .then(({data, error}) => {
          if(data) {
            return data
          }

          if(error) {
            this.errorsState = error
            throw error
          }

        })
    },

    async createOrder(orderable: Object) {
      const url = `${useRuntimeConfig().public.apiBase}/orders`

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
            this.errorsState = error
            throw error
          }

        })
    }
  },
})