<script setup>
const {t} = useI18n()

const props = defineProps({
  error: {
    type: Object
  },
  message: {
    type: String,
    default: ''
  }
})

const messageText = computed(() => {
  return props.message || t('error.article_not_found')
})
</script>

<style src="./article.scss" lang="scss" scoped></style>
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <div class="error error-article">
    <div class="status">{{ error?.statusCode || 404 }}</div>
    <div class="text">{{ messageText }}</div>
  </div>
</template>
