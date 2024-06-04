<script setup>
const {t} = useI18n({useScope: 'global'})

const payments = computed(() => {
  return usePaymentVendor().all
})

const types = computed(() => {
  return usePaymentVendor().types
})
</script>

<style src="./../../box.scss" lang="scss" scoped></style>
<style src="./box.scss" lang="scss" scoped></style>

<template>
  <div class="product-box">
    <div class="product-box-label">{{ t('messages.accept_payment') }}:</div>
    <div class="payment">
      <div v-for="payment in payments" :key="payment.id" class="payment-item">
        <nuxt-img
          v-if="payment.logo"
          :src = "payment.logo"
          width="60"
          height="40"
          sizes = "mobile:80px tablet:80px desktop:80px"
          format = "webp"
          quality = "60"
          loading = "lazy"
          fit="outside"
          class="payment-image"
        />
      </div>
    </div>
    <div class="product-box-label">{{ t('messages.payment_methods') }}:</div>
    <div class="type">
      <div v-for="item in types" :key="item.id" class="type-item">{{ item.title }}</div>
    </div>
  </div>
</template>