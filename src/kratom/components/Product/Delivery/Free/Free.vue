<script setup>
const {t} = useI18n()
const {get} = useSettings()

const props = defineProps({
  value: {
    type: Number,
    default: null
  }
})

const freeDeliveryAmount = computed(() => {
  return get('shipping.free_min_price') || 0
})

const freeDeliveryEnabled = computed(() => {
  return !!(get('shipping.free_enabled') || 0)
})

const freeDelivery = computed(() => {
  if(!freeDeliveryEnabled.value) {
    return false
  }

  if(props.value === null) {
    return true
  }

  return props.value >= freeDeliveryAmount.value
})

const freeDeliveryLeft = computed(() => {
  if(props.value === null) {
    return 0
  }

  return Math.max(freeDeliveryAmount.value - props.value, 0)
})

const freeDeliveryProgress = computed(() => {
  if(props.value === null || freeDeliveryAmount.value <= 0) {
    return 0
  }

  const percent = (props.value / freeDeliveryAmount.value) * 100
  return Math.min(percent, 100)
})

const showProgress = computed(() => {
  return freeDeliveryEnabled.value &&
    props.value !== null &&
    freeDeliveryAmount.value > 0 &&
    !freeDelivery.value
})
</script>

<style src="./free.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <transition name="fade-in">
    <div v-if="freeDelivery" class="free-delivery">
      <IconCSS name="solar:gift-bold-duotone" class="free-delivery-icon"></IconCSS>
      <span class="free-delivery-name">
        {{ t('free') }}
        <span class="free-delivery-desc">{{ t('order') }} <span class="bold">{{ t('from') }} <simple-price :value="freeDeliveryAmount" class="free-delivery-price"></simple-price></span> <span v-if="value !== null">&nbsp;{{ t('activated') }}</span>.</span>
      </span>
    </div>
    <div v-else-if="showProgress" class="free-delivery-progress">
      <div class="free-delivery-progress-text">
        <span>{{ t('left') }}</span>
        <simple-price :value="freeDeliveryLeft" class="free-delivery-progress-price"></simple-price>
        <span>{{ t('to_free') }}</span>
      </div>
      <div class="free-delivery-progress-bar">
        <div class="free-delivery-progress-fill" :style="{width: `${freeDeliveryProgress}%`}"></div>
      </div>
    </div>
  </transition>
</template>
