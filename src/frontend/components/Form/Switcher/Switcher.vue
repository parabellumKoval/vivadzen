<script setup>

const props = defineProps({
  modelValue: {
    type: [Boolean, Array, Number, String],
    default: false
  },
  trueValue: {
    default: true
  },
  falseValue: {
    default: false
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
  }
})

const emit = defineEmits([
  'update:modelValue'
])

const v = ref(null)

//WATCHER
watch(v, (value) => {
  emit('update:modelValue', value)
}, {
  deep: true
})

// Set value
v.value = props.modelValue

// onMounted(() => {
// })
</script>

<style src="./switcher.scss" lang="scss" scoped />

<template>
  <div class="wrapper">
    <div class="slot-wrapper">
      <slot />
    </div>
    <label class="switch">
      <input
        v-model="v"
        :true-value="trueValue"
        :false-value="falseValue"
        type="checkbox"
      />
      <span class="slider"></span>
    </label>
  </div>
</template>