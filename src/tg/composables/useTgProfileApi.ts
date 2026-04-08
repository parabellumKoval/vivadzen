import { useTgUserStore, type TgCheckoutProfile, type TgSavedAddress } from '~/stores/user'

const profilePayload = (profile: Partial<TgCheckoutProfile>) => ({
  first_name: profile.first_name || null,
  last_name: profile.last_name || null,
  phone: profile.phone || null,
  email: profile.email || null,
  avatar_url: profile.avatar || null,
  payment_method: profile.paymentMethod || null
})

const addressPayload = (address: Partial<TgSavedAddress>) => ({
  id: address.id,
  deliveryMethod: address.deliveryMethod || null,
  settlement: address.settlement || null,
  street: address.street || null,
  house: address.house || null,
  room: address.room || null,
  zip: address.zip || null,
  warehouse: address.warehouse || null,
  price: address.price ?? null,
  priceCurrency: address.priceCurrency || null,
  title: address.title || null
})

const responseProfile = (response: any) => response?.data || response?.profile || response || null

export const useTgProfileApi = () => {
  const { $api } = useNuxtApp()
  const userStore = useTgUserStore()
  const { isTelegram } = useTelegram()
  const loading = useState('tg-profile-loading', () => false)

  const hasBackendIdentity = computed(() => Boolean(isTelegram.value && userStore.initData))

  const loadProfile = async () => {
    if (!hasBackendIdentity.value || loading.value) return null

    loading.value = true

    try {
      const response = await $api('/tg/profile', { method: 'GET' })
      const profile = responseProfile(response)
      userStore.hydrateBackendProfile(profile)
      return profile
    } catch {
      return null
    } finally {
      loading.value = false
    }
  }

  const saveProfile = async (profile: Partial<TgCheckoutProfile>) => {
    userStore.saveCheckoutProfile(profile)

    if (!hasBackendIdentity.value) return null

    try {
      const response = await $api('/tg/profile', {
        method: 'PUT',
        body: profilePayload({ ...userStore.checkoutProfile, ...profile })
      })
      const backendProfile = responseProfile(response)
      userStore.hydrateBackendProfile(backendProfile)
      return backendProfile
    } catch {
      return null
    }
  }

  const savePaymentMethod = async (method: string | null) => {
    return saveProfile({ paymentMethod: method })
  }

  const saveAddress = async (address: TgSavedAddress | null) => {
    if (!address) return null

    const localAddress = userStore.upsertAddress(address)
    if (!localAddress || !hasBackendIdentity.value) return localAddress

    try {
      const response = await $api('/tg/profile/addresses', {
        method: 'POST',
        body: addressPayload(localAddress)
      })
      const profile = responseProfile(response)
      userStore.hydrateBackendProfile(profile)
      return response?.address || localAddress
    } catch {
      return localAddress
    }
  }

  const updateAddress = async (address: TgSavedAddress) => {
    const localAddress = userStore.upsertAddress(address)
    if (!localAddress || !hasBackendIdentity.value) return localAddress

    try {
      const response = await $api(`/tg/profile/addresses/${encodeURIComponent(localAddress.id)}`, {
        method: 'PUT',
        body: addressPayload(localAddress)
      })
      const profile = responseProfile(response)
      userStore.hydrateBackendProfile(profile)
      return response?.address || localAddress
    } catch {
      return localAddress
    }
  }

  const deleteAddress = async (addressId: string) => {
    userStore.removeAddress(addressId)

    if (!hasBackendIdentity.value) return true

    try {
      const response = await $api(`/tg/profile/addresses/${encodeURIComponent(addressId)}`, {
        method: 'DELETE'
      })
      const profile = responseProfile(response)
      userStore.hydrateBackendProfile(profile)
      return true
    } catch {
      return false
    }
  }

  return {
    loading,
    hasBackendIdentity,
    loadProfile,
    saveProfile,
    savePaymentMethod,
    saveAddress,
    updateAddress,
    deleteAddress
  }
}
