<script setup>
import {useCartStore} from '~/store/cart'
import { usePaymentStore } from '~/store/payment'

const {t} = useI18n()
const regionPath = useToLocalePath()
const { orderable, isAuthenticated } = useAuth()
const { syncContactDetails, syncDeliveryAddress } = useCheckoutProfileSync()
const { region, fallbackRegion } = useRegion()
const runtimeConfig = useRuntimeConfig()
const { get: getSetting } = useSettings()

const props = defineProps({
  cart: {
    type: Object
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  disabledMessage: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['scrollToError'])


// COMPUTEDS
const isDefaultRegion = computed(() => {
  return region.value === fallbackRegion
})

const productsLength = computed(() => {
  return useCartStore().cart?.length || 0
})

const productsTotal = computed(() => {
  return useCartStore().totalProducts
})

const finishTotal = computed(() => {
  const v = useCartStore().finishTotal
  return v < 0 ? 0 : v
})

const order = computed(() => {
  return useCartStore().order
})

const errors = computed(() => {
  return useCartStore().errors
})

const adultoPublicKey = computed(() => {
  return String(runtimeConfig.public.adultoPublicKey || '').trim()
})

const ageVerificationEnvEnabled = computed(() => {
  return Boolean(runtimeConfig.public.kratomCheckoutAgeVerificationEnabled)
})

const ageVerificationFeatureEnabled = computed(() => {
  return ageVerificationEnvEnabled.value && Boolean(getSetting('store.age_verification.adulto.enabled', false))
})

const cartRequiresAgeVerification = computed(() => {
  return useCartStore().cart.some((product) => {
    return Boolean(product?.requiresAgeVerification || product?.requires_age_verification)
  })
})

const isCzechRegion = computed(() => {
  return String(region.value || '').toLowerCase() === 'cz'
})

const ageVerificationRequired = computed(() => {
  return ageVerificationFeatureEnabled.value && isCzechRegion.value && cartRequiresAgeVerification.value
})

const ageVerificationReady = computed(() => {
  return String(order.value?.age_verification_uid || '').trim().length > 0
})

const ageVerificationUnavailable = computed(() => {
  return ageVerificationRequired.value && adultoPublicKey.value.length === 0
})

const actionBlocked = computed(() => {
  if (props.disabled) {
    return true
  }

  if (isDefaultRegion.value) {
    return true
  }

  if (ageVerificationUnavailable.value) {
    return true
  }

  if (ageVerificationRequired.value && !ageVerificationReady.value) {
    return true
  }

  return false
})

const ageVerificationErrorMessage = computed(() => {
  return errors.value?.age_verification_uid?.[0] || null
})


// METHODS
const ensureAgeVerificationBeforeSubmit = () => {
  if (ageVerificationUnavailable.value) {
    useNoty().setNoty({
      title: t('error.error'),
      content: t('messages.adulto_unavailable'),
      type: 'error'
    }, 7000)
    return false
  }

  if (ageVerificationRequired.value && !ageVerificationReady.value) {
    useNoty().setNoty({
      title: t('error.error'),
      content: t('messages.verify_age_before_order'),
      type: 'error'
    }, 7000)
    return false
  }

  return true
}

const ageVerificationSuccessHandler = (uid) => {
  order.value.age_verification_uid = uid

  if (errors.value?.age_verification_uid) {
    errors.value.age_verification_uid = null
  }
}

const ageVerificationErrorHandler = (message) => {
  useNoty().setNoty({
    title: t('error.error'),
    content: message || t('messages.verify_age_before_order'),
    type: 'error'
  }, 7000)
}

const paymentProviders = {
  liqpay_online: 'liqpay',
  niftipay_online: 'niftipay',
}

const formElement = ref(null)
const form = ref({})
const isPaymentLoading = ref(false)
const onlinePaymentMethods = Object.keys(paymentProviders)
const isOnlinePayment = computed(() => onlinePaymentMethods.includes(order.value?.payment?.method))

watch(formElement, (value) => {
  if (value && form.value.action && form.value.data && form.value.signature) {
    value.submit()
  }
})

const redirectToPaymentGateway = async (response) => {
  const provider = paymentProviders[order.value?.payment?.method] || 'liqpay'
  const paymentResponse = await usePaymentStore().createPayment(provider, {
    action: 'pay',
    amount: response.price,
    currency: response.currencyCode || response.currency || 'CZK',
    description: t('kratom.checkout.order_label', { code: response.code }),
    order: response.code,
    email: response.user?.email,
  })

  const payment = paymentResponse?.value || paymentResponse

  if (payment?.type === 'form') {
    form.value = payment || {}
    return
  }

  const paymentUrl = payment?.url || payment?.payUrl
  if (paymentUrl) {
    window.location.replace(paymentUrl)
    return
  }

  throw new Error('Payment gateway URL was not returned')
}

const syncProfileBeforeSubmit = async () => {
  if (!isAuthenticated.value) {
    return
  }

  try {
    await syncContactDetails(order.value?.user)
  } catch (error) {
    console.error('[kratom-checkout] Failed to sync user profile before submit', error)
  }

  try {
    await syncDeliveryAddress(order.value?.delivery, { makeMain: false })
  } catch (error) {
    console.error('[kratom-checkout] Failed to sync delivery address before submit', error)
  }
}

const markCurrentAddressAsMain = async () => {
  if (!isAuthenticated.value) {
    return
  }

  try {
    await syncDeliveryAddress(order.value?.delivery, { makeMain: true })
  } catch (error) {
    console.error('[kratom-checkout] Failed to promote delivery address to main', error)
  }
}

// HANDLERS
const goCompleteHandler = async () => {
  if (props.disabled) {
    if (props.disabledMessage) {
      useNoty().setNoty({
        title: t('error.error'),
        content: props.disabledMessage,
        type: 'warning',
      }, 6000)
    }

    emit('scrollToError')
    return
  }

  if (!ensureAgeVerificationBeforeSubmit()) {
    return
  }

  await syncProfileBeforeSubmit()

  const payload = { ...orderable.value }
  useCartStore().createOrder(payload).then(async (response) => {
    if(response?.code) {
      await markCurrentAddressAsMain()
      useCartStore().$reset()
      navigateTo(regionPath('/checkout/complete/' + response.code))
    }
  }).catch((e) => {
    useNoty().setNoty({
      title: t('error.error'),
      content: t('error.check_fields'),
      type: 'error'
    }, 7000)

    emit('scrollToError')
  })
}

const goPayHandler = async () => {
  if (props.disabled) {
    if (props.disabledMessage) {
      useNoty().setNoty({
        title: t('error.error'),
        content: props.disabledMessage,
        type: 'warning',
      }, 6000)
    }

    emit('scrollToError')
    return
  }

  if (!ensureAgeVerificationBeforeSubmit()) {
    return
  }

  await syncProfileBeforeSubmit()

  isPaymentLoading.value = true
  const payload = { ...orderable.value }
  useCartStore().createOrder(payload).then(async (response) => {
    if(response?.code) {
      await markCurrentAddressAsMain()
      await redirectToPaymentGateway(response)
    }
  }).catch((e) => {
    useNoty().setNoty({
      title: t('error.error'),
      content: t('error.check_fields'),
      type: 'error'
    }, 7000)

    emit('scrollToError')
  }).finally(() => {
    isPaymentLoading.value = false
  })
}
// WATCHERS
</script>

<style src='./sale.scss' lang='scss' scoped></style>

<template>
  <div class="sale">
    <form
      v-if="form.action && form.data && form.signature"
      ref="formElement"
      :action="form.action"
      method="POST"
      accept-charset="utf-8"
      hidden
    >
      <input type="hidden" name="data" :value="form.data" />
      <input type="hidden" name="signature" :value="form.signature" />
    </form>

    <div class="sale-list">
      <div class="sale-item">
        <div class="sale-label">{{ productsLength }} {{ t('messages.products_total') }}</div>
        <div class="sale-value">
          <simple-price :value="productsTotal" class="price price-total"></simple-price>
        </div>
      </div>

      <checkout-sale-delivery></checkout-sale-delivery>

      <div class="sale-features">
        <div v-if="ageVerificationRequired" class="sale-age-verification">
          <checkout-age-verification-adulto
            v-model="order.age_verification_uid"
            :required="ageVerificationRequired"
            :public-key="adultoPublicKey"
            @update:modelValue="ageVerificationSuccessHandler"
            @error="ageVerificationErrorHandler"
          />
          <div v-if="ageVerificationErrorMessage" class="sale-age-verification__error">
            {{ ageVerificationErrorMessage }}
          </div>
        </div>

        <div class="sale-footer">
          <div class="sale-item sale-item--summary">
            <div class="sale-label sale-label--summary">{{ t('messages.to_pay') }}</div>
            <div class="sale-value sale-value--summary large">
              <simple-price :value="finishTotal" class="price price-total"></simple-price>
            </div>
          </div>
        </div>

        
      </div>

      <div v-if="disabledMessage && disabled" class="sale-block-note">
        {{ disabledMessage }}
      </div>

      <transition name="fade-in">
        <button
          v-if="isOnlinePayment"
          @click="goPayHandler"
          :class="{disabled: actionBlocked, loading: isPaymentLoading}"
          :disabled="actionBlocked || isPaymentLoading"
          class="button primary sale-button"
        >
          <span>{{ t('button.pay') }}</span>
          <IconCSS name="ph:arrow-right" size="22" class="sale-button__icon" />
        </button>
        <button
          v-else
          @click="goCompleteHandler"
          :class="{disabled: actionBlocked}"
          :disabled="actionBlocked"
          class="button primary sale-button"
        >
          <span>{{ t('button.create_order') }}</span>
          <IconCSS name="ph:arrow-right" size="22" class="sale-button__icon" />
        </button>
      </transition>
    </div>
  </div>
</template>
