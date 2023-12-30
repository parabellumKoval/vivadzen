<script>
export default {
  data() {
    return {
      onFocus: false
    }
  },

  props: {
    modelValue: {
      type: String
    },
    placeholder: {
      type: String
    },
    required: {
      type: Boolean,
      default: false
    },
    error: {
      type: [Object, Array, String, Boolean],
      default: false
    },
    isDisabled: {
      type: Boolean,
      default: false
    },
    readonly: {
      type: Boolean,
      default: false
    },
    type: {
      type: String,
      default: 'text'
    }
  },

  computed: {
    isActive() {
      return this.onFocus || this.modelValue?.length > 0
    },

    id() {
      return 'input-' + (Math.random() + 1).toString(36).substring(7);
    }
  },

  methods: {
    changeHandler(v) {
      this.$emit('update:modelValue', v.target.value)
    },

    focusHandler() {
      this.onFocus = true
      this.$emit('focused')
    },

    blurHandler() {
      this.onFocus = false
      this.$emit('blured')
    }
  },
}
</script>

<style src="./text.scss" lang="sass" scoped />

<template>
  <div
    :class="{
      error: error && error.length,
      disabled: isDisabled
    }"
    class="input__wrapper"
  >
    <input
      :value="modelValue"
      :id="id"
      @input="changeHandler"
      @focus="focusHandler"
      @blur="blurHandler"
      :placeholder="placeholder"
      :readonly="readonly"
      :type="type"
      class="main-input"
    >

    <slot name="icon-right"></slot>
    
    <form-error :error="error"></form-error>

  </div>
</template>