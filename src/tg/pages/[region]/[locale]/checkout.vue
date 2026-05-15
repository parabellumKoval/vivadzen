<script setup lang="ts">
import { useTgCartStore } from '~/stores/cart'
import { useTgUiStore } from '~/stores/ui'
import { useTgUserStore, type TgSavedAddress } from '~/stores/user'

const cart = useTgCartStore()
const ui = useTgUiStore()
const userStore = useTgUserStore()
const { $api } = useNuxtApp()
const { t } = useTgI18n()
const { pathFor, catalogPath, region } = useTgRouting()
const { user: telegramUser, webApp, haptic } = useTelegram()
const { formatMoney } = useTgProductUtils()
const { loadSettings, deliveryMethods, pickupLocations, paymentMethodsFor } = useTgCheckoutOptions()
const { pickPudo: pickPacketaPoint, isConfigured: packetaConfigured } = useTgPacketa()
const {
  loadProfile: loadBackendProfile,
  saveProfile: saveBackendProfile,
  savePaymentMethod: saveBackendPaymentMethod,
  saveAddress: saveBackendAddress
} = useTgProfileApi()

const order = cart.orderState
const loading = ref(false)
const settingsReady = ref(false)
const expandedCart = ref(false)
const activeStep = ref(1)
const selectedSavedAddressId = ref('')
const errors = reactive<Record<string, string>>({})

const paymentMethods = computed(() => paymentMethodsFor(order.delivery.method))

const selectedDelivery = computed(() => {
  return deliveryMethods.value.find((method) => method.key === order.delivery.method) || null
})

const selectedPayment = computed(() => {
  return paymentMethods.value.find((method) => method.key === order.payment.method) || null
})

const phonePlaceholder = computed(() => region.value === 'ua' ? '+380' : '+420')
const needsWarehouse = computed(() => String(order.delivery.method || '').includes('warehouse'))
const needsPickupLocation = computed(() => String(order.delivery.method || '') === 'default_pickup')
const needsAddress = computed(() => String(order.delivery.method || '').includes('address'))
const needsHouse = computed(() => ['novaposhta_address', 'messenger_address', 'default_address'].includes(String(order.delivery.method || '')))
const needsZip = computed(() => ['novaposhta_address', 'packeta_address', 'messenger_address'].includes(String(order.delivery.method || '')))
const isPacketaWarehouse = computed(() => order.delivery.method === 'packeta_warehouse')
const usesManualWarehouseFields = computed(() => needsWarehouse.value && !isPacketaWarehouse.value)
const packetaPickerLoading = ref(false)

const recipientName = computed(() => {
  return [order.user.first_name, order.user.last_name].filter(Boolean).join(' ')
})

const recipientSummary = computed(() => {
  return [recipientName.value, order.user.phone, order.user.email].filter(Boolean).join(', ')
})

const paymentSummary = computed(() => selectedPayment.value?.title || order.payment.method || '')

const deliveryDetails = computed(() => {
  const addressLine = [order.delivery.street, order.delivery.house, order.delivery.room].filter(Boolean).join(', ')
  return [order.delivery.settlement, order.delivery.warehouse || addressLine || order.delivery.zip].filter(Boolean).join(', ')
})

const deliverySummary = computed(() => {
  const method = selectedDelivery.value?.title || order.delivery.method || t('delivery')
  return deliveryDetails.value ? `${method}: ${deliveryDetails.value}` : method
})

const isUserStepComplete = computed(() => Boolean(
  order.user.first_name
  && order.user.last_name
  && order.user.phone
  && order.user.email
))
const isPaymentStepComplete = computed(() => Boolean(
  order.payment.method
  && (!paymentMethods.value.length || paymentMethods.value.some((method) => method.key === order.payment.method))
))
const isDeliveryStepComplete = computed(() => {
  if (!order.delivery.method) return false
  if (needsPickupLocation.value) return Boolean(order.delivery.warehouse)
  if (needsWarehouse.value) return Boolean(order.delivery.settlement && order.delivery.warehouse)
  if (needsAddress.value) {
    return Boolean(
      order.delivery.settlement
      && order.delivery.street
      && (!needsHouse.value || order.delivery.house)
      && (!needsZip.value || order.delivery.zip)
    )
  }
  return true
})

const allCheckoutStepsComplete = computed(() => (
  isUserStepComplete.value
  && isPaymentStepComplete.value
  && isDeliveryStepComplete.value
))

const deliveryPriceLabel = (price: any) => {
  if (price && typeof price === 'object') {
    return formatMoney(price.amount, price.currency)
  }
  return price || '—'
}

const clearErrors = () => {
  Object.keys(errors).forEach((key) => delete errors[key])
}

const addUserErrors = () => {
  if (!order.user.first_name) errors.first_name = t('required')
  if (!order.user.last_name) errors.last_name = t('required')
  if (!order.user.phone) errors.phone = t('required')
  if (!order.user.email) errors.email = t('required')
}

const addPaymentErrors = () => {
  if (!order.payment.method) errors.payment = t('select_payment')
}

const addDeliveryErrors = () => {
  if (!order.delivery.method) errors.delivery = t('select_delivery')
  if (needsPickupLocation.value && !order.delivery.warehouse) errors.warehouse = t('required')
  if (needsWarehouse.value && !order.delivery.settlement) errors.settlement = t('required')
  if (needsWarehouse.value && !order.delivery.warehouse) errors.warehouse = t('required')
  if (needsAddress.value && !order.delivery.settlement) errors.settlement = t('required')
  if (needsAddress.value && !order.delivery.street) errors.street = t('required')
  if (needsHouse.value && !order.delivery.house) errors.house = t('required')
  if (needsZip.value && !order.delivery.zip) errors.zip = t('required')
}

const validateStep = (step: number) => {
  clearErrors()

  if (step === 1) addUserErrors()
  if (step === 2) addDeliveryErrors()
  if (step === 3) addPaymentErrors()

  return Object.keys(errors).length === 0
}

const validate = () => {
  clearErrors()
  addUserErrors()
  addPaymentErrors()
  addDeliveryErrors()
  return Object.keys(errors).length === 0
}

const firstIncompleteStep = () => {
  if (!isUserStepComplete.value) return 1
  if (!isDeliveryStepComplete.value) return 2
  if (!isPaymentStepComplete.value) return 3
  return 4
}

const isStepComplete = (step: number) => {
  if (step === 1) return isUserStepComplete.value
  if (step === 2) return isDeliveryStepComplete.value
  if (step === 3) return isPaymentStepComplete.value
  return allCheckoutStepsComplete.value
}

const isStepLocked = (step: number) => {
  if (step === 1) return false
  if (step === 2) return !isUserStepComplete.value
  if (step === 3) return !isUserStepComplete.value || !isDeliveryStepComplete.value
  return !allCheckoutStepsComplete.value
}

const showSummary = (step: number) => isStepComplete(step) && activeStep.value !== step

const stepClass = (step: number) => ({
  'checkout-step--active': activeStep.value === step,
  'checkout-step--complete': isStepComplete(step),
  'checkout-step--locked': isStepLocked(step)
})

const stepStatusLabel = (step: number) => {
  if (activeStep.value === step) return step === 4 ? t('ready_to_order') : t('active_step')
  if (isStepComplete(step)) return t('completed')
  if (isStepLocked(step)) return t('step_locked')
  return t('not_completed')
}

const openStep = (step: number) => {
  if (isStepLocked(step)) {
    haptic('error')
    return
  }

  activeStep.value = step
  haptic('selection')
}

const saveUserStep = async () => {
  const profile = {
    first_name: order.user.first_name,
    last_name: order.user.last_name,
    phone: order.user.phone,
    email: order.user.email,
    avatar: userStore.avatarUrl || telegramUser.value?.photo_url || null
  }

  userStore.saveCheckoutProfile(profile)
  await saveBackendProfile(profile)
}

const savePaymentStep = async () => {
  userStore.savePaymentMethod(order.payment.method)
  await saveBackendPaymentMethod(order.payment.method)
}

const saveDeliveryStep = async () => {
  const address = userStore.saveAddressFromDelivery(order.delivery)
  const savedAddress = await saveBackendAddress(address)
  if (savedAddress?.id) selectedSavedAddressId.value = savedAddress.id
}

const continueStep = async (step: number) => {
  if (isStepLocked(step)) {
    activeStep.value = firstIncompleteStep()
    haptic('error')
    return
  }

  if (!validateStep(step)) {
    haptic('error')
    return
  }

  if (step === 1) await saveUserStep()
  if (step === 2) await saveDeliveryStep()
  if (step === 3) await savePaymentStep()

  activeStep.value = Math.min(step + 1, 4)
  haptic('selection')
}

const persistCheckoutProgress = async () => {
  if (isUserStepComplete.value) await saveUserStep()
  if (isDeliveryStepComplete.value) await saveDeliveryStep()
  if (isPaymentStepComplete.value) await savePaymentStep()
}

const syncDeliveryPrice = () => {
  const method = selectedDelivery.value
  if (method?.price && typeof method.price === 'object') {
    cart.setDeliveryPrice(method.price)
    return
  }

  cart.setDeliveryPrice(null)
}

const resetInvalidPayment = () => {
  if (order.payment.method && !paymentMethods.value.some((method) => method.key === order.payment.method)) {
    order.payment.method = null
    if (activeStep.value > 3) {
      activeStep.value = 3
    }
  }
}

const applySavedPayment = () => {
  const savedMethod = userStore.checkoutProfile.paymentMethod
  if (!order.payment.method && savedMethod && paymentMethods.value.some((method) => method.key === savedMethod)) {
    order.payment.method = savedMethod
  }
}

const applySavedAddress = (address: TgSavedAddress, activateStep = true) => {
  if (!userStore.applyAddressToDelivery(address.id, order.delivery)) return

  selectedSavedAddressId.value = address.id
  syncDeliveryPrice()
  resetInvalidPayment()
  applySavedPayment()

  if (activateStep) {
    activeStep.value = 2
    haptic('selection')
  }
}

const savedAddressLine = (address: TgSavedAddress) => {
  const streetLine = [address.street, address.house, address.room].filter(Boolean).join(', ')
  return [address.settlement, address.warehouse || streetLine || address.zip].filter(Boolean).join(', ') || address.title
}

const selectedPickupLocationId = computed(() => {
  const current = String(order.delivery.warehouse || '').trim()
  if (!current) return ''
  return pickupLocations.value.find((item) => item.label === current || item.address === current)?.id || ''
})

const applyPickupLocation = (location: { id: string; title: string; address: string; label: string }) => {
  order.delivery.warehouse = location.label || location.address
  order.delivery.street = location.address || null
  delete errors.warehouse
}

const choosePacketaPoint = async () => {
  if (packetaPickerLoading.value) return
  packetaPickerLoading.value = true
  try {
    const point = await pickPacketaPoint()
    if (!point) return
    order.delivery.warehouse = point.name || ''
    order.delivery.settlement = point.city || ''
    order.delivery.street = point.street || ''
    order.delivery.zip = point.zip || ''
    delete errors.warehouse
    delete errors.settlement
    haptic('success')
  } catch (error) {
    console.error('Packeta widget failed', error)
    ui.showToast(t('packeta_loading_error'), 'error')
    haptic('error')
  } finally {
    packetaPickerLoading.value = false
  }
}

const syncPickupSelection = () => {
  if (order.delivery.method !== 'default_pickup') {
    return
  }

  if (!pickupLocations.value.length) {
    order.delivery.warehouse = null
    order.delivery.street = null
    return
  }

  const current = pickupLocations.value.find((item) => item.id === selectedPickupLocationId.value)
  if (current) {
    applyPickupLocation(current)
    return
  }

  if (!order.delivery.warehouse) {
    applyPickupLocation(pickupLocations.value[0])
  }
}

const deliveryMethodTitle = (key: string | null) => {
  return deliveryMethods.value.find((method) => method.key === key)?.title || key || t('delivery')
}

const setInitialStep = () => {
  activeStep.value = firstIncompleteStep()
}

watch(() => order.delivery.method, () => {
  syncDeliveryPrice()
  syncPickupSelection()
  resetInvalidPayment()
  applySavedPayment()
})

watch(pickupLocations, () => {
  syncPickupSelection()
}, {
  immediate: true
})

watch(paymentMethods, () => {
  resetInvalidPayment()
  applySavedPayment()
})

watch([isUserStepComplete, isPaymentStepComplete, isDeliveryStepComplete], () => {
  if (isStepLocked(activeStep.value)) {
    activeStep.value = firstIncompleteStep()
  }
})

onMounted(async () => {
  order.provider = 'data'
  order.storefront = 'telegram'
  order.storefront_code = 'telegram'
  userStore.sync(telegramUser.value, webApp.value?.initData || '')

  await Promise.all([
    loadSettings(),
    loadBackendProfile()
  ])

  userStore.fillOrderUser(order.user)

  if (!order.delivery.method && userStore.addresses[0]) {
    applySavedAddress(userStore.addresses[0], false)
  }

  syncDeliveryPrice()
  syncPickupSelection()
  resetInvalidPayment()
  applySavedPayment()
  setInitialStep()
  settingsReady.value = true
})

const submit = async () => {
  if (!cart.items.length || loading.value) return
  if (!validate()) {
    activeStep.value = firstIncompleteStep()
    haptic('error')
    return
  }

  await persistCheckoutProgress()
  loading.value = true
  cart.clearErrors()

  const telegramProfile = telegramUser.value || userStore.user || null

  try {
    const response = await $api('/order', {
      method: 'POST',
      body: {
        ...order,
        provider: 'data',
        storefront: 'telegram',
        storefront_code: 'telegram',
        telegram_user_id: telegramProfile?.id || null,
        telegram_user: telegramProfile,
        products: cart.cartPayload,
      }
    })

    cart.flashOrder = response?.data || response || null
    cart.clear()
    haptic('success')
    const code = cart.flashOrder?.code || cart.flashOrder?.id || ''
    await navigateTo({ path: pathFor('thank-you'), query: code ? { order: code } : {} }, { replace: true })
  } catch (error: any) {
    const apiErrors = error?.data?.options || error?.response?._data?.options || {}
    Object.entries(apiErrors).forEach(([key, value]) => {
      errors[key] = Array.isArray(value) ? String(value[0]) : String(value)
    })
    ui.showToast(error?.data?.message || t('order_error'), 'error')
    haptic('error')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <TgLayout :title="t('checkout')" :show-back="true" :show-lang="true">
    <form class="tg-page checkout-page" @submit.prevent="submit">
      <div v-if="!cart.items.length" class="tg-empty">
        <div>
          <div class="tg-empty__icon"><TgIcon name="bag" :size="32" :stroke="2.2" /></div>
          <p class="tg-empty__title">{{ t('cart_empty') }}</p>
          <NuxtLink :to="catalogPath()" class="tg-btn checkout-page__empty-btn">{{ t('back_to_catalog') }}</NuxtLink>
        </div>
      </div>

      <template v-else>
        <section class="checkout-card">
          <button type="button" class="checkout-card__summary" @click="expandedCart = !expandedCart">
            <span>{{ cart.totalQty }} · {{ formatMoney(cart.totalPrice, cart.items[0]?.currency) }}</span>
            <TgIcon :name="expandedCart ? 'chevron-up' : 'chevron-down'" :size="18" :stroke="2.4" />
          </button>
          <div v-if="expandedCart" class="checkout-card__items">
            <div v-for="item in cart.items" :key="`${item.productId}-${item.variantId}`" class="checkout-item">
              <NuxtImg
                :src="item.image"
                :alt="item.name"
                width="84"
                height="84"
                densities="1x 2x"
                format="webp"
                quality="70"
                loading="lazy"
                decoding="async"
              />
              <span>{{ item.name }} × {{ item.quantity }}</span>
              <strong>{{ formatMoney(item.price * item.quantity, item.currency) }}</strong>
            </div>
          </div>
          <TgFreeDeliveryProgress :total="cart.totalPrice" />
        </section>

        <section class="checkout-step" :class="stepClass(1)">
          <button type="button" class="checkout-step__header" @click="openStep(1)">
            <span class="checkout-step__number">
              <TgIcon v-if="isStepComplete(1) && activeStep !== 1" name="check" :size="16" :stroke="3" />
              <template v-else>1</template>
            </span>
            <span class="checkout-step__title">
              <strong>{{ t('step_user') }}</strong>
              <small>{{ stepStatusLabel(1) }}</small>
            </span>
            <span v-if="isStepComplete(1) && activeStep !== 1" class="checkout-step__badge">{{ t('done') }}</span>
          </button>

          <div v-if="showSummary(1)" class="checkout-summary">
            <p>{{ t('recipient') }}: {{ recipientSummary }}</p>
            <button type="button" class="checkout-link" @click="openStep(1)">{{ t('edit') }}</button>
          </div>

          <div v-else-if="activeStep === 1" class="checkout-step__body">
            <label>
              <span>{{ t('first_name') }}</span>
              <input v-model="order.user.first_name" class="tg-field" :class="{ 'tg-field--error': errors.first_name }">
              <small v-if="errors.first_name" class="tg-error">{{ errors.first_name }}</small>
            </label>
            <label>
              <span>{{ t('last_name') }}</span>
              <input v-model="order.user.last_name" class="tg-field" :class="{ 'tg-field--error': errors.last_name }">
              <small v-if="errors.last_name" class="tg-error">{{ errors.last_name }}</small>
            </label>
            <label>
              <span>{{ t('phone') }}</span>
              <input v-model="order.user.phone" class="tg-field" :placeholder="phonePlaceholder" :class="{ 'tg-field--error': errors.phone }" inputmode="tel">
              <small v-if="errors.phone" class="tg-error">{{ errors.phone }}</small>
            </label>
            <label>
              <span>{{ t('email') }}</span>
              <input v-model="order.user.email" class="tg-field" type="email" inputmode="email" :class="{ 'tg-field--error': errors.email }">
              <small v-if="errors.email" class="tg-error">{{ errors.email }}</small>
            </label>
            <button type="button" class="tg-btn" @click="continueStep(1)">{{ t('continue') }}</button>
          </div>
        </section>

        <section class="checkout-step" :class="stepClass(2)">
          <button type="button" class="checkout-step__header" @click="openStep(2)">
            <span class="checkout-step__number">
              <TgIcon v-if="isStepComplete(2) && activeStep !== 2" name="check" :size="16" :stroke="3" />
              <template v-else>2</template>
            </span>
            <span class="checkout-step__title">
              <strong>{{ t('step_delivery') }}</strong>
              <small>{{ stepStatusLabel(2) }}</small>
            </span>
            <span v-if="isStepComplete(2) && activeStep !== 2" class="checkout-step__badge">{{ t('done') }}</span>
          </button>

          <div v-if="showSummary(2)" class="checkout-summary">
            <p>{{ t('delivery') }}: {{ deliverySummary }}</p>
            <button type="button" class="checkout-link" @click="openStep(2)">{{ t('edit') }}</button>
          </div>

          <div v-else-if="activeStep === 2" class="checkout-step__body">
            <div v-if="userStore.addresses.length" class="saved-addresses">
              <strong>{{ t('saved_addresses') }}</strong>
              <button
                v-for="address in userStore.addresses"
                :key="address.id"
                type="button"
                class="saved-address"
                :class="{ 'saved-address--active': selectedSavedAddressId === address.id }"
                @click="applySavedAddress(address)"
              >
                <span>{{ savedAddressLine(address) }}</span>
                <small>{{ deliveryMethodTitle(address.deliveryMethod) }}</small>
              </button>
            </div>

            <div class="radio-list">
              <label
                v-for="method in deliveryMethods"
                :key="method.key"
                class="radio-card"
                :class="{ 'radio-card--active': order.delivery.method === method.key }"
              >
                <input v-model="order.delivery.method" type="radio" :value="method.key">
                <span class="radio-card__title">
                  <strong>{{ method.title }}</strong>
                  <small>{{ method.label }}</small>
                </span>
                <em>{{ deliveryPriceLabel(method.price) }}</em>
              </label>
            </div>
            <small v-if="errors.delivery" class="tg-error">{{ errors.delivery }}</small>

            <div v-if="order.delivery.method" class="checkout-step__fields">
              <div v-if="needsPickupLocation" class="saved-addresses">
                <strong>{{ t('warehouse') }}</strong>
                <button
                  v-for="location in pickupLocations"
                  :key="location.id"
                  type="button"
                  class="saved-address"
                  :class="{ 'saved-address--active': selectedPickupLocationId === location.id }"
                  @click="applyPickupLocation(location)"
                >
                  <span>{{ location.label }}</span>
                  <small v-if="location.schedule">{{ location.schedule }}</small>
                </button>
                <small v-if="errors.warehouse" class="tg-error">{{ errors.warehouse }}</small>
              </div>

              <div v-if="isPacketaWarehouse" class="packeta-picker">
                <button
                  type="button"
                  class="tg-btn tg-btn--lime packeta-picker__btn"
                  :disabled="packetaPickerLoading || !packetaConfigured"
                  @click="choosePacketaPoint"
                >
                  <TgIcon name="package" :size="18" :stroke="2.4" />
                  {{ packetaPickerLoading ? t('loading') : (order.delivery.warehouse ? t('change') : t('choose_pickup_point')) }}
                </button>
                <div v-if="order.delivery.warehouse" class="packeta-picker__point">
                  <strong>{{ order.delivery.warehouse }}</strong>
                  <small v-if="order.delivery.settlement || order.delivery.street || order.delivery.zip">
                    {{ [order.delivery.street, order.delivery.settlement, order.delivery.zip].filter(Boolean).join(', ') }}
                  </small>
                </div>
                <small v-if="errors.warehouse" class="tg-error">{{ errors.warehouse }}</small>
              </div>

              <label v-if="usesManualWarehouseFields || needsAddress">
                <span>{{ t('city') }}</span>
                <input v-model="order.delivery.settlement" class="tg-field" :class="{ 'tg-field--error': errors.settlement }">
                <small v-if="errors.settlement" class="tg-error">{{ errors.settlement }}</small>
              </label>

              <label v-if="usesManualWarehouseFields">
                <span>{{ t('warehouse') }}</span>
                <input v-model="order.delivery.warehouse" class="tg-field" :class="{ 'tg-field--error': errors.warehouse }">
                <small v-if="errors.warehouse" class="tg-error">{{ errors.warehouse }}</small>
              </label>

              <template v-if="needsAddress">
                <label>
                  <span>{{ t('address') }}</span>
                  <input v-model="order.delivery.street" class="tg-field" :class="{ 'tg-field--error': errors.street }">
                  <small v-if="errors.street" class="tg-error">{{ errors.street }}</small>
                </label>
                <label v-if="needsHouse">
                  <span>{{ t('house') }}</span>
                  <input v-model="order.delivery.house" class="tg-field" :class="{ 'tg-field--error': errors.house }">
                  <small v-if="errors.house" class="tg-error">{{ errors.house }}</small>
                </label>
                <label>
                  <span>{{ t('flat') }}</span>
                  <input v-model="order.delivery.room" class="tg-field">
                </label>
                <label v-if="needsZip">
                  <span>{{ t('zip') }}</span>
                  <input v-model="order.delivery.zip" class="tg-field" :class="{ 'tg-field--error': errors.zip }">
                  <small v-if="errors.zip" class="tg-error">{{ errors.zip }}</small>
                </label>
              </template>
            </div>

            <button type="button" class="tg-btn" @click="continueStep(2)">{{ t('continue') }}</button>
          </div>
        </section>

        <section class="checkout-step" :class="stepClass(3)">
          <button type="button" class="checkout-step__header" @click="openStep(3)">
            <span class="checkout-step__number">
              <TgIcon v-if="isStepComplete(3) && activeStep !== 3" name="check" :size="16" :stroke="3" />
              <template v-else>3</template>
            </span>
            <span class="checkout-step__title">
              <strong>{{ t('step_payment') }}</strong>
              <small>{{ stepStatusLabel(3) }}</small>
            </span>
            <span v-if="isStepComplete(3) && activeStep !== 3" class="checkout-step__badge">{{ t('done') }}</span>
          </button>

          <div v-if="showSummary(3)" class="checkout-summary">
            <p>{{ t('payment') }}: {{ paymentSummary }}</p>
            <button type="button" class="checkout-link" @click="openStep(3)">{{ t('edit') }}</button>
          </div>

          <div v-else-if="activeStep === 3" class="checkout-step__body">
            <div class="radio-list">
              <label
                v-for="method in paymentMethods"
                :key="method.key"
                class="radio-card"
                :class="{ 'radio-card--active': order.payment.method === method.key }"
              >
                <input v-model="order.payment.method" type="radio" :value="method.key">
                <span class="radio-card__title">
                  <strong>{{ method.title }}</strong>
                </span>
              </label>
            </div>
            <small v-if="errors.payment" class="tg-error">{{ errors.payment }}</small>
            <button type="button" class="tg-btn" @click="continueStep(3)">{{ t('continue') }}</button>
          </div>
        </section>

        <section class="checkout-step" :class="stepClass(4)">
          <button type="button" class="checkout-step__header" @click="openStep(4)">
            <span class="checkout-step__number">4</span>
            <span class="checkout-step__title">
              <strong>{{ t('step_confirm') }}</strong>
              <small>{{ stepStatusLabel(4) }}</small>
            </span>
          </button>

          <div v-if="activeStep === 4" class="checkout-step__body">
            <div class="checkout-confirm">
              <p>{{ t('recipient') }}: {{ recipientSummary }}</p>
              <p>{{ t('payment') }}: {{ paymentSummary }}</p>
              <p>{{ t('delivery') }}: {{ deliverySummary }}</p>
            </div>

            <label>
              <span>{{ t('comment') }}</span>
              <textarea v-model="order.comment" class="tg-field" rows="3" />
            </label>

            <section class="checkout-total">
              <div>
                <span>{{ t('subtotal') }}</span>
                <strong>{{ formatMoney(cart.totalPrice, cart.items[0]?.currency) }}</strong>
              </div>
              <div>
                <span>{{ t('delivery') }}</span>
                <strong>{{ settingsReady ? formatMoney(cart.deliveryPrice, cart.items[0]?.currency) : t('loading') }}</strong>
              </div>
              <div class="checkout-total__final">
                <span>{{ t('total') }}</span>
                <strong>{{ formatMoney(cart.finishTotal, cart.items[0]?.currency) }}</strong>
              </div>
            </section>

            <button type="submit" class="tg-btn tg-btn--accent" :disabled="loading">
              {{ loading ? t('loading') : t('place_order') }}
            </button>
          </div>
        </section>
      </template>
    </form>
  </TgLayout>
</template>

<style scoped>
.checkout-page {
  display: grid;
  gap: 14px;
}

.checkout-card,
.checkout-step,
.checkout-total {
  display: grid;
  gap: 12px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-lg);
  background: var(--color-white);
  padding: 14px;
  box-shadow: var(--shadow-card);
  transition: border-color 0.12s ease, background 0.12s ease, box-shadow 0.12s ease;
}

.checkout-card__summary,
.checkout-step__header {
  display: flex;
  min-height: 44px;
  border: 0;
  background: transparent;
  color: var(--color-text);
  align-items: center;
  justify-content: space-between;
  padding: 0;
  text-align: left;
}

.checkout-card__summary {
  font-family: var(--font-display);
  font-size: 15px;
  letter-spacing: 0.02em;
  text-transform: uppercase;
}

.checkout-step__header {
  display: grid;
  grid-template-columns: 36px minmax(0, 1fr) auto;
  gap: 12px;
  align-items: center;
}

.checkout-step__number {
  display: grid;
  width: 36px;
  height: 36px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-full);
  background: var(--color-bg-card);
  color: var(--color-ink);
  place-items: center;
  font-family: var(--font-display);
  font-size: 14px;
}

.checkout-step__title {
  display: grid;
}

.checkout-step__header strong,
.checkout-step__header small {
  display: block;
}

.checkout-step__header strong {
  font-family: var(--font-display);
  font-size: 14px;
  letter-spacing: 0.02em;
  text-transform: uppercase;
}

.checkout-step__header small {
  margin-top: 3px;
  color: var(--color-text-muted);
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.checkout-step__badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-full);
  background: var(--color-lime);
  color: var(--color-ink);
  padding: 4px 10px;
  font-family: var(--font-display);
  font-size: 10px;
  letter-spacing: 0.08em;
  line-height: 1;
  text-transform: uppercase;
}

.checkout-step--active .checkout-step__number {
  background: var(--color-primary);
  color: var(--color-white);
}

.checkout-step--complete {
  border-color: var(--color-primary-dark);
  background: linear-gradient(0deg, rgba(24, 184, 90, 0.06), rgba(24, 184, 90, 0.06)), var(--color-white);
  box-shadow: 4px 4px 0 0 var(--color-primary-dark);
}

.checkout-step--complete .checkout-step__number {
  background: var(--color-primary);
  color: var(--color-white);
}

.checkout-step--complete.checkout-step--active {
  background: var(--color-white);
}

.checkout-step--locked {
  opacity: 0.55;
}

.checkout-step--locked .checkout-step__header {
  cursor: not-allowed;
}

.checkout-step__body,
.checkout-step__fields,
.checkout-card__items,
.radio-list,
.saved-addresses {
  display: grid;
  gap: 10px;
}

.checkout-step__body label,
.checkout-step__fields label {
  display: grid;
  gap: 6px;
  font-family: var(--font-display);
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--color-ink);
}

.checkout-summary,
.checkout-confirm {
  display: grid;
  gap: 8px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-md);
  background: var(--color-bg-input);
  padding: 12px;
}

.checkout-summary p,
.checkout-confirm p {
  margin: 0;
  color: var(--color-text);
  font-size: 13px;
  font-weight: 600;
  line-height: 1.4;
}

.checkout-link {
  width: max-content;
  border: 0;
  background: transparent;
  color: var(--color-primary-dark);
  padding: 0;
  font-family: var(--font-display);
  font-size: 11px;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  text-decoration: underline;
  text-decoration-thickness: 2px;
  text-underline-offset: 3px;
}

.checkout-item {
  display: grid;
  grid-template-columns: 42px minmax(0, 1fr) auto;
  gap: 8px;
  align-items: center;
  color: var(--color-text-muted);
  font-size: 12px;
  font-weight: 600;
}

.checkout-item img {
  width: 42px;
  height: 42px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-sm);
  background: var(--color-bg-card);
  object-fit: contain;
}

.checkout-item strong {
  font-family: var(--font-display);
  color: var(--color-accent);
}

.radio-card {
  display: grid !important;
  grid-template-columns: 22px minmax(0, 1fr) auto;
  min-height: 56px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-md);
  background: var(--color-white);
  padding: 12px;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  box-shadow: var(--shadow-card-sm);
  transition: transform 0.08s ease, box-shadow 0.08s ease, background 0.08s ease;
}

.radio-card:hover {
  transform: translate(-1px, -1px);
  box-shadow: 3px 3px 0 0 var(--color-ink);
}

.radio-card--active {
  background: var(--color-lime);
  box-shadow: 3px 3px 0 0 var(--color-ink);
}

.radio-card input {
  width: 18px;
  height: 18px;
  accent-color: var(--color-primary);
}

.radio-card__title strong {
  font-family: var(--font-display);
  font-size: 13px;
  letter-spacing: 0.02em;
  text-transform: uppercase;
}

.radio-card__title small {
  display: block;
  margin-top: 3px;
  color: var(--color-text-muted);
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.radio-card em {
  font-family: var(--font-display);
  color: var(--color-accent-dark);
  font-size: 13px;
  font-style: normal;
  letter-spacing: 0.02em;
}

.saved-addresses > strong {
  font-family: var(--font-display);
  font-size: 12px;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.saved-address {
  display: grid;
  gap: 4px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-md);
  background: var(--color-white);
  padding: 12px;
  text-align: left;
  box-shadow: var(--shadow-card-sm);
  cursor: pointer;
  transition: transform 0.08s ease, box-shadow 0.08s ease, background 0.08s ease;
}

.saved-address:hover {
  transform: translate(-1px, -1px);
  box-shadow: 3px 3px 0 0 var(--color-ink);
}

.saved-address--active {
  background: var(--color-lime);
  box-shadow: 3px 3px 0 0 var(--color-ink);
}

.saved-address span {
  color: var(--color-text);
  font-family: var(--font-display);
  font-size: 13px;
  letter-spacing: 0.02em;
  text-transform: uppercase;
}

.saved-address small {
  color: var(--color-text-muted);
  font-size: 11px;
  font-weight: 600;
}

.packeta-picker {
  display: grid;
  gap: 10px;
}

.packeta-picker__btn {
  width: 100%;
}

.packeta-picker__point {
  display: grid;
  gap: 4px;
  border: 2px solid var(--color-ink);
  border-radius: var(--radius-md);
  background: var(--color-lime);
  padding: 12px;
  box-shadow: var(--shadow-card-sm);
}

.packeta-picker__point strong {
  font-family: var(--font-display);
  font-size: 13px;
  letter-spacing: 0.02em;
  text-transform: uppercase;
}

.packeta-picker__point small {
  color: var(--color-ink);
  font-size: 11px;
  font-weight: 600;
}

.checkout-total {
  background: var(--color-bg-input);
}

.checkout-total div {
  display: flex;
  align-items: center;
  justify-content: space-between;
  color: var(--color-text-muted);
  font-size: 14px;
  font-weight: 600;
}

.checkout-total strong {
  color: var(--color-text);
}

.checkout-total__final {
  border-top: 2px solid var(--color-ink);
  padding-top: 12px;
  color: var(--color-text) !important;
}

.checkout-total__final span,
.checkout-total__final strong {
  font-family: var(--font-display);
  font-size: 16px;
  letter-spacing: 0.02em;
  text-transform: uppercase;
}

.checkout-total__final strong {
  color: var(--color-accent);
  font-size: 22px;
}

.checkout-page__empty-btn {
  display: inline-flex;
  width: auto;
  margin-top: 14px;
  align-items: center;
}
</style>
