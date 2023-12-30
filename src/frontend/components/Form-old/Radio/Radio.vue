<script>
export default {
  props: {
    modelValue: {
      required: true
    },
    value: {
      type: String
    },
    name: {
      type: String
    }
  },

  methods: {
    changeHandler(v) {
      console.log(v.target.value)
      this.$emit('update:modelValue', v.target.value)
    }
  }
}
</script>

<style src="./radio.scss" lang="sass" scoped />

<template>
  <label clickable class="radio">
    <input :value="value" @change="changeHandler" :name="name" :checked="value === modelValue" type="radio" class="input-radio" >
    <span class="radio-custom">
      <IconCSS name="fluent:checkmark-12-regular" size="16px" class="icon"></IconCSS>
    </span>

    <slot />
  </label>
</template>