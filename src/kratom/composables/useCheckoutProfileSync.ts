export const useCheckoutProfileSync = () => {
  const { user: authUser, isAuthenticated, updateProfile } = useAuth()
  const { region } = useRegion()
  const {
    sanitizeAddresses,
    extractAddressFromDelivery,
    upsertSavedAddress,
  } = useSavedDeliveryAddresses()

  const normalizeText = (value: unknown) => String(value || '').trim()

  const syncContactDetails = async (userState: Record<string, any> | null | undefined) => {
    if (!isAuthenticated.value || !authUser.value || !userState) {
      return false
    }

    const payload: Record<string, string> = {}

    ;['first_name', 'last_name', 'phone'].forEach((field) => {
      const nextValue = normalizeText(userState[field])
      const currentValue = normalizeText(authUser.value?.[field])

      if (nextValue && nextValue !== currentValue) {
        payload[field] = nextValue
      }
    })

    if (!Object.keys(payload).length) {
      return false
    }

    await updateProfile(payload)
    return true
  }

  const syncDeliveryAddress = async (
    deliveryState: Record<string, any> | null | undefined,
    options: { makeMain?: boolean } = {},
  ) => {
    if (!isAuthenticated.value || !authUser.value || !deliveryState) {
      return false
    }

    const fallbackCountry = String(region.value || '').toUpperCase()
    const currentAddress = extractAddressFromDelivery(deliveryState, fallbackCountry)

    if (!currentAddress) {
      return false
    }

    const currentList = sanitizeAddresses(authUser.value?.saved_delivery_addresses, fallbackCountry)
    const nextList = upsertSavedAddress(currentList, currentAddress, {
      fallbackCountry,
      makeMain: Boolean(options.makeMain),
    })

    if (JSON.stringify(currentList) === JSON.stringify(nextList)) {
      return false
    }

    await updateProfile({
      saved_delivery_addresses: nextList,
    })

    return true
  }

  return {
    syncContactDetails,
    syncDeliveryAddress,
  }
}
