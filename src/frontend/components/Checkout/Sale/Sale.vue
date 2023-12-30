<script setup>
const {t} = useI18n()
const props = defineProps({
  cart: {
    type: Object
  }
})

// COMPUTEDS
const productsPrice = computed(() => {
  return props.cart.products.reduce((carry, item) => {
    return carry + item.price * item.amount
  }, 0)
})

const total = computed(() => {
  return productsPrice.value
})
// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='./sale.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="sale">
    <div class="sale-list">
      <div class="sale-item">
        <div class="sale-label">{{ cart.products.length }} {{ t('messages.products_total') }}</div>
        <div class="sale-value">{{ $n(productsPrice, 'currency') }}</div>
      </div>
      <div class="sale-item">
        <div class="sale-label">{{ t('label.promocode') }}</div>
        <div class="sale-value">{{ $n(500, 'currency') }}</div>
      </div>
      <div class="sale-item">
        <div class="sale-label">{{ t('messages.delivery_price') }}</div>
        <div class="sale-value">{{ t('messages.delivery_vendor_price') }}</div>
      </div>
      <div class="promocode-wrapper">
        <simple-button-text icon="fluent:tag-28-regular" :text="t('messages.use_promocode')" class="promocode-open-btn"></simple-button-text>
      </div>
      <div class="sale-footer">
        <div class="sale-item">
          <div class="sale-label">{{ t('messages.to_pay') }}</div>
          <div class="sale-value large">{{ $n(total, 'currency') }}</div>
        </div>
      </div>

      <button class="button primary sale-button">
        <span>{{ t('button.pay') }}</span>
      </button>
    </div>
  </div>
</template>