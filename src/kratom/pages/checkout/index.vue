<script setup lang="ts">
import type { SavedDeliveryAddress } from '~/composables/useSavedDeliveryAddresses'

const { t } = useI18n()
const route = useRoute()
const regionPath = useToLocalePath()
const cartStore = useCartStore()
const {
  user: authUser,
  avatar,
  token,
  isAuthenticated,
  init,
  me,
} = useAuth()
const { syncContactDetails, syncDeliveryAddress } = useCheckoutProfileSync()
const { methods: deliveryMethods } = useDelivery()
const { methods: paymentMethods } = usePayment()
const {
  normalizeAddress,
  methodTitle,
  needsWarehouse,
  needsPickupLocation,
  needsAddress,
  requiresHouse,
  requiresZip,
  buildAddressSummary,
  addressSelectLabel,
  addressesEqual,
  clearDeliveryState,
  preferredSavedAddress,
  applyAddressToDelivery,
} = useSavedDeliveryAddresses()

definePageMeta({
  bg: '#f3ece2',
  ssr: false,
})

await init()

const breadcrumbs = computed(() => [
  { name: t('title.home'), item: '/' },
  { name: t('title.checkout'), item: '/checkout' },
])

useSeo().setPageSeo('checkout', {
  fallbackTitle: () => t('title.checkout'),
  robots: 'noindex, nofollow',
})

const products = computed(() => cartStore.cart)
const order = computed(() => cartStore.order)
const errors = computed(() => cartStore.errors)

const activeStep = ref(1)
const selectedSavedAddressKey = ref('')
const isApplyingSavedAddress = ref(false)
const checkoutAvatarFailed = ref(false)

const savedAddresses = computed<SavedDeliveryAddress[]>(() => {
  if (!Array.isArray(authUser.value?.saved_delivery_addresses)) {
    return []
  }

  return authUser.value.saved_delivery_addresses.map((item: SavedDeliveryAddress) => normalizeAddress(item))
})

const isUserNameRequired = computed(() => true)

const selectedDeliveryMethod = computed(() => {
  return deliveryMethods.value.find((item) => item.key === order.value.delivery.method) || null
})

const selectedPaymentMethod = computed(() => {
  return paymentMethods.value.find((item) => item.key === order.value.payment.method) || null
})

const savedAddressOptions = computed(() => {
  return savedAddresses.value.map((address, index) => ({
    key: deliveryAddressIdentity(address, index),
    label: addressSelectLabel(address),
    address,
    index,
  }))
})

const userErrors = computed(() => Boolean(errors.value.user && Object.keys(errors.value.user).length))
const deliveryErrors = computed(() => Boolean(errors.value.delivery && Object.keys(errors.value.delivery).length))
const paymentErrors = computed(() => Boolean(errors.value.payment && Object.keys(errors.value.payment).length))

const recipientName = computed(() => {
  return [order.value.user.first_name, order.value.user.last_name].filter(Boolean).join(' ')
})

const recipientSummary = computed(() => {
  return [recipientName.value, order.value.user.phone, order.value.user.email].filter(Boolean).join(', ')
})

const deliverySummary = computed(() => {
  const label = selectedDeliveryMethod.value?.title || methodTitle(order.value.delivery.method)
  const detail = buildAddressSummary(order.value.delivery)
  return detail && detail !== label ? `${label}: ${detail}` : label
})

const paymentSummary = computed(() => {
  return selectedPaymentMethod.value?.title || order.value.payment.method || t('title.payment')
})

const isUserStepComplete = computed(() => Boolean(
  order.value.user.first_name
  && order.value.user.last_name
  && order.value.user.phone
  && order.value.user.email
))

const isDeliveryStepComplete = computed(() => {
  if (!order.value.delivery.method) return false

  if (needsPickupLocation(order.value.delivery.method)) {
    return Boolean(order.value.delivery.warehouse)
  }

  if (needsWarehouse(order.value.delivery.method)) {
    return Boolean(order.value.delivery.settlement && order.value.delivery.warehouse)
  }

  if (needsAddress(order.value.delivery.method)) {
    return Boolean(
      order.value.delivery.settlement
      && order.value.delivery.street
      && (!requiresHouse(order.value.delivery.method) || order.value.delivery.house)
      && (!requiresZip(order.value.delivery.method) || order.value.delivery.zip)
    )
  }

  return true
})

const isPaymentStepComplete = computed(() => {
  return Boolean(
    order.value.payment.method
    && paymentMethods.value.some((item) => item.key === order.value.payment.method)
  )
})

const allCheckoutStepsComplete = computed(() => {
  return isUserStepComplete.value && isDeliveryStepComplete.value && isPaymentStepComplete.value
})

const checkoutDisabledMessage = computed(() => {
  if (allCheckoutStepsComplete.value) {
    return ''
  }

  return t('checkout.complete_steps_before_submit')
})

const authRedirectQuery = computed(() => ({ redirect: route.fullPath }))

const openCatalogHandler = async () => {
  await navigateTo(regionPath('/catalog'))
}

const stepErrorTarget = (step: number) => {
  return document.querySelector(`[data-step="${step}"]`)
}

const scrollToStep = (step: number) => {
  if (!process.client) {
    return
  }

  stepErrorTarget(step)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

const ensureStepErrors = (scope: 'user' | 'delivery' | 'payment') => {
  if (!errors.value[scope] || typeof errors.value[scope] !== 'object') {
    errors.value[scope] = {}
  }

  return errors.value[scope] as Record<string, string | null>
}

const clearStepErrors = (scope: 'user' | 'delivery' | 'payment') => {
  errors.value[scope] = {}
}

const syncUserProfileFromCheckout = async () => {
  if (!isAuthenticated.value) {
    return
  }

  try {
    await syncContactDetails(order.value.user)
  } catch (error) {
    console.error('[kratom-checkout] Failed to sync contact details', error)
  }
}

const syncDeliveryProfileFromCheckout = async () => {
  if (!isAuthenticated.value || !isDeliveryStepComplete.value) {
    return
  }

  try {
    await syncDeliveryAddress(order.value.delivery, { makeMain: false })
  } catch (error) {
    console.error('[kratom-checkout] Failed to sync delivery address', error)
  }
}

const validateUserStep = () => {
  const stepErrors = ensureStepErrors('user')
  Object.keys(stepErrors).forEach((key) => { stepErrors[key] = null })

  if (!order.value.user.first_name) stepErrors.first_name = t('error.required')
  if (!order.value.user.last_name) stepErrors.last_name = t('error.required')
  if (!order.value.user.phone) stepErrors.phone = t('error.required')
  if (!order.value.user.email) stepErrors.email = t('error.required')

  return !Object.values(stepErrors).some(Boolean)
}

const validateDeliveryStep = () => {
  const stepErrors = ensureStepErrors('delivery')
  Object.keys(stepErrors).forEach((key) => { stepErrors[key] = null })

  if (!order.value.delivery.method) {
    stepErrors.method = t('error.required')
  }

  if (needsPickupLocation(order.value.delivery.method) && !order.value.delivery.warehouse) {
    stepErrors.warehouse = t('error.required')
  }

  if (needsWarehouse(order.value.delivery.method) || needsAddress(order.value.delivery.method)) {
    if (!order.value.delivery.settlement) {
      stepErrors.settlement = t('error.required')
    }
  }

  if (needsWarehouse(order.value.delivery.method) && !order.value.delivery.warehouse) {
    stepErrors.warehouse = t('error.required')
  }

  if (needsAddress(order.value.delivery.method)) {
    if (!order.value.delivery.street) {
      stepErrors.street = t('error.required')
    }

    if (requiresHouse(order.value.delivery.method) && !order.value.delivery.house) {
      stepErrors.house = t('error.required')
    }

    if (requiresZip(order.value.delivery.method) && !order.value.delivery.zip) {
      stepErrors.zip = t('error.required')
    }
  }

  return !Object.values(stepErrors).some(Boolean)
}

const validatePaymentStep = () => {
  const stepErrors = ensureStepErrors('payment')
  Object.keys(stepErrors).forEach((key) => { stepErrors[key] = null })

  if (!order.value.payment.method) {
    stepErrors.method = t('error.required')
  }

  return !Object.values(stepErrors).some(Boolean)
}

const validateStep = (step: number) => {
  if (step === 1) return validateUserStep()
  if (step === 2) return validateDeliveryStep()
  if (step === 3) return validatePaymentStep()
  return false
}

const firstIncompleteStep = () => {
  if (!isUserStepComplete.value) return 1
  if (!isDeliveryStepComplete.value) return 2
  return 3
}

const setInitialStep = () => {
  if (isUserStepComplete.value && isDeliveryStepComplete.value) {
    activeStep.value = 3
    return
  }

  activeStep.value = firstIncompleteStep()
}

const isStepComplete = (step: number) => {
  if (step === 1) return isUserStepComplete.value
  if (step === 2) return isDeliveryStepComplete.value
  if (step === 3) return isPaymentStepComplete.value
  return false
}

const isStepLocked = (step: number) => {
  if (step === 1) return false
  if (step === 2) return !isUserStepComplete.value
  if (step === 3) return !isUserStepComplete.value || !isDeliveryStepComplete.value
  return true
}

const stepClass = (step: number) => ({
  'kratom-checkout-step--active': activeStep.value === step,
  'kratom-checkout-step--complete': isStepComplete(step),
  'kratom-checkout-step--locked': isStepLocked(step),
  'checkout-box': true,
  error: step === 1 ? userErrors.value : step === 2 ? deliveryErrors.value : paymentErrors.value,
})

const stepStatusLabel = (step: number) => {
  if (activeStep.value === step) return t('checkout.active_step')
  if (isStepComplete(step)) return t('checkout.completed')
  if (isStepLocked(step)) return t('checkout.step_locked')
  return t('checkout.not_completed')
}

const showStepSummary = (step: number) => {
  return isStepComplete(step) && activeStep.value !== step
}

const openStep = (step: number) => {
  if (isStepLocked(step)) {
    return
  }

  activeStep.value = step
  nextTick(() => scrollToStep(step))
}

const continueStep = async (step: number) => {
  if (isStepLocked(step)) {
    activeStep.value = firstIncompleteStep()
    nextTick(() => scrollToStep(activeStep.value))
    return
  }

  if (!validateStep(step)) {
    nextTick(() => scrollToStep(step))
    return
  }

  if (step === 1) {
    await syncUserProfileFromCheckout()
  }

  if (step === 2) {
    await syncDeliveryProfileFromCheckout()
  }

  if (step < 3) {
    activeStep.value = step + 1
    nextTick(() => scrollToStep(step + 1))
    return
  }

  nextTick(() => scrollToStep(step))
}

const resetInvalidPayment = () => {
  if (order.value.payment.method && !paymentMethods.value.some((item) => item.key === order.value.payment.method)) {
    order.value.payment.method = null
  }
}

const deliveryAddressIdentity = (address: SavedDeliveryAddress, index = -1) => {
  return address.id
    || address.fingerprint
    || [
      address.method,
      address.settlement,
      address.warehouse,
      address.street,
      address.house,
      address.room,
      address.zip,
      index >= 0 ? index : '',
    ].join(':')
}

const sameDeliveryValue = (left: unknown, right: unknown) => {
  return String(left || '').trim() === String(right || '').trim()
}

const savedAddressMatchesOrder = (address: SavedDeliveryAddress) => {
  const normalized = normalizeAddress(address)

  return sameDeliveryValue(normalized.method, order.value.delivery.method)
    && sameDeliveryValue(normalized.settlement, order.value.delivery.settlement)
    && sameDeliveryValue(normalized.settlementRef, order.value.delivery.settlementRef)
    && sameDeliveryValue(normalized.region, order.value.delivery.region)
    && sameDeliveryValue(normalized.area, order.value.delivery.area)
    && sameDeliveryValue(normalized.street, order.value.delivery.street)
    && sameDeliveryValue(normalized.streetRef, order.value.delivery.streetRef)
    && sameDeliveryValue(normalized.type, order.value.delivery.type)
    && sameDeliveryValue(normalized.house, order.value.delivery.house)
    && sameDeliveryValue(normalized.room, order.value.delivery.room)
    && sameDeliveryValue(normalized.zip, order.value.delivery.zip)
    && sameDeliveryValue(normalized.warehouse, order.value.delivery.warehouse)
    && sameDeliveryValue(normalized.warehouseRef, order.value.delivery.warehouseRef)
}

const syncSelectedAddress = () => {
  const index = savedAddresses.value.findIndex((address) => savedAddressMatchesOrder(address))
  selectedSavedAddressKey.value = index >= 0 ? deliveryAddressIdentity(savedAddresses.value[index], index) : ''
}

const selectSavedAddress = (key: string, activateStep = true) => {
  if (!key) {
    startNewAddress()
    return
  }

  const match = savedAddressOptions.value.find((item) => item.key === key)

  if (!match) {
    return
  }

  applySavedAddressSelection(match.address, activateStep, match.index)
}

const applySavedAddressSelection = (address: SavedDeliveryAddress, activateStep = true, index = -1) => {
  isApplyingSavedAddress.value = true
  applyAddressToDelivery(address, order.value.delivery as Record<string, any>)
  selectedSavedAddressKey.value = deliveryAddressIdentity(address, index)
  resetInvalidPayment()

  nextTick(() => {
    isApplyingSavedAddress.value = false
  })

  if (activateStep) {
    activeStep.value = 2
    nextTick(() => scrollToStep(2))
  }
}

const startNewAddress = () => {
  isApplyingSavedAddress.value = true
  selectedSavedAddressKey.value = ''

  clearDeliveryState(order.value.delivery as Record<string, any>)

  clearStepErrors('delivery')

  nextTick(() => {
    isApplyingSavedAddress.value = false
  })
}

const hydrateOrderFromProfile = () => {
  order.value.storefront = 'kratom'
  order.value.storefront_code = 'kratom'

  if (!authUser.value) {
    setInitialStep()
    return
  }

  if (!order.value.user.first_name && authUser.value.first_name) {
    order.value.user.first_name = authUser.value.first_name
  }

  if (!order.value.user.last_name && authUser.value.last_name) {
    order.value.user.last_name = authUser.value.last_name
  }

  if (!order.value.user.phone && authUser.value.phone) {
    order.value.user.phone = authUser.value.phone
  }

  if (!order.value.user.email && authUser.value.email) {
    order.value.user.email = authUser.value.email
  }

  const mainAddress = preferredSavedAddress(savedAddresses.value)

  if (!order.value.delivery.method && mainAddress) {
    const index = savedAddresses.value.findIndex((item) => addressesEqual(item, mainAddress))
    applySavedAddressSelection(mainAddress, false, index)
  } else {
    syncSelectedAddress()
  }

  setInitialStep()
}

const scrollToErrorHandler = () => {
  activeStep.value = firstIncompleteStep()
  nextTick(() => scrollToStep(activeStep.value))
}

watch(
  [() => authUser.value, () => token.value],
  ([currentUser, currentToken]) => {
    if (!currentUser && currentToken) {
      me(true).catch(() => {})
    }
  },
  { immediate: true },
)

watch(
  authUser,
  () => {
    hydrateOrderFromProfile()
  },
  { immediate: true, deep: true },
)

watch(
  avatar,
  () => {
    checkoutAvatarFailed.value = false
  },
  { immediate: true },
)

watch(
  () => order.value.delivery.settlement,
  () => {
    const delivery = order.value.delivery

    if (!delivery || delivery.method === 'packeta_warehouse' || isApplyingSavedAddress.value) {
      return
    }

    delivery.warehouse = null
    delivery.street = null
  },
)

watch(
  () => order.value.delivery.method,
  () => {
    clearStepErrors('delivery')
    resetInvalidPayment()
  },
  { immediate: true },
)

watch(
  () => order.value.payment.method,
  () => {
    clearStepErrors('payment')
  },
  { immediate: true },
)

watch(paymentMethods, () => {
  resetInvalidPayment()
})

watch(
  () => [
    order.value.delivery.method,
    order.value.delivery.settlement,
    order.value.delivery.settlementRef,
    order.value.delivery.region,
    order.value.delivery.area,
    order.value.delivery.street,
    order.value.delivery.streetRef,
    order.value.delivery.type,
    order.value.delivery.house,
    order.value.delivery.room,
    order.value.delivery.zip,
    order.value.delivery.warehouse,
    order.value.delivery.warehouseRef,
  ],
  () => {
    syncSelectedAddress()
  },
)

await useAsyncData('kratom-cart-products', async () => cartStore.fetchCartProducts())
await useAsyncData('kratom-cart-rules', async () => cartStore.rules())

cartStore.clearErrors()
cartStore.clearBonusUsage()
cartStore.removeCode()
cartStore.setPromocode(null)
hydrateOrderFromProfile()
</script>

<template>
  <div class="page-base kratom-checkout-page">
    <div class="container kratom-page-container kratom-checkout-shell">
      <the-breadcrumbs :crumbs="breadcrumbs" />

      <section class="kratom-checkout-hero">
        <h1 class="kratom-checkout-hero__title">{{ t('title.checkout') }}</h1>
        <p class="kratom-checkout-hero__text">{{ t('kratom.checkout.text') }}</p>
      </section>

      <div class="kratom-checkout-layout">
        <div class="kratom-checkout-main">
          <section class="checkout-box">
            <div class="title-secondary">{{ t('title.cart') }}</div>
            <template v-if="products?.length">
              <product-card-checkout v-for="product in products" :key="product.id" :item="product" class="checkout-product" />
            </template>
            <template v-else>
              <div class="kratom-checkout-empty">
                {{ t('kratom.checkout.empty_prefix') }}
                <button class="text-link" @click="openCatalogHandler"><span>{{ t('kratom.checkout.empty_action') }}</span></button>
                {{ t('kratom.checkout.empty_suffix') }}
              </div>
            </template>
          </section>

          <section :class="stepClass(1)" data-step="1">
            <button type="button" class="kratom-checkout-step__header" @click="openStep(1)">
              <span class="kratom-checkout-step__number">1</span>
              <span class="kratom-checkout-step__heading">
                <strong>{{ t('checkout.step_user') }}</strong>
                <small>{{ stepStatusLabel(1) }}</small>
              </span>
            </button>

            <div v-if="showStepSummary(1)" class="kratom-checkout-step__summary">
              <p>{{ recipientSummary }}</p>
              <button type="button" class="auth-text-button" @click="openStep(1)">{{ t('checkout.edit') }}</button>
            </div>

            <div v-else-if="activeStep === 1" class="kratom-checkout-step__body">
              <div v-if="isAuthenticated" class="kratom-checkout-auth-state">
                <nuxt-img
                  v-if="avatar && !checkoutAvatarFailed"
                  :src="avatar"
                  width="42"
                  height="42"
                  format="webp"
                  quality="70"
                  fit="cover"
                  class="kratom-checkout-auth-state__avatar"
                  @error="checkoutAvatarFailed = true"
                />
                <div v-else class="kratom-checkout-auth-state__icon">
                  <IconCSS name="ph:user-circle-fill" size="22" />
                </div>
                <div>
                  <div class="kratom-checkout-auth-state__title">{{ t('checkout.use_account_data') }}</div>
                  <div class="kratom-checkout-auth-state__text">{{ authUser?.email }}</div>
                </div>
                <NuxtLink :to="regionPath('/account/settings')" class="auth-text-button">
                  {{ t('checkout.manage_profile') }}
                </NuxtLink>
              </div>

              
              <div v-else class="kratom-checkout-auth-gate">
                <div>
                  <div class="kratom-checkout-auth-gate__title">{{ t('checkout.auth_title') }}</div>
                  <div class="kratom-checkout-auth-gate__text">{{ t('checkout.auth_text') }}</div>
                </div>
                <div class="kratom-checkout-auth-gate__actions">
                  <NuxtLink :to="{ path: regionPath('/auth/login'), query: authRedirectQuery }" class="button secondary">
                    {{ t('button.enter') }}
                  </NuxtLink>
                  <NuxtLink :to="{ path: regionPath('/auth/register'), query: authRedirectQuery }" class="button primary">
                    {{ t('button.register') }}
                  </NuxtLink>
                </div>
              </div>
             

              <div class="form-grid">
                <form-text
                  v-if="isUserNameRequired"
                  v-model="order.user.first_name"
                  :error="errors?.user?.first_name"
                  :placeholder="t('form.firstname')"
                  :required="cartStore.isFieldRequired('user.children.first_name')"
                  @input="() => errors?.user?.first_name && (errors.user.first_name = null)"
                />
                <form-text
                  v-if="isUserNameRequired"
                  v-model="order.user.last_name"
                  :error="errors?.user?.last_name"
                  :placeholder="t('form.lastname')"
                  :required="cartStore.isFieldRequired('user.children.last_name')"
                  @input="() => errors?.user?.last_name && (errors.user.last_name = null)"
                />
                <form-phone-region
                  v-model="order.user.phone"
                  :error="errors?.user?.phone"
                  :placeholder="t('form.phone')"
                  :required="cartStore.isFieldRequired('user.children.phone')"
                  @input="() => errors?.user?.phone && (errors.user.phone = null)"
                />
                <form-text
                  v-model="order.user.email"
                  :readonly="isAuthenticated"
                  :error="errors?.user?.email"
                  :placeholder="t('form.email')"
                  :required="cartStore.isFieldRequired('user.children.email')"
                  @input="() => errors?.user?.email && (errors.user.email = null)"
                />
              </div>

              <button type="button" class="button primary kratom-checkout-step__button" @click="continueStep(1)">
                {{ t('checkout.continue') }}
              </button>
            </div>
          </section>

          <section :class="stepClass(2)" data-step="2">
            <button type="button" class="kratom-checkout-step__header" @click="openStep(2)">
              <span class="kratom-checkout-step__number">2</span>
              <span class="kratom-checkout-step__heading">
                <strong>{{ t('checkout.step_delivery') }}</strong>
                <small>{{ stepStatusLabel(2) }}</small>
              </span>
            </button>

            <div v-if="showStepSummary(2)" class="kratom-checkout-step__summary">
              <p>{{ deliverySummary }}</p>
              <button type="button" class="auth-text-button" @click="openStep(2)">{{ t('checkout.edit') }}</button>
            </div>

            <div v-else-if="activeStep === 2" class="kratom-checkout-step__body">
              <div v-if="isAuthenticated && savedAddresses.length" class="kratom-checkout-addresses">
                <div class="kratom-checkout-addresses__header">
                  <div>
                    <div class="kratom-checkout-addresses__title">{{ t('checkout.saved_addresses_title') }}</div>
                    <div class="kratom-checkout-addresses__text">{{ t('checkout.saved_addresses_text') }}</div>
                  </div>
                  <div class="kratom-checkout-addresses__actions">
                    <NuxtLink :to="regionPath('/account/addresses')" class="auth-text-button">
                      {{ t('checkout.manage_addresses') }}
                    </NuxtLink>
                    <button type="button" class="auth-text-button" @click="startNewAddress">
                      {{ t('checkout.new_address') }}
                    </button>
                  </div>
                </div>

                <label class="kratom-checkout-addresses__select-wrapper">
                  <span>{{ t('checkout.saved_addresses_select') }}</span>
                  <select v-model="selectedSavedAddressKey" class="kratom-checkout-addresses__select" @change="selectSavedAddress(selectedSavedAddressKey)">
                    <option value="">{{ t('checkout.new_address') }}</option>
                    <option
                      v-for="item in savedAddressOptions"
                      :key="item.key"
                      :value="item.key"
                    >
                      {{ item.label }}
                    </option>
                  </select>
                </label>

                <div class="kratom-checkout-addresses__grid">
                  <button
                    v-for="item in savedAddressOptions"
                    :key="item.key"
                    type="button"
                    class="kratom-checkout-address"
                    :class="{ 'is-active': selectedSavedAddressKey === item.key }"
                    @click="applySavedAddressSelection(item.address, true, item.index)"
                  >
                    <strong>{{ item.address.title || methodTitle(item.address.method) }}</strong>
                    <span>{{ buildAddressSummary(item.address) }}</span>
                  </button>
                </div>
              </div>

              <checkout-delivery />

              <button type="button" class="button primary kratom-checkout-step__button" @click="continueStep(2)">
                {{ t('checkout.continue') }}
              </button>
            </div>
          </section>

          <section :class="stepClass(3)" data-step="3">
            <button type="button" class="kratom-checkout-step__header" @click="openStep(3)">
              <span class="kratom-checkout-step__number">3</span>
              <span class="kratom-checkout-step__heading">
                <strong>{{ t('checkout.step_payment') }}</strong>
                <small>{{ stepStatusLabel(3) }}</small>
              </span>
            </button>

            <div v-if="showStepSummary(3)" class="kratom-checkout-step__summary">
              <p>{{ paymentSummary }}</p>
              <button type="button" class="auth-text-button" @click="openStep(3)">{{ t('checkout.edit') }}</button>
            </div>

            <div v-else-if="activeStep === 3" class="kratom-checkout-step__body">
              <checkout-payment />
            </div>
          </section>

          <section class="checkout-box">
            <div class="title-secondary">{{ t('title.other') }}</div>
            <form-textarea
              v-model="order.comment"
              :error="errors?.comment"
              :placeholder="t('form.comment')"
              @input="() => errors?.comment && (errors.comment = null)"
            />
          </section>
        </div>

        <aside class="kratom-checkout-side">
          <div class="checkout-box kratom-checkout-side__sticky">
            <div class="title-secondary">{{ t('label.total') }}</div>
            <checkout-sale
              :disabled="!allCheckoutStepsComplete"
              :disabled-message="checkoutDisabledMessage"
              @scroll-to-error="scrollToErrorHandler"
              @scrollToError="scrollToErrorHandler"
            />

            <i18n-t keypath="kratom.checkout.consent_text" tag="p" scope="global" class="kratom-checkout-side__consent">
              <template #terms>
                <NuxtLink :to="regionPath('/terms')" class="kratom-checkout-side__consent-link">
                  {{ t('kratom.checkout.consent_terms_link') }}
                </NuxtLink>
              </template>
              <template #policy>
                <NuxtLink :to="regionPath('/policy')" class="kratom-checkout-side__consent-link">
                  {{ t('kratom.checkout.consent_policy_link') }}
                </NuxtLink>
              </template>
            </i18n-t>

            <div class="kratom-checkout-side__trust">
              <div class="kratom-checkout-side__trust-item">
                <IconCSS name="ph:shield-check" size="18" class="kratom-checkout-side__trust-icon" />
                <span>{{ t('kratom.checkout.secure_payment') }}</span>
              </div>
              <span class="kratom-checkout-side__trust-dot" aria-hidden="true">&bull;</span>
              <div class="kratom-checkout-side__trust-item">
                <IconCSS name="ph:seal-check" size="18" class="kratom-checkout-side__trust-icon" />
                <span>{{ t('kratom.checkout.quality_guarantee') }}</span>
              </div>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.kratom-checkout-shell {
  padding-top: 24px;
  padding-bottom: 40px;
}

.kratom-checkout-hero {
  margin-bottom: 28px;
  padding: 0;
  border-radius: 0;
  background: none;
  border: 0;
}

.kratom-checkout-hero__eyebrow {
  margin-bottom: 12px;
  color: #8a5a2b;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 12px;
  font-weight: 700;
}

.kratom-checkout-hero__title {
  margin-bottom: 12px;
  font-size: 48px;
  line-height: 0.98;
}

.kratom-checkout-hero__text {
  max-width: 700px;
  color: #5f6458;
  line-height: 1.7;
}

.kratom-checkout-layout {
  display: grid;
  gap: 22px;

  @include desktop {
    grid-template-columns: minmax(0, 1.4fr) minmax(320px, 0.75fr);
  }
}

.kratom-checkout-main {
  display: grid;
  gap: 22px;
}

.form-grid {
  display: grid;
  gap: 14px;
}

.checkout-box {
  padding: 24px;
  border-radius: 28px;
  background: rgba(255, 250, 244, 0.92);
  border: 1px solid rgba(74, 91, 68, 0.1);
  box-shadow: 0 24px 60px rgba(39, 49, 36, 0.06);

  &.error {
    border-color: rgba(181, 60, 60, 0.3);
  }
}

.kratom-checkout-empty {
  color: #667160;
  line-height: 1.7;
}

.kratom-checkout-step__header {
  width: 100%;
  display: grid;
  grid-template-columns: 42px minmax(0, 1fr);
  gap: 14px;
  align-items: center;
  text-align: left;
  background: transparent;
  border: 0;
  padding: 0;
}

.kratom-checkout-step__number {
  width: 42px;
  height: 42px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  background: rgba(53, 82, 74, 0.08);
  color: #5b6754;
  font-size: 15px;
  font-weight: 800;
}

.kratom-checkout-step__heading {
  display: grid;
  gap: 4px;

  strong {
    color: #1f2b1d;
    font-size: 24px;
    line-height: 1.04;
  }

  small {
    color: #667160;
    font-size: 13px;
    font-weight: 600;
  }
}

.kratom-checkout-step__body,
.kratom-checkout-step__summary {
  margin-top: 18px;
}

.kratom-checkout-step__body {
  display: grid;
  gap: 18px;
}

.kratom-checkout-step__summary {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 18px 20px;
  border-radius: 22px;
  background: rgba(255, 255, 255, 0.7);
  border: 1px solid rgba(74, 91, 68, 0.1);

  p {
    margin: 0;
    color: #40503d;
    line-height: 1.6;
  }
}

.kratom-checkout-step--active {
  .kratom-checkout-step__number {
    background: $color-orange;
    color: #fff;
  }
}

.kratom-checkout-step--complete {
  .kratom-checkout-step__number {
    background: $color-green;
    color: #fff;
  }
}

.kratom-checkout-step--locked {
  opacity: 0.68;

  .kratom-checkout-step__header {
    cursor: not-allowed;
  }
}

.kratom-checkout-auth-gate,
.kratom-checkout-auth-state,
.kratom-checkout-addresses {
  padding: 18px 20px;
  border-radius: 22px;
  background: rgba(255, 255, 255, 0.68);
  border: 1px solid rgba(74, 91, 68, 0.1);
}

.kratom-checkout-auth-gate {
  display: grid;
  gap: 16px;
}

.kratom-checkout-auth-gate__title,
.kratom-checkout-auth-state__title,
.kratom-checkout-addresses__title {
  color: #1f2b1d;
  font-size: 18px;
  font-weight: 700;
}

.kratom-checkout-auth-gate__text,
.kratom-checkout-auth-state__text,
.kratom-checkout-addresses__text,
.kratom-checkout-step__hint {
  margin-top: 4px;
  color: #667160;
  line-height: 1.55;
}

.kratom-checkout-auth-gate__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.kratom-checkout-auth-state {
  display: grid;
  grid-template-columns: auto minmax(0, 1fr) auto;
  gap: 14px;
  align-items: center;
}

.kratom-checkout-auth-state__icon {
  width: 42px;
  height: 42px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 16px;
  background: rgba(53, 82, 74, 0.08);
  color: #35524a;
}

.kratom-checkout-auth-state__avatar {
  width: 42px;
  height: 42px;
  border-radius: 16px;
  object-fit: cover;
  background: rgba(53, 82, 74, 0.08);
}

.kratom-checkout-addresses {
  display: grid;
  gap: 16px;
}

.kratom-checkout-addresses__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.kratom-checkout-addresses__actions {
  display: inline-flex;
  flex-wrap: wrap;
  gap: 12px;
}

.kratom-checkout-addresses__select-wrapper {
  display: grid;
  gap: 8px;
  color: #667160;
  font-size: 14px;
}

.kratom-checkout-addresses__select {
  min-height: 56px;
  padding: 0 16px;
  border-radius: 18px;
  border: 1px solid rgba(74, 91, 68, 0.16);
  background: #fffdf9;
  color: #1f2b1d;
}

.kratom-checkout-addresses__grid {
  display: grid;
  gap: 12px;
}

.kratom-checkout-address {
  padding: 16px 18px;
  border-radius: 20px;
  border: 1px solid rgba(74, 91, 68, 0.12);
  background: #fffdf9;
  display: grid;
  gap: 6px;
  text-align: left;
  color: #40503d;

  strong {
    color: #1f2b1d;
    font-size: 16px;
    font-weight: 700;
  }

  &.is-active {
    border-color: rgba(138, 90, 43, 0.32);
    background: linear-gradient(135deg, rgba(138, 90, 43, 0.12), rgba(53, 82, 74, 0.08));
  }
}

.kratom-checkout-step__button {
  width: 100%;
  max-width: 240px;
}

.kratom-checkout-step__footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.kratom-checkout-side__sticky {
  position: sticky;
  top: 98px;
  display: grid;
  gap: 18px;
}

.kratom-checkout-side__consent {
  color: #667160;
  line-height: 1.65;
}

.kratom-checkout-side__consent-link {
  color: #35524a;
  font-weight: 700;
}

.kratom-checkout-side__trust {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 12px;
  padding-top: 6px;
  color: #667160;
  font-size: 14px;
}

.kratom-checkout-side__trust-item {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.kratom-checkout-side__trust-icon {
  color: #35524a;
}

.kratom-checkout-side__trust-dot {
  color: #b6b8b1;
}

@media (max-width: 767px) {
  .kratom-checkout-shell {
    padding-bottom: 28px;
  }

  .kratom-checkout-hero__title {
    font-size: 38px;
  }

  .checkout-box {
    padding: 20px;
    border-radius: 24px;
  }

  .kratom-checkout-step__heading strong {
    font-size: 22px;
  }

  .kratom-checkout-step__summary,
  .kratom-checkout-auth-state {
    grid-template-columns: 1fr;
  }

  .kratom-checkout-addresses__header,
  .kratom-checkout-step__footer {
    display: grid;
  }

  .kratom-checkout-step__button {
    max-width: none;
  }
}
</style>

<i18n src="./lang.yaml" lang="yaml"></i18n>
