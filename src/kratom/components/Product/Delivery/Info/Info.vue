<script setup lang="ts">
const { t } = useI18n()
const { methods: deliveryMethods } = useDelivery()
const { methodsInfo: paymentMethods } = usePayment()

const deliveryItems = computed(() => {
  return deliveryMethods.value.map((method) => ({
    key: method.key,
    title: method.title || method.label || t(`delivery.${method.key}`),
    image: method.image || method.logo,
  }))
})

const paymentItems = computed(() => {
  return paymentMethods.value.map((method) => ({
    key: method.key,
    title: method.title || method.label || t(`payments.${method.key}.title`),
    image: method.image || method.logo,
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
      <p class="product-delivery-info__note">{{ t('kratom.product.delivery_methods_note') }}</p>
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
    </div>

    <div v-if="paymentItems.length" class="product-delivery-info__section">
      <div class="product-delivery-info__title">{{ t('kratom.product.payment_methods_title') }}</div>
      <p class="product-delivery-info__note">{{ t('kratom.product.payment_methods_note') }}</p>
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
    </div>
  </section>
</template>

<style scoped lang="scss">
.product-delivery-info {
  display: grid;
  gap: 30px;
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
  gap: 5px;
}

.product-delivery-info__title {
  font-size: 15px;
  font-weight: 700;
  color: #203019;
}

.product-delivery-info__list {
  display: grid;
  gap: 5px;
}

.product-delivery-info__item {
  display: flex;
  align-items: center;
  gap: 12px;
  min-height: 40px;
  padding: 10px 12px;
  border-radius: 9px;
  font-weight: 600;
  background: rgba(255, 255, 255, 0.72);
  border: 1px solid $color-border;
  overflow: hidden;
}

.product-delivery-info__logo {
  display: block;
  width: 100px;
  height: 42px;
  object-fit: contain;
  object-position: left center;
  flex-shrink: 0;
  background: #f8f8f8;
  padding: 10px 15px;
  margin: -10px 0 -10px -12px;
  border-right: 1px solid #eee;
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
