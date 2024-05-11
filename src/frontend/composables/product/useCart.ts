import {useCartStore} from '~/store/cart'

export const useCart = (productData: Product) => {

  const {t} = useI18n()

  const toCartHandler = () => {
    const data = {
      ...productData,
      amount: 1
    }
    
    useCartStore().add(data).then(() => {
      useNoty().setNoty({
        content: t('noty.product_to_cart', {product: productData.name})
      }, 2000)
    })
  }

  return {
    toCartHandler
  }
}