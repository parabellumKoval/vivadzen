export const useMenu = () => {
  const {t} = useI18n()

  const customer = computed(() => {
    return [
      {
        id: 1,
        link: '/o-nas',
        title: 'Договор публичной оферты'
      },{
        id: 7,
        link: '/science',
        title: 'Условия возврата товара'
      },{
        id: 8,
        link: '/certification',
        title: 'Вопрос / Ответ'
      },{
        id: 2,
        link: '/guarantees',
        title: t('crumbs.guarantees')
      },{
        id: 3,
        link: '/delivery',
        title: t('crumbs.delivery')
      },{
        id: 4,
        link: '/payment',
        title: t('crumbs.payment')
      }
    ]
  })

  const info = computed(() => {
    return [
      {
        id: 1,
        link: '/o-nas',
        title: t('crumbs.o-nas')
      },{
        id: 2,
        link: '/science',
        title: 'Новости'
      },{
        id: 3,
        link: '/articles',
        title: 'Отзывы'
      },{
        id: 4,
        link: '/contacts',
        title: 'Блог'
      },{
        id: 5,
        link: '/contacts',
        title: 'Акции'
      },{
        id: 6,
        link: '/contacts',
        title: 'Контакты'
      }
    ]
  })

  return {
    customer,
    info
  }
}