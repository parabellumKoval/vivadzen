type ProductAttributeValue = {
  id?: number | string
  value?: string | null
  slug?: string | null
}

type ProductAttribute = {
  id?: number | string
  name?: string | null
  slug?: string | null
  value?: ProductAttributeValue[] | ProductAttributeValue | string | null
}

type BadgeResult = {
  attribute: ProductAttribute
  label: string
  key: string
  image: string
}

const normalizeText = (value: unknown) => {
  return String(value || '')
    .trim()
    .toLowerCase()
    .normalize('NFKD')
    .replace(/[\u0300-\u036f]/g, '')
}

const normalizeKey = (value: unknown) => {
  return normalizeText(value)
    .replace(/&/g, ' and ')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
}

const toValueList = (value: ProductAttribute['value']): ProductAttributeValue[] => {
  if (Array.isArray(value)) {
    return value.filter((item) => item && typeof item === 'object')
  }

  if (value && typeof value === 'object') {
    return [value]
  }

  if (typeof value === 'string' && value.trim() !== '') {
    return [{ value }]
  }

  return []
}

const getAttributeLabel = (attribute: ProductAttribute) => {
  const values = toValueList(attribute?.value)
  const joinedValues = values
    .map((item) => String(item?.value || '').trim())
    .filter(Boolean)
    .join(', ')

  if (joinedValues) {
    return joinedValues
  }

  if (typeof attribute?.value === 'string') {
    return attribute.value.trim()
  }

  return ''
}

const getPrimaryValueKey = (attribute: ProductAttribute) => {
  const values = toValueList(attribute?.value)
  const primary = values[0]

  if (!primary) {
    return ''
  }

  return normalizeKey(primary.slug || primary.value || '')
}

const findAttribute = (
  attributes: ProductAttribute[],
  options: { slugs?: string[]; nameHints?: string[] } | undefined,
) => {
  const slugCandidates = (options?.slugs || []).map(normalizeKey).filter(Boolean)
  const nameHints = (options?.nameHints || []).map(normalizeText).filter(Boolean)

  const bySlug = attributes.find((attribute) => {
    const slug = normalizeKey(attribute?.slug)
    return slug && slugCandidates.includes(slug)
  })

  if (bySlug) {
    return bySlug
  }

  return attributes.find((attribute) => {
    const name = normalizeText(attribute?.name)
    return name && nameHints.some((hint) => name.includes(hint))
  }) || null
}

const resolveBadge = (
  kind: 'color' | 'origin',
  attributes: ProductAttribute[],
  runtimeConfig: ReturnType<typeof useRuntimeConfig>,
): BadgeResult | null => {
  const storeConfig = runtimeConfig.public.kratomStore || {}
  const attributeConfig = storeConfig.badgeAttributes?.[kind]
  const imageMap = storeConfig.badgeImages?.[kind] || {}
  const attribute = findAttribute(attributes, attributeConfig)

  if (!attribute) {
    return null
  }

  const key = getPrimaryValueKey(attribute)
  const image = key ? imageMap[key] : null
  const label = getAttributeLabel(attribute)

  if (!image || !label) {
    return null
  }

  return {
    attribute,
    key,
    label,
    image,
  }
}

export const useKratomBadges = (attributesInput: ProductAttribute[] | null | undefined) => {
  const runtimeConfig = useRuntimeConfig()
  const attributes = Array.isArray(attributesInput) ? attributesInput : []

  const color = resolveBadge('color', attributes, runtimeConfig)
  const origin = resolveBadge('origin', attributes, runtimeConfig)

  return {
    color,
    origin,
    list: [origin, color].filter(Boolean) as BadgeResult[],
  }
}

export const formatKratomAttributeValue = (attribute: ProductAttribute | null | undefined) => {
  if (!attribute) {
    return ''
  }

  return getAttributeLabel(attribute)
}
