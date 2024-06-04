export const useCard = (product: Product) => {

  const { t } = useI18n({useScope: 'global'})

  const photos = computed(() => {
    if(product.images?.length) {
      return product.images.filter((item) => {
        return item.src
      }).map((item) => {
        return {
          alt: item.alt || product.name,
          title: item.title || product.name,
          src: '/server/images/products/' + item.src
        }
      })
    }else {
      return [{
        alt: product.name,
        title: product.name,
        src: './images/noimage.png'
      }]
    }
  })

  const photoAlt = computed(() => {
    return product.image?.alt || product.name
  })

  const photoTitle = computed(() => {
    return product.image?.title || product.name
  })

  const photoSize = computed(() => {
    return product.image?.size || ''
  })

  const photo = computed(() => {
    if(product.image?.src) {
      return '/server/images/products/' + product.image.src
    } else {
      return '/images/noimage.png'
    }
  })

  const stock = computed(() => {
    return product.inStock > 0? 'in-stock': 'not-in-stock'
  })

  const label = computed(() => {
    if(!product.oldPrice || product.oldPrice <= product.price)
      return null


    if(product.isTop){
      return {
        class: 'violet',
        text: t('label.product.top')
      }
    }else if(product.oldPrice - product.price >= product.price / 10){
      return {
        class: 'orange',
        text: t('label.product.price')
      }
    }else if(product.oldPrice > product.price) {
      return {
        class: 'red',
        text: t('label.product.sale')
      }
    }else {
      return null
    }
  })

  return {
    photos,
    photoAlt,
    photoTitle,
    photoSize,
    photo,
    stock,
    label
  }
}