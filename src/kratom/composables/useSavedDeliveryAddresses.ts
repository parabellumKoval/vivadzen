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
}

const FULL_ADDRESS_METHODS = ['novaposhta_address', 'packeta_address', 'messenger_address', 'default_address']
const HOUSE_REQUIRED_METHODS = ['novaposhta_address', 'messenger_address', 'default_address']
const ZIP_REQUIRED_METHODS = ['novaposhta_address', 'packeta_address', 'messenger_address']
const WAREHOUSE_METHODS = ['novaposhta_warehouse', 'packeta_warehouse']

export const useSavedDeliveryAddresses = () => {
  const { t } = useI18n()
  const { methods } = useDelivery()

  const createEmptyAddress = (): SavedDeliveryAddress => ({
    ...BASE_ADDRESS,
    method: methods.value[0]?.key || 'packeta_warehouse',
  })

  const normalizeAddress = (value: Partial<SavedDeliveryAddress> | null | undefined): SavedDeliveryAddress => {
    const next = { ...BASE_ADDRESS }

    Object.keys(BASE_ADDRESS).forEach((key) => {
      const candidate = value?.[key as keyof SavedDeliveryAddress]
      next[key as keyof SavedDeliveryAddress] = typeof candidate === 'string' ? candidate : ''
    })

    return next
  }

  const methodTitle = (method: string | null | undefined) => {
    if (!method) return t('title.delivery')
    return methods.value.find((item) => item.key === method)?.title || method
  }

  const needsWarehouse = (method: string | null | undefined) => WAREHOUSE_METHODS.includes(String(method || ''))
  const needsAddress = (method: string | null | undefined) => FULL_ADDRESS_METHODS.includes(String(method || ''))
  const requiresHouse = (method: string | null | undefined) => HOUSE_REQUIRED_METHODS.includes(String(method || ''))
  const requiresZip = (method: string | null | undefined) => ZIP_REQUIRED_METHODS.includes(String(method || ''))

  const buildAddressSummary = (value: Partial<SavedDeliveryAddress> | null | undefined) => {
    const address = normalizeAddress(value)
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

  const applyAddressToDelivery = (addressLike: Partial<SavedDeliveryAddress>, deliveryState: Record<string, any>) => {
    const address = normalizeAddress(addressLike)

    const resetPayload = {
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
    }

    Object.assign(deliveryState, resetPayload, {
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
    needsAddress,
    requiresHouse,
    requiresZip,
    buildAddressSummary,
    applyAddressToDelivery,
  }
}
