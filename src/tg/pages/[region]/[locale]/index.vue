<script setup lang="ts">
definePageMeta({
  key: (route) => `${route.params.region}-${route.params.locale}-home`
})
</script>

<template>
  <TgHomePage />
</template>
