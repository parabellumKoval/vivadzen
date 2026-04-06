<script setup>
import {useCartStore} from '~/store/cart'

const {t} = useI18n()
const regionPath = useToLocalePath()
const { orderable } = useAuth()
const { region, fallbackRegion } = useRegion()
const runtimeConfig = useRuntimeConfig()
const { get: getSetting } = useSettings()

const props = defineProps({
  cart: {
    type: Object
  }
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

// HANDLERS
const goCompleteHandler = () => {
  if (!ensureAgeVerificationBeforeSubmit()) {
    return
  }

  const payload = { ...orderable.value }
  useCartStore().createOrder(payload).then((response) => {
    if(response?.code) {
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

const goPayHandler = () => {
  if (!ensureAgeVerificationBeforeSubmit()) {
    return
  }

  const payload = { ...orderable.value }
  useCartStore().validate(payload).then((response) => {
    if(response) {
      navigateTo(regionPath('/checkout/payment'))
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
// WATCHERS
</script>

<style src='./sale.scss' lang='scss' scoped></style>

<template>
  <div class="sale">
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

      <transition name="fade-in">
        <button
          v-if="order.payment.method === 'liqpay_online'"
          @click="goPayHandler"
          :class="{disabled: actionBlocked}"
          :disabled="actionBlocked"
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
