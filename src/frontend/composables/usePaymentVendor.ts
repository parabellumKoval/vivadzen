export const usePaymentVendor = () => {
  const {t} = useI18n({useScope: 'global'})
  
  const all = [
    {
      id: 1,
      logo: '/images/logo/google-pay.png',
      title: 'Google Pay'
    },{
      id: 2,
      logo: '/images/logo/apple-pay.png',
      title: 'Apple Pay'
    },{
      id: 3,
      logo: '/images/logo/24-pay.png',
      title: '24 Pay'
    },{
      id: 4,
      logo: '/images/logo/mono-mini.png',
      title: 'Monobank'
    },
    {
      id: 5,
      logo: '/images/logo/visa.png',
      title: 'Visa'
    },{
      id: 6,
      logo: '/images/logo/mastercard.png',
      title: 'Mastercard'
    }
  ]

  const main = [
    {
      id: 1,
      logo: '/images/logo/visa.png',
      title: 'Visa'
    },{
      id: 2,
      logo: '/images/logo/mastercard.png',
      title: 'Mastercard'
    }
  ]

  const types = [
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
  ]

  return {
    all,
    main,
    types
  }
}