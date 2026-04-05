<script setup>
import {useCartStore} from '~/store/cart'

const {t} = useI18n()
const {isFieldRequired} = useCartStore()

const order = computed(() => {
  return useCartStore().order
})

const errors = computed(() => {
  return useCartStore().errors
})
</script>

<template>
  <!-- <div class="messenger-delivery-intro">
    <img
      src="/images/logo/messenger.svg"
      alt="Messenger.cz"
      class="messenger-delivery-intro__logo"
    >
    <strong class="messenger-delivery-intro__title">{{ t('delivery.messenger_address') }}</strong>
  </div> -->

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

<style scoped lang="scss">
.messenger-delivery-intro {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  margin-bottom: 4px;
  border: 1px solid rgba(74, 91, 68, 0.12);
  border-radius: 18px;
  background: rgba(255, 248, 229, 0.72);
}

.messenger-delivery-intro__logo {
  display: block;
  width: 116px;
  max-width: 100%;
  height: 26px;
  object-fit: contain;
  object-position: left center;
}

.messenger-delivery-intro__title {
  color: #203019;
  font-size: 14px;
  line-height: 1.35;
}

@include mobile {
  .messenger-delivery-intro {
    flex-wrap: wrap;
  }
}
</style>
