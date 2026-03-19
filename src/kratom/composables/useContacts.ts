export const useContacts = () => {
  const {get} = useSettings()

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

  const schedule = computed(() => {
    return get('site.contacts.schedule')
  })


  const all = computed(() => {
    return {
      phone: phone.value,
      email: email.value,
      address: address.value,
      schedule: schedule.value,
      map: map.value
    }
  })

  return {
    phone,
    email,
    address,
    map,
    schedule,
    all
  }
}