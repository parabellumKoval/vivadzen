<script setup lang="ts">
import {useNp} from '~/composables/form/useNp'
import type {Delivery} from '~/types/order';
import type {PropType} from 'vue';

const {t} = useI18n()

const {getSettlements, settlements, settlement, isLoadingSettlements, setSettlement} = useNp()

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

const emit = defineEmits([
  'update:modelValue',
  'update:ref'
])

const search = ref(null)

// COMPUTEDS
const settlementString = computed(() => {
  const settlementDitaled = [
    props.modelValue?.area || settlement.value?.area || null,
    props.modelValue?.region || settlement.value?.region || null,
    props.modelValue?.type || settlement.value?.type || null,
    props.modelValue?.settlement || settlement.value?.settlement || null
  ].filter(item => item !== null)

  const string = settlementDitaled.join(', ')

  return string
})

// METHODS
const updateModelValue = (v) => {
  const searched = settlements.value.find((item) => {
    return item.settlementRef === v
  })

  if(!searched) {
    console.warn('Settlement was not fond in the settlements list')
    return
  }

  // setSettlement(searched)

  let newValue = {
    ...props.modelValue
  }
  
  newValue.settlement = searched.settlement
  newValue.settlementRef = searched.settlementRef
  newValue.area = searched.area
  newValue.region = searched.region
  newValue.type = searched.type

  emit('update:modelValue', newValue)
}

watch(search, (val) => {
  getSettlements(val)
})

watch(() => props.modelValue.settlementRef, (v) => {

  // If settlements list does not exists get list by modelValue 
  if(v && !settlements.value?.length){
    getSettlements(null, v)
  }

  if(v) {
    setSettlement(props.modelValue)
  }

}, {
  immediate: true
})

</script>
<style src="./settlement.scss" lang="scss" scoped />

<template>
  <div class="settlement">
    <transition name="scale-x">
      <simple-loader v-if="isLoadingSettlements"></simple-loader>
    </transition>

    <form-dropdown
      :model-value = "modelValue.settlement"
      @update:modelValue = "updateModelValue"
      v-model:search-value = "search"
      :values = "settlements"
      :placeholder="$t('form.delivery.settlement')"
      :min-symbols="1"
      list-value="value"
      list-key="settlementRef"
      :error="error"
      required
    >
    </form-dropdown>

    <p v-if="settlementString" class="value-desc">{{ settlementString }}</p>
  </div>
</template>
