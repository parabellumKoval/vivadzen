export type TgUser = {
  id?: number
  first_name?: string
  last_name?: string
  username?: string
  language_code?: string
  photo_url?: string
  phone?: string
  phone_number?: string
  email?: string
} | null

export type TgCheckoutProfile = {
  first_name: string | null
  last_name: string | null
  phone: string | null
  email: string | null
  avatar: string | null
  paymentMethod: string | null
}

export type TgSavedAddress = {
  id: string
  deliveryMethod: string | null
  settlement: string | null
  street: string | null
  house: string | null
  room: string | null
  zip: string | null
  warehouse: string | null
  price: number | null
  priceCurrency: string | null
  title: string
  updatedAt: string
}

export type TgBackendProfile = Record<string, any> & {
  telegram_user_id?: number
  username?: string | null
  first_name?: string | null
  last_name?: string | null
  phone?: string | null
  email?: string | null
  avatar_url?: string | null
  language_code?: string | null
  payment_method?: string | null
  addresses?: TgSavedAddress[]
}

const clean = (value: unknown) => {
  const text = String(value ?? '').trim()
  return text || null
}

const defaultProfile = (): TgCheckoutProfile => ({
  first_name: null,
  last_name: null,
  phone: null,
  email: null,
  avatar: null,
  paymentMethod: null
})

const addressTitle = (address: TgSavedAddress) => {
  const streetLine = [address.street, address.house, address.room].filter(Boolean).join(', ')
  const pointLine = address.warehouse || streetLine || address.zip
  return [address.settlement, pointLine].filter(Boolean).join(', ') || 'Address'
}

const addressKey = (address: TgSavedAddress) => [
  address.deliveryMethod,
  address.settlement,
  address.street,
  address.house,
  address.room,
  address.zip,
  address.warehouse
].map((value) => clean(value) || '').join('|')

const hasAddressData = (address: TgSavedAddress) => Boolean(
  address.deliveryMethod
  && (address.settlement || address.street || address.house || address.zip || address.warehouse)
)

const addressFromDelivery = (delivery: Record<string, any>): TgSavedAddress => {
  const address = {
    id: `addr-${Date.now()}`,
    deliveryMethod: clean(delivery.method),
    settlement: clean(delivery.settlement),
    street: clean(delivery.street),
    house: clean(delivery.house),
    room: clean(delivery.room),
    zip: clean(delivery.zip),
    warehouse: clean(delivery.warehouse),
    price: typeof delivery.price === 'number' ? delivery.price : null,
    priceCurrency: clean(delivery.priceCurrency),
    title: '',
    updatedAt: new Date().toISOString()
  }

  address.title = addressTitle(address)
  return address
}

const addressFromPayload = (payload: Record<string, any>): TgSavedAddress => {
  const address = {
    id: clean(payload.id) || `addr-${Date.now()}`,
    deliveryMethod: clean(payload.deliveryMethod || payload.delivery_method || payload.method),
    settlement: clean(payload.settlement),
    street: clean(payload.street),
    house: clean(payload.house),
    room: clean(payload.room),
    zip: clean(payload.zip),
    warehouse: clean(payload.warehouse),
    price: typeof payload.price === 'number' ? payload.price : null,
    priceCurrency: clean(payload.priceCurrency || payload.price_currency),
    title: clean(payload.title) || '',
    updatedAt: clean(payload.updatedAt || payload.updated_at) || new Date().toISOString()
  }

  address.title = address.title || addressTitle(address)
  return address
}

export const useTgUserStore = defineStore('tgUserStore', {
  persist: true,

  state: () => ({
    user: null as TgUser,
    initData: '',
    checkoutProfile: defaultProfile(),
    addresses: [] as TgSavedAddress[]
  }),

  getters: {
    fullName: (state) => {
      const profileName = [state.checkoutProfile.first_name, state.checkoutProfile.last_name].filter(Boolean).join(' ')
      const tgName = [state.user?.first_name, state.user?.last_name].filter(Boolean).join(' ')
      return profileName || tgName || state.user?.username || ''
    },

    avatarUrl: (state) => state.checkoutProfile.avatar || state.user?.photo_url || '',

    phone: (state) => state.checkoutProfile.phone || '',

    email: (state) => state.checkoutProfile.email || '',

    usernameLabel: (state) => state.user?.username ? `@${state.user.username}` : '',

    hasCheckoutProfile: (state) => Boolean(
      state.checkoutProfile.first_name
      && state.checkoutProfile.last_name
      && state.checkoutProfile.phone
      && state.checkoutProfile.email
    )
  },

  actions: {
    sync(user: TgUser, initData = '') {
      if (user) {
        this.user = user
      } else if (!this.user) {
        this.user = null
      }

      if (initData) {
        this.initData = initData
      }

      const tgPhone = clean(user?.phone_number || user?.phone)
      const tgEmail = clean(user?.email)

      this.checkoutProfile = {
        ...defaultProfile(),
        ...this.checkoutProfile,
        first_name: this.checkoutProfile.first_name || clean(user?.first_name),
        last_name: this.checkoutProfile.last_name || clean(user?.last_name),
        phone: this.checkoutProfile.phone || tgPhone,
        email: this.checkoutProfile.email || tgEmail,
        avatar: this.checkoutProfile.avatar || clean(user?.photo_url)
      }
    },

    saveCheckoutProfile(data: Partial<TgCheckoutProfile>) {
      const next = {
        ...defaultProfile(),
        ...this.checkoutProfile
      }

      if ('first_name' in data) next.first_name = clean(data.first_name)
      if ('last_name' in data) next.last_name = clean(data.last_name)
      if ('phone' in data) next.phone = clean(data.phone)
      if ('email' in data) next.email = clean(data.email)
      if ('avatar' in data) next.avatar = clean(data.avatar)
      if ('paymentMethod' in data) next.paymentMethod = clean(data.paymentMethod)

      this.checkoutProfile = next
    },

    hydrateBackendProfile(profile: TgBackendProfile | null) {
      if (!profile) return

      this.user = {
        ...(this.user || {}),
        id: Number(profile.telegram_user_id || this.user?.id || 0) || this.user?.id,
        username: clean(profile.username) || this.user?.username,
        first_name: clean(profile.first_name) || this.user?.first_name,
        last_name: clean(profile.last_name) || this.user?.last_name,
        language_code: clean(profile.language_code) || this.user?.language_code,
        photo_url: clean(profile.avatar_url) || this.user?.photo_url
      }

      this.checkoutProfile = {
        ...defaultProfile(),
        ...this.checkoutProfile,
        first_name: clean(profile.first_name) || this.checkoutProfile.first_name,
        last_name: clean(profile.last_name) || this.checkoutProfile.last_name,
        phone: clean(profile.phone) || this.checkoutProfile.phone,
        email: clean(profile.email) || this.checkoutProfile.email,
        avatar: clean(profile.avatar_url) || this.checkoutProfile.avatar,
        paymentMethod: clean(profile.payment_method) || this.checkoutProfile.paymentMethod
      }

      if (Array.isArray(profile.addresses)) {
        this.addresses = profile.addresses.map((address) => addressFromPayload(address)).filter(hasAddressData)
      }
    },

    savePaymentMethod(method: string | null) {
      this.saveCheckoutProfile({ paymentMethod: method })
    },

    fillOrderUser(orderUser: Record<string, any>) {
      orderUser.first_name = orderUser.first_name || this.checkoutProfile.first_name || this.user?.first_name || null
      orderUser.last_name = orderUser.last_name || this.checkoutProfile.last_name || this.user?.last_name || null
      orderUser.phone = orderUser.phone || this.checkoutProfile.phone || this.user?.phone_number || this.user?.phone || null
      orderUser.email = orderUser.email || this.checkoutProfile.email || this.user?.email || null
    },

    saveAddressFromDelivery(delivery: Record<string, any>) {
      const next = addressFromDelivery(delivery)
      if (!hasAddressData(next)) return null

      return this.upsertAddress(next)
    },

    upsertAddress(next: TgSavedAddress | Record<string, any>) {
      const normalized = addressFromPayload(next)
      if (!hasAddressData(normalized)) return null

      const key = addressKey(normalized)
      const existing = this.addresses.find((address) => addressKey(address) === key || address.id === normalized.id)
      const address = existing
        ? { ...existing, ...normalized, id: existing.id, updatedAt: normalized.updatedAt, title: addressTitle(normalized) }
        : normalized

      this.addresses = [
        address,
        ...this.addresses.filter((item) => item.id !== address.id && addressKey(item) !== key)
      ].slice(0, 8)

      return address
    },

    removeAddress(addressId: string) {
      this.addresses = this.addresses.filter((address) => address.id !== addressId)
    },

    applyAddressToDelivery(addressId: string, delivery: Record<string, any>) {
      const address = this.addresses.find((item) => item.id === addressId)
      if (!address) return false

      delivery.method = address.deliveryMethod
      delivery.settlement = address.settlement
      delivery.street = address.street
      delivery.house = address.house
      delivery.room = address.room
      delivery.zip = address.zip
      delivery.warehouse = address.warehouse
      delivery.price = address.price
      delivery.priceCurrency = address.priceCurrency

      return true
    }
  }
})
