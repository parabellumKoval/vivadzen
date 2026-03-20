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
// METHODS
// HANDLERS
// WATCHERS
</script>

<style src='' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <form-text
    v-model="order.delivery.settlement"
    :error="errors?.delivery?.settlement"
    @input="() => errors?.delivery?.settlement && (errors.delivery.settlement = null)"
    :placeholder="t('form.delivery.settlement')"
    :required="isFieldRequired('delivery.children.settlement')"
  ></form-text>
  <form-text
    v-model="order.delivery.street"
    :error="errors?.delivery?.street"
    @input="() => errors?.delivery?.street && (errors.delivery.street = null)"
    :placeholder="t('form.delivery.street')"
    :required="isFieldRequired('delivery.children.street')"
  ></form-text>
  <form-text
    v-model="order.delivery.house"
    :error="errors?.delivery?.house"
    @input="() => errors?.delivery?.house && (errors.delivery.house = null)"
    :placeholder="t('form.delivery.house')"
    :required="isFieldRequired('delivery.children.house')"
  ></form-text>
  <form-text
    v-model="order.delivery.room"
    :error="errors?.delivery?.room"
    @input="() => errors?.delivery?.room && (errors.delivery.room = null)"
    :placeholder="t('form.delivery.room')"
    :required="isFieldRequired('delivery.children.room')"
  ></form-text>
  <form-text
    v-model="order.delivery.zip"
    :error="errors?.delivery?.zip"
    @input="() => errors?.delivery?.zip && (errors.delivery.zip = null)"
    :placeholder="t('form.delivery.zip')"
    :required="isFieldRequired('delivery.children.zip')"
  ></form-text>
</template>