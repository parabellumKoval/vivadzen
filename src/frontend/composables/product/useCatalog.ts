import {useProductStore} from '~/store/product'
import {useAttributeStore} from '~/store/attribute'

export const useCatalog = (query: Object) => {

  const getReviews =  async (query: Object) => {

  }

  const getCategory =  async (query: Object) => {

  }
  
  const getBrand =  async (query: Object) => {

  }

  const prepareAttrs = (filters) => {
    // console.log('prepareAttrs', filters)
    
    const attrs = []

    for (const [key, value] of Object.entries(filters)) {
      console.log(key,value)

      if(Array.isArray(value)) {
        for(const v of value) {
          attrs.push({
            attr_id: parseInt(key),
            attr_value_id: parseInt(v)
          })

        }
      }else if(typeof value === 'object'){
        attrs.push({
          attr_id: parseInt(key),
          from: value.min,
          to: value.max,
        })
      }
    }


    // console.log('attrs', attrs)
    return attrs
  }



  const getProducts = async (query: Object, refresh: boolean) => {

    // let searchParams = new URLSearchParams(query);
    // let searchParamsString = searchParams.toString()
    // new URLSearchParams(body).toString()
    // var json = JSON.stringify(query);
    // let q = encodeURIComponent(json)

    return useAsyncData('catelog', () => useProductStore().index(query)).then(({data, error}) => {

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

  const getAttributes = async (query: Object) => {
    return useAsyncData(`attributes`, () => useAttributeStore().index(query)).then(({data, error}) => {
      console.log('getAttributes', data)
      return {
        attributes: data.value? prepareAttributes(data.value): []
      }
    })
  }

  const prepareAttributes = (values: Object[]) => {
    const filtersCopy = [...values]

    filtersCopy.push({
      id: 'price',
      name: 'Цена',
      si: 'грн.',
      isOpen: true,
      type: 'number'
    })

    return filtersCopy
  }

  return {
    getProducts,
    getAttributes,
    prepareAttrs
  }
}