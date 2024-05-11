export const useMenu = () => {
  const {t} = useI18n()

  const customer = computed(() => {
    return [
      {
        id: 1,
        link: '/agreement',
        title: t('title.agreement')
      },{
        id: 7,
        link: '/returns',
        title: t('title.returns')
      },{
        id: 8,
        link: '/faq',
        title: t('title.qa')
      },{
        id: 2,
        link: '/guarantees',
        title: t('title.guarantees')
      },{
        id: 3,
        link: '/delivery',
        title: t('title.delivery')
      },{
        id: 4,
        link: '/payment',
        title: t('title.payment')
      }
    ]
  })

  const info = computed(() => {
    return [
      {
        id: 1,
        link: '/o-nas',
        title: t('title.o-nas')
      },{
        id: 2,
        link: '/news',
        title: t('title.news')
      },{
        id: 3,
        link: '/reviews/products',
        title: t('title.reviews')
      },{
        id: 4,
        link: '/brands',
        title: t('title.brands')
      },{
        id: 5,
        link: '/blog',
        title: t('title.blog')
      },{
        id: 6,
        link: '/sales',
        title: t('title.sales')
      },{
        id: 7,
        link: '/contacts',
        title: t('title.contacts')
      }
    ]
  })

  return {
    customer,
    info
  }
}