import {useComparisonStore} from '~/store/comparison'

export const useComparison = (productId: Number) => {

  const {t} = useI18n()
  const id = ref(productId)

  const isComparison = computed(() => {
    return useComparisonStore().isActive(id.value)
  })

  const toComparisonHandler = async() => {
    await useComparisonStore().toggle(id.value).then((r) => {
      if(r === 'add') {
        useNoty().setNoty({
          title: t('noty.add'),
          content: t('noty.comparison.add'),
          type: 'success'
        })
      }else if(r === 'remove') {
        useNoty().setNoty({
          title: t('noty.remove'),
          content: t('noty.comparison.remove'),
          type: 'warning'
        })
      }
    })
  }

  return {
    isComparison,
    toComparisonHandler
  }
}