export const useContacts = () => {
  const {get} = useSettings()

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

  const address = computed(() => {
    return get('site.contacts.address')
  })

  const map = computed(() => {
    return get('site.contacts.map')
  })

  const mapSrc = computed(() => {
    return extractMapSrc(map.value)
  })

  const schedule = computed(() => {
    return get('site.contacts.schedule')
  })


  const all = computed(() => {
    return {
      phone: phone.value,
      email: email.value,
      address: address.value,
      schedule: schedule.value,
      map: map.value,
      mapSrc: mapSrc.value
    }
  })

  return {
    phone,
    email,
    address,
    map,
    mapSrc,
    schedule,
    all
  }
}
