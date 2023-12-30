<script setup>
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
        <div class="text">{{ error.statusMessage }}</div>
        <NuxtLink :to="localePath('/')" class="button primary btn">
          <IconCSS name="fluent:arrow-hook-up-right-28-regular" size="20px" class="icon"></IconCSS>
          <span>Перейти на главную</span>
        </NuxtLink>
      </div>
    </div>
  </NuxtLayout>
</template>