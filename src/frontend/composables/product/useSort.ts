export const useSort = () => {

  const {t} = useI18n({useScope: 'global'})
  
  const options = computed(() => {
    return [
      {
        by: 'created_at',
        dir: 'asc',
        caption: t('label.sorting.news_asc')
      },{
        by: 'created_at',
        dir: 'desc',
        caption: t('label.sorting.news_desc')
      },{
        by: 'price',
        dir: 'asc',
        caption: t('label.sorting.price_asc')
      }, {
        by: 'price',
        dir: 'desc',
        caption: t('label.sorting.price_desc')
      }
    ]
  })

  return {
    options
  }
}