export const useContacts = () => {
  const {t} = useI18n()

  const phone = '+38 (099) 777-33-45'
  const phone2 = '+38 (097) 777-33-45'
  const email = 'djini.in.ua@gmail.com'
  const address = computed(() => {
    return t('meta.address')
  })

  const all = computed(() => {
    return {
      phone: phone,
      phone2: phone2,
      email: email,
      address: address.value
    }
  })

  return {
    phone,
    phone2,
    email,
    address,
    all
  }
}