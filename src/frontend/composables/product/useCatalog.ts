
import {useProductStore} from '~/store/product'

export const useCatalog = (query: Object) => {
  const catalogMeta = useState('catalogMeta', () => {return {}})
  const pending = useState('pendingState', () => {return false})

  const getProducts = async (query: Object, name = 'catalog', state = true) => {
    if(state) {
      pending.value = true
    }

    return useAsyncData(name, () => useProductStore().index(query)).then(({data, error}) => {

      if(error?.value){
        throw error.value
      }

      if(state) {
        catalogMeta.value = data?.value?.meta
      }

      return {
        products: data?.value?.products,
        meta: data?.value?.meta,
        filters: data?.value?.filters
      }
    }).finally(() => {
      if(state) {
        pending.value = false
      }
    })
  }

  return {
    getProducts,
    catalogMeta,
    pending
  }
}