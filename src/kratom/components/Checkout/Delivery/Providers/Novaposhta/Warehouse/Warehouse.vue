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
  <form-novaposhta-settlement
    v-model="order.delivery"
    :error="errors?.delivery?.settlement"
    @input="() => errors?.delivery?.settlement && (errors.delivery.settlement = null)"
    :required="isFieldRequired('delivery.children.settlement')"
  ></form-novaposhta-settlement>

  <form-novaposhta-warehouse
    v-model="order.delivery"
    :error="errors?.delivery?.warehouse"
    @input="() => errors?.delivery?.warehouse && (errors.delivery.warehouse = null)"
    :required="isFieldRequired('delivery.children.warehouse')"
  ></form-novaposhta-warehouse>
</template>