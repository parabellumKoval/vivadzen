
import {useProductStore} from '~/store/product'

export const useCatalog = (query: Object) => {

  const getProducts = async (query: Object) => {

    return useAsyncData('catalog', () => useProductStore().index(query)).then(({data, error}) => {

      if(error?.value){
        throw error.value
      }

      return {
        products: data?.value?.products,
        meta: data?.value?.meta,
        filters: data?.value?.filters
      }
    })
  }

  return {
    getProducts
  }
}