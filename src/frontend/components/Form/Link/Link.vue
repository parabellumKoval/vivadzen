<script>
export default {
  data() {
    return {}
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
      type: [Object, Boolean],
      default: false
    },
    isDisabled: {
      type: Boolean,
      default: false
    }
  },

  methods: {
    copyHandler() {
      navigator.clipboard.writeText(this.modelValue)
      useNoty().setNoty(this.$t('noty.copied', {text: this.modelValue}))
    }
  }
}
</script>

<style src="./link.scss" lang="sass" scoped />

<template>
  <form-text
    :modelValue="modelValue"
    :placeholder="placeholder"
    :required="required"
    :error="error"
    :is-disabled="isDisabled"
    readonly
  >
    <template v-slot:icon-right>
      <button @click="copyHandler" clickable class="copy-referral-link">
        <img src="~assets/svg-icons/copy.svg" class="icon"/>
      </button>
    </template>
  </form-text>
</template>