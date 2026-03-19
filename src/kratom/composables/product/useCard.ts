export const useCard = (product: Product) => {

  const { t } = useI18n({useScope: 'global'})
  const { getLabel: getCampaignLabel, getTimerText, isActive: isCampaignActive } = useCampaignPresentation()

  const photos = computed(() => {
    if(product.images?.length) {
      return product.images.filter((item) => {
        return item.src
      }).map((item) => {
        return {
          alt: item?.alt || product.name,
          title: item?.title || product.name,
          // src: useImg().folderSrc(item.src, 'products')
          src: item.src
        }
      })
    }else {
      return [{
        alt: product.name,
        title: product.name,
        src: useImg().noImage
      }]
    }
  })

  const mainPhoto = computed(() => {
    return photos.value?.[0]
  })

  const photoAlt = computed(() => {
    return product.image?.alt || mainPhoto.value?.alt || product.name
  })

  const photoTitle = computed(() => {
    return product.image?.title || mainPhoto.value?.title || product.name
  })

  const photoSize = computed(() => {
    return product.image?.size || mainPhoto.value?.size || ''
  })

  const photo = computed(() => {
    // return useImg().product(product.image)
    return product.image?.src || mainPhoto.value?.src || useImg().noImage
  })

  const stock = computed(() => {
    return product.inStock > 0? 'in-stock': 'not-in-stock'
  })

  const salePercent = computed(() => {
    if(!product.oldPrice)
      return null

    return  Math.ceil(100 - (product.price * 100 / product.oldPrice))
  })

  const hasSale = computed(() => {
    return Boolean(product.oldPrice && product.oldPrice > product.price)
  })

  const hasActiveCampaign = computed(() => {
    return isCampaignActive(product?.campaign)
  })

  const campaignLabel = computed(() => {
    return getCampaignLabel(product?.campaign)
  })

  const campaignTimer = computed(() => {
    return getTimerText(product?.campaign, 'card')
  })

  const label = computed(() => {
    if (campaignLabel.value) {
      return {
        class: 'campaign',
        text: campaignLabel.value,
        marquee: campaignLabel.value.length > 18
      }
    }

    if(!hasSale.value)
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
        text: t('label.product.sale') + ' -' + salePercent.value + '%'
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
    label,
    campaignTimer,
    hasSale,
    hasActiveCampaign
  }
}
