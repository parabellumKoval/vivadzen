export type TgId = number | string

export type TgImage = {
  src?: string
  alt?: string
  title?: string
  url?: string
} | string | null

export type TgCategory = Record<string, any> & {
  id?: TgId
  name?: string
  slug?: string
  count?: number
  products_count?: number
  children?: TgCategory[]
}

export type TgProductVariant = Record<string, any> & {
  id?: TgId
  name?: string
  short_name?: string
  slug?: string
  price?: number
  oldPrice?: number
  old_price?: number
  currency?: string
  inStock?: number
}

export type TgProduct = Record<string, any> & {
  id?: TgId
  name?: string
  slug?: string
  price?: number
  oldPrice?: number
  old_price?: number
  currency?: string
  image?: TgImage
  images?: TgImage[]
  modifications?: TgProductVariant[]
  category?: TgCategory
  categories?: TgCategory[]
  content?: string
  description?: string
  short_text?: string
  inStock?: number
}

export type TgCartItem = {
  productId: TgId
  variantId?: TgId | null
  quantity: number
  name: string
  slug?: string
  image: string
  price: number
  originalPrice?: number | null
  currency?: string
  variantLabel?: string
}

export type TgDeliveryMethod = {
  key: string
  title: string
  label?: string
  price?: string | { amount: number; currency: string } | null
  isPriceObject?: boolean
}

export type TgPaymentMethod = {
  key: string
  title: string
  label?: string
  payments?: string[]
}

export type TgOrder = Record<string, any> & {
  id?: TgId
  code?: string
  status?: string
  created_at?: string
  price?: number
  currency?: string
  products?: unknown[]
}
