import type { TgImage, TgProduct, TgProductVariant } from '~/types/tg'

const numberValue = (value: unknown, fallback = 0) => {
  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : fallback
}

const cleanHtml = (value?: string | null) => {
  return String(value || '')
    .replace(/<script[\s\S]*?>[\s\S]*?<\/script>/gi, '')
    .replace(/<style[\s\S]*?>[\s\S]*?<\/style>/gi, '')
}

const imageSrc = (image: TgImage): string => {
  if (!image) return ''
  if (typeof image === 'string') return image
  return image.src || image.url || ''
}

export const useTgProductUtils = () => {
  const config = useRuntimeConfig()
  const { currency } = useTgRouting()

  const noImage = computed(() => String(config.public.noimage || '/images/noimage.png'))

  const imagesOf = (product?: TgProduct | null) => {
    const images: string[] = []

    if (product?.image) {
      const src = imageSrc(product.image)
      if (src) images.push(src)
    }

    if (Array.isArray(product?.images)) {
      for (const image of product.images) {
        const src = imageSrc(image)
        if (src && !images.includes(src)) images.push(src)
      }
    }

    return images.length ? images : [noImage.value]
  }

  const imageOf = (product?: TgProduct | null) => imagesOf(product)[0]

  const variantsOf = (product?: TgProduct | null): TgProductVariant[] => {
    return Array.isArray(product?.modifications)
      ? product.modifications.filter((variant) => variant && variant.id)
      : []
  }

  const priceOf = (product?: TgProduct | TgProductVariant | null) => numberValue(product?.price)
  const oldPriceOf = (product?: TgProduct | TgProductVariant | null) => {
    return numberValue(product?.oldPrice ?? product?.old_price, 0)
  }

  const currencyOf = (product?: TgProduct | TgProductVariant | null) => {
    return product?.currency || currency.value
  }

  const hasSale = (product?: TgProduct | TgProductVariant | null) => {
    const price = priceOf(product)
    const oldPrice = oldPriceOf(product)
    return oldPrice > 0 && oldPrice > price
  }

  const variantLabel = (variant?: TgProductVariant | null) => {
    return variant?.short_name || variant?.name || ''
  }

  const formatMoney = (value: unknown, code?: string | null) => {
    const amount = numberValue(value)
    const currencyCode = code || currency.value
    try {
      return new Intl.NumberFormat(undefined, {
        style: 'currency',
        currency: currencyCode,
        maximumFractionDigits: Number.isInteger(amount) ? 0 : 2
      }).format(amount)
    } catch {
      return `${amount} ${currencyCode}`
    }
  }

  const descriptionOf = (product?: TgProduct | null) => {
    return cleanHtml(product?.content || product?.description || product?.short_text || '')
  }

  const isInStock = (product?: TgProduct | TgProductVariant | null) => {
    const stock = product?.inStock ?? product?.in_stock
    return stock === undefined || stock === null || Number(stock) > 0
  }

  return {
    noImage,
    imagesOf,
    imageOf,
    variantsOf,
    priceOf,
    oldPriceOf,
    currencyOf,
    hasSale,
    variantLabel,
    formatMoney,
    descriptionOf,
    isInStock
  }
}
