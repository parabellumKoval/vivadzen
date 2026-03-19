<script setup>
import {useCartStore} from '~/store/cart'

const {t} = useI18n()
const props = defineProps({})
const {isFieldRequired} = useCartStore()

// COMPUTEDS
const order = computed(() => {
  return useCartStore().order
})

const errors = computed(() => {
  return useCartStore().errors
})

const isAddressAlreadyCollected = () => {
  return useCartStore().isAddressCollected
}
// HANDLERS
// WATCHERS
</script>

<!-- <style src='./../../../../checkout.scss' lang='scss' scoped></style> -->
<style src='./transfer.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="form-row">
    <div>{{ t('payments.bank_transfer.desc') }}</div>
    <div v-if="!isAddressAlreadyCollected()" class="title">{{ t('payments.bank_transfer.fill_contacts') }}</div>
  </div>
  <template v-if="!isAddressAlreadyCollected()">
    <form-text
      v-model="order.payment.settlement"
      :error="errors?.payment?.settlement"
      @input="() => errors?.payment?.settlement && (errors.payment.settlement = null)"
      :placeholder="t('form.delivery.settlement')"
      :required="isFieldRequired('payment.children.settlement')"
    ></form-text>
    <form-text
      v-model="order.payment.street"
      :error="errors?.payment?.street"
      @input="() => errors?.payment?.street && (errors.payment.street = null)"
      :placeholder="t('form.delivery.street')"
      :required="isFieldRequired('payment.children.street')"
    ></form-text>
    <form-text
      v-model="order.payment.house"
      :error="errors?.payment?.house"
      @input="() => errors?.payment?.house && (errors.payment.house = null)"
      :placeholder="t('form.delivery.house')"
      :required="isFieldRequired('payment.children.house')"
    ></form-text>
    <form-text
      v-model="order.payment.room"
      :error="errors?.payment?.room"
      @input="() => errors?.payment?.room && (errors.payment.room = null)"
      :placeholder="t('form.delivery.room')"
      :required="isFieldRequired('payment.children.room')"
    ></form-text>
    <form-text
      v-model="order.payment.zip"
      :error="errors?.payment?.zip"
      @input="() => errors?.payment?.zip && (errors.payment.zip = null)"
      :placeholder="t('form.delivery.zip')"
      :required="isFieldRequired('payment.children.zip')"
    ></form-text>
  </template>
</template>