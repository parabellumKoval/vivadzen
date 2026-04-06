export const PARTNER_STORES_HASH = 'partner-stores'

type RawPartnerStore = Record<string, unknown>

export interface PartnerStore {
  name: string
  city: string
  address: string
  schedule: string
  phone: string
  email: string
  map: string
  mapSrc: string
}

const normalizeText = (value: unknown) => {
  if (typeof value === 'string') {
    return value.trim()
  }

  if (value && typeof value === 'object' && 'value' in value && typeof (value as { value?: unknown }).value === 'string') {
    return String((value as { value: string }).value).trim()
  }

  return ''
}

const extractMapSrc = (value: unknown) => {
  const raw = normalizeText(value)
  if (!raw) return ''

  const match = raw.match(/src=["']([^"']+)["']/i)
  if (match?.[1]) {
    return match[1].trim()
  }

  return raw
}

export const usePartnerStores = () => {
  const { get } = useSettings()

  const partners = computed<PartnerStore[]>(() => {
    const rawPartners = get('site.contacts.partners', [])
    const items = Array.isArray(rawPartners) ? rawPartners : []

    return items
      .map((item) => {
        const partner = (item && typeof item === 'object' ? item : {}) as RawPartnerStore

        return {
          name: normalizeText(partner.name),
          city: normalizeText(partner.city),
          address: normalizeText(partner.address),
          schedule: normalizeText(partner.schedule),
          phone: normalizeText(partner.phone),
          email: normalizeText(partner.email),
          map: normalizeText(partner.map),
          mapSrc: extractMapSrc(partner.map),
        }
      })
      .filter((partner) => partner.name && partner.city && partner.address)
  })

  const uniqueCities = computed(() => {
    const seen = new Set<string>()

    return partners.value.reduce<string[]>((cities, partner) => {
      const cityKey = partner.city.trim().toLocaleLowerCase()
      if (!cityKey || seen.has(cityKey)) {
        return cities
      }

      seen.add(cityKey)
      cities.push(partner.city)
      return cities
    }, [])
  })

  return {
    partners,
    uniqueCities,
  }
}
