export const useContacts = () => {
  const {get} = useSettings()

  const normalizeRows = (value: unknown) => {
    if (Array.isArray(value)) {
      return value
    }

    if (typeof value === 'string') {
      try {
        const parsed = JSON.parse(value)
        return Array.isArray(parsed) ? parsed : []
      } catch {
        return []
      }
    }

    return []
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

  const phone = computed(() => {
    return get('site.contacts.phone')
  })

  const email = computed(() => {
    return get('site.contacts.email')
  })

  const legacyMap = computed(() => {
    return get('site.contacts.map')
  })

  const legacySchedule = computed(() => {
    return get('site.contacts.schedule')
  })

  const legacyAddress = computed(() => {
    return get('site.contacts.address')
  })

  const pickupLocations = computed(() => {
    const rawValue = get('site.contacts.pickup_locations')
    const fallbackAddress = normalizeText(legacyAddress.value)
    const fallbackSchedule = normalizeText(legacySchedule.value)
    const fallbackMap = normalizeText(legacyMap.value)

    const rows = normalizeRows(rawValue)
    const normalized = rows.map((item, index) => {
      if (typeof item === 'string') {
        const address = normalizeText(item)
        if (!address) return null

        return {
          id: `pickup-${index + 1}`,
          title: '',
          address,
          schedule: index === 0 ? fallbackSchedule : '',
          map: index === 0 ? fallbackMap : '',
          mapSrc: index === 0 ? extractMapSrc(fallbackMap) : '',
          label: address,
        }
      }

      if (!item || typeof item !== 'object') {
        return null
      }

      const address = normalizeText((item as Record<string, unknown>).address ?? (item as Record<string, unknown>).value)
      if (!address) return null

      const title = normalizeText((item as Record<string, unknown>).title ?? (item as Record<string, unknown>).name ?? (item as Record<string, unknown>).label)
      const itemSchedule = normalizeText((item as Record<string, unknown>).schedule) || (index === 0 ? fallbackSchedule : '')
      const itemMap = normalizeText((item as Record<string, unknown>).map) || (index === 0 ? fallbackMap : '')

      return {
        id: normalizeText((item as Record<string, unknown>).id) || `pickup-${index + 1}`,
        title,
        address,
        schedule: itemSchedule,
        map: itemMap,
        mapSrc: extractMapSrc(itemMap),
        label: [title, address].filter(Boolean).join(', '),
      }
    }).filter(Boolean) as Array<{
      id: string
      title: string
      address: string
      schedule: string
      map: string
      mapSrc: string
      label: string
    }>

    if (normalized.length) {
      return normalized
    }

    if (!fallbackAddress) {
      return []
    }

    return [{
      id: 'pickup-1',
      title: '',
      address: fallbackAddress,
      schedule: fallbackSchedule,
      map: fallbackMap,
      mapSrc: extractMapSrc(fallbackMap),
      label: fallbackAddress,
    }]
  })

  const address = computed(() => {
    return pickupLocations.value[0]?.address || normalizeText(legacyAddress.value)
  })

  const schedule = computed(() => {
    return pickupLocations.value[0]?.schedule || normalizeText(legacySchedule.value)
  })

  const scheduleLines = computed(() => {
    const hasManyLocations = pickupLocations.value.length > 1

    return pickupLocations.value
      .map((item) => {
        if (!item.schedule) {
          return ''
        }

        return hasManyLocations ? `${item.label}: ${item.schedule}` : item.schedule
      })
      .filter(Boolean)
  })

  const scheduleSummary = computed(() => {
    return scheduleLines.value.join(' | ') || schedule.value
  })

  const mapLocations = computed(() => {
    return pickupLocations.value.filter((item) => item.map)
  })

  const map = computed(() => {
    return mapLocations.value[0]?.map || normalizeText(legacyMap.value)
  })

  const mapSrc = computed(() => {
    return mapLocations.value[0]?.mapSrc || extractMapSrc(legacyMap.value)
  })

  const addressSummary = computed(() => {
    return pickupLocations.value.map((item) => item.label).join(' | ')
  })

  const addressLines = computed(() => {
    return pickupLocations.value.map((item) => item.label)
  })

  return {
    phone,
    email,
    address,
    addressSummary,
    addressLines,
    pickupLocations,
    map,
    mapLocations,
    mapSrc,
    schedule,
    scheduleLines,
    scheduleSummary
  }
}
