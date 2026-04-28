export type SavedDeliveryAddress = {
  id?: string
  title?: string
  country?: string
  method?: string
  settlement?: string
  settlementRef?: string
  region?: string
  area?: string
  street?: string
  streetRef?: string
  type?: string
  house?: string
  room?: string
  zip?: string
  warehouse?: string
  warehouseRef?: string
  fingerprint?: string
  created_at?: string
  updated_at?: string
  is_main?: boolean
  main?: boolean
  default?: boolean
}

const BASE_ADDRESS: SavedDeliveryAddress = {
  id: '',
  title: '',
  country: '',
  method: '',
  settlement: '',
  settlementRef: '',
  region: '',
  area: '',
  street: '',
  streetRef: '',
  type: '',
  house: '',
  room: '',
  zip: '',
  warehouse: '',
  warehouseRef: '',
  fingerprint: '',
  created_at: '',
  updated_at: '',
  is_main: false,
  main: false,
  default: false,
}

const STRING_FIELDS: Array<keyof SavedDeliveryAddress> = [
  'id',
  'title',
  'country',
  'method',
  'settlement',
  'settlementRef',
  'region',
  'area',
  'street',
  'streetRef',
  'type',
  'house',
  'room',
  'zip',
  'warehouse',
  'warehouseRef',
  'fingerprint',
  'created_at',
  'updated_at',
]

const BOOLEAN_FIELDS: Array<keyof SavedDeliveryAddress> = ['is_main', 'main', 'default']

const FULL_ADDRESS_METHODS = ['novaposhta_address', 'packeta_address', 'messenger_address', 'default_address']
const HOUSE_REQUIRED_METHODS = ['novaposhta_address', 'messenger_address', 'default_address']
const ZIP_REQUIRED_METHODS = ['novaposhta_address', 'packeta_address', 'messenger_address']
const WAREHOUSE_METHODS = ['novaposhta_warehouse', 'packeta_warehouse']
const PICKUP_SELECTION_METHODS = ['default_pickup']

const normalizeBoolean = (value: unknown): boolean => {
  if (typeof value === 'boolean') return value
  if (typeof value === 'number') return value === 1
  if (typeof value === 'string') {
    return ['1', 'true', 'yes'].includes(value.trim().toLowerCase())
  }
  return false
}

const sameValue = (left: unknown, right: unknown) => {
  return String(left || '').trim() === String(right || '').trim()
}

export const useSavedDeliveryAddresses = () => {
  const { t } = useI18n()
  const { methods } = useDelivery()

  const createEmptyAddress = (): SavedDeliveryAddress => ({
    ...BASE_ADDRESS,
    method: methods.value[0]?.key || 'packeta_warehouse',
  })

  const normalizeAddress = (value: Partial<SavedDeliveryAddress> | null | undefined): SavedDeliveryAddress => {
    const next = { ...BASE_ADDRESS }

    STRING_FIELDS.forEach((key) => {
      const candidate = value?.[key]
      next[key] = typeof candidate === 'string' ? candidate : ''
    })

    BOOLEAN_FIELDS.forEach((key) => {
      next[key] = normalizeBoolean(value?.[key])
    })

    return next
  }

  const methodTitle = (method: string | null | undefined) => {
    if (!method) return t('title.delivery')
    return methods.value.find((item) => item.key === method)?.title || method
  }

  const needsWarehouse = (method: string | null | undefined) => WAREHOUSE_METHODS.includes(String(method || ''))
  const needsPickupLocation = (method: string | null | undefined) => PICKUP_SELECTION_METHODS.includes(String(method || ''))
  const needsAddress = (method: string | null | undefined) => FULL_ADDRESS_METHODS.includes(String(method || ''))
  const requiresHouse = (method: string | null | undefined) => HOUSE_REQUIRED_METHODS.includes(String(method || ''))
  const requiresZip = (method: string | null | undefined) => ZIP_REQUIRED_METHODS.includes(String(method || ''))
  const isMainAddress = (value: Partial<SavedDeliveryAddress> | null | undefined) => {
    const address = normalizeAddress(value)
    return Boolean(address.is_main || address.main || address.default)
  }

  const isAddressComplete = (value: Partial<SavedDeliveryAddress> | null | undefined) => {
    const address = normalizeAddress(value)

    if (!address.method || address.method === 'default_pickup') {
      return false
    }

    if (needsWarehouse(address.method)) {
      return Boolean(address.settlement && address.warehouse)
    }

    if (needsAddress(address.method)) {
      return Boolean(
        address.settlement
        && address.street
        && (!requiresHouse(address.method) || address.house)
        && (!requiresZip(address.method) || address.zip)
      )
    }

    return false
  }

  const buildAddressSummary = (value: Partial<SavedDeliveryAddress> | null | undefined) => {
    const address = normalizeAddress(value)
    if (needsPickupLocation(address.method)) {
      return address.warehouse || address.street || address.title || methodTitle(address.method)
    }

    const detailLine = needsWarehouse(address.method)
      ? [address.settlement, address.warehouse].filter(Boolean).join(', ')
      : [
          address.settlement,
          [address.street, address.house, address.room].filter(Boolean).join(' '),
          address.zip,
        ]
          .filter(Boolean)
          .join(', ')

    return detailLine || address.title || methodTitle(address.method)
  }

  const addressSelectLabel = (value: Partial<SavedDeliveryAddress> | null | undefined) => {
    const address = normalizeAddress(value)
    const head = address.title || methodTitle(address.method)
    const summary = buildAddressSummary(address)
    return summary && summary !== head ? `${head} - ${summary}` : head
  }

  const addressesEqual = (
    left: Partial<SavedDeliveryAddress> | null | undefined,
    right: Partial<SavedDeliveryAddress> | null | undefined,
  ) => {
    const a = normalizeAddress(left)
    const b = normalizeAddress(right)

    return sameValue(a.method, b.method)
      && sameValue(a.settlement, b.settlement)
      && sameValue(a.settlementRef, b.settlementRef)
      && sameValue(a.region, b.region)
      && sameValue(a.area, b.area)
      && sameValue(a.street, b.street)
      && sameValue(a.streetRef, b.streetRef)
      && sameValue(a.type, b.type)
      && sameValue(a.house, b.house)
      && sameValue(a.room, b.room)
      && sameValue(a.zip, b.zip)
      && sameValue(a.warehouse, b.warehouse)
      && sameValue(a.warehouseRef, b.warehouseRef)
      && sameValue(a.country, b.country)
  }

  const clearDeliveryState = (deliveryState: Record<string, any>) => {
    Object.assign(deliveryState, {
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
    })
  }

  const extractAddressFromDelivery = (deliveryState: Record<string, any> | null | undefined, fallbackCountry = '') => {
    if (!deliveryState) {
      return null
    }

    const next = normalizeAddress({
      method: deliveryState.method,
      settlement: deliveryState.settlement,
      settlementRef: deliveryState.settlementRef,
      region: deliveryState.region,
      area: deliveryState.area,
      street: deliveryState.street,
      streetRef: deliveryState.streetRef,
      type: deliveryState.type,
      house: deliveryState.house,
      room: deliveryState.room,
      zip: deliveryState.zip,
      warehouse: deliveryState.warehouse,
      warehouseRef: deliveryState.warehouseRef,
      country: deliveryState.country || fallbackCountry,
    })

    return isAddressComplete(next) ? next : null
  }

  const sanitizeAddresses = (
    addresses: Array<Partial<SavedDeliveryAddress>> | null | undefined,
    fallbackCountry = '',
  ) => {
    return (addresses || [])
      .map((item) => normalizeAddress(item))
      .filter((item) => isAddressComplete(item))
      .map((item) => {
        const sanitized = {} as SavedDeliveryAddress

        STRING_FIELDS.forEach((key) => {
          sanitized[key] = item[key]
        })

        sanitized.country = sanitized.country || fallbackCountry
        return sanitized
      })
  }

  const preferredSavedAddress = (addresses: Array<Partial<SavedDeliveryAddress>> | null | undefined) => {
    const normalized = (addresses || [])
      .map((item) => normalizeAddress(item))
      .filter((item) => isAddressComplete(item))

    if (!normalized.length) {
      return null
    }

    return normalized.find((item) => isMainAddress(item)) || normalized[0]
  }

  const upsertSavedAddress = (
    addresses: Array<Partial<SavedDeliveryAddress>> | null | undefined,
    addressLike: Partial<SavedDeliveryAddress> | null | undefined,
    options: { fallbackCountry?: string; makeMain?: boolean } = {},
  ) => {
    const fallbackCountry = String(options.fallbackCountry || '').toUpperCase()
    const makeMain = Boolean(options.makeMain)
    const list = sanitizeAddresses(addresses, fallbackCountry)
    const candidate = normalizeAddress({
      ...addressLike,
      country: normalizeAddress(addressLike).country || fallbackCountry,
    })

    if (!isAddressComplete(candidate)) {
      return list
    }

    const currentIndex = list.findIndex((item) => addressesEqual(item, candidate))

    if (currentIndex >= 0) {
      const existing = list[currentIndex]
      const merged = normalizeAddress({
        ...existing,
        ...candidate,
        title: candidate.title || existing.title,
        id: existing.id || candidate.id,
        fingerprint: existing.fingerprint || candidate.fingerprint,
        created_at: existing.created_at || candidate.created_at,
        updated_at: candidate.updated_at || existing.updated_at,
      })

      if (makeMain) {
        return [merged, ...list.filter((_, index) => index !== currentIndex)]
      }

      return list.map((item, index) => (index === currentIndex ? merged : item))
    }

    return makeMain ? [candidate, ...list] : [...list, candidate]
  }

  const applyAddressToDelivery = (addressLike: Partial<SavedDeliveryAddress>, deliveryState: Record<string, any>) => {
    const address = normalizeAddress(addressLike)

    clearDeliveryState(deliveryState)

    Object.assign(deliveryState, {
      method: address.method || null,
      settlement: address.settlement || null,
      settlementRef: address.settlementRef || null,
      region: address.region || null,
      area: address.area || null,
      street: address.street || null,
      streetRef: address.streetRef || null,
      type: address.type || null,
      house: address.house || null,
      room: address.room || null,
      zip: address.zip || null,
      warehouse: address.warehouse || null,
      warehouseRef: address.warehouseRef || null,
    })
  }

  return {
    createEmptyAddress,
    normalizeAddress,
    methodTitle,
    needsWarehouse,
    needsPickupLocation,
    needsAddress,
    requiresHouse,
    requiresZip,
    isMainAddress,
    isAddressComplete,
    buildAddressSummary,
    addressSelectLabel,
    addressesEqual,
    clearDeliveryState,
    extractAddressFromDelivery,
    sanitizeAddresses,
    preferredSavedAddress,
    upsertSavedAddress,
    applyAddressToDelivery,
  }
}
