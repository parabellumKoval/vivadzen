import type { TgDeliveryMethod, TgPaymentMethod } from '~/types/tg'

type TgPickupLocation = {
  id: string
  title: string
  address: string
  schedule: string
  label: string
}

const getByPath = (root: Record<string, any> | null, path: string, fallback?: any) => {
  if (!root) return fallback
  return path.split('.').reduce((current, segment) => {
    if (current && Object.prototype.hasOwnProperty.call(current, segment)) {
      return current[segment]
    }
    return undefined
  }, root as any) ?? fallback
}

const normalizeRates = (value: unknown): Array<Record<string, any>> => {
  if (!value) return []
  if (Array.isArray(value)) return value
  if (typeof value === 'string') {
    try {
      const parsed = JSON.parse(value)
      return Array.isArray(parsed) ? parsed : []
    } catch {
      return []
    }
  }
  return []
}

const minRatePrice = (value: unknown) => {
  const rates = normalizeRates(value)
  const prices = rates.map((rate) => Number(rate?.price)).filter(Number.isFinite)
  return prices.length ? Math.min(...prices) : null
}

const normalizeText = (value: unknown) => {
  if (typeof value === 'string') {
    return value.trim()
  }

  if (value && typeof value === 'object' && 'value' in value && typeof (value as { value?: unknown }).value === 'string') {
    return String((value as { value: string }).value).trim()
  }

  return ''
}

export const useTgCheckoutOptions = () => {
  const { $api } = useNuxtApp()
  const { t } = useTgI18n()
  const { region, currency } = useTgRouting()

  const settings = useState<Record<string, any> | null>('tg-settings', () => null)
  const settingsLoading = ref(false)

  const loadSettings = async () => {
    if (settings.value || settingsLoading.value) return settings.value
    settingsLoading.value = true

    try {
      const response = await $api('/settings/nested', { method: 'GET' })
      settings.value = response?.data || response || {}
    } catch {
      settings.value = {}
    } finally {
      settingsLoading.value = false
    }

    return settings.value
  }

  const priceFromSettings = (method: string) => {
    const data = settings.value || {}
    const currencyCode = String(
      getByPath(data, 'shipping.zasilkovna.currency')
      || getByPath(data, 'shipping.novaposhta.currency')
      || currency.value
    ).toUpperCase()

    const rateMap: Record<string, string> = {
      packeta_warehouse: 'shipping.zasilkovna.pickup_rates',
      packeta_address: 'shipping.zasilkovna.home_rates',
      novaposhta_warehouse: 'shipping.novaposhta.branch_rates',
      novaposhta_address: 'shipping.novaposhta.courier_rates',
      messenger_address: 'shipping.messenger.address_rates'
    }

    const path = rateMap[method]
    const amount = path ? minRatePrice(getByPath(data, path)) : null
    return amount === null ? null : { amount, currency: currencyCode }
  }

  const allDeliveryMethods = computed<TgDeliveryMethod[]>(() => [
    {
      key: 'packeta_warehouse',
      title: region.value === 'cz' ? 'Zásilkovna' : 'Zasilkovna',
      label: t('warehouse'),
      price: priceFromSettings('packeta_warehouse')
    },
    {
      key: 'packeta_address',
      title: 'Zasilkovna',
      label: t('address'),
      price: priceFromSettings('packeta_address')
    },
    {
      key: 'novaposhta_warehouse',
      title: 'Нова пошта',
      label: t('warehouse'),
      price: priceFromSettings('novaposhta_warehouse')
    },
    {
      key: 'novaposhta_address',
      title: 'Нова пошта',
      label: t('address'),
      price: priceFromSettings('novaposhta_address')
    },
    {
      key: 'default_pickup',
      title: t('delivery'),
      label: t('warehouse'),
      price: null
    },
    {
      key: 'messenger_address',
      title: 'Messenger',
      label: t('address'),
      price: priceFromSettings('messenger_address')
    },
    {
      key: 'default_address',
      title: t('delivery'),
      label: t('address'),
      price: null
    }
  ])

  const fallbackDeliveryKeys = computed(() => {
    if (region.value === 'ua') {
      return ['novaposhta_warehouse', 'novaposhta_address', 'default_pickup']
    }
    return ['packeta_warehouse', 'packeta_address', 'default_pickup', 'default_address', 'messenger_address']
  })

  const deliveryMethods = computed(() => {
    const settingsKeys = getByPath(settings.value, 'shipping.methods', null)
    const configuredKeys = Array.isArray(settingsKeys)
      ? settingsKeys.filter((key) => allDeliveryMethods.value.some((method) => method.key === key))
      : []
    const keys = configuredKeys.length ? configuredKeys : fallbackDeliveryKeys.value

    return allDeliveryMethods.value
      .filter((method) => keys.includes(method.key))
      .map((method) => ({
        ...method,
        isPriceObject: Boolean(method.price && typeof method.price === 'object')
      }))
  })

  const pickupLocations = computed<TgPickupLocation[]>(() => {
    const raw = getByPath(settings.value, 'site.contacts.pickup_locations', null)
    const fallbackAddress = normalizeText(getByPath(settings.value, 'site.contacts.address', null))
    const fallbackSchedule = normalizeText(getByPath(settings.value, 'site.contacts.schedule', null))
    const rows = Array.isArray(raw) ? raw : []

    const normalized = rows.map((item, index) => {
      if (typeof item === 'string') {
        const address = normalizeText(item)
        if (!address) return null

        return {
          id: `pickup-${index + 1}`,
          title: '',
          address,
          schedule: index === 0 ? fallbackSchedule : '',
          label: address
        }
      }

      if (!item || typeof item !== 'object') {
        return null
      }

      const address = normalizeText((item as Record<string, unknown>).address ?? (item as Record<string, unknown>).value)
      if (!address) return null

      const title = normalizeText((item as Record<string, unknown>).title ?? (item as Record<string, unknown>).name ?? (item as Record<string, unknown>).label)

      return {
        id: normalizeText((item as Record<string, unknown>).id) || `pickup-${index + 1}`,
        title,
        address,
        schedule: normalizeText((item as Record<string, unknown>).schedule) || (index === 0 ? fallbackSchedule : ''),
        label: [title, address].filter(Boolean).join(', ')
      }
    }).filter(Boolean) as TgPickupLocation[]

    if (normalized.length) {
      return normalized
    }

    if (!fallbackAddress) {
      return []
    }

    return [{
      id: 'pickup-1',
      title: '',
      address: fallbackAddress,
      schedule: fallbackSchedule,
      label: fallbackAddress
    }]
  })

  const allPaymentMethods = computed<TgPaymentMethod[]>(() => [
    {
      key: 'zasilkovna_cod',
      title: 'Dobírka Zasilkovna',
      payments: ['packeta_warehouse', 'packeta_address']
    },
    {
      key: 'novaposhta_cod',
      title: 'Післяплата Нова пошта',
      payments: ['novaposhta_warehouse', 'novaposhta_address']
    },
    {
      key: 'default_cash',
      title: 'Cash',
      payments: ['default_pickup']
    },
    {
      key: 'liqpay_online',
      title: 'LiqPay',
      payments: ['packeta_warehouse', 'packeta_address', 'novaposhta_warehouse', 'novaposhta_address', 'default_address', 'default_pickup']
    },
    {
      key: 'niftipay_online',
      title: 'NiftiPay',
      payments: ['packeta_warehouse', 'packeta_address', 'novaposhta_warehouse', 'novaposhta_address', 'default_address', 'default_pickup']
    },
    {
      key: 'card_online',
      title: 'Card online',
      payments: ['packeta_warehouse', 'packeta_address', 'novaposhta_warehouse', 'novaposhta_address', 'default_address', 'default_pickup']
    },
    {
      key: 'bank_transfer',
      title: 'Bank transfer',
      payments: ['packeta_warehouse', 'packeta_address', 'default_address', 'default_pickup']
    },
    {
      key: 'messenger_cod',
      title: 'Messenger COD',
      payments: ['messenger_address']
    }
  ])

  const fallbackPaymentKeys = computed(() => {
    if (region.value === 'ua') return ['novaposhta_cod', 'liqpay_online', 'card_online']
    return ['zasilkovna_cod', 'card_online', 'bank_transfer', 'default_cash', 'messenger_cod']
  })

  const paymentMethodsFor = (deliveryMethod: string | null) => {
    const settingsKeys = getByPath(settings.value, 'payment.methods', null)
    const configuredKeys = Array.isArray(settingsKeys)
      ? settingsKeys.filter((key) => allPaymentMethods.value.some((method) => method.key === key))
      : []
    const keys = configuredKeys.length ? configuredKeys : fallbackPaymentKeys.value

    const methods = allPaymentMethods.value.filter((method) => {
      return keys.includes(method.key) && (!deliveryMethod || method.payments?.includes(deliveryMethod))
    })

    if (methods.length || !configuredKeys.length) {
      return methods
    }

    return allPaymentMethods.value.filter((method) => {
      return fallbackPaymentKeys.value.includes(method.key) && (!deliveryMethod || method.payments?.includes(deliveryMethod))
    })
  }

  return {
    settings,
    settingsLoading,
    loadSettings,
    deliveryMethods,
    pickupLocations,
    paymentMethodsFor
  }
}
