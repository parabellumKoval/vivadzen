<script setup>
const {t} = useI18n()
const props = defineProps({
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
})


const copyHandler = () => {
  navigator.clipboard.writeText(props.modelValue)
  useNoty().setNoty({content: t('noty.copied', {text: props.modelValue})})
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
        <IconCSS name="iconoir:copy" class="icon"></IconCSS>
      </button>
    </template>
  </form-text>
</template>