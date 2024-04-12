<script setup>
const {t} = useI18n()

const props = defineProps({
  error: {
    type: Object
  }
})

console.log('error', props.error)
</script>

<style src="~/assets/scss/error.scss" lang="scss" scoped />

<template>
  <NuxtLayout name="default">
    <div class="container">
      <div class="error">
        <div class="status">{{ error.statusCode }}</div>
        <div class="text">{{ error.statusMessage || error.message }}</div>
        <NuxtLink :to="localePath('/')" class="button primary action-btn">
          <IconCSS name="iconoir:home" size="20px" class="icon"></IconCSS>
          <span>{{ t('button.go_home') }}</span>
        </NuxtLink>
      </div>
    </div>
  </NuxtLayout>
</template>