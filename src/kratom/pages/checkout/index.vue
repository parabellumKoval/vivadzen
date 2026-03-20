<script setup lang="ts">
const { t } = useI18n()
const cartStore = useCartStore()
const regionPath = useToLocalePath()

definePageMeta({
  bg: '#f3ece2',
  ssr: false,
})

const breadcrumbs = computed(() => [
  { name: t('title.home'), item: '/' },
  { name: t('title.checkout'), item: '/checkout' },
])

const products = computed(() => cartStore.cart)
const order = computed(() => cartStore.order)
const errors = computed(() => cartStore.errors)

const userErrors = computed(() => Boolean(errors.value.user && Object.keys(errors.value.user).length))
const deliveryErrors = computed(() => Boolean(errors.value.delivery && Object.keys(errors.value.delivery).length))
const paymentErrors = computed(() => Boolean(errors.value.payment && Object.keys(errors.value.payment).length))

const isUserNameRequired = computed(() => true)

const openCatalogHandler = async () => {
  await navigateTo(regionPath('/catalog'))
}

const scrollToErrorHandler = () => {
  if (process.client) {
    const target = document.querySelector('.checkout-box.error')
    target?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

watch(() => order.value.delivery.settlement, () => {
  const delivery = order.value?.delivery
  if (!delivery || delivery.method === 'packeta_warehouse') {
    return
  }

  delivery.warehouse = null
  delivery.street = null
})

watch(() => order.value.delivery.method, () => {
  errors.value.delivery = {}
}, { immediate: true })

watch(() => order.value.payment.method, () => {
  errors.value.payment = {}
}, { immediate: true })

await useAsyncData('kratom-cart-products', async () => cartStore.fetchCartProducts())
await useAsyncData('kratom-cart-rules', async () => cartStore.rules())
cartStore.clearErrors()
cartStore.clearBonusUsage()
cartStore.removeCode()
cartStore.setPromocode(null)
</script>

<template>
  <div class="page-base kratom-checkout-page">
    <div class="container kratom-checkout-shell">
      <the-breadcrumbs :crumbs="breadcrumbs" />

      <section class="kratom-checkout-hero">
        <p class="kratom-checkout-hero__eyebrow">{{ t('kratom.checkout.eyebrow') }}</p>
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

          <section id="delivery-box" class="checkout-box" :class="{ error: deliveryErrors }">
            <div class="title-secondary">{{ t('title.delivery') }}</div>
            <checkout-delivery />
          </section>

          <section id="payment-box" class="checkout-box" :class="{ error: paymentErrors }">
            <div class="title-secondary">{{ t('title.payment') }}</div>
            <checkout-payment />
          </section>

          <section id="customer-box" class="checkout-box" :class="{ error: userErrors }">
            <div class="title-secondary">{{ t('label.customer') }}</div>
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
                :error="errors?.user?.email"
                :placeholder="t('form.email')"
                :required="cartStore.isFieldRequired('user.children.email')"
                @input="() => errors?.user?.email && (errors.user.email = null)"
              />
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
            <checkout-sale @scroll-to-error="scrollToErrorHandler" @scrollToError="scrollToErrorHandler" />
          </div>
          <checkout-contacts class="kratom-checkout-contacts" />
        </aside>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.kratom-checkout-shell {
  padding-top: 32px;
}

.kratom-checkout-hero {
  margin-bottom: 28px;
  padding: 28px;
  border-radius: 32px;
  background: linear-gradient(135deg, rgba(255, 247, 236, 0.96), rgba(240, 231, 219, 0.92));
  border: 1px solid rgba(74, 91, 68, 0.1);
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

.checkout-box {
  padding: 24px;
  border-radius: 28px;
  background: rgba(255, 250, 244, 0.92);
  border: 1px solid rgba(74, 91, 68, 0.1);
  box-shadow: 0 24px 60px rgba(39, 49, 36, 0.06);
}

.kratom-checkout-side__sticky {
  @include desktop {
    position: sticky;
    top: 98px;
  }
}

.kratom-checkout-empty {
  line-height: 1.7;
}

.kratom-checkout-contacts {
  margin-top: 18px;
}
</style>
