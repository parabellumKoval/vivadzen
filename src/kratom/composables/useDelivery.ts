
export const useDelivery = () => {
  const {t} = useI18n()
  const {get} = useSettings()
  const {region, currency} = useRegion()

  const vendors = ref([
    {
      id: 1,
      logo: '/images/logo/np.png',
      title: 'Novaposhta',
      countries: ['ua']
    },{
      id: 2,
      logo: '/images/logo/zasilkovna.png',
      title: 'Zasilkovna',
      countries: ['cz','es','de']
    },{
      id: 3,
      logo: '/images/logo/ceska.png',
      title: 'Ceska posta',
      countries: ['cz','es','de']
    },{
      id: 4,
      logo: '/images/logo/ppl.png',
      title: 'PPL',
      countries: ['cz','es','de']
    },{
      id: 5,
      logo: '/images/logo/dhl.png',
      title: 'DHL',
      countries: ['cz','es','de']
    },{
      id: 6,
      logo: '/images/logo/dpd.png',
      title: 'Dpd',
      countries: ['cz','es','de']
    }
  ])

  const providerTariffsLabel = computed(() => t('delivery.provider_tariffs'))
  const fromShopLabel = computed(() => t('delivery.from_shop'))

  const resolveCurrency = (value: unknown) => {
    if (typeof value === 'string' && value.trim()) {
      return value.trim().toUpperCase()
    }
    if (currency.value) {
      return String(currency.value).toUpperCase()
    }
    return null
  }

  const buildPrice = (ratesValue: unknown, currencyValue: unknown) => {
    const rates = normalizeRates(ratesValue)
    const amount = minRatePrice(rates)
    if (amount === null) return null
    const currencyCode = resolveCurrency(currencyValue)
    if (!currencyCode) return null
    return { amount, currency: currencyCode }
  }

  const packetaPickupPrice = computed(() => {
    return buildPrice(
      get('shipping.zasilkovna.pickup_rates'),
      get('shipping.zasilkovna.currency')
    )
  })

  const packetaHomePrice = computed(() => {
    return buildPrice(
      get('shipping.zasilkovna.home_rates'),
      get('shipping.zasilkovna.currency')
    )
  })

  const novaposhtaWarehousePrice = computed(() => {
    const branchRates = normalizeRates(get('shipping.novaposhta.branch_rates'))
    const lockerRates = normalizeRates(get('shipping.novaposhta.locker_rates'))
    const rates = [...branchRates, ...lockerRates]
    return buildPrice(rates, get('shipping.novaposhta.currency'))
  })

  const novaposhtaCourierPrice = computed(() => {
    return buildPrice(
      get('shipping.novaposhta.courier_rates'),
      get('shipping.novaposhta.currency')
    )
  })

  const defaultPrice = (methodKey = 'pickup') => {
    switch (methodKey) {
      case 'pickup':
      case 'packeta_warehouse':
        return packetaPickupPrice.value
      case 'home':
      case 'packeta_address':
        return packetaHomePrice.value
      case 'novaposhta_warehouse':
        return novaposhtaWarehousePrice.value
      case 'novaposhta_address':
        return novaposhtaCourierPrice.value
      default:
        return null
    }
  }

  const methods = computed(() => {
    const packetaPickup = packetaPickupPrice.value
    const packetaHome = packetaHomePrice.value
    const novaposhtaWarehouse = novaposhtaWarehousePrice.value
    const novaposhtaCourier = novaposhtaCourierPrice.value

    return [
      {
        key: 'packeta_warehouse',
        title: t('delivery.packeta_warehouse'),
        label: t('delivery.z_warehouse'),
        icon: 'iconoir:delivery-truck',
        image: '/images/logo/zasilkovna.png',
        logo: '/images/logo/z-mini.png',
        price: packetaPickup || providerTariffsLabel.value,
        isPriceObject: !!packetaPickup
      }, 
      {
        key: 'packeta_address',
        title: t('delivery.packeta_address'),
        label: t('delivery.z_address'),
        icon: 'iconoir:delivery',
        image: '/images/logo/zasilkovna.png',
        logo: '/images/logo/z-mini.png',
        price: packetaHome || providerTariffsLabel.value,
        isPriceObject: !!packetaHome
      }, 
      {
        key: 'novaposhta_warehouse',
        title: t('delivery.novaposhta_warehouse'),
        label: t('delivery.np_warehouse'),
        icon: 'iconoir:delivery-truck',
        image: '/images/logo/np.png',
        logo: '/images/logo/np-mini.png',
        price: novaposhtaWarehouse || providerTariffsLabel.value,
        isPriceObject: !!novaposhtaWarehouse
      }, 
      {
        key: 'novaposhta_address',
        title: t('delivery.novaposhta_address'),
        label: t('delivery.np_warehouse'),
        icon: 'iconoir:delivery',
        image: '/images/logo/np.png',
        logo: '/images/logo/np-mini.png',
        price: novaposhtaCourier || providerTariffsLabel.value,
        isPriceObject: !!novaposhtaCourier
      }, 
      {
        key: 'default_pickup',
        title: t('delivery.default_pickup'),
        label: t('delivery.pickup'),
        icon: 'iconoir:shop-four-tiles',
        image: '/images/company.png',
        logo: '/images/logo/company-mini.png',
        price: fromShopLabel.value,
        isPriceObject: false
      }, 
      {
        key: 'default_address',
        title: t('delivery.default_address'),
        label: t('delivery.address'),
        icon: 'iconoir:shop-four-tiles',
        image: '/images/company.png',
        logo: '/images/logo/company-mini.png',
        price: providerTariffsLabel.value,
        isPriceObject: false
      }
    ]
  })

  const deliveryMethods = computed(() => {
    const methodKeys = get('shipping.methods') || []
    return methods.value.filter(method => methodKeys.includes(method.key))
  })


  const deliveryVendors = computed(() => {
    return vendors.value.filter(vendor => vendor.countries.includes(region.value))
  })

  return {
    vendors: deliveryVendors,
    methods: deliveryMethods,
    defaultPrice
  }
}

const normalizeRates = (value: unknown): Array<Record<string, any>> => {
  if (!value) return []
  if (Array.isArray(value)) return value as Array<Record<string, any>>
  if (typeof value === 'string') {
    try {
      const parsed = JSON.parse(value)
      if (Array.isArray(parsed)) return parsed as Array<Record<string, any>>
    } catch {
      return []
    }
  }
  return []
}

const minRatePrice = (rates: Array<Record<string, any>>): number | null => {
  if (!rates.length) return null
  const prices = rates
    .map((rate) => Number(rate?.price))
    .filter((price) => Number.isFinite(price))
  if (!prices.length) return null
  return Math.min(...prices)
}
