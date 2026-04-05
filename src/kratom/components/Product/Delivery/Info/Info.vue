<script setup lang="ts">
const { t } = useI18n()
const { get } = useSettings()

const DELIVERY_CATALOG = [
  {
    key: 'messenger_address',
    titleKey: 'delivery.messenger_address',
    image: '/images/logo/messenger.svg',
  },
  {
    key: 'packeta_warehouse',
    titleKey: 'delivery.packeta_warehouse',
    image: '/images/logo/zasilkovna.png',
  },
  {
    key: 'packeta_address',
    titleKey: 'delivery.packeta_address',
    image: '/images/logo/zasilkovna.png',
  },
  {
    key: 'default_pickup',
    titleKey: 'delivery.default_pickup',
    image: '/images/company.png',
  },
]

const PAYMENT_CATALOG = [
  {
    key: 'messenger_cod',
    titleKey: 'payments.messenger_cod.title',
    image: '/images/logo/messenger.svg',
  },
  {
    key: 'zasilkovna_cod',
    titleKey: 'payments.zasilkovna_cod.title',
    image: '/images/logo/zasilkovna.png',
  },
  {
    key: 'default_cash',
    titleKey: 'payments.default_cash.title',
    image: '/images/company.png',
  },
  {
    key: 'card_online',
    titleKey: 'payments.card_online.title',
    image: '/images/logo/GpayApplepay.png',
  },
  {
    key: 'bank_transfer',
    titleKey: 'payments.bank_transfer.title',
    image: '/images/logo/bank.png',
  },
]

const DELIVERY_FALLBACK_KEYS = DELIVERY_CATALOG.map((item) => item.key)
const PAYMENT_FALLBACK_KEYS = PAYMENT_CATALOG.map((item) => item.key)

const normalizeKeys = (value: unknown, fallback: string[]) => {
  if (Array.isArray(value) && value.length) {
    return value.map((item) => String(item))
  }

  if (typeof value === 'string') {
    try {
      const parsed = JSON.parse(value)
      if (Array.isArray(parsed) && parsed.length) {
        return parsed.map((item) => String(item))
      }
    } catch {
      return fallback
    }
  }

  return fallback
}

const deliveryKeys = computed(() => {
  return normalizeKeys(get('shipping.methods', null), DELIVERY_FALLBACK_KEYS)
})

const paymentKeys = computed(() => {
  return normalizeKeys(get('payment.methods', null), PAYMENT_FALLBACK_KEYS)
})

const deliveryItems = computed(() => {
  return DELIVERY_CATALOG
    .filter((item) => deliveryKeys.value.includes(item.key))
    .map((item) => ({
      key: item.key,
      title: t(item.titleKey),
      image: item.image,
    }))
})

const paymentItems = computed(() => {
  return PAYMENT_CATALOG
    .filter((item) => paymentKeys.value.includes(item.key))
    .map((item) => ({
      key: item.key,
      title: t(item.titleKey),
      image: item.image,
    }))
})
</script>

<template>
  <section
    v-if="deliveryItems.length || paymentItems.length"
    class="product-delivery-info"
    :aria-label="t('kratom.product.shipping_payment')"
  >
    <p class="product-delivery-info__eyebrow">{{ t('kratom.product.shipping_payment') }}</p>

    <div v-if="deliveryItems.length" class="product-delivery-info__section">
      <div class="product-delivery-info__title">{{ t('kratom.product.delivery_methods_title') }}</div>
      <div class="product-delivery-info__list">
        <div v-for="item in deliveryItems" :key="item.key" class="product-delivery-info__item">
          <img
            v-if="item.image"
            :src="item.image"
            :alt="item.title"
            class="product-delivery-info__logo"
          >
          <span class="product-delivery-info__name">{{ item.title }}</span>
        </div>
      </div>
      <p class="product-delivery-info__note">{{ t('kratom.product.delivery_methods_note') }}</p>
    </div>

    <div v-if="paymentItems.length" class="product-delivery-info__section">
      <div class="product-delivery-info__title">{{ t('kratom.product.payment_methods_title') }}</div>
      <div class="product-delivery-info__list">
        <div v-for="item in paymentItems" :key="item.key" class="product-delivery-info__item">
          <img
            v-if="item.image"
            :src="item.image"
            :alt="item.title"
            class="product-delivery-info__logo"
          >
          <span class="product-delivery-info__name">{{ item.title }}</span>
        </div>
      </div>
      <p class="product-delivery-info__note">{{ t('kratom.product.payment_methods_note') }}</p>
    </div>
  </section>
</template>

<style scoped lang="scss">
.product-delivery-info {
  display: grid;
  gap: 18px;
  padding: 22px 24px;
  border-radius: 32px;
  border: 1px solid rgba(74, 91, 68, 0.08);
  background:
    radial-gradient(circle at top right, rgba(255, 212, 124, 0.18), transparent 34%),
    linear-gradient(160deg, rgba(255, 249, 239, 0.98), rgba(246, 239, 229, 0.94));
}

.product-delivery-info__eyebrow {
  margin: 0;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: #f28d1a;
}

.product-delivery-info__section {
  display: grid;
  gap: 10px;
}

.product-delivery-info__title {
  font-size: 15px;
  font-weight: 700;
  color: #203019;
}

.product-delivery-info__list {
  display: grid;
  gap: 10px;
}

.product-delivery-info__item {
  display: flex;
  align-items: center;
  gap: 12px;
  min-height: 52px;
  padding: 10px 12px;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.72);
  border: 1px solid rgba(74, 91, 68, 0.08);
}

.product-delivery-info__logo {
  display: block;
  width: 92px;
  height: 24px;
  object-fit: contain;
  object-position: left center;
  flex-shrink: 0;
}

.product-delivery-info__name {
  color: #293626;
  line-height: 1.4;
}

.product-delivery-info__note {
  margin: 0;
  color: #687162;
  font-size: 13px;
  line-height: 1.6;
}

@include mobile {
  .product-delivery-info {
    padding: 20px;
    border-radius: 28px;
  }

  .product-delivery-info__item {
    align-items: flex-start;
    flex-direction: column;
  }

  .product-delivery-info__logo {
    width: 104px;
  }
}
</style>
