import { useCartStore } from "~/store/cart"

export const usePayment = () => {
  const {t} = useI18n()
  const {get, all} = useSettings()
  const {region} = useRegion()
  const {order} = useCartStore()

  const orderDeliveryMethod = computed(() => {
    return order.delivery.method
  })

  const vendors = ref([
    {
      id: 1,
      logo: '/images/logo/google-pay.png',
      title: 'Google Pay',
      countries: ['ua', 'de', 'es', 'cz']
    },{
      id: 2,
      logo: '/images/logo/apple-pay.png',
      title: 'Apple Pay',
      countries: ['ua', 'de', 'es', 'cz']
    },{
      id: 3,
      logo: '/images/logo/24-pay.png',
      title: '24 Pay',
      countries: ['ua']
    },{
      id: 4,
      logo: '/images/logo/mono-mini.png',
      title: 'Monobank',
      countries: ['ua']
    },{
      id: 5,
      logo: '/images/logo/visa.png',
      title: 'Visa',
      countries: ['ua', 'de', 'es', 'cz']
    },{
      id: 6,
      logo: '/images/logo/mastercard.png',
      title: 'Mastercard',
      countries: ['ua', 'de', 'es', 'cz']
    }
  ])

  const main = ref([
    {
      id: 1,
      logo: '/images/logo/visa.png',
      title: 'Visa'
    },{
      id: 2,
      logo: '/images/logo/mastercard.png',
      title: 'Mastercard'
    }
  ])

  const types = ref([
    {
      id: 1,
      title: t('payment.type.delivery')
    },{
      id: 2,
      title: t('payment.type.cash')
    },{
      id: 3,
      title: t('payment.type.online')
    }
  ])

  const methods = ref([
    {
      key: 'zasilkovna_cod',
      title: t('payments.zasilkovna_cod.title'),
      label: t('payments.zasilkovna_cod.title'),
      icon: 'iconoir:wallet',
      image: '/images/logo/zasilkovna.png',
      logo: '/images/logo/z-mini.png',
      payments: ['packeta_warehouse', 'packeta_address'],
      // countries: ['cz']
    }, 
    {
      key: 'novaposhta_cod',
      title: t('payments.novaposhta_cod.title'),
      label: t('payments.novaposhta_cod.title'),
      icon: 'iconoir:wallet',
      image: '/images/logo/np.png',
      logo: '/images/logo/np-mini.png',
      payments: ['novaposhta_warehouse', 'novaposhta_address'],
      // countries: ['ua']
    }, 
    {
      key: 'default_cash',
      title: t('payments.default_cash.title'),
      label: t('payments.default_cash.title'),
      icon: 'iconoir:hand-cash',
      image: '/images/logo/company.png',
      logo: '/images/logo/z-mini.png',
      payments: ['default_pickup'],
      // countries: ['ua',]
    }, 
    {
      key: 'liqpay_online',
      title: t('payments.liqpay_online.title'),
      label: t('payments.liqpay_online.title'),
      icon: 'iconoir:laptop',
      image: '/images/logo/liqpay.png',
      logo: '/images/logo/np-mini.png',
      payments: ['packeta_warehouse', 'packeta_address', 'novaposhta_warehouse', 'novaposhta_address', 'default_address', 'default_pickup']
    }, 
    {
      key: 'card_online',
      title: t('payments.card_online.title'),
      label: t('payments.card_online.title'),
      icon: 'iconoir:credit-cards',
      image: '/images/logo/GpayApplepay.png',
      logo: '/images/logo/company-mini.png',
      payments: ['packeta_warehouse', 'packeta_address', 'novaposhta_warehouse', 'novaposhta_address', 'default_address', 'default_pickup']
    }, 
    {
      key: 'bank_transfer',
      title: t('payments.bank_transfer.title'),
      label: t('payments.bank_transfer.title'),
      icon: 'iconoir:bank',
      image: '/images/logo/bank.png',
      logo: '/images/logo/company-mini.png',
      payments: ['packeta_warehouse', 'packeta_address', 'default_address', 'default_pickup']
    }
  ])

  const paymentMethods = computed(() => {
    const methodKeys = get('payment.methods') || []
    return methods.value.filter((method) => {
      return methodKeys.includes(method.key) && method.payments.includes(orderDeliveryMethod.value)
    })
  })

  const paymentMethodsInfo = computed(() => {
    const methodKeys = get('payment.methods') || []
    return methods.value.filter((method) => {
      return methodKeys.includes(method.key)
    })
  })


  const paymentVendors = computed(() => {
    return vendors.value.filter(vendor => vendor.countries.includes(region.value))
  })

  return {
    vendors: paymentVendors,
    methods: paymentMethods,
    methodsInfo: paymentMethodsInfo,
    types,
    main
  }
}
