
import {
  normalizeStructuredArray,
  resolveCurrencyCode,
  resolveRateDisplayPrice,
} from '~/utils/shipping-pricing'

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
    },{
      id: 7,
      logo: '/images/logo/company.png',
      title: 'Messenger.cz',
      countries: ['cz']
    }
  ])

  const providerTariffsLabel = computed(() => t('delivery.provider_tariffs'))
  const fromShopLabel = computed(() => t('delivery.from_shop'))
  const fallbackCurrency = computed(() => resolveCurrencyCode(currency.value, 'USD'))

  const packetaPickupPrice = computed(() => {
    return resolveRateDisplayPrice({
      rates: get('shipping.zasilkovna.pickup_rates'),
      currency: get('shipping.zasilkovna.currency'),
      fallbackCurrency: fallbackCurrency.value,
      vatRate: get('shipping.zasilkovna.vat_rate'),
      vatIncluded: get('shipping.zasilkovna.vat_included'),
    })
  })

  const packetaHomePrice = computed(() => {
    return resolveRateDisplayPrice({
      rates: get('shipping.zasilkovna.home_rates'),
      currency: get('shipping.zasilkovna.currency'),
      fallbackCurrency: fallbackCurrency.value,
      vatRate: get('shipping.zasilkovna.vat_rate'),
      vatIncluded: get('shipping.zasilkovna.vat_included'),
    })
  })

  const novaposhtaWarehousePrice = computed(() => {
    const branchRates = normalizeStructuredArray(get('shipping.novaposhta.branch_rates'))
    const lockerRates = normalizeStructuredArray(get('shipping.novaposhta.locker_rates'))

    return resolveRateDisplayPrice({
      rates: [...branchRates, ...lockerRates],
      currency: get('shipping.novaposhta.currency'),
      fallbackCurrency: fallbackCurrency.value,
      vatRate: get('shipping.novaposhta.vat_rate'),
      vatIncluded: get('shipping.novaposhta.vat_included'),
    })
  })

  const novaposhtaCourierPrice = computed(() => {
    return resolveRateDisplayPrice({
      rates: get('shipping.novaposhta.courier_rates'),
      currency: get('shipping.novaposhta.currency'),
      fallbackCurrency: fallbackCurrency.value,
      vatRate: get('shipping.novaposhta.vat_rate'),
      vatIncluded: get('shipping.novaposhta.vat_included'),
    })
  })

  const messengerAddressPrice = computed(() => {
    return resolveRateDisplayPrice({
      rates: get('shipping.messenger.address_rates'),
      currency: get('shipping.messenger.currency'),
      fallbackCurrency: fallbackCurrency.value,
      vatRate: get('shipping.messenger.vat_rate'),
      vatIncluded: get('shipping.messenger.vat_included'),
      fuelPercent: get('shipping.messenger.fuel_surcharge_percent'),
    })
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
      case 'messenger_address':
        return messengerAddressPrice.value
      default:
        return null
    }
  }

  const methods = computed(() => {
    const packetaPickup = packetaPickupPrice.value
    const packetaHome = packetaHomePrice.value
    const novaposhtaWarehouse = novaposhtaWarehousePrice.value
    const novaposhtaCourier = novaposhtaCourierPrice.value
    const messengerAddress = messengerAddressPrice.value

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
        isPriceObject: false,
        meta: fromShopLabel.value,
        isMetaPriceObject: false
      }, 
      {
        key: 'messenger_address',
        title: t('delivery.messenger_address'),
        label: t('delivery.messenger_address'),
        icon: 'iconoir:delivery',
        image: '/images/logo/messenger.svg',
        logo: '/images/logo/messenger.svg',
        price: messengerAddress || providerTariffsLabel.value,
        isPriceObject: !!messengerAddress,
        meta: messengerAddress || providerTariffsLabel.value,
        isMetaPriceObject: !!messengerAddress
      },
      {
        key: 'default_address',
        title: t('delivery.default_address'),
        label: t('delivery.address'),
        icon: 'iconoir:shop-four-tiles',
        image: '/images/company.png',
        logo: '/images/logo/company-mini.png',
        price: providerTariffsLabel.value,
        isPriceObject: false,
        meta: providerTariffsLabel.value,
        isMetaPriceObject: false
      }
    ].map((method) => ({
      ...method,
      meta: method.meta ?? method.price,
      isMetaPriceObject: method.isMetaPriceObject ?? method.isPriceObject
    }))
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
