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

const emit = defineEmits(['selected', 'update:modelValue', 'update:ref'])

const {t} = useI18n()

const {getStreets, streets, isLoadingStreets, settlement, street, setStreet} = useNp()
const search = ref('')


// COMPUTEDS
const streetString = computed(() => {
  return props.modelValue?.street || street.value?.street || null
})

const isDisabled = computed(() => {
  return !settlement.value.settlementRef
})

// METHODS
const updateStreet = (value) => {

  setStreet(value)

  let newValue = {
    ...props.modelValue
  }

  newValue.street = value.street
  newValue.streetRef = value.streetRef

  emit('update:modelValue', newValue)

}

// HANDLERS
const updateModelValueHandler = (ref) => {

  const searched = streets.value.find((item) => {
    return item.streetRef === ref
  })

  if(!searched) {
    console.warn('Street was not fond in the streets list')
    return
  }



  updateStreet(searched)
}

// WATCH
watch(() => search.value, (val) => {
  let settlementRef = settlement.value.settlementRef || null
  getStreets(val, settlementRef)
})

// clear street if settlement was updated
watch(() => settlement.value, (v) => {

  if(!v.settlementRef) {
    return
  }

  updateStreet({street: null, streetRef: null})
}, {
  deep: true,
  immediate: true
})

</script>
<style src="./street.scss" lang="scss" scoped />

<template>
  <div :class="{'disabled': isDisabled}" class="warehouse">
    <transition name="scale-x">
      <simple-loader v-if="isLoadingStreets"></simple-loader>
    </transition>

    <form-dropdown
      :model-value = "modelValue.street"
      @update:modelValue="updateModelValueHandler"
      v-model:search-value = "search"
      :values = "streets"
      :placeholder="$t('form.delivery.street')"
      :error="error"
      :is-disabled="isDisabled"
      list-value="street"
      list-key="streetRef"
      required
    >
    </form-dropdown>

    <p v-if="streetString" class="value-desc">{{ streetString }}</p>
  </div>
</template>
