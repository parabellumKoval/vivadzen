<script setup lang="ts">
import { usePaymentStore } from '~/store/payment'

const { t } = useI18n()
const regionPath = useToLocalePath()
const { orderable } = useAuth()

definePageMeta({
  bg: '#f3ece2',
  ssr: false,
})

const breadcrumbs = computed(() => [
  { name: t('title.home'), item: '/' },
  { name: t('title.checkout'), item: '/checkout' },
  { name: t('title.checkout_payment'), item: '/checkout/payment' },
])

useSeo().setPageSeo('checkout_payment', {
  fallbackTitle: () => t('title.checkout_payment'),
  robots: 'noindex, nofollow',
})

const formElement = ref<HTMLFormElement | null>(null)
const form = ref<{ action?: string | null; data?: string | null; signature?: string | null }>({})
const isLoading = ref(false)

const paymentProviders: Record<string, string> = {
  liqpay_online: 'liqpay',
  niftipay_online: 'niftipay',
}

watch(formElement, (value) => {
  if (value && form.value.action && form.value.data && form.value.signature) {
    value.submit()
  }
})

const submitHandler = async () => {
  isLoading.value = true
  try {
    const response = await useCartStore().createOrder({ ...orderable.value })
    if (!response?.code) {
      throw new Error('Order was not created')
    }

    const provider = paymentProviders[useCartStore().order.payment.method as string] || 'liqpay'
    const paymentResponse = await usePaymentStore().createPayment(provider, {
      action: 'pay',
      amount: response.price,
      currency: response.currencyCode || response.currency || 'CZK',
      description: t('kratom.checkout.order_label', { code: response.code }),
      order: response.code,
      email: response.user?.email,
    })

    if (paymentResponse.value?.type === 'form') {
      form.value = paymentResponse.value || {}
      return
    }

    const paymentUrl = paymentResponse.value?.url || paymentResponse.value?.payUrl
    if (paymentUrl) {
      window.location.replace(paymentUrl)
    }
  } catch (err) {
    useNoty().setNoty({
      title: t('error.error'),
      content: t('error.check_fields'),
      type: 'error',
    }, 7000)
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="page-base kratom-payment-page">
    <div class="container kratom-page-container kratom-content-shell">
      <the-breadcrumbs :crumbs="breadcrumbs" />
      <h1 class="title-common">{{ t('title.checkout_payment') }}</h1>

      <div class="kratom-content-card kratom-payment-card">
        <form
          v-if="form.action && form.data && form.signature"
          ref="formElement"
          :action="form.action"
          method="POST"
          accept-charset="utf-8"
        >
          <input type="hidden" name="data" :value="form.data" />
          <input type="hidden" name="signature" :value="form.signature" />
        </form>

        <p>{{ t('messages.to_pay') }}: <simple-price :value="useCartStore().total" /></p>
        <p>{{ t('kratom.checkout.payment_redirect') }}</p>

        <div class="kratom-payment-card__actions">
          <NuxtLink :to="regionPath('/checkout')" class="button alternate">{{ t('button.back') }}</NuxtLink>
          <button type="button" class="button primary" :class="{ loading: isLoading }" @click="submitHandler">
            {{ t('button.pay') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.kratom-content-shell {
  padding-top: 24px;
}

.kratom-content-card {
  padding: 28px;
  border-radius: 32px;
  background: rgba(255, 250, 244, 0.92);
  border: 1px solid rgba(74, 91, 68, 0.1);
}

.kratom-payment-card {
  display: grid;
  gap: 18px;
}

.kratom-payment-card__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}
</style>
