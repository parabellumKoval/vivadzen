type ProductImageRecord = Record<string, any>

const PRODUCT_IMAGE_FOLDER = 'products'

const isNonEmptyString = (value: unknown): value is string => {
  return typeof value === 'string' && value.trim().length > 0
}

const extractImageSource = (image: unknown): string | null => {
  if (isNonEmptyString(image)) {
    return image.trim()
  }

  if (!image || typeof image !== 'object') {
    return null
  }

  const candidate = image as ProductImageRecord
  const directKeys = ['src', 'url', 'path', 'original']

  for (const key of directKeys) {
    if (isNonEmptyString(candidate[key])) {
      return candidate[key].trim()
    }
  }

  if (candidate.image) {
    return extractImageSource(candidate.image)
  }

  return null
}

const normalizeProductImageSrc = (src: string | null) => {
  if (!src) {
    return null
  }

  if (/^(https?:)?\/\//i.test(src) || src.startsWith('/') || src.startsWith('data:') || src.startsWith('blob:')) {
    return src
  }

  return useImg().folderSrc(src, PRODUCT_IMAGE_FOLDER)
}

export const normalizeKratomProductImage = (image: unknown, fallbackText = '') => {
  const src = normalizeProductImageSrc(extractImageSource(image))

  if (!src) {
    return null
  }

  const base = image && typeof image === 'object' ? image as ProductImageRecord : {}
  const alt = isNonEmptyString(base.alt) ? base.alt.trim() : fallbackText
  const title = isNonEmptyString(base.title) ? base.title.trim() : (alt || fallbackText)

  return {
    ...base,
    src,
    alt,
    title,
  }
}

export const normalizeKratomProductImages = (images: unknown, fallbackText = '') => {
  const list = Array.isArray(images)
    ? images
      .map((image) => normalizeKratomProductImage(image, fallbackText))
      .filter((image): image is NonNullable<ReturnType<typeof normalizeKratomProductImage>> => Boolean(image?.src))
    : []

  if (list.length) {
    return list
  }

  return [{
    src: useImg().noImage,
    alt: fallbackText,
    title: fallbackText,
  }]
}

export const resolveKratomProductImageSrc = (product: ProductImageRecord | null | undefined) => {
  const fallbackText = isNonEmptyString(product?.name) ? product.name.trim() : ''
  const primaryImage = normalizeKratomProductImage(product?.image, fallbackText)

  if (primaryImage?.src) {
    return primaryImage.src
  }

  return normalizeKratomProductImages(product?.images, fallbackText)[0]?.src || useImg().noImage
}
