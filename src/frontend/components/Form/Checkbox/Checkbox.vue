<script setup>

const props = defineProps({
  modelValue: {
    type: [Boolean, Array, Number, String],
    default: false
  },
  value: {
    type: String,
    default: true
  },
  name: {
    type: String
  },
  required: {
    type: Boolean,
    default: false
  },
  error: {
    type: [Object, Boolean],
    default: false
  },
  isDisabled: {
    type: Boolean,
    default: false
  },
  isBoolean: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits([
  'update:modelValue'
])

const changeHandler = (v) => {
  if(props.isBoolean) {
    emit('update:modelValue', !props.modelValue)
  }else {
    emit('update:modelValue', v.target.value)
  }
}

const isChecked = computed(() => {
  if(props.isBoolean){
    return  props.modelValue
  }

  if(Array.isArray(props.modelValue)) {
    return props.modelValue.find((item) => {
      return item === props.value
    })
  }else {
    return props.modelValue === props.value
  }
})
</script>

<style src="./checkbox.scss" lang="sass" scoped />

<template>
  <label clickable class="input__wrapper input__wrapper-checkbox">
    <input
      :checked="isChecked"
      :value="value"
      :name="name"
      @change="changeHandler"
      type="checkbox"
      class="input-checkbox"
    />
    
    <span class="custome-checkbox">
      <IconCSS name="ph:check-bold" size="14px" class="icon"></IconCSS>
    </span>

    <slot />
  </label>
</template>