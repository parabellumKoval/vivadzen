<script setup>
import {useCartStore} from '~/store/cart'

const {t} = useI18n()
const {pickupLocations} = useContacts()

const order = computed(() => {
  return useCartStore().order
})

const errors = computed(() => {
  return useCartStore().errors
})

const selectedLocationId = computed(() => {
  const currentId = String(order.value?.delivery?.warehouseRef || '').trim()
  if (currentId) {
    return currentId
  }

  const currentLabel = String(order.value?.delivery?.warehouse || '').trim()
  if (!currentLabel) {
    return ''
  }

  return pickupLocations.value.find((item) => item.label === currentLabel)?.id || ''
})

const applyLocation = (location) => {
  if (!location || !order.value?.delivery) {
    return
  }

  order.value.delivery.warehouseRef = location.id
  order.value.delivery.warehouse = location.label || location.address
  order.value.delivery.street = location.address || null

  if (errors.value?.delivery?.warehouse) {
    errors.value.delivery.warehouse = null
  }
}

const syncLocationSelection = () => {
  if (order.value?.delivery?.method !== 'default_pickup') {
    return
  }

  if (!pickupLocations.value.length) {
    order.value.delivery.warehouseRef = null
    order.value.delivery.warehouse = null
    order.value.delivery.street = null
    return
  }

  const current = pickupLocations.value.find((item) => item.id === selectedLocationId.value)
  if (current) {
    applyLocation(current)
    return
  }

  if (!order.value.delivery.warehouse) {
    applyLocation(pickupLocations.value[0])
  }
}

watch(
  [pickupLocations, () => order.value?.delivery?.method],
  syncLocationSelection,
  { immediate: true },
)
</script>

<style scoped lang="scss">
.pickup-locations {
  display: grid;
  gap: 12px;
  grid-column: 1 / -1;
}

.pickup-locations__list {
  display: grid;
  gap: 10px;

  @include desktop {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

.pickup-locations__item {
  display: grid;
  gap: 4px;
  width: 100%;
  padding: 14px 16px;
  text-align: left;
  border: 1px solid $color-border;
  border-radius: $radius;
  background: $color-8;
  color: inherit;
  transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;

  &.active {
    border-color: $color-primary;
    box-shadow: 0 0 0 1px rgba($color-primary, 0.18);
  }

  &:hover {
    transform: translateY(-1px);
  }
}

.pickup-locations__title {
  font-weight: 700;
}

.pickup-locations__address {
  color: #1f2b1d;
}

.pickup-locations__schedule {
  color: #8b9387;
}
</style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="form-static pickup-locations">
    <div class="label">{{ t('label.our_address') }}</div>
    <div v-if="pickupLocations.length" class="pickup-locations__list">
      <button
        v-for="location in pickupLocations"
        :key="location.id"
        type="button"
        class="pickup-locations__item"
        :class="{ active: selectedLocationId === location.id }"
        @click="applyLocation(location)"
      >
        <div v-if="location.title" class="pickup-locations__title">{{ location.title }}</div>
        <div class="pickup-locations__address">{{ location.address }}</div>
        <div v-if="location.schedule" class="pickup-locations__schedule">{{ location.schedule }}</div>
      </button>
    </div>
    <div v-else>—</div>
    <form-error :error="errors?.delivery?.warehouse"></form-error>
  </div>
</template>
