import {useAttributeStore} from '~/store/attribute'
import {useBrandStore} from '~/store/brand'

export const useFilter = (query: Object) => {
  const {t} = useI18n()

  const getFilters = async (query: Object) => {
    return useAsyncData('filters', async () => {
      return await Promise.all([
        getBrands(query).then((data) => {
          // console.log('brands - ', data)
          return data || []
        }),
        getAttributes(query).then((data) => {
          // console.log('attributes - ', data)
          return data || []
        })
      ]).then(([brands, attributes]) => {
        // console.log('brands, attributes - ', brands, attributes)
        return prepareFilters(brands, attributes)
      })
    }).then((data) => {
      return data
    })
  }

  const getBrands = async (query: Object) => {
    return await useBrandStore().filter(query).then((data) => {
      return data
    })
  }

  const getAttributes = async (query: Object) => {
    return await useAttributeStore().index(query).then((data) => {
      return data
    })
  }

  const prepareFilters = (brands: Object[], attributes: Object[]) => {
    const filtersCopy = [...attributes]

    filtersCopy.push({
      id: 'price',
      name: t('label.price'),
      si: t('label.grn'),
      isOpen: true,
      type: 'number'
    })

    filtersCopy.unshift({
      id: 'brand',
      name: t('label.brand'),
      si: null,
      isOpen: true,
      noMeta: true,
      type: 'brand',
      values: [...brands]
    })

    filtersCopy.unshift({
      id: 'selections',
      name: t('label.selections'),
      si: null,
      isOpen: true,
      noMeta: true,
      noSearch: true,
      type: 'checkbox',
      values: [
        {
          id: 'with_sales',
          value: '🎁 ' + t('title.sales')
        },{
          id: 'top_sales',
          value: '🛍 ' + t('label.top_sales')
        },{
          id: 'top_price',
          value: '💸 ' + t('label.top_price')
        },{
          id: 'with_rating',
          value: '💬 ' + t('label.with_rates')
        },{
          id: 'in_stock',
          value: '📦 ' + t('label.only_in_stock')
        }
      ]
    })

    return filtersCopy
  }

  const prepareAttrs = (attrs) => {

    let filters = []
    let brands = null
    let selections = null
    let price = null

    for(let attr of attrs){
      const key = attr.id
      const value = attr.values

      if(key === 'price') {
        price = {...value}
        break
      }

      if(key === 'brand') {
        brands = [...value]
        break
      }

      if(key === 'selections') {
        selections = [...value]
        continue
      }

      if(Array.isArray(value)) {
        for(const v of value) {
          filters.push({
            attr_id: parseInt(key),
            attr_value_id: parseInt(v)
          })
        }
      }else if(typeof value === 'object'){
        filters.push({
          attr_id: parseInt(key),
          from: value.min,
          to: value.max,
        })
      }
    }

    return {filters, brands, selections, price}
  }

  return {
    getAttributes,
    getBrands,
    getFilters,
    prepareAttrs
  }
}