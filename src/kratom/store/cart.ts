
type ProductSmall = {
  id: number,
  name: string,
  short_name: string,
  slug: string,
  price: number,
  old_price?: number,
  oldPrice?: number,
  basePrice?: number,
  campaignDiscount?: number,
  campaign?: Record<string, any> | null,
  totalPrice: number
  totalOldPrice: number | null
  image: object,
  amount: number
}; 
export const useCartStore = defineStore('cartStore', {
  persist: true,

  state: () => ({
    orderState: {
      delivery: {
        method: null,
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
        price: null,
        priceCurrency: null,
      },
      payment: {
        method: null,
        settlement: null,
        street: null,
        house: null,
        room: null,
        zip: null
      },
      user: {
        phone: null,
        email: null,
        first_name: null,
        last_name: null,
      },
      comment: null,
      promocode: null,
      bonus: null,
      bonusInFiat: null,
      age_verification_uid: null,
      provider: 'data'
    },
    
    data: [] as Product[],

    cartIdAmounts: {} as Record<number, number>,
    cartProductsState: [] as ProductSmall[],

    errorsState: {},

    flashOrder: null,

    promocodeData: null,

    deliveryPriceState: null as { amount: number; currency: string } | null,
    deliveryQuoteState: null as unknown,

    rulesState: {}
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
    personalDiscount: (state) => {
      const {user} = useAuth()
      if(!user.value) return 0

      const percent = user.value.discount_percent
      return (useCartStore().totalProducts * percent) / 100
    },
    productIds: (state) => Object.keys(state.cartIdAmounts),
    cartLength: (state) => Object.values(state.cartIdAmounts).length,
    cart: (state) => state.cartProductsState,
    totalProducts: (state) => {
      const v = state.cartProductsState.reduce((carry, item) => {
        return carry + item.totalPrice
      }, 0)
    
      return Number(v.toFixed(2))
    },
    campaignDiscountTotal: (state) => {
      const total = state.cartProductsState.reduce((carry, item) => {
        const amount = Number(item.amount || 1)
        const explicit = Number(item.campaignDiscount || 0)

        if (explicit > 0) {
          return carry + explicit * amount
        }

        if (!item.campaign) {
          return carry
        }

        const base = Number(item.basePrice || item.oldPrice || item.old_price || 0)
        const price = Number(item.price || 0)
        const diff = Math.max(0, base - price)

        return carry + diff * amount
      }, 0)

      return Number(total.toFixed(2))
    },
    total: (state) => {
      const v = useCartStore().totalProducts - useCartStore().promocodeSale
      return v
    },
    finishTotal: (state) => {
      const { get } = useSettings()
      const includeDeliveryCost = !!get('shipping.add_to_order_enabled', false)
      const deliveryCost = includeDeliveryCost ? (state.deliveryPriceState?.amount || 0) : 0
      return useCartStore().total + deliveryCost
    },
    order: (state) => state.orderState,
    deliveryPrice: (state) => state.deliveryPriceState,
    deliveryQuote: (state) => state.deliveryQuoteState,
    errors: (state) => state.errorsState,
    filled: (state) => {
      return (key: string) => {
        if(key === 'user') {
          return state.orderState.user.first_name && state.orderState.user.phone && state.orderState.user.email
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
    flash: (state) => state.flashOrder,
    isAddressCollected: (state) => {
      const methodsWithFullAddress = ['novaposhta_address', 'packeta_address', 'messenger_address', 'default_address']
      const method = state.orderState.delivery.method

      if(method && methodsWithFullAddress.includes(method)) return true
      else return false
    }
  },

  actions: {
    removeCode() {
      this.orderState.promocode = null
    },

    setPromocode(data) {
      this.promocodeData = data
    },

    clearBonusUsage() {
      this.orderState.bonus = null
      this.orderState.bonusInFiat = null
    },

    setDeliveryFields(fields: Partial<typeof this.orderState.delivery>) {
      // Создаем новый объект где все поля null
      const resetDelivery = Object.keys(this.orderState.delivery).reduce((acc, key) => {
        acc[key] = null
        return acc
      }, {} as typeof this.orderState.delivery)

      // Объединяем с переданными полями
      // this.orderState.delivery = {
      //   ...resetDelivery,
      //   ...fields
      // }
      this.orderState.delivery = fields
    },

    setDeliveryPricing(payload?: { price?: { amount: number; currency: string } | null; quote?: unknown }) {
      const price = payload?.price ?? null
      if (!price) {
        this.deliveryPriceState = null
        this.deliveryQuoteState = payload?.quote ?? null
        return
      }

      const amount = Number(price.amount)
      const currency = price.currency || null

      if (!Number.isFinite(amount) || !currency) {
        this.deliveryPriceState = null
      } else {
        this.deliveryPriceState = { amount, currency }
      }

      this.deliveryQuoteState = payload?.quote ?? null
    },

    add(product: Product) {
      if(this.cartIdAmounts[product.id]) {
        this.cartIdAmounts[product.id] += product.amount || 1
      }else{
        this.cartIdAmounts[product.id] = product.amount || 1
      }
        
      return Promise.resolve(true)
    },


    async fetchCartProducts() {
      const runtimeConfig = useRuntimeConfig()
      const url = `${runtimeConfig.public.apiBase}/product/cart`;
      const query = {
        'cart': this.cartIdAmounts
      }

      return await useApiFetch(url, query, 'GET', {lazy: false}).then(({data, error}) => {
          if(data?.value?.data) {
            this.cartProductsState = this.addAmountToProducts(data?.value?.data)
            return data?.value?.data
          }

          if(error)
            throw new Error(error)
        })
    },

    addAmountToProducts(products: ProductSmall[]) {
      return products.map((item) => {
        const oldPrice = item.old_price ?? item.oldPrice ?? null
        item.amount = this.cartIdAmounts[item.id] || 1
        item.totalPrice = item.amount * item.price 
        item.totalOldPrice = oldPrice ? item.amount * oldPrice : null
        return item
      })
    },
    // add(data: Product) {
    //   const product: Product = this.toProductType(data)
    //   const issetProduct = this.data.find((item) => item.id === product.id)

    //   console.log('this.data', this.data)

    //   if(!issetProduct)
    //     this.data.push(product)
    //   else
    //     issetProduct.amount += product.amount
        
    //   return Promise.resolve(true)
    // },
    
    remove(id: number) {
      // Удаляем с примитивного объекта
      delete this.cartIdAmounts[id]

      // Удаляем зафетченный товар из стора
      const index = this.cartProductsState.findIndex(item => item.id === id);
      if (index !== -1) {
        this.cartProductsState.splice(index, 1);
      }
      
      return Promise.resolve(true)
    },

    updateAmount(id: number, amount: number) {
      // Меняем в примитивном объекте
      this.cartIdAmounts[id] = amount

      // Меняем в зафетченном массиве товаров
      const index = this.cartProductsState.findIndex(item => item.id === id);
      if (index !== -1) {
        this.cartProductsState[index].amount = amount;
        const oldPrice = this.cartProductsState[index].old_price
          ?? this.cartProductsState[index].oldPrice
          ?? null
        this.cartProductsState[index].totalPrice = this.cartProductsState[index].amount * this.cartProductsState[index].price 
        this.cartProductsState[index].totalOldPrice = oldPrice
          ? this.cartProductsState[index].amount * oldPrice
          : null
      }
    },

    clearCart() {
      this.data = []
      this.deliveryPriceState = null
      this.deliveryQuoteState = null
      this.orderState.delivery.price = null
      this.orderState.delivery.priceCurrency = null
    },

    clearErrors() {
      this.errorsState = {}
    },

    toProductType(data: Product) {
      const {id, name, slug, price, oldPrice, amount, images, currency} = data
      const image  = images?.length? images[0]: null
      return {id, name, slug, price, oldPrice, amount, image, currency} as Product
    },

    serializeCart() {
      let serialized = {}

      for(const index in this.data){
        const item = this.data[index]
        serialized[item.id] = item.amount
      }

      return serialized
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

          if(data.value) {
            return data.value
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

      const {isAuthenticated} = useAuth()

      // Auth or Data
      this.orderState.provider = isAuthenticated.value? 'auth': 'data'

      if(this.orderState.provider === 'data') {
        this.clearBonusUsage()
      }

      // Clear other user fields
      // if(this.orderState.provider === 'auth') {
      //   this.keepKeys(this.orderState.user, ['email', 'phone'])
      // }
    },

    keepKeys(object: Object, keys: Array<string>) {
      // Create a new object with only the desired keys
      const filteredObject = {} as Record<string, any>
      
      // Copy only the specified keys to the new object
      keys.forEach(key => {
        if (key in object) {
          filteredObject[key] = object[key]
        }
      })

      // Assign all properties back to the original object
      Object.keys(object).forEach(key => {
        delete object[key]
      })
      Object.assign(object, filteredObject)
    },


    async rules() {
      const url = `${useRuntimeConfig().public.apiBase}/order/rules`
      
      return await useApiFetch(url)
        .then(({data, error}) => {

          if(data.value) {
            this.rulesState = data.value
            return data.value
          }

          if(error.value) {
            throw error
          }

        })
    },


    isFieldRequired (rulePath: string) {
      // 5.1) Достаём объект правила
      const ruleObj = this.getRuleByPath(rulePath)
      if (!ruleObj) {
        return false
      }

      // 5.2) Если явно required: true
      if (ruleObj.required) {
        return true
      }

      // 5.3) Если есть условие requiredIf
      if (ruleObj.requiredIf) {
        const { field, values } = ruleObj.requiredIf
        const otherValue = this.getFieldValue(field)
        return values.includes(otherValue)
      }

      // 5.4) В остальных случаях — не обязательное
      return false
    },

    getRuleByPath (rulePath: string) {
      return rulePath
        .split('.')
        .reduce((acc, segment) => (acc && acc[segment] !== undefined ? acc[segment] : null), this.rulesState)
    },

    getFieldValue (path) {
      return path
        .split('.')
        .reduce((acc, key) => (acc && acc[key] !== undefined ? acc[key] : null), this.orderState)
    },

    async validate(orderable: Object) {
      const url = `${useRuntimeConfig().public.apiBase}/order/validate`

      const dataPost = {
        ...this.orderState,
        products: this.cartIdAmounts,
      }
      
      return await useApiFetch(url, dataPost, 'POST')
        .then(({data, error}) => {

          if(data.value) {
            return true
          }

          if(error.value) {
            this.errorsState = error.value.data?.options || {}
            throw error
          }

        })
    },

    async createOrder(orderable: Object) {
      const url = `${useRuntimeConfig().public.apiBase}/order`

      // Normalize delivery at first
      this.normalizedOrderState()

      const dataPost = {
        // ...orderable,
        ...this.orderState,
        // products: this.serializeCart(),
        products: this.cartIdAmounts,
      }
      
      return await useApiFetch(url, dataPost, 'POST')
        .then(({data, error}) => {

          if(data.value) {
            this.flashOrder = data.value
            return data.value
          }

          if(error.value) {
            this.errorsState = error.value.data?.options || {}
            throw error
          }

        })
    }
  },
})
