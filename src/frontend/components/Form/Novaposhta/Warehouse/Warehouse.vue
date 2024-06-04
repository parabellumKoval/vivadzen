<script setup lang="ts">
import {useNp} from '~/composables/form/useNp'
import type {Delivery} from '~/types/order';
import type {PropType} from 'vue';

const props = defineProps({
  modelValue: {
    type: Object as PropType<Delivery>,
    default: null
  },
  error: {
    type: [Object, Array, String, Boolean],
    default: false
  }
})

const {t} = useI18n()

const {getWarehouses, warehouses, isLoadingWarehouses, settlement, warehouse, setWarehouse} = useNp()
const emit = defineEmits(['selected', 'update:modelValue'])

const search = ref('')


// COMPUTED
const warehouseString = computed(() => {
  return props.modelValue?.warehouse || warehouse.value?.warehouse || null
})

const isDisabled = computed(() => {
  return !settlement.value.settlementRef
})

// METHODS
const updateWarehouse = (value) => {

  setWarehouse(value)

  let newValue = {
    ...props.modelValue
  }

  newValue.warehouse = value.warehouse
  newValue.warehouseRef = value.warehouseRef

  emit('update:modelValue', newValue)

}

// WATCH
watch(() => search.value, (val) => {
  const settlementRef = settlement.value.settlementRef || null
  getWarehouses(val, settlementRef)
})

// update warehouses if settlement was updated
watch(() => settlement.value, (v) => {

  if(!v.settlementRef) {
    return
  }

  updateWarehouse({warehouse: null, warehouseRef: null})

  setTimeout(() => {
    // get warehouses without search by string
    getWarehouses(null, v.settlementRef)
  }, 1000)
}, {
  deep: true,
  immediate: true
})

// HANDLERS
const updateModelValueHandler = (ref) => {
  const searched = warehouses.value.find((item) => {
    return item.warehouseRef === ref
  })

  if(!searched) {
    console.warn('Warehouse was not fond in the warehouses list')
    return
  }

  updateWarehouse(searched)
}

</script>
<style src="./warehouse.scss" lang="scss" scoped />

<template>
  <div :class="{'disabled': isDisabled}" class="warehouse">
    <transition name="scale-x">
      <simple-loader v-if="isLoadingWarehouses"></simple-loader>
    </transition>

    <form-dropdown
      :model-value = "modelValue.warehouse"
      @update:modelValue="updateModelValueHandler"
      v-model:search-value = "search"
      :values = "warehouses"
      :placeholder="$t('form.delivery.warehouse')"
      :error="error"
      :is-disabled="isDisabled"
      list-value="warehouse"
      list-key="warehouseRef"
      required
    >
    </form-dropdown>

    <p v-if="warehouseString" class="value-desc">{{ warehouseString }}</p>
  </div>
</template>
