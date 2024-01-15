<script setup>
const {t} = useI18n()
const props = defineProps({
  value: {
    required: true
  }
})
// COMPUTEDS
// METHODS

const copyHandler = () => {
  navigator.clipboard.writeText(props.value)
  useNoty().setNoty({content: t('noty.copied', {text: props.value})})
}
// HANDLERS
// WATCHERS
</script>

<style src='./code.scss' lang='scss' scoped></style>

<template>
  <div>
    <button @click="copyHandler" class="code">
      <span class="code-value">{{ value }}</span>
      <IconCSS name="iconoir:copy" class="code-icon"></IconCSS>
    </button>
  </div>
</template>