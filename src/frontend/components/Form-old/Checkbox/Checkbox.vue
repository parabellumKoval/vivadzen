<script>
export default {
  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    value: {
      type: String
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
  },

  methods: {
    changeHandler(v) {
      this.$emit('update:modelValue', v.target.value)
    }
  }
}
</script>

<style src="./checkbox.scss" lang="sass" scoped />

<template>
  <label clickable class="input__wrapper input__wrapper-checkbox">
    <input
      :checked="modelValue === value"
      :value="value"
      :name="name"
      @change="changeHandler"
      type="checkbox"
      class="input-checkbox"
    />
    
    <span class="custome-checkbox">
      <IconCSS name="fluent:checkmark-12-filled" size="14px" class="icon"></IconCSS>
    </span>

    <slot />
  </label>
</template>