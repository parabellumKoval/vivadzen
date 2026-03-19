<script setup>
const {t} = useI18n()
const localePath = useLocalePath()

const props = defineProps({
  error: {
    type: Object
  }
})

// Check if this is an article 404 error (type passed via statusMessage prefix)
const isArticleError = computed(() => {
  return props.error?.statusCode === 404 && props.error?.statusMessage?.startsWith('[article]')
})

const errorMessage = computed(() => {
  if (isArticleError.value) {
    return props.error?.statusMessage?.replace('[article]', '').trim() || 'Article Not Found'
  }
  return props.error?.statusMessage || props.error?.message
})

</script>

<style src="~/assets/scss/error.scss" lang="scss" scoped />

<template>
  <NuxtLayout name="default">
    <div class="container">
      <!-- Article 404: Use dedicated component -->
      <error-article v-if="isArticleError" :error="error" :message="errorMessage" />
      
      <!-- Default error page -->
      <div v-else class="error">
        <div class="status">{{ error.statusCode }}</div>
        <div class="text">{{ errorMessage }}</div>
        <NuxtLink :to="localePath('/')" class="button primary action-btn">
          <IconCSS name="mynaui:home-smile" size="20px" class="icon"></IconCSS>
          <span>{{ t('button.go_home') }}</span>
        </NuxtLink>
      </div>
    </div>
  </NuxtLayout>
</template>